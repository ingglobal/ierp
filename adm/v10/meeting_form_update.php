<?php
$sub_menu = '960265';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

// 변수 설정, 필드 구조 및 prefix 추출
$participant_tbl = $g5['meeting_participant_table'];
$mtg_tbl = $g5['meeting_table'];
$mtg_fields = sql_field_names($mtg_tbl);
// 변수 재설정
for($i=0;$i<sizeof($mtg_fields);$i++) {
    // 공백 제거
    $_POST[$mtg_fields[$i]] = trim($_POST[$mtg_fields[$i]]);
    // 타임값 뒤에 추가로 :00값을 붙임
    if(preg_match("/_time$/",$mtg_fields[$i]))
        $_POST[$mtg_fields[$i]] = $_POST[$mtg_fields[$i]].':00';
    // 천단위 제거
    if(preg_match("/_price$/",$mtg_fields[$i]))
        $_POST[$mtg_fields[$i]] = preg_replace("/,/","",$_POST[$mtg_fields[$i]]);
}

$mtg_skips = array('mtg_idx','mtg_reg_dt','mtg_update_dt');
for($i=0;$i<sizeof($mtg_fields);$i++) {
    if(in_array($mtg_fields[$i],$mtg_skips)) {continue;}
    $mtg_commons[] = " ".$mtg_fields[$i]." = '".$_POST[$mtg_fields[$i]]."' ";
}

$mtg_common = (is_array($mtg_commons)) ? implode(",",$mtg_commons) : '';

// print_r2($mtg_commons);
// echo $mtg_common;
// exit;
// print_r2($_POST);exit;

if($w == '') {
    $sql = " INSERT into {$mtg_tbl} SET 
                {$mtg_common} 
                , mtg_reg_dt = '".G5_TIME_YMDHIS."'
                , mtg_update_dt = '".G5_TIME_YMDHIS."'
	";
    // echo $sql."<br><br><br>";
    sql_query($sql,1);
	$mtg_idx = sql_insert_id();

    foreach($mtp_idx as $idx => $mtpidx){
        $sql = " INSERT INTO {$participant_tbl} SET
                    mtg_idx = '{$mtg_idx}'
                    , mtp_belong = '{$mtp_belong[$idx]}'
                    , mtp_name = '{$mtp_name[$idx]}'
                    , mtp_rank = '{$mtp_rank[$idx]}'
                    , mtp_phone = '{$mtp_phone[$idx]}'
        ";
        // echo $sql."<br><br>";
        sql_query($sql,1);
    }
}
else if($w == 'u') {
    $sql = " UPDATE {$mtg_tbl} SET 
                {$mtg_common} 
                , mtg_update_dt = '".G5_TIME_YMDHIS."'
            WHERE mtg_idx = '{$mtg_idx}'
	";
    // echo $sql."<br><br><br>";
    sql_query($sql,1);

    foreach($mtp_idx as $idx => $mtpidx){
        //$mtpidx값이 있으면 수정(update)
        if($mtpidx){
            $sql = " UPDATE {$participant_tbl} SET
                        mtp_belong = '{$mtp_belong[$idx]}'
                        , mtp_name = '{$mtp_name[$idx]}'
                        , mtp_rank = '{$mtp_rank[$idx]}'
                        , mtp_phone = '{$mtp_phone[$idx]}'
                    WHERE mtp_idx = '{$mtpidx}'
            ";
        }
        //$mtpidx값이 없으면 추가(insert)
        else{
            $sql = " INSERT INTO {$participant_tbl} SET
                        mtg_idx = '{$mtg_idx}'
                        , mtp_belong = '{$mtp_belong[$idx]}'
                        , mtp_name = '{$mtp_name[$idx]}'
                        , mtp_rank = '{$mtp_rank[$idx]}'
                        , mtp_phone = '{$mtp_phone[$idx]}'
            ";
        }
        // echo $sql."<br><br>";
        sql_query($sql,1);
    }
}

// exit;


//파일 삭제처리
$merge_del = array();
$del_arr = array();
if(@count($mtg_del)){
	foreach($mtg_del as $k=>$v) {
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
upload_multi_file($_FILES['mtg_datas'],'mtg',$mtg_idx,'mtg');


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

$qstr .= '&mtg_idx='.$mtg_idx;

goto_url('./meeting_view.php?'.$qstr, false);