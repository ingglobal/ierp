<?php
$sub_menu = "960266";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');
// print_r2($_POST);exit;
// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form_update/","",$g5['file_name']); // _form_update를 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}

// 공통쿼리
$skips = array($pre.'_idx','mb_id',$pre.'_reg_dt',$pre.'_update_dt');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}

$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';

// print_r2($sql_commons);
// echo $sql_common;
// exit;

if($w == '') {
    $sql = " INSERT into {$g5_table_name} SET 
                {$sql_common} 
                , mb_id = '{$member['mb_id']}'
                , ppc_reg_dt = '".G5_TIME_YMDHIS."'
                , ppc_update_dt = '".G5_TIME_YMDHIS."'
	";
    // echo $sql;exit;
    sql_query($sql,1);
	$ppc_idx = sql_insert_id();

    // 지출분배 테이블에도 등록
    $sqlc = " INSERT into {$g5['project_purchase_divide_table']} SET
        ppc_idx = '{$ppc_idx}'
        , ppd_content = '{$ppc_subject}-지출'
        , ppd_price = '{$ppc_price}'
        , ppd_plan_date = '{$ppc_date}'
        , ppd_done_date = '{$ppc_date}'
        , ppd_bank = 'bank'
        , ppd_type = 'all'
        , ppd_status = 'ok'
        , ppd_reg_dt = '".G5_TIME_YMDHIS."'
        , ppd_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sqlc,1);
}
else if($w == 'u') {
    $total_price = 0;
    $complete_flag = 1;
    foreach($ppd_idx as $pd_idx){
        $ppd_price[$pd_idx] = preg_replace("/,/","",$ppd_price[$pd_idx]);

        $total_price += $ppd_price[$pd_idx];
        $ppd_status = ($ppd_done_date[$pd_idx] && $ppd_done_date[$pd_idx] != '0000-00-00') ? 'complete' : 'ok';
        if($ppd_status == 'ok'){
            $complete_flag = 0;
        }

        $sqld = " UPDATE {$g5['project_purchase_divide_table']} SET
                    ppd_content = '{$ppd_content[$pd_idx]}'
                    , ppd_price = '{$ppd_price[$pd_idx]}'
                    , ppd_plan_date = '{$ppd_plan_date[$pd_idx]}'
                    , ppd_done_date = '{$ppd_done_date[$pd_idx]}'
                    , ppd_bank = '{$ppd_bank[$pd_idx]}'
                    , ppd_type = '{$ppd_type[$pd_idx]}'
                    , ppd_status = '{$ppd_status}'
                    , ppd_update_dt = '".G5_TIME_YMDHIS."'
                WHERE ppd_idx = '{$pd_idx}'
        ";
        sql_query($sqld,1);
    }

    $ppc_price = $total_price;

    $ppc_status = ($complete_flag == 1) ? 'complete' : $ppc_status;
    $sql = " UPDATE {$g5_table_name} SET 
					com_idx = '{$com_idx}'
                    , prj_idx = '{$prj_idx}'
                    , mb_id = '{$member['mb_id']}'
                    , ppc_date = '{$ppc_date}'
                    , ppc_subject = '{$ppc_subject}'
                    , ppc_content = '{$ppc_content}'
                    , ppc_price = '{$ppc_price}'
                    , ppc_status = '{$ppc_status}'
					, ppc_update_dt = '".G5_TIME_YMDHIS."'
			WHERE ppc_idx = '".$ppc_idx."' 
	";
    sql_query($sql,1);

}
else if($w == 'd') {

}




//파일 삭제처리
$merge_del = array();
$del_arr = array();
if(@count($ppt_del)){
	foreach($ppt_del as $k=>$v) {
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
upload_multi_file($_FILES['ppc_datas'],'ppc',$ppc_idx,'ppc');


foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

goto_url('./'.$fname.'_list.php?'.$qstr, false);
?>