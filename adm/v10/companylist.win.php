<?php
$sub_menu = '960220';
include_once('./_common.php');

$sql_common = " FROM {$g5['company_table']} AS com";

$where = array();
$where[] = " com_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " com_class = 'normal' ";   // 디폴트 검색조건
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
html,body{overflow:hidden;}
#com_sch_list{padding:20px;}
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
		<thead>
			<th scope="col">회사명</th>
			<th scope="col">마진율</th>
			<th scope="col">관리</th>
		</thead>
		<tbody>
		<?php
		for ($i=0; $row=sql_fetch_array($result); $i++){
			//print_r2($row);
			$choice = ($row['cra_percent']) ? '<a href="javascript:" class="a_mag" v="'.$row['cra_percent'].'">선택</a>' : '';
		?>
			<tr>
			<td class="td_com_name"><!-- 업체명 -->
				<b><?php echo get_text($row['com_name']); ?></b>
			</td>
			<td class="td_com_mgn"><!-- 마진 -->
				<b><?php echo get_text($row['cra_percent']); ?><?php if($row['cra_percent']){ ?>%<?php } ?></b>
			</td>
			<td class="td_mng"><!-- 관리 -->
				<?=$choice?>
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
$('body').attr({'onresize':'parent.resizeTo(400,500)','onload':'parent.resizeTo(400,500)'});
$('.a_mag').on('click',function(){
	opener.document.getElementById('mag_act_txt').value = $(this).attr('v');
	window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>