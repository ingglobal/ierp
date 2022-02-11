<?php
include_once('./_common.php');
//$g5['project_exprice_table']
//'prj_idx': prj_idx,'prx_type': type, 'com_idx': com_idx, 'prx_name': prx_name, 'prx_price': prx_price, 'reg_flag': 'r'/'u'/'d', 'prx_done_date': prx_done_date
//등록모드일때
$prx_type = trim($_POST['prx_type']);
if($mode == 'r' || $mode == 'u'){
	$prx_name = trim($_POST['prx_name']);
	$prx_price = trim($_POST['prx_price']);
	$prx_done_date = trim($_POST['prx_done_date']);
	$prx_price = preg_replace("/,/","",$prx_price);
	
	$sql_common = " prj_idx = '{$prj_idx}'
		,com_idx = '{$com_idx}'
		,prx_type = '{$prx_type}'
		,prx_price = '{$prx_price}'
		,prx_name = '{$prx_name}'
		,prx_content = '{$prx_name}'
		,prx_plan_date = '{$prx_done_date}'
		,prx_done_date = '{$prx_done_date}'
		,mb_id = '{$member['mb_id']}'
		,prx_status = 'ok'
	";
}
$msg = '';
if($mode == 'r') {
	$sql = " INSERT INTO {$g5['project_exprice_table']} SET
		{$sql_common}
		,prx_reg_dt = '".G5_TIME_YMDHIS."'
		,prx_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql,1);
	$msg = 'reg';
}
//수정모드일때
else if($mode == 'u') {
	$sql = " UPDATE {$g5['project_exprice_table']} SET
				{$sql_common}
				,prx_update_dt = '".G5_TIME_YMDHIS."'
			WHERE prx_idx = '{$prx_idx}'
	";
	sql_query($sql,1);
	$msg = 'upd';
}
//삭제모드일때
else if($mode == 'd') {
	//우선해당 prx_idx으로 등록된 fle_idx를 추출한다.
	$fle_idxs = sql_fetch(" SELECT GROUP_CONCAT(fle_idx) AS fidxs FROM {$g5['file_table']} WHERE
					fle_db_table = 'project_exprice'
					AND fle_db_id = '{$prx_idx}'
					AND fle_type = '{$prx_type}'
	");
	$del_arr = ($fle_idxs['fidxs']) ? explode(',',$fle_idxs['fidxs']) : array();
	//파일 삭제처리
	if(@count($del_arr)) delete_idx_file($del_arr);

	$sql = " DELETE FROM {$g5['project_exprice_table']} WHERE prx_idx = '{$prx_idx}' ";
	sql_query($sql,1);
	$msg = 'del';
}

echo $msg;
/*
//파일 삭제처리
$merge_del = array();
$del_arr = array();

if(@count($prexp_con_del)){
	foreach($prexp_con_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}

if(@count($prexp_ord_del)){
	foreach($prexp_ord_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}

if(count($merge_del)){
	foreach($merge_del as $k=>$v) {
		array_push($del_arr,$k);
	}
}

if(count($del_arr)) delete_idx_file($del_arr);

//멀티파일처리
upload_multi_file($_FILES['prj_prexp_con_datas'],'expense',$prj_idx,'prexp_con');
upload_multi_file($_FILES['prj_prexp_ord_datas'],'expense',$prj_idx,'prexp_ord');
*/