<?php
include_once('./_common.php');

/*
$ppc_idx
$ppt_idxs
$ppt_prices
*/
$ppt_arr = ($ppt_idxs) ? explode(',',$ppt_idxs) : array();

$cres = sql_fetch(" SELECT SUM(ppd_price) AS ppd_sum_price FROM {$g5['project_purchase_divide_table']} WHERE ppc_idx = '{$ppc_idx}' ");
$sum_price = $cres['ppd_sum_price'] + $ppt_prices;

$tres = sql_fetch(" SELECT GROUP_CONCAT(ppt_subject) AS ppt_subjects FROM {$g5['project_purchase_tmp_table']} WHERE ppt_idx IN ({$ppt_idxs}) ");

//$ppt_prices값을 $ppc_idx의 기존값에 더하기
$pcsql = " UPDATE {$g5['project_purchase_table']} SET ppc_price = '{$sum_price}', ppc_subject = CONCAT(ppc_subject,',{$tres['ppt_subjects']}')  WHERE ppc_idx = '{$ppc_idx}' ";
sql_query($pcsql,1);


$dsql = " INSERT INTO {$g5['project_purchase_divide_table']} SET
            ppc_idx = '{$ppc_idx}'
            , ppd_content = '{$tres['ppt_subjects']}'
            , ppd_price = '{$ppt_prices}'
            , ppd_plan_date = '".G5_TIME_YMD."'
            , ppd_done_date = '0000-00-00'
            , ppd_bank = 'bank'
            , ppd_type = 'radd'
            , ppd_status = 'ok'
            , ppd_reg_dt = '".G5_TIME_YMDHIS."'
            , ppd_update_dt = '".G5_TIME_YMDHIS."'
";
sql_query($dsql,1);

foreach($ppt_arr as $ppt_idx){
    $sql = " UPDATE {$g5['project_purchase_tmp_table']} SET
                ppc_idx = '{$ppc_idx}'
            WHERE ppt_idx = '{$ppt_idx}'
    ";
    sql_query($sql,1);
}

echo 'ok';