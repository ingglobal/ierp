<?php
$sub_menu = "960268";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = 'prj_purchasetmp_list';//preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명


// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}

// 공통쿼리
$skips = array($pre.'_idx','ppc_idx','mb_id',$pre.'_status',$pre.'_reg_dt',$pre.'_update_dt');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}

$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';


$sql = " INSERT into {$g5_table_name} SET 
    {$sql_common} 
    , mb_id = '{$member['mb_id']}'
    , ppc_status = 'ok'
    , ppc_reg_dt = '".G5_TIME_YMDHIS."'
    , ppc_update_dt = '".G5_TIME_YMDHIS."'
";
// echo $sql;exit;
sql_query($sql,1);
$ppc_idx = sql_insert_id();


$ppt_arr = ($ppt_idxs) ? explode(',',$ppt_idxs) : array();
$table_name2 = 'project_purchase_tmp';
$g5_table_name2 = $g5[$table_name2.'_table'];
if($ppc_idx){
    foreach($ppt_arr as $ppt_idx){
        $sql2 = " UPDATE {$g5_table_name2} SET ppc_idx = '{$ppc_idx}' WHERE ppt_idx = '{$ppt_idx}' ";
        sql_query($sql2,1);
    }
    
    //멀티파일처리
    upload_multi_file($_FILES['ppc_datas'],'ppc',$ppc_idx,'ppc');
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
goto_url('./'.$fname.'.php?'.$qstr);