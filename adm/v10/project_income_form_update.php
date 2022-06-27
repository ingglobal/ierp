<?php
include_once('./_common.php');

//등록모드일때
$prn_type = trim($_POST['prn_type']);
if($mode == 'r' || $mode == 'u'){
	$prn_name = trim($_POST['prn_name']);
	$prn_content = trim($_POST['prn_content']);
	$prn_price = trim($_POST['prn_price']);
	$prn_plan_date = trim($_POST['prn_plan_date']);
	$prn_done_date = trim($_POST['prn_done_date']);
	$prn_price = preg_replace("/,/","",$prn_price);
	
	$sql_common = " prj_idx = '{$prj_idx}'
		,com_idx = '{$com_idx}'
		,prn_type = '{$prn_type}'
		,prn_price = '{$prn_price}'
		,prn_name = '{$prn_name}'
		,prn_content = '{$prn_content}'
		,prn_plan_date = '{$prn_plan_date}'
		,prn_done_date = '{$prn_done_date}'
		,mb_id = '{$member['mb_id']}'
		,prn_status = 'ok'
	";
}
$msg = '';
if($mode == 'r') {
	$sql = " INSERT INTO {$g5['project_inprice_table']} SET
		{$sql_common}
		,prn_reg_dt = '".G5_TIME_YMDHIS."'
		,prn_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql,1);
	$msg = 'reg';
}
//수정모드일때
else if($mode == 'u') {
	$sql = " UPDATE {$g5['project_inprice_table']} SET
				{$sql_common}
				,prn_update_dt = '".G5_TIME_YMDHIS."'
			WHERE prn_idx = '{$prn_idx}'
	";
	sql_query($sql,1);
	$msg = 'upd';
}
//삭제모드일때
else if($mode == 'd') {
	//우선해당 prx_idx으로 등록된 fle_idx를 추출한다.
	$fle_idxs = sql_fetch(" SELECT GROUP_CONCAT(fle_idx) AS fidxs FROM {$g5['file_table']} WHERE
					fle_db_table = 'project_inprice'
					AND fle_db_id = '{$prn_idx}'
					AND fle_type = '{$prn_type}'
	");
	$del_arr = ($fle_idxs['fidxs']) ? explode(',',$fle_idxs['fidxs']) : array();
	//파일 삭제처리
	if(@count($del_arr)) delete_idx_file($del_arr);

	$sql = " DELETE FROM {$g5['project_inprice_table']} WHERE prn_idx = '{$prn_idx}' ";
	sql_query($sql,1);
	$msg = 'del';
}

echo $msg;
