<?php
//특근시간( hours )
if($board['bo_3_subj'] && $board['bo_3'] && preg_match("/,/",$board['bo_3']) && preg_match("/=/",$board['bo_3'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_3']));
    $valname = $board['bo_3_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        //${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		//${'bo_'.$valname.'_reverse'}[$value] = $key;
		//${'bo_'.$valname.'_arr'}[] = $key;
    }
}
//특근수당비율( exrate )
if($board['bo_5_subj'] && $board['bo_5'] && preg_match("/,/",$board['bo_5']) && preg_match("/=/",$board['bo_5'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_5']));
    $valname = $board['bo_5_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        //${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		//${'bo_'.$valname.'_reverse'}[$value] = $key;
		//${'bo_'.$valname.'_arr'}[] = $key;
    }
}

$prc_sql = sql_fetch(" SELECT mb_7 FROM {$g5['member_table']} WHERE mb_id = '{$_POST['wr_mb_id_worker']}' ");
$_POST['wr_hour_price'] = $prc_sql['mb_7'] * $bo_exrate_value[$_POST['wr_work_type']];
$_POST['wr_total_price'] = $_POST['wr_hour_price'] * $bo_hours_value[$_POST['wr_hour_count']];

foreach($_POST as $pk => $pv){
    ${$pk} = $pv;
}
$_POST['wr_work_dt'] = $_POST['wr_work_date'].' '.$_POST['wr_work_time'];
$wr_work_dt = $_POST['wr_work_dt'];

//exit;