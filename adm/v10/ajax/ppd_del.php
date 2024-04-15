<?php
include_once('./_common.php');

/*
$ppd_idx
*/
// 지출분배정보 삭제
$sqld = " DELETE FROM {$g5['project_purchase_divide_table']} WHERE
    ppd_idx = '{$ppd_idx}'
";
sql_query($sqld,1);


echo 'ok';