<?php
$sub_menu = "960245";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$mcn = array();
$elt = array();
$etc = array();
$ppc = array();//complete
$ppcs = array();//complete
$prx = array();
$exp_sql = " SELECT
				(
					SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = '{$prj_idx}' AND prx_type = 'etc' AND prx_done_date != '0000-00-00'
				) AS total
				,(
					SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = '{$prj_idx}' AND prx_type = 'machine' AND prx_done_date != '0000-00-00'
				) AS mcn_total
				,(
					SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = '{$prj_idx}' AND prx_type = 'electricity' AND prx_done_date != '0000-00-00'
				) AS elt_total
				,(
					SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = '{$prj_idx}' AND prx_type = 'etc' AND prx_done_date != '0000-00-00'
				) AS etc_total
				, (
					SELECT SUM(ppc_price) FROM {$g5['project_purchase_table']} WHERE prj_idx = '{$prj_idx}' AND ppc_status IN ('ok','complete')
				) AS pur_total_price
            	, (
					SELECT SUM(ppc_price) FROM {$g5['project_purchase_table']} WHERE prj_idx = '{$prj_idx}' AND ppc_status = 'ok'
				) AS pur_ok_price
				, (
					SELECT SUM(ppc_price) FROM {$g5['project_purchase_table']} WHERE prj_idx = '{$prj_idx}' AND ppc_status = 'complete'
				) AS pur_complete_price
			FROM {$g5['project_exprice_table']}
			WHERE prj_idx = '{$prj_idx}' AND prx_status = 'ok'

";
// print_r3($exp_sql);
$exp = sql_fetch($exp_sql); //$exp['total'],$exp['mcn_total'],$exp['elt_total'],$exp['etc_total']

$ppc_sql = " SELECT
				(
					SELECT SUM(ppc_price) FROM {$g5['project_purchase_table']} WHERE prj_idx = '{$prj_idx}' AND ppc_status IN ('ok','complete')
				) AS pur_total_price
            	, (
					SELECT SUM(ppc_price) FROM {$g5['project_purchase_table']} WHERE prj_idx = '{$prj_idx}' AND ppc_status = 'ok'
				) AS pur_ok_price
				, (
					SELECT SUM(ppc_price) FROM {$g5['project_purchase_table']} WHERE prj_idx = '{$prj_idx}' AND ppc_status = 'complete'
				) AS pur_complete_price
			FROM {$g5['project_purchase_table']}
			WHERE prj_idx = '{$prj_idx}' AND ppc_status IN ('ok','complete')

";
// print_r3($ppc_sql);
$puc = sql_fetch($ppc_sql); //$puc['total'],$puc['mcn_total'],$puc['elt_total'],$puc['etc_total']




$inp_sql = " SELECT SUM(prn_price) AS prn_tot_price
				FROM {$g5['project_inprice_table']}
			WHERE prj_idx = '{$prj_idx}' 
				AND prn_type = 'etc'
				AND prn_done_date != '0000-00-00'
				AND prn_status = 'ok'
";
$inp = sql_fetch($inp_sql);
// 변수 설정, 필드 구조 및 prefix 추출
$sql = " SELECT prx.*, com.com_name FROM {$g5['project_exprice_table']} AS prx
			LEFT JOIN {$g5['company_table']} AS com ON prx.com_idx = com.com_idx
			WHERE prj_idx = '{$prj_idx}' AND prx_status = 'ok'
";
$res = sql_query($sql,1);

for($i=0;$row=sql_fetch_array($res);$i++){
	$row['prx_price'] = number_format($row['prx_price']);
	array_push($prx,$row['prx_idx']);
	if($row['prx_type'] == 'machine')
		$mcn[$row['prx_idx']] = $row;
	else if($row['prx_type'] == 'electricity')
		$elt[$row['prx_idx']] = $row;
	else
		$etc[$row['prx_idx']] = $row;
}

$csql = " SELECT ppc.*, com.com_name FROM {$g5['project_purchase_table']} ppc
			LEFT JOIN {$g5['company_table']} com ON ppc.com_idx = com.com_idx
			WHERE prj_idx = '{$prj_idx}' AND ppc_status IN ('complete','ok')
";
$cres = sql_query($csql, 1);

for($i=0;$row=sql_fetch_array($cres);$i++){
	$row['ppc2_price'] = number_format($row['ppc_price']);
	array_push($ppcs,$row['ppc_idx']);
	$ppc[$row['ppc_idx']] = $row;
}

$sqlf = "SELECT * FROM {$g5['file_table']}
			WHERE fle_db_table = 'project_exprice'
				AND fle_type IN ('machine','electricity','etc')
				AND fle_db_id IN (".((count($prx))?implode(',',$prx):0).")
			ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sqlf,1);
