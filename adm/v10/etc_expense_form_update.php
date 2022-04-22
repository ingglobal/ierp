<?php
$sub_menu = "960255";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');


// print_r2($_POST);exit;

if(!$_POST['prx_name']) alert('지출명을 입력해 주세요.');
if(!$_POST['prx_price']) alert('지출금액을 입력해 주세요.');
if(!$_POST['prx_plan_date']) alert('지출예정일을 입력해 주세요.');

if($w == '' || $w == 'u'){
    $prx_name = trim($_POST['prx_name']);
    $prx_content = trim($_POST['prx_content']);
    $prx_price = trim($_POST['prx_price']);
    $prx_plan_date = trim($_POST['prx_plan_date']);
    $prx_done_date = trim($_POST['prx_done_date']);
    $prx_price = preg_replace("/,/","",$prx_price);
    
    $sql_common = " com_idx = '{$com_idx}'
        ,prx_type = 'etc'
        ,prx_price = '{$prx_price}'
        ,prx_name = '{$prx_name}'
        ,prx_content = '{$prx_content}'
        ,prx_plan_date = '{$prx_plan_date}'
        ,prx_done_date = '{$prx_done_date}'
        ,mb_id = '{$member['mb_id']}'
        ,prx_status = 'ok'
    ";
}

if($w == '') {
    $sql = " INSERT INTO {$g5['etc_exprice_table']} SET
		{$sql_common}
		,prx_reg_dt = '".G5_TIME_YMDHIS."'
		,prx_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql,1);
    $prx_idx = sql_insert_id();
}
else if($w == 'u') {
    $sql = " UPDATE {$g5['etc_exprice_table']} SET
				{$sql_common}
				,prx_update_dt = '".G5_TIME_YMDHIS."'
			WHERE prx_idx = '{$prx_idx}'
	";
	sql_query($sql,1);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 파일 처리2 (파일 타입이 여러개면 일련번호 붙여서 확장해 주세요.) ----------------
//파일 삭제처리
$merge_del = array();
$del_arr = array();
if(@count($etcexpense_del)){
	foreach($etcexpense_del as $k=>$v) {
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
upload_multi_file($_FILES['prx_etcexpense_files'],'etc_exprice',$prx_idx,'etcexpense');

$qstr .= '&from_date='.$from_date.'&to_date='.$to_date; // 추가로 확장해서 넘겨야 할 변수들

alert('데이터가 등록되었습니다.','./etc_expense_list.php?'.$qstr, false);