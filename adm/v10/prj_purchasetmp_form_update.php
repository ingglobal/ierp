<?php
$sub_menu = "960268";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase_tmp';
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
$skips = array($pre.'_idx','ppc_idx','mb_id',$pre.'_reg_dt',$pre.'_update_dt');
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
                , ".$pre."_reg_dt = '".G5_TIME_YMDHIS."'
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
	";
    // echo $sql;exit;
    sql_query($sql,1);
	${$pre."_idx"} = sql_insert_id();
}
else if($w == 'u') {
    $sql = " UPDATE {$g5_table_name} SET 
					{$sql_common}
					, ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
			WHERE ".$pre."_idx = '".${$pre."_idx"}."' 
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
upload_multi_file($_FILES['ppt_datas'],'ppt',$ppt_idx,'ppt');


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