// print_r3($sqlf);
//파일배열
$machine_fles = array();
$machine_idxs = array();
$electricity_fles = array();
$electricity_idxs = array();
$etc_fles = array();
$etc_idxs = array();
for($i=0;$row2=sql_fetch_array($rs);$i++){
	// print_r2($row2);
	$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="fle_del['.$row2['fle_idx'].']" class="fle_del" no="'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
	if(!is_array(${$row2['fle_type'].'_fles'}[$row2['fle_db_id']])) ${$row2['fle_type'].'_fles'}[$row2['fle_db_id']] = array();
	@array_push(${$row2['fle_type'].'_fles'}[$row2['fle_db_id']],array('file'=>$file_down_del));
	if(!is_array(${$row2['fle_type'].'_fidxs'}[$row2['fle_db_id']])) ${$row2['fle_type'].'_fidxs'}[$row2['fle_db_id']] = array();
	@array_push(${$row2['fle_type'].'_fidxs'}[$row2['fle_db_id']],$row2['fle_idx']);
}

$sqlf2 = "SELECT * FROM {$g5['file_table']}
			WHERE fle_db_table = 'ppc'
				AND fle_type = 'ppc'
				AND fle_db_id IN (".((count($ppcs))?implode(',',$ppcs):0).")
			ORDER BY fle_reg_dt DESC ";
