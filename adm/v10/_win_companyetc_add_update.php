<?php
include_once('./_common.php');

$com_name = trim($_POST['com_name']);
$com_president = trim($_POST['com_president']);
$com_tel = trim($_POST['com_tel']);

$msg = '';

// $chk_sql = " SELECT COUNT(*) AS cnt FROM {$g5['companyetc_table']} WHERE com_status NOT IN('delete','del','trash','cancel') AND com_name = '{$com_name}' AND com_president = '{$com_president}' ";
$chk_sql = " SELECT COUNT(*) AS cnt FROM {$g5['companyetc_table']} WHERE com_status NOT IN('delete','del','trash','cancel') AND com_name LIKE '%{$com_name}%' ";
$chk = sql_fetch($chk_sql);

//중복여부 확인
if($chk['cnt']) {
    $msg = 'dp';
}
//중복없으면 새로 등록
else {
    $sql = " INSERT INTO {$g5['companyetc_table']} SET
                com_name = '".addslashes($_POST['com_name'])."'
                , com_tel = '{$com_tel}'
                , com_status = 'ok'
                , com_president = '{$com_president}'
                , com_reg_dt = '".G5_TIME_YMDHIS."'
                , com_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql,1);
    $msg = 'ok';
}

echo $msg;