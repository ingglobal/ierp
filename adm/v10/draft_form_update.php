<?php
$sub_menu = '960270';
include_once('./_common.php');
// print_r2($_POST);
// exit;
auth_check($auth[$sub_menu], "r");

if(!$drf_status && $w != 'd'){
    alert('상태값은 반드시 선택해 주셔야 합니다.');
}

// 변수 설정, 필드 구조 및 prefix 추출
$drf_tbl = $g5['draft_table'];
$drf_fields = sql_field_names($drf_tbl);
// 변수 재설정
for($i=0;$i<sizeof($drf_fields);$i++) {
    // 공백 제거
    $_POST[$drf_fields[$i]] = trim($_POST[$drf_fields[$i]]);
    // 타임값 뒤에 추가로 :00값을 붙임
    if(preg_match("/_time$/",$drf_fields[$i]))
        $_POST[$drf_fields[$i]] = $_POST[$drf_fields[$i]].':00';
    // 천단위 제거
    if(preg_match("/_price$/",$drf_fields[$i]))
        $_POST[$drf_fields[$i]] = preg_replace("/,/","",$_POST[$drf_fields[$i]]);
}

$drf_skips = array('drf_idx','drf_who_check','drf_reg_dt','drf_update_dt');
for($i=0;$i<sizeof($drf_fields);$i++) {
    if(in_array($drf_fields[$i],$drf_skips)) {continue;}
    $drf_commons[] = " ".$drf_fields[$i]." = '".$_POST[$drf_fields[$i]]."' ";
}

$drf_common = (is_array($drf_commons)) ? implode(",",$drf_commons) : '';

// print_r2($drf_commons);
// echo $drf_common;
// exit;
// print_r2($_POST);exit;

if($w == '') {
    $sql = " INSERT into {$drf_tbl} SET 
                {$drf_common} 
                , drf_who_check = '1'
                , drf_reg_dt = '".G5_TIME_YMDHIS."'
                , drf_update_dt = '".G5_TIME_YMDHIS."'
	";
    // echo $sql."<br><br><br>";
    sql_query($sql,1);
	$drf_idx = sql_insert_id();
}
else if($w == 'u') {
    $who_check = 1;
    if($mb_id_approval == $member['mb_id']){
        $who_check = 2;
    }
    if($super_ceo_admin){
        $who_check = 3;
    }
    $sql = " UPDATE {$drf_tbl} SET 
                {$drf_common} 
                , drf_who_check = '{$who_check}'
                , drf_update_dt = '".G5_TIME_YMDHIS."'
            WHERE drf_idx = '{$drf_idx}'
	";
    // echo $sql."<br><br><br>";
    sql_query($sql,1);
}
else if($w == 'd'){
    // 먼저 해당 drf_idx와 관련된 모든파일을 삭제
    $dfres = sql_fetch("SELECT GROUP_CONCAT(DISTINCT fle_idx) AS fle_idxs FROM {$g5['file_table']}
        WHERE fle_db_table = 'drf' AND fle_type = 'drf' AND fle_db_id = '{$drf_idx}' ");
    $dfarr = ($dfres['fle_idxs']) ? explode(',',$dfres['fle_idxs']) : array();
    if(count($dfarr)){
        delete_idx_file($dfarr);
        // ppt_idx와 관련된 fle_idx 데이터를 전부 삭제
        $dfsql = " DELETE FROM {$g5['file_table']}
            WHERE fle_db_table = 'drf' AND fle_type = 'drf' AND fle_db_id = '{$drf_idx}'
        ";
        sql_query($dfsql,1);
    }

    // drf테이블에서 drf_idx를 가지고 있는 레코드를 삭제한다.
    $gsql = " DELETE FROM {$g5['draft_table']} WHERE drf_idx = '{$drf_idx}' ";
    sql_query($gsql,1);
}

// exit;

if($w == '' || $w == 'u'){
    //파일 삭제처리
    $merge_del = array();
    $del_arr = array();
    if(@count($drf_del)){
        foreach($drf_del as $k=>$v) {
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
    upload_multi_file($_FILES['drf_datas'],'drf',$drf_idx,'drf');
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
    $qstr .= '&drf_idx='.$drf_idx;
    goto_url('./draft_view.php?'.$qstr, false);
}
else if($w == 'd'){
    goto_url('./draft_list.php?'.$qstr, false);
}