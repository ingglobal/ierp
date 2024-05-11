<?php
$sub_menu = '960300';
include_once('./_common.php');
// print_r2($_POST);
// exit;
auth_check($auth[$sub_menu], "r");

// 변수 설정, 필드 구조 및 prefix 추출
$wrp_tbl = $g5['workreport_table'];
$wrp_fields = sql_field_names($wrp_tbl);
// 변수 재설정
for($i=0;$i<sizeof($wrp_fields);$i++) {
    // 공백 제거
    $_POST[$wrp_fields[$i]] = trim($_POST[$wrp_fields[$i]]);
    // 타임값 뒤에 추가로 :00값을 붙임
    if(preg_match("/_time$/",$wrp_fields[$i]))
        $_POST[$wrp_fields[$i]] = $_POST[$wrp_fields[$i]].':00';
    // 천단위 제거
    if(preg_match("/_price$/",$wrp_fields[$i]))
        $_POST[$wrp_fields[$i]] = preg_replace("/,/","",$_POST[$wrp_fields[$i]]);
}

$wrp_skips = array('wrp_idx','wrp_reg_dt','wrp_update_dt');
for($i=0;$i<sizeof($wrp_fields);$i++) {
    if(in_array($wrp_fields[$i],$wrp_skips)) {continue;}
    $wrp_commons[] = " ".$wrp_fields[$i]." = '".$_POST[$wrp_fields[$i]]."' ";
}

$wrp_common = (is_array($wrp_commons)) ? implode(",",$wrp_commons) : '';

// print_r2($wrp_commons);
// echo $wrp_common;
// exit;
// print_r2($_POST);exit;





if($w == '') {
    $sql_common = "";
    if($wrp_type == 'day'){
        $sql_common = " AND wrp_date = '{$wrp_date}'
                        AND wrp_type = '{$wrp_type}'
        ";
    }
    else if($wrp_type == 'week'){
        $yy = substr($wrp_date,0,4);
        $sql_common = " AND wrp_type = '{$type}'
                        AND wrp_date LIKE '{$yy}-%'
                        AND wrp_week = '{$wrp_week}'
                        AND wrp_month = '{$wrp_month}'
        ";
    }
    else if($wrp_type == 'month'){
        $yy = substr($wrp_date,0,4);
        $sql_common = " AND wrp_type = '{$wrp_type}'
                        AND wrp_date LIKE '{$yy}-%'
                        AND wrp_month = '{$wrp_month}'
        ";
    }
    
    $csql = " SELECT COUNT(wrp_idx) AS cnt FROM {$g5['workreport_table']}
                    WHERE mb_id = '{$member['mb_id']}'
                        {$sql_common}
    ";
    
    $cres = sql_fetch($csql,1);
    if($cres['cnt']){
        alert($member['mb_name'].'님의 '.$g5['set_wrp_type_value'][$type].'서가 이미 존재합니다.');
    }

    $sql = " INSERT into {$wrp_tbl} SET 
                {$wrp_common} 
                , wrp_reg_dt = '".G5_TIME_YMDHIS."'
                , wrp_update_dt = '".G5_TIME_YMDHIS."'
	";
    // echo $sql."<br><br><br>";
    sql_query($sql,1);
	$wrp_idx = sql_insert_id();
}
else if($w == 'u') {

    $sql = " UPDATE {$wrp_tbl} SET 
                {$wrp_common} 
                , wrp_update_dt = '".G5_TIME_YMDHIS."'
            WHERE wrp_idx = '{$wrp_idx}'
	";
    // echo $sql."<br><br><br>";
    sql_query($sql,1);
}
else if($w == 'd'){
    // 먼저 해당 wrp_idx와 관련된 모든파일을 삭제
    $dfres = sql_fetch("SELECT GROUP_CONCAT(DISTINCT fle_idx) AS fle_idxs FROM {$g5['file_table']}
        WHERE fle_db_table = 'wrp' AND fle_type = 'wrp' AND fle_db_id = '{$wrp_idx}' ");
    $dfarr = ($dfres['fle_idxs']) ? explode(',',$dfres['fle_idxs']) : array();
    if(count($dfarr)){
        delete_idx_file($dfarr);
        // ppt_idx와 관련된 fle_idx 데이터를 전부 삭제
        $dfsql = " DELETE FROM {$g5['file_table']}
            WHERE fle_db_table = 'wrp' AND fle_type = 'wrp' AND fle_db_id = '{$wrp_idx}'
        ";
        sql_query($dfsql,1);
    }

    // wrp테이블에서 wrp_idx를 가지고 있는 레코드를 삭제한다.
    $gsql = " DELETE FROM {$g5['workreport_table']} WHERE wrp_idx = '{$wrp_idx}' ";
    sql_query($gsql,1);
}

// exit;

if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(@count($wrp_del)){
        foreach($wrp_del as $k=>$v) {
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
    upload_multi_file($_FILES['wrp_datas'],'wrp',$wrp_idx,'wrp');
}


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
if($w == '' || $w == 'u'){
    // $qstr .= '&wrp_idx='.$wrp_idx;
    goto_url('./workreport_view.php?wrp_idx='.$wrp_idx.'&type='.$wrp_type.'&yy='.substr($wrp_date,0,4).'&mm='.$wrp_month, false);
}
else if($w == 'd'){
    goto_url('./workreport_calendar.php?type='.$type.'&yy='.$yy.'&mm='.$mm, false);
}