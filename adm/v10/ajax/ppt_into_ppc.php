<?php
include_once('./_common.php');

/*
$ppc_idx
$ppt_idxs
$ppt_prices
*/
$ppt_arr = ($ppt_idxs) ? explode(',',$ppt_idxs) : array();

//$ppt_prices값을 $ppc_idx의 기존값에 더하기
$pcsql = " UPDATE {$g5['project_purchase_table']} SET ppc_price = ppc_price + {$ppt_prices} WHERE ppc_idx = '{$ppc_idx}' ";
sql_query($pcsql,1);

foreach($ppt_arr as $ppt_idx){
    $sql = " UPDATE {$g5['project_purchase_tmp_table']} SET
                ppc_idx = '{$ppc_idx}'
            WHERE ppt_idx = '{$ppt_idx}'
    ";
    sql_query($sql,1);
}

echo 'ok';