$rs2 = sql_query($sqlf2,1);
$ppc_fles = array();
$ppc_idxs = array();
for($i=0;$row3=sql_fetch_array($rs2);$i++){
	// print_r2($row3);
	$file_down_del2 = (is_file(G5_PATH.$row3['fle_path'].'/'.$row3['fle_name'])) ? $row3['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row3['fle_path'].'/'.$row3['fle_name']).'&file_name_orig='.$row3['fle_name_orig'].'" file_path="'.$row3['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row3['fle_reg_dt']:''.PHP_EOL;
	if(!is_array(${$row3['fle_type'].'_fles'}[$row3['fle_db_id']])) ${$row3['fle_type'].'_fles'}[$row3['fle_db_id']] = array();
	@array_push(${$row3['fle_type'].'_fles'}[$row3['fle_db_id']],array('file'=>$file_down_del2));
	if(!is_array(${$row3['fle_type'].'_fidxs'}[$row3['fle_db_id']])) ${$row3['fle_type'].'_fidxs'}[$row3['fle_db_id']] = array();
	@array_push(${$row3['fle_type'].'_fidxs'}[$row3['fle_db_id']],$row3['fle_idx']);
}

/*
print_r3($machine_fles);
print_r3($exp);
print_r3($mcn);
print_r3($elt);
print_r3($etc);
*/
if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	//$row = ${$pre};
    if (!$prj_idx)
		alert('존재하지 않는 자료입니다.');

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');




//해당 프로젝트 정보 추출
$pj_field = sql_fetch('SELECT prj_name,prj_reg_dt,prj_type,prj_mng_rate FROM '.$g5['project_table'].' WHERE prj_idx = "'.$prj_idx.'" ');
$prj_name = $pj_field['prj_name'];
// 수주금액 추출
$prs1 = sql_fetch('SELECT prp_price FROM '.$g5['project_price_table'].' WHERE prj_idx = "'.$prj_idx.'" AND prp_type = "order" ');


$prj_mng_rate = $pj_field['prj_mng_rate'];
$prj_mng_price = round(($pj_field['prj_mng_rate']*$prs1['prp_price'])/100);

//수금완료 합계를 구한다
$ssql = " SELECT SUM(prp_price) AS sum_price
FROM {$g5['project_price_table']}
WHERE prj_idx = '".$prj_idx."'
	AND prp_type NOT IN ('submit','nego','order','')
	AND prp_pay_date != '0000-00-00'
	AND prp_status = 'ok'
";
//미수금관련
$sugeum = sql_fetch($ssql);
$mis_price = $prs1['prp_price'] - $sugeum['sum_price'];
$mis_per = ($prs1['prp_price'])?round($mis_price / $prs1['prp_price'] * 100,2):0;

//수금완료금관련
$sugm_price = $sugeum['sum_price'];
$sugm_per = ($prs1['prp_price'])?round($sugm_price / $prs1['prp_price'] * 100,2):0;

//지출상태
$exp['total'] = $exp['total'] + $puc['pur_total_price']; //추가(240415)
$stat_price = $sugm_price - $exp['total'];

//계약금에 대한 총지출금액 비율
$exp_per = ($prs1['prp_price'])?round($exp['total'] / ($prs1['prp_price']) * 100,2):0;

$dif_price = $prs1['prp_price'] - $exp['total'];
$dif_per = ($prs1['prp_price'])?round($dif_price / ($prs1['prp_price']) * 100,2):0;
$ppc_per = ($exp['total'])?round(($puc['pur_total_price']/$exp['total'])*100,2):0;
$etc_per = ($exp['total'])?round($exp['etc_total']/$exp['total']*100,2):0;


$html_title = '';//($w=='')?'추가':'수정';
$g5['title'] = $prj_idx.'-['.$prj_name.'] 지출관리 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
/*
$g5['setting']['set_exprice_type']	machine=기계지출,electricity=전기지출,etc=기타지출
$g5['setting']['set_exprice_status'] pending=대기,ok=정상
$super_admin
*/
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>

<style>
.tbl_frm01 td .btn{height:35px;line-height:35px;}
.th_exprice{position:relative;}
.th_exprice i{position:absolute;bottom:15px;right:10px;font-size:1.2em;cursor:pointer;}
.lst_exp{margin-top:5px;}
.lst_exp strong{display:inline-block;width:40px;}
.prx_price{text-align:right;width:90px;}
.prx_name{width:130px;}
.prx_content{width:150px;}
.com_name{background:#ddd;cursor:pointer;width:100px;}
.exp_box input[type="text"]{padding:0 5px;}
.prx_price{width:110px;}
.prx_plan_date{width:90px;}
.prx_done_date{width:90px;}

.lst_ppc .lst_up{background:#f1f1f1;padding:10px;}
.lst_ppc .lst_up::after{display:block;visibility:hidden;clear:both;content:'';}
.lst_ppc .lst_up span{float:left;margin-right:10px;border:1px solid #ddd;height:35px;line-height:35px;padding:0 5px;}
.lst_ppc .lst_up span.ppc_idx{width:60px;}
.lst_ppc .lst_up span.com_name2{width:150px;background:#ccc;}
.lst_ppc .lst_up span.ppc_subject{width:300px;}
.lst_ppc .lst_up span.ppc2_price{width:140px;text-align:right;}
.lst_ppc .lst_up span.ppc2_price::after{content:' 원'}
.lst_ppc .lst_up span.ppc_date{}
.lst_ppc .lst_up span.ppc_content{display:none;}
.lst_ppc .lst_up span.ppc_file{border:0px;}
.lst_ppc .lst_up span.ppc_file button{position:relative;top:-3px;}
.lst_ppc .lst_up span.file_cnt{}

.lst_fle i{font-size:1.3em;margin-left:5px;}
.lst_down{padding:0px 20px 20px;display:none;background:#f1f1f1;position:relative;}
.lst_down.focus{padding-bottom:20px;display:block;}
/*멀티파일관련*/
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#ddd;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
.btn_file{position:absolute;top:53px;left:275px;background:#463cc5;color:#fff;margin-bottom:5px;border:1px solid #ddd;padding:3px 5px;border-radius:3px;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{font-size:14px;border:1px solid #ccc;background:#eee;padding:2px 5px;border-radius:3px;line-height:1.2em;}

.sm_tbl{width:500px;}
.sm_tbl th{width:40%;background:none;font-weight:400;}
.sm_tbl th,.sm_tbl td{padding:5px;border-top:1px dotted #ddd;border-bottom:1px dotted #ddd;}
.sm_tbl td{text-align:right;position:relative;width:60%;}
.sm_tbl .th_ord{font-weight:600;}
.sm_tbl .td_ord{font-weight:600;color:orange;}
.sm_tbl .th_inp{font-weight:600;}
.sm_tbl .td_inp{font-weight:600;color:green;}
.sm_tbl .th_top{font-weight:600;}
.sm_tbl .td_top{font-weight:600;color:brown;font-size:1.2em;}
.sm_tbl .th_tot{color:darkred;}
.sm_tbl .td_tot{color:red;}
.sm_tbl .th_sug{color:darkblue;}
.sm_tbl .td_sug{color:blue;}
.sm_tbl .th_mis{color:red;}
.sm_tbl .td_mis{color:red;}
.sm_tbl .th_sta{color:darkgreen;}
.sm_tbl .td_sta{color:green;}
.sm_tbl .th_dif{color:darkblue;}
.sm_tbl .td_dif{color:blue;}
.sm_tbl .th_mcn{}
.sm_tbl .td_mcn{}
.sm_tbl .th_elt{}
.sm_tbl .td_elt{}
.sm_tbl .th_cmp{}
.sm_tbl .td_cmp{}
.sm_tbl .th_etc{}
.sm_tbl .td_etc{}

#td_info{position:relative;}
#td_info #mng_box{position:fixed;top:130px;right:30px;border:2px dotted #ddd;padding:10px;background:#efefef;}
#td_info #mng_box #mng_rate{text-align:right;padding:0 5px;}
#td_info #mng_box #mng_rate_price{margin-left:20px;}

.grp_box{display:block;position:absolute;bottom:4px;left:0px;width:100%;height:5px;overflow:hidden;background:#ccc;}
.grp_box .grp_in{display:block;position:absolute;top:0px;left:0px;height:5px;background:orange;}
.grp_box .grp_in_mi{background:red;}
.grp_box .grp_in_su{background:orange;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="grp" value="<?php echo $grp ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="g" value="<?php echo $g ?>">
<input type="hidden" name="prj_idx" value="<?php echo $prj_idx ?>">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<!--input type="hidden" name="<?=$pre?>_pay_no" value="<?php //echo ${$pre}['prp_pay_no'] ?>"-->

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적추가 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
	</colgroup>
	<tbody>
    <tr>
		<th scope="row"><label for="prj_idx">지출정보</label></th>
		<td colspan="3" id="td_info">
			<table class="sm_tbl">
				<tbody>
					<?php if($super_admin){ ?>
					<tr>
						<th class="th_ord">수주금액</th>
						<td class="td_ord"><?=number_format($prs1['prp_price'])?>원</td>
					</tr>
					<tr style="display:no ne;">
						<th class="th_dif">매출이익(<?=$dif_per?>%)<br>(수주금액 - 총지출 기준%)</th>
						<td class="td_dif" style="font-weight:bold;">
							<div class="grp_box"><div class="grp_in" style="width:<?=$dif_per?>%"></div></div>
							<?=number_format($dif_price)?>원
						</td>
					</tr>
					<tr>
						<th class="th_sug">수금완료(<?=$sugm_per?>%)<br>(수주금액기준%)</th>
						<td class="td_sug">
							<div class="grp_box"><div class="grp_in grp_in_su" style="width:<?=$sugm_per?>%"></div></div>
							<?=number_format($sugm_price)?>원
						</td>
					</tr>
					<tr>
						<th class="th_mis">미수금(<?=$mis_per?>%)<br>(수주금액기준%)</th>
						<td class="td_mis">
							<div class="grp_box"><div class="grp_in grp_in_mi" style="width:<?=$mis_per?>%"></div></div>
							<?=number_format($mis_price)?>원
						</td>
					</tr>
					<tr style="display:none;">
						<th class="th_sta">지출상태<br>(수금합계 - 지출합계)</th>
						<td class="td_sta"><?=number_format($sugm_price)?> - <?=number_format($exp['total'])?> = <b style="font-size:1.05em;color:<?=(($stat_price<0)?'red':'')?>;"><?=number_format($stat_price)?></b>원</td>
					</tr>
					<?php } ?>
					<tr style="display:no ne;">
						<th class="th_tot">총지출금액<?php if($super_admin){ ?>(<?=$exp_per?>%)<br>(수주금액 + 지출총액 기준%)<?php } ?></th>
						<td class="td_tot" style="color:#000;">
							<?php if($super_admin){ ?><div class="grp_box"><div class="grp_in" style="width:<?=$exp_per?>%"></div></div><?php } ?>
							<?=number_format($exp['total'])?>원
						</td>
					</tr>
					<?php if(true){ //if($puc['pur_complete_price']){ ?>
					<tr>
						<th class="th_cmp">매입지출(<?=$ppc_per?>%)<br><span>(총지출기준%)</span></th>
						<td class="td_cmp">
							<div class="grp_box"><div class="grp_in" style="width:<?=$ppc_per?>%"></div></div>
							<?=number_format($puc['pur_total_price'])?>원
						</td>
					</tr>
					<?php } ?>
					<?php if(true){ //if($exp['etc_total']){ ?>
					<tr>
						<th class="th_etc">추가 총기타지출(<?=$etc_per?>%)<br><span>(총지출기준%)</span></th>
						<td class="td_etc">
							<div class="grp_box"><div class="grp_in" style="width:<?=$etc_per?>%"></div></div>
							<?=number_format($exp['etc_total'])?>원
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<span style="color:#818181;">(등록일 : <?=substr($pj_field['prj_reg_dt'],0,10)?>)</span>
			<?php if($super_admin){ ?>
			<div id="mng_box">
				<strong>관리비율: </strong>
				<input type="text" id="mng_rate" value="<?=$prj_mng_rate?>" class="frm_input" style="width:35px;" onclick="javascript:only_number(this)"> &nbsp;%
				<button type="button" id="rate_btn" class="btn btn_03">적용</button>
				<span id="mng_rate_price"><?=number_format($prj_mng_price)?></span> 원
			</div>
			<?php } ?>
		</td>
    </tr>
	<tr>
		<th scope="row" class="th_exprice" id="th_purchase"><label for="purchase_exprice">매입지출</label></th>
		<td colspan="3">
			<?php echo help("매입관련 지출정보를 표시합니다."); ?>
			<div class="exp_box" id="exp_electricity">
				<?php
				if(count($ppc)){
				$i=0;
				foreach($ppc as $k=>$v){
					/*
					[ppc_idx] => 5
					[com_idx] => 307
					[prj_idx] => 736
					[mb_id] => super
					[ppc_date] => 2024-04-15
					[ppc_subject] => 등록
					[ppc_content] => 
					[ppc_price] => 30
					[ppc_status] => complete
					[ppc_reg_dt] => 2024-04-15 08:19:29
					[ppc_update_dt] => 2024-04-15 17:26:18
					[com_name] => DH
					[ppc2_price] => 30
					*/
					$i++;
				?>
				<div class="lst_exp lst_ppc lst_ppc_<?=$i?>">
					<div class="lst_up">
						<span class="ppc_idx" ppc_idx="<?=$v['ppc_idx']?>">[<?=$i?>]</strong></span>
						<span class="com_name2"><?=cut_str($v['com_name'],12,'...')?></span>
						<span class="ppc_subject"><?=cut_str($v['ppc_subject'],46,'...')?></span>
						<span class="ppc2_price"><?=$v['ppc2_price']?></span>
						<span class="ppc_date"><?=$v['ppc_date']?></span>
						<span class="ppc_content"><?=$v['ppc_content']?></span>
						<?php if(@count($ppc_fles[$k])){ ?>
						<span class="ppc_file"><button type="button" class="btn btn_03 lst_fle" ppc_idx="<?=$k?>" typ="ppc">파일<i class="fa fa-angle-down" aria-hidden="true"></i></button></span>
						<span class="file_cnt">(<?=@count($ppc_fles[$k])?>)</span>
						<?php } ?>
					</div>
					<div class="lst_down">
						<?php
						if(@count($ppc_fles[$k])){
							echo '<ul>'.PHP_EOL;
							for($j=0;$j<count($ppc_fles[$k]);$j++) {
								echo "<li>[".($j+1).']'.$ppc_fles[$k][$j]['file']."</li>".PHP_EOL;
							}
							echo '</ul>'.PHP_EOL;
						}
						?>
					</div>
				</div>
				<?php
				}
				}
				?>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row" class="th_exprice" id="th_etc"><label for="electricity_exprice">기타지출</label><i id="i_etc" typ="etc" class="fa fa-plus-square-o i_exp" aria-hidden="true"></i></th>
		<td colspan="3">
			<?php echo help("기타 지출을 관리하는 영역입니다."); ?>
			<div class="exp_box" id="exp_etc">
				<?php
				if(count($etc)){
				$i=0;
				foreach($etc as $k=>$v){
					$i++;
				?>
				<div class="lst_exp lst_<?=$v['prx_type']?> lst_<?=$v['prx_type']?>_<?=$i?>">
					<div class="lst_up">
						<span><input type="hidden" name="prx_idx" value="<?=$k?>"><strong class="">[<?=$i?>]</strong></span>
						<span>
							<input type="hidden" name="com_idx" value="<?=$v['com_idx']?>">
							<input type="text" name="com_name" placeholder="업체명" value="<?=$v['com_name']?>" link="./_win_company_provider_select.php?file_name=${file_name}" readonly class="frm_input com_name">
						</span>
						<span><input type="text" name="prx_name" placeholder="지출제목" value="<?=$v['prx_name']?>" class="frm_input prx_name"></span>
						<span><input type="text" name="prx_price" placeholder="지출금액" value="<?=$v['prx_price']?>" class="frm_input prx_price" onclick="javascript:only_number_comma(this)"></span>
						<span><input type="text" name="prx_plan_date" placeholder="지출예정일" value="<?=$v['prx_plan_date']?>" readonly class="frm_input prx_plan_date"></span>
						<span><input type="text" name="prx_done_date" placeholder="지출완료일" value="<?=$v['prx_done_date']?>" readonly class="frm_input prx_done_date"></span>
						<span><input type="text" name="prx_content" placeholder="메모" value="<?=$v['prx_content']?>" class="frm_input prx_content"></span>
						<span><button type="button" class="btn btn_02 lst_mod" prx_idx="<?=$k?>" typ="<?=$v['prx_type']?>">수정</button></span>
						<span><button type="button" class="btn btn_00 lst_del" prx_idx="<?=$k?>" typ="<?=$v['prx_type']?>">삭제</button></span>
						<span><button type="button" class="btn btn_03 lst_fle" prx_idx="<?=$k?>" typ="<?=$v['prx_type']?>">파일<i class="fa fa-angle-down" aria-hidden="true"></i></button></span>
						<?php if(@count(${$v['prx_type'].'_fles'}[$k])){ ?>
						<span>(<?=@count(${$v['prx_type'].'_fles'}[$k])?>)</span>
						<?php } ?>
					</div>
					<div class="lst_down">
						<?php
						if(@count(${$v['prx_type'].'_fles'}[$k])){
							echo '<ul>'.PHP_EOL;
							for($j=0;$j<count(${$v['prx_type'].'_fles'}[$k]);$j++) {
								echo "<li>[".($j+1).']'.${$v['prx_type'].'_fles'}[$k][$j]['file']."</li>".PHP_EOL;
							}
							echo '</ul>'.PHP_EOL;
						}
						?>
					</div>
				</div>
				<?php
				}
				}
				?>
			</div>
		</td>
	</tr>
	</tbody>
	</table>
</div>


<div class="btn_fixed_top">
    <a href="./project_expense<?=$divid?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
</div>
</form>
<script>
var prj_idx = <?=$prj_idx?>;
var cur_obj; //현재 지출그룹객체
var multifile;//현재 멀티파일 객체
$(function(){ //###########################################################
//최초로딩시 모든 이벤트 활성화
events_reg();

//지출추가버튼 이벤트
$('.i_exp').on('click',function(){
	var typ = $(this).attr('typ');
	//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
	if($('.reg_prev').length || $('.lst_down.focus').length){
		alert('이전 작업을 먼저 진행 또는 취소 후에 실행해 주세요.');
		return false;
	}

	//우선 모든 이벤트 비활성화
	events_del();
	var cnt = $('#exp_'+typ).find('.lst_exp').length+1;
	var tag = `<div class="lst_exp lst_${typ} lst_${typ}_${cnt}">
		<div class="lst_up">
			<span><input type="hidden" name="prx_idx" value=""><strong class="reg_prev">[${cnt}]</strong></span>
			<span>
				<input type="hidden" name="com_idx" value="">
				<input type="text" name="com_name" placeholder="업체명" value="" link="./_win_company_provider_select.php?file_name=${file_name}" readonly class="frm_input com_name">
			</span>
			<span><input type="text" name="prx_name" placeholder="지출제목" value="" class="frm_input prx_name"></span>
			<span><input type="text" name="prx_price" placeholder="지출금액" value="" class="frm_input prx_price" onclick="javascript:only_number_comma(this)"></span>
			<span><input type="text" name="prx_plan_date" placeholder="지출예정일" value="" readonly class="frm_input prx_plan_date"></span>
			<span><input type="text" name="prx_done_date" placeholder="지출완료일" value="" readonly class="frm_input prx_done_date"></span>
			<span><input type="text" name="prx_content" placeholder="메모" value="" class="frm_input prx_content"></span>
			<span><button type="button" class="btn btn_01 lst_reg" typ="${typ}">등록</button></span>
			<span><button type="button" class="btn btn_00 lst_del" typ="${typ}">삭제</button></span>
		</div>
	</div>`;
	$(tag).appendTo('#exp_'+typ);
	//새로운 구성으로 다시 모든 이벤트 활성화
	events_reg();
});

}); //#####################################################################
var prj_price = <?=$prs1['prp_price']?>;
function events_reg(){
	//업체명 선택팝업
	$('.com_name').on('click',function(e){
		e.preventDefault();
		cur_obj = $(this).closest('.lst_exp');
		var href = $(this).attr('link');
		winProviderSelect = window.open(href, "winProviderSelect","left=600,top=150,width=550,height=600,scrollbars=1");
		winProviderSelect.focus();
	});
	//지출완료일
	$("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", closeText:'취소', showButtonPanel: true, yearRange: "c-99:c+99", onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val(''); } } });
	$("input[name$=_date]").datepicker('option','disabled',false);

	//등록버튼
	$('.lst_reg').on('click',function(){
		cur_obj = $(this).closest('.lst_exp');
		var prj_idx = <?=$prj_idx?>;
		var com_idx = cur_obj.find('input[name="com_idx"]').val();
		var com_name = $.trim(cur_obj.find('input[name="com_name"]').val());
		var prx_name = $.trim(cur_obj.find('input[name="prx_name"]').val());
		var prx_content = $.trim(cur_obj.find('input[name="prx_content"]').val());
		var prx_price = $.trim(cur_obj.find('input[name="prx_price"]').val());
		var prx_plan_date = $.trim(cur_obj.find('input[name="prx_plan_date"]').val());
		var prx_done_date = $.trim(cur_obj.find('input[name="prx_done_date"]').val());
		var type = $(this).attr('typ');
		if(!prj_idx){
			alert('프로젝트 고유번호가 제대로 넘어오지 않았습니다.');
			return false;
		}
		if(!com_name){
			alert('업체명을 선택해 주세요.');
			cur_obj.find('input[name="com_name"]').focus();
			return false;
		}
		if(!prx_name){
			alert('지출제목을 입력해 주세요.');
			cur_obj.find('input[name="prx_name"]').focus();
			return false;
		}
		if(!prx_price){
			alert('지출금액을 입력해 주세요.');
			cur_obj.find('input[name="prx_price"]').focus();
			return false;
		}
		if(!prx_plan_date){
			alert('지출예정일을 입력해 주세요.');
			cur_obj.find('input[name="prx_plan_date"]').focus();
			return false;
		}
		exp_reg(prj_idx,type,com_idx,prx_name,prx_content,prx_price,prx_plan_date,prx_done_date);
	});

	//수정버튼
	$('.lst_mod').on('click',function(e){
		e.stopImmediatePropagation();
		//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
		if($('.reg_prev').length || $('.lst_down.focus').length){
			alert('이전 등록작업 또는 파일작업을 먼저 진행한 후에 실행해 주세요d.');
			return false;
		}
		cur_obj = $(this).closest('.lst_exp');
		var prx_idx = $(this).attr('prx_idx');
		var prj_idx = <?=$prj_idx?>;
		var com_idx = cur_obj.find('input[name="com_idx"]').val();
		var com_name = $.trim(cur_obj.find('input[name="com_name"]').val());
		var prx_name = $.trim(cur_obj.find('input[name="prx_name"]').val());
		var prx_content = $.trim(cur_obj.find('input[name="prx_content"]').val());
		var prx_price = $.trim(cur_obj.find('input[name="prx_price"]').val());
		var prx_plan_date = $.trim(cur_obj.find('input[name="prx_plan_date"]').val());
		var prx_done_date = $.trim(cur_obj.find('input[name="prx_done_date"]').val());
		var type = $(this).attr('typ');
		if(!prj_idx){
			alert('프로젝트 고유번호가 제대로 넘어오지 않았습니다.');
			return false;
		}
		if(!com_name){
			alert('업체명을 선택해 주세요.');
			cur_obj.find('input[name="com_name"]').focus();
			return false;
		}
		if(!prx_name){
			alert('지출내용을 입력해 주세요.');
			cur_obj.find('input[name="prx_name"]').focus();
			return false;
		}
		if(!prx_price){
			alert('지출금액을 입력해 주세요.');
			cur_obj.find('input[name="prx_price"]').focus();
			return false;
		}
		if(!prx_plan_date){
			alert('지출예정일을 입력해 주세요.');
			cur_obj.find('input[name="prx_plan_date"]').focus();
			return false;
		}
		exp_upd(prx_idx,prj_idx,type,com_idx,prx_name,prx_content,prx_price,prx_plan_date,prx_done_date);
	});


	//삭제버튼
	$('.lst_del').on('click',function(e){
		e.preventDefault();
		cur_obj = $(this).closest('.lst_exp');
		if(cur_obj.find('strong').hasClass('reg_prev')){
			//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
			if($('.reg_prev').length > 1 || $('.lst_down.focus').length){
				alert('이전 등록작업 또는 파일작업을 먼저 진행한 후에 실행해 주세요.');
				return false;
			}
			cur_obj.remove();
		}
		else{
			//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
			if($('.reg_prev').length || $('.lst_down.focus').length){
				if($(this).attr('prx_idx') != cur_obj.find('.btn_file').attr('prx_idx')){
					alert('이전 등록작업 또는 파일작업을 먼저 진행한 후에 실행해 주세요.');
					return false;
				}
			}
			if(!confirm('지출정보와 관련파일 전부 삭제되어 복구가 불가능합니다.\n정말로 삭제하시겠습니까?')){
				return false;
			}
			var prx_idx = $(this).attr('prx_idx');
			var	type = $(this).attr('typ');
			exp_del(prx_idx,type);
		}
		events_del();
		events_reg();
	});

	//파일열기/닫기 버튼
	$('.lst_fle').on('click',function(){
		if($('.reg_prev').length){
			alert('이전 신규지출 등록작업을 먼저 진행한 후에 파일작업을 실행해 주세요.');
			return false;
		}
		cur_obj = $(this).closest('.lst_exp');
		var cur_down = $(this).parent().parent().siblings('.lst_down');


		events_del();
		if(cur_down.hasClass('focus')){
			//기존 모든 멀티파일객체를 제거
			multifile_remove();
		}
		else{
			//기존 모든 멀티파일객체를 제거
			multifile_remove();
			//해당 파일열기버튼의 .lst_down안에만 멀티파일객체 생성한다.
			multifile_insert($(this));
		}
		events_reg();
	});
}

function events_del(){
	$('.com_name').off('click');
	$("input[name$=_date]").datepicker('option','disabled',true);
	$('.lst_reg').off('click');
	$('.lst_del').off('click');
	$('.lst_fle').off('click');
}

//파일 열기버튼을 누르면 해당 .lst_down안에 multifile객체를 넣는 함수
function multifile_insert(btn){
	//멀티파일영역을 닫거나 다른 영역을 열려고 하면 기존에 파일등록하려던 작업을 전부 초기화(삭제) 한다.
	multifile_remove();
	$('.lst_fle').find('i').attr('class','fa fa-angle-down');
	$('.lst_down').removeClass('focus');
	btn.find('i').attr('class','fa fa-angle-up');
	btn.parent().parent().siblings('.lst_down').addClass('focus');
	var lst_down = btn.parent().parent().siblings('.lst_down');
	var prx_idx = btn.attr('prx_idx');
	var type = btn.attr('typ');
	var lst_down = btn.parent().parent().siblings('.lst_down');
	var btn_file = btn.parent().parent().siblings('.lst_down').find('.btn_file');
	// console.log(prx_idx);return false;
	var form_obj = `<form name="multi_file" id="mfile" method="post" enctype="multipart/form-data">
	<button type="button" class="btn_file" prx_idx="${prx_idx}" typ="${type}">파일등록/삭제</button>
	<input type="file" name="prx_files[]" multiple class="multi_files" id="prx_files">
	</form>`;
	if(btn.attr('typ') != 'ppc'){
		$(form_obj).prependTo(lst_down);
		$('#prx_files').MultiFile();
	}
	$('.btn_file').on('click',function(){
		btn_file_click($(this));
	});
}
//파일등록 버튼 클릭시 호출하는 함수
function btn_file_click(btn){
	var prx_idx = btn.attr('prx_idx');
	var typ = btn.attr('typ');
	var fileObj = btn.siblings('.MultiFile-wrap');
	file_update(prx_idx,typ,fileObj);
}
//파일 닫기버튼을 누르면 multifile을 제거하는 함수
function multifile_remove(){
	$('.lst_fle').find('i').attr('class','fa fa-angle-down');
	$('.lst_down').removeClass('focus');
	$('#mfile').remove();
}

$('#rate_btn').on('click',function(){
	var rate = Number($(this).siblings('#mng_rate').val());

	var prj_mng_price = (prj_price)?Math.round((rate * prj_price)/100):0;
	var prj_mng_price_str = thousand_comma(prj_mng_price);
	$('#mng_rate_price').text(prj_mng_price_str);

	var link = '<?=G5_USER_ADMIN_URL?>/project_expense_mng_rate_update.php';
	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prj_idx': prj_idx, 'rate': rate},
		success : function(res){
			;
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
});

//관리비 0~100까지의 숫자만 입력 가능한 함수
function only_number(inp){ //inp = #mng_rate
	$(inp).keyup(function(){
		var rate = $.trim($(this).val());
		rate = $(this).val().replace(/[^0-9]/g,"");
		if(rate > 100) rate = 100;
		else if(rate < 0) rate = 0;
		else if(rate == '') rate = 10;
		$(this).val(rate);
	});
}

//지출금액 숫자만 입력 출력은 천단위 콤마로표시
function only_number_comma(inp){
	$(inp).keyup(function(){
		var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
		price = (price == '0') ? '' : price;
		$(this).val(price);
	});
}

//새로운 지출내역 등록
function exp_reg(prj_idx,type,com_idx,prx_name,prx_content,prx_price,prx_plan_date,prx_done_date){
	var link = '<?=G5_USER_ADMIN_URL?>/project_expense_form_update.php';

	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prj_idx': prj_idx, 'prx_type': type, 'com_idx': com_idx, 'prx_name': prx_name, 'prx_content': prx_content, 'prx_price': prx_price, 'mode': 'r', 'prx_plan_date': prx_plan_date, 'prx_done_date': prx_done_date},
		success : function(res){
			if(res == 'reg'){
				alert('지출내역을 성공적으로 등록했습니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}

//새로운 지출내역 수정
function exp_upd(prx_idx,prj_idx,type,com_idx,prx_name,prx_content,prx_price,prx_plan_date,prx_done_date){
	var link = '<?=G5_USER_ADMIN_URL?>/project_expense_form_update.php';

	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prx_idx': prx_idx,'prj_idx': prj_idx, 'prx_type': type, 'com_idx': com_idx, 'prx_name': prx_name, 'prx_content': prx_content, 'prx_price': prx_price, 'mode': 'u', 'prx_plan_date': prx_plan_date, 'prx_done_date': prx_done_date},
		success : function(res){
			if(res == 'upd'){
				alert('지출내역을 성공적으로 수정했습니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}

//새로운 지출내역 삭제
function exp_del(prx_idx,type){
	var link = '<?=G5_USER_ADMIN_URL?>/project_expense_form_update.php';

	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prx_idx': prx_idx, 'prx_type': type, 'mode': 'd'},
		success : function(res){
			if(res == 'del'){
				alert('지출내역을 성공적으로 삭제하였습니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}

function file_update(prx_idx,typ,fObj){
	var prx_idx = prx_idx;
	var type = typ;
	var fobj = fObj;
	var btn_file = fobj.siblings('.btn_file');
	var form = $('#mfile');
	var up_ul = btn_file.parent().siblings('ul');
	var fle_idxs = '';
	//삭제할 fle_idx저장
	up_ul.find('.fle_del').each(function(){
		if($(this).is(':checked')){
			if(fle_idxs == ''){
				fle_idxs = $(this).attr('no');
			}
			else{
				fle_idxs += ','+$(this).attr('no');
			}
		}
	});

	if(form.find('.MultiFile-list').find('.MultiFile-label').length == 0 && !fle_idxs){
		alert('업로드할 파일 또는 삭제할 항목을 최소 한 개 이상 선택해 주세요.');
		// multifile_remove();
		return false;
	}

	var formData = new FormData();
	formData.append("prx_idx",prx_idx);
	formData.append("type",type);
	formData.append("dels",fle_idxs);

	// console.log(form.find('.multi_files').attr('name'));return false;
	// console.log(form.find('.multi_files')[0].files);return false;
	// console.log(form.find('.multi_files').attr('name'));return false;

	$(form.find('.multi_files')[0].files).each(function(index,file){
		formData.append(form.find('.multi_files').attr('name'),file);
	});

	var link = '<?=G5_USER_ADMIN_URL?>/project_expense_file_update.php';
	// console.log(formData);return false;
	// console.log(prx_idx);return false;
	$.ajax({
		type : "POST",
		url : link,
		processData : false, //file전송시 필수
		contentType : false, //file전송시 필수
		data : formData,
		success : function(res){
			if(res == 'ok'){
				alert('성공적으로 반영되었습니다..');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
			multifile_remove();
		}
	});
}
</script>

<?php
include_once ('./_tail.php');
