<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
// print_r2($_POST);exit;
if ($act_button === "선택수정") {
    for ($i=0; $i<$count_chk; $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $csr_idx = clean_xss_tags($_POST['csr_idx'][$k],1,1);
        $csr_start_date = isset($_POST['csr_start_date'][$k]) ? clean_xss_tags($_POST['csr_start_date'][$k],1,1):'';
        $csr_end_date = isset($_POST['csr_end_date'][$k]) ? clean_xss_tags($_POST['csr_end_date'][$k],1,1):'';
        $csr_memo = isset($_POST['csr_memo'][$k]) ? strip_tags(clean_xss_attributes($_POST['csr_memo'][$k])):'';
        $csr_status = isset($_POST['csr_status'][$k]) ? clean_xss_tags($_POST['csr_status'][$k],1,1):'';

        $sql = " UPDATE {$g5['card_user_table']}
                SET csr_start_date = '".sql_real_escape_string($csr_start_date)."',
                    csr_end_date = '".sql_real_escape_string($csr_end_date)."',
                    csr_memo = '{$csr_memo}',
                    csr_status = '".sql_real_escape_string($csr_status)."',
                    csr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE csr_idx = '{$csr_idx}'
        ";
        // echo $sql."<br>";
        sql_query($sql,1);
    }
    // exit;
} else if ($act_button === "선택삭제") {
    for ($i=0; $i<$count_chk; $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        
        $csr_idx = clean_xss_tags($_POST['csr_idx'][$k],1,1);
        $sql = " UPDATE {$g5['card_user_table']}
                SET csr_status = 'trash',
                    csr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE csr_idx = '{$csr_idx}'
        ";
        sql_query($sql,1);
    }
}

$qstr .= $qstr.'&sch_crd_idx='.$sch_crd_idx;
goto_url('./card_user_list.php?'.$qstr);