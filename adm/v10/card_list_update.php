<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

if ($act_button === "선택수정") {
    for ($i=0; $i<$count_chk; $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        
        $crd_idx = clean_xss_tags($_POST['crd_idx'][$k],1,1);
        $crd_code = isset($_POST['crd_code'][$k]) ? clean_xss_tags($_POST['crd_code'][$k],1,1):'';
        $crd_no = isset($_POST['crd_no'][$k]) ? clean_xss_tags($_POST['crd_no'][$k],1,1):'';
        $crd_no = preg_replace("/-/","",$crd_no);
        $crd_expire_month = isset($_POST['crd_expire_month'][$k]) ? clean_xss_tags($_POST['crd_expire_month'][$k],1,1):'';
        $crd_expire_year = isset($_POST['crd_expire_year'][$k]) ? clean_xss_tags($_POST['crd_expire_year'][$k],1,1):'';
        $crd_memo = isset($_POST['crd_memo'][$k]) ? strip_tags(clean_xss_attributes($_POST['crd_memo'][$k])):'';
        $crd_status = isset($_POST['crd_status'][$k]) ? clean_xss_tags($_POST['crd_status'][$k],1,1):'';
        $sql = " UPDATE {$g5['card_table']}
                SET crd_code = '".sql_real_escape_string($crd_code)."',
                    crd_no = '".sql_real_escape_string($crd_no)."',
                    crd_expire_month = '".sql_real_escape_string($crd_expire_month)."',
                    crd_expire_year = '".sql_real_escape_string($crd_expire_year)."',
                    crd_memo = '{$crd_memo}',
                    crd_status = '".sql_real_escape_string($crd_status)."',
                    crd_update_dt = '".G5_TIME_YMDHIS."'
                WHERE crd_idx = '{$crd_idx}'
        ";
        
        sql_query($sql,1);
    }
} else if ($act_button === "선택삭제") {
    for ($i=0; $i<$count_chk; $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        
        $crd_idx = clean_xss_tags($_POST['crd_idx'][$k],1,1);
        $sql = " UPDATE {$g5['card_table']}
                SET crd_status = 'trash',
                    crd_update_dt = '".G5_TIME_YMDHIS."'
                WHERE crd_idx = '{$crd_idx}'
        ";
        sql_query($sql,1);
    }
}

$qstr .= $qstr.'&sch_crd_code='.$sch_crd_code.'&sch_crd_expire_year='.$sch_crd_expire_year;
goto_url('./card_list.php?'.$qstr);