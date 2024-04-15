<?php
include_once('./_common.php');

/*
$ppc_idx
$ppd_type
$ppd_content
$ppd_price
$ppd_plan_date
$ppd_done_date
$ppd_bank
*/
// 천단위 제거
if(preg_match("/_price$/",$ppd_price))
    $ppd_price = preg_replace("/,/","",$ppd_price);

$ppd_status = ($ppd_done_date && $ppd_done_date != '0000-00-00') ? 'complete' : 'ok';

// 지출분배 테이블에 등록
$sqlc = " INSERT into {$g5['project_purchase_divide_table']} SET
    ppc_idx = '{$ppc_idx}'
    , ppd_content = '{$ppd_content}'
    , ppd_price = '{$ppd_price}'
    , ppd_plan_date = '{$ppd_plan_date}'
    , ppd_done_date = '{$ppd_done_date}'
    , ppd_bank = '{$ppd_bank}'
    , ppd_type = '{$ppd_type}'
    , ppd_status = '{$ppd_status}'
    , ppd_reg_dt = '".G5_TIME_YMDHIS."'
    , ppd_update_dt = '".G5_TIME_YMDHIS."'
";
sql_query($sqlc,1);


echo 'ok';