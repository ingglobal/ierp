<?php
$sub_menu = '960220';
include_once('./_common.php');

$sql_common = " FROM {$g5['company_table']} AS com";

$where = array();
$where[] = " com_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
//$where[] = " com_class = 'normal' ";   // 디폴트 검색조건
//$where[] = " com_type NOT IN ('buyer') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
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
    $sst = "com_reg_dt";
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


$sql = " SELECT 
			com_idx,
			com_name,
			com_biz_no,
			com_addr1,
			com_addr2,
			com_addr3,
			com_tel,
			com_fax,
			( SELECT cra_percent FROM {$g5['company_rate_table']} WHERE com_idx = com.com_idx ORDER BY cra_start_date,cra_idx DESC LIMIT 1 ) AS cra_percent
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";
$result = sql_query($sql,1);

// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_table']} AS com {$sql_join} WHERE com_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '견적처목록';
//검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
include_once(G5_PATH.'/head.sub.php');
?>
<style>
html,body{overflow:hidden;overflow-y:auto;}
#com_sch_list{padding:20px;}
.td_com_name{text-align:left !important;border-left:0 !important;border-right:0 !important;}
.td_com_name .td_a{display:block;background:#efefef;font-size:1.1em;color:#777;padding:10px;}
.td_com_name ul{display:none;}
.td_com_name ul.focus{display:block;}
.td_com_name ul li{}
.td_com_name ul li .a_mng{display:block;background:#f1f1f1;padding:10px 10px 10px 30px;border-top:1px solid #ddd;overflow-x:hidden;}
</style>
<div id="com_sch_list">
	<div class="local_ov01 local_ov">
		<?php echo $listall ?>
		<span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
		<span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
	</div>
	<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
	<label for="sfl" class="sound_only">검색대상</label>
	<select name="sfl" id="sfl">
		<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
	</select>
	<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
	<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
	<input type="submit" class="btn_submit" value="검색">
	</form>
	<div class="tbl_head01 tbl_wrap">
		<table class="table table-bordered table-condensed">
		<caption><?php echo $g5['title']; ?> 목록</caption>
		<tbody>
		<?php
		for ($i=0; $row=sql_fetch_array($result); $i++){
			//print_r2($row);
			$choice = ($row['cra_percent']) ? '<a href="javascript:" class="a_mag" v="'.$row['cra_percent'].'">선택</a>' : '';
			$sql = " SELECT cm.mb_id, mb.mb_name, mb.mb_email FROM {$g5['company_member_table']} AS cm
						LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cm.mb_id
					WHERE cm.com_idx = '{$row['com_idx']}' AND cm.cmm_status = 'ok'
			";
			$mresult = sql_query($sql,1);
		?>
			<tr>
				<td class="td_com_name"><!-- 업체명 -->
					<a href="javascript:" class="td_a a_com"><?php echo get_text($row['com_name']); ?></a>
					<?php if($mresult->num_rows){ ?>
					<ul>
						<?php for ($j=0; $mrow=sql_fetch_array($mresult); $j++) { ?>
						<li><a href="javascript:" class="a_mng" com_idx="<?=$row['com_idx']?>" com_name="<?=$row['com_name']?>" com_biz_no="<?=$row['com_biz_no']?>" com_addr="<?=($row['com_addr1'].' '.$row['com_addr2'].' '.$row['com_addr3'])?>" com_tel="<?=$row['com_tel']?>" com_fax="<?=$row['com_fax']?>" mb_id="<?=$mrow['mb_id']?>" mb_name="<?=$mrow['mb_name']?>" mb_email="<?=$mrow['mb_email']?>"><?=$mrow['mb_name']?>&nbsp;&nbsp;(<?=(($mrow['mb_email']) ? $mrow['mb_email'] : 'No-Email')?>)</a></li>
						<?php } ?>
					</ul>
					<?php } ?>
				</td>
			</tr>
		<?php
		}
		if ($i == 0)
			echo "<tr><td class='td_empty' colspan='3'>자료가 없습니다.</td></tr>";
		?>
		</tbody>
		</table>
	</div>
	<?php
	//echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page=');
	echo get_paging($config['cf_mobile_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page=');
	
	?>
</div><!--#com_sch_list-->
<script>
$('body').attr({'onresize':'parent.resizeTo(400,600)','onload':'parent.resizeTo(400,600)'});
$('.a_com').on('click',function(){
	if($(this).next('ul').length > 0 && $(this).next('ul').is(':visible')){
		$(this).next('ul').css('display','none');
		return false;
	}
	$('.td_com_name ul').css('display','none');
	if($(this).next('ul').length > 0) $(this).next('ul').css('display','block');
});
$('.a_mng').on('click',function(){
	//#com_idx/#com_name/#mb_id/#mb_name
	/*
	com_biz_no,
	com_addr1,
	com_addr2,
	com_addr3,
	com_tel,
	com_fax,
	*/
	opener.document.getElementById('com_idx').value = $(this).attr('com_idx');
	opener.document.getElementById('com_name').value = $(this).attr('com_name');
	opener.document.getElementById('com_biz_no').value = $(this).attr('com_biz_no');
	opener.document.getElementById('com_addr').value = $(this).attr('com_addr');
	opener.document.getElementById('com_tel').value = $(this).attr('com_tel');
	opener.document.getElementById('com_fax').value = $(this).attr('com_fax');
	opener.document.getElementById('mng_id').value = $(this).attr('mb_id');
	opener.document.getElementById('mng_name').value = $(this).attr('mb_name');
	opener.document.getElementById('mng_email').value = $(this).attr('mb_email');
	window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>