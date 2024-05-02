<?php
include_once('./_common.php');

/*
$arm_idx
*/
// 물품관리자정보 삭제
$sqld = " DELETE FROM {$g5['assets_manager_table']} WHERE
    asm_idx = '{$asm_idx}'
";
sql_query($sqld,1);


echo 'ok';