<?php
$sub_menu = '960210';
include_once('./_common.php');

$sql_join = " LEFT JOIN {$g5['company_table']} As com ON com.com_idx = cmm.com_idx
			  LEFT JOIN {$g5['member_table']} As mb ON mb.mb_id = cmm.mb_id ";

$sql_common = " FROM {$g5['company_member_table']} AS cmm {$sql_join} ";

$where = array();
$where[] = " mb.mb_level = 4 ";   // 디폴트 검색조건
$where[] = " cmm.cmm_status = 'ok' ";   // 디폴트 검색조건
//$where[] = " com_class = 'normal' ";   // 디폴트 검색조건
//$where[] = " com_type NOT IN ('buyer') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'mb.mb_name' :
            $where[] = " ( mb.mb_name LIKE '%{$stx}%' ) ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "cmm.cmm_reg_dt";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common.$sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 6;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " SELECT *
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";
// echo $sql;
$result = sql_query($sql,1);
$rcnt = $result->num_rows;
// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_member_table']} AS cmm {$sql_join} WHERE cmm_status = 'ok' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '제출담당자목록';
//검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
include_once(G5_PATH.'/head.sub.php');
//$g5['set_mb_ranks_value'][$key]
?>
<style>
html,body{overflow:hidden;}
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_submit btn_close" onclick="window.close()">닫기</a>
	<?php } ?>
	<h1><?php echo $g5['title']; ?></h1>
	<div id="com_sch_list" class="new_win">
		<div class="local_ov01 local_ov">
			<?php echo $listall ?>
			<span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
			<span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
		</div>
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
		<input type="hidden" name="com_idx" value="<?=$com_idx?>">
		<label for="sfl" class="sound_only">검색대상</label>
		<select name="sfl" id="sfl">
			<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
			<option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>회원명</option>
			<option value="mb.mb_id"<?php echo get_selected($_GET['sfl'], "mb.mb_id"); ?>>회원ID</option>
		</select>
		<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
		<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:130px;">
		<input type="submit" class="btn_submit" value="검색">
		</form>
		<div class="tbl_head01 tbl_wrap">
			<table class="table table-bordered table-condensed">
			<caption><?php echo $g5['title']; ?> 목록</caption>
			<thead>
				<th scope="col">업체</th>
				<th scope="col">직책</th>
				<th scope="col">성명</th>
				<th scope="col">선택</th>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++){
				//print_r2($g5['set_mb_ranks_value']);
				$choice = '<a href="javascript:" class="a_mag btn btn_02" mb_id="'.$row['mb_id'].'" mb_name="'.$row['mb_name'].'">선택</a>';
			?>
				<tr>
				<td class="td_com_name"><!-- 업체 -->
					<b><?php echo get_text($row['com_name']); ?></b>
				</td>
				<td class="td_cmm_rank" style="text-align:center;"><!-- 직책 -->
					<b><?php echo $g5['set_mb_ranks_value'][$row['cmm_title']]; ?></b>
				</td>
				<td class="td_mb_name" style="text-align:center;"><!-- 마진 -->
					<b><?php echo get_text($row['mb_name']); ?></b>
				</td>
				<td class="td_mng" style="text-align:center;"><!-- 관리 -->
					<?=$choice?>
				</td>
				</tr>
			<?php
			}
			if ($rcnt == 0){
			?>
				<tr>
					<td class='td_empty' colspan='4'>
						자료가 없습니다<br>
						<a href="javascript:" id="btn_submitter" target="_blank" class="ov_listall" style="margin-top:5px;">담당자등록</a>
					</td>
				</tr>
				<script>
					$('#btn_submitter').click(function(){
						var href = "<?=G5_USER_ADMIN_URL?>/company_member_form.php?com_idx=<?=$com_idx?>&ex_page=<?=$g5['file_name']?>";
						winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=700,scrollbars=1");
						winCompanyMember.focus();
						return false;
					});
				</script>
			<?php } ?>
			</tbody>
			</table>
		</div>
		<?php
		//echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page=');
		echo get_paging($config['cf_mobile_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page=');
		
		?>
	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(400,640)','onload':'parent.resizeTo(400,640)'});
$('.a_mag').on('click',function(){
	//alert($(this).attr('mb_id'));
	opener.document.getElementById('mb_id_company').value = $(this).attr('mb_id');
	opener.document.getElementById('mb_name_sb').value = $(this).attr('mb_name');
	window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>