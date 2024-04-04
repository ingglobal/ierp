<?php
include_once('./_common.php');

$err_msg = '';
if(!$prj_idx){
	$err_msg = '프로젝트정보가 제대로 넘어오지 못했습니다.';
}
if(!$com_idx){
	$err_msg = '업체정보가 제대로 넘어오지 못했습니다.';
}
if(!$ppt_idxs){
	$err_msg = '개별발주정보가 제대로 넘어오지 못했습니다.';
}
if(!$ppt_prices){
	$ppt_prices = 0;
}
$ppt_prices = (int) $ppt_prices;

$sql_common = " FROM {$g5['project_purchase_table']} AS ppc
                LEFT JOIN {$g5['project_table']} AS prj ON ppc.prj_idx = prj.prj_idx
				LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = ppc.com_idx
";

$where = array();
$where[] = " ppc_status != 'trash' ";
$where[] = " ppc.com_idx = '{$com_idx}' ";
$where[] = " ppc.prj_idx = '{$prj_idx}' ";

if ($stx) {
    switch ($sfl) {
		case ($sfl == 'ppc.prj_idx' || $sfl == 'ppc_idx' || $sfl == 'ppc.com_idx') :
			$where[] = " {$sfl} = '{$stx}' ";
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
    $sst = "ppc_idx";
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
			ppc_idx,
            ppc_subject,
			ppc_date,
			ppc.prj_idx,
			prj_name,
			prj_reg_dt,
            ppc.com_idx,
			com_name
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";
// echo $sql;
$result = sql_query($sql,1);


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '그룹발주목록';
//검색어 확장
// $qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
include_once(G5_PATH.'/head.sub.php');
?>
<style>
html,body{overflow:hidden;}
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
.td_ppc_idx{}
.td_ppc_subject{}
.td_prj_name{text-align:left !important;}
.td_com_name{text-align:left !important;}
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
		</div>
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
		<label for="sfl" class="sound_only">검색대상</label>
		<select name="sfl" id="sfl" style="">
			<option value="ppc_idx"<?php echo get_selected($_GET['sfl'], "ppc_idx"); ?>>그룹발주번호</option>
			<option value="ppc_subject"<?php echo get_selected($_GET['sfl'], "ppc_subject"); ?>>그룹발주명</option>
			<option value="ppc.com_idx"<?php echo get_selected($_GET['sfl'], "ppc.com_idx"); ?>>업체번호</option>
			<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
			<option value="ppc.prj_idx"<?php echo get_selected($_GET['sfl'], "ppc.prj_idx"); ?>>프로젝트번호</option>
			<option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
		</select>
		<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
		<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:130px;">
		<input type="submit" class="btn_submit" value="검색">
		</form>
		<div class="tbl_head01 tbl_wrap">
			<table class="table table-bordered table-condensed">
			<caption><?php echo $g5['title']; ?> 목록</caption>
			<thead>
				<th scope="col">그룹발주번호</th>
				<th scope="col">그룹발주명</th>
				<th scope="col">프로젝트명</th>
				<th scope="col">공급업체</th>
				<th scope="col">그룹발주일</th>
				<th scope="col">관리</th>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++){
				//print_r2($row);continue;
				$choice = '<a href="javascript:" class="a_mag btn btn_02" ppc_idx="'.$row['ppc_idx'].'">연결</a>';
			?>
				<tr>
					<td class="td_ppc_idx"><?=$row['ppc_idx']?></td>
					<td class="td_ppc_subject">
						<?=get_text($row['ppc_subject'])?><br>
					</td>
					<td class="td_prj_name"><!-- 업체명 -->
						(<?=$row['prj_idx']?>) <b><?php echo get_text($row['prj_name']); ?></b><br>
						<?php echo substr($row['prj_reg_dt'],0,10); ?>
					</td>
					<td class="td_com_name" style="text-align:center;"><!-- 마진 -->
						(<?=$row['com_idx']?>) <b><?php echo get_text($row['com_name']); ?></b>
					</td>
					<td class="td_ppc_date" style="text-algin:center;"><?=substr($row['ppc_date'],2,8)?></td>
					<td class="td_mng" style="text-align:center;width:60px;"><!-- 관리 -->
						<?=$choice?>
					</td>
				</tr>
			<?php
			}
			if ($i == 0)
				echo "<tr><td class='td_empty' colspan='6'>자료가 없습니다.</td></tr>";
			?>
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
$('body').attr({'onresize':'parent.resizeTo(700,800)','onload':'parent.resizeTo(700,800)'});
let err_msg = '<?=$err_msg?>';
if(err_msg != ''){
	alert(err_msg);
	window.close();
}

$('.a_mag').on('click',function(){
	let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/ppt_into_ppc.php';
	let ppc_idx = $(this).attr('ppc_idx');
	let ppt_idxs = '<?=$ppt_idxs?>';
	let ppt_prices = <?=$ppt_prices?>;
	
	$.ajax({
		type: 'POST',
		dataType: 'text',
		url: ajxurl,
		data: {'ppc_idx': ppc_idx, 'ppt_idxs': ppt_idxs, 'ppt_prices': ppt_prices},
		success: function(res){
			if(res == 'ok'){
				window.opener.location.reload();
				window.close();
			}
		},
		error: function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
});


// opener.document.getElementById('prj_idx').value = $(this).attr('prj_idx');
// opener.document.getElementById('prj_name').value = $(this).attr('prj_name');
// window.close();
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>