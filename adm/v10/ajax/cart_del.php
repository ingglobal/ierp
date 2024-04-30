<?php
include_once('./_common.php');

/*
$ct_id
$od_id
*/
$msg = 'ok';
// 지출분배정보 삭제
// $sqld = " DELETE FROM {$g5['project_purchase_divide_table']} WHERE
//     ppd_idx = '{$ppd_idx}'
// ";
// sql_query($sqld,1);

$ct = sql_fetch(" SELECT * FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '{$ct_id}' ");
$od = sql_fetch(" SELECT * FROM {$g5['g5_shop_order_table']} WHERE od_id = '{$od_id}' ");

if(!$ct['ct_id'])
    $msg = '주문바구니정보가 존재하지 않습니다.';

if(!$od['od_id'])
    $msg = '주문정보가 존재하지 않습니다.';

if($ct['ct_id'] && $od['od_id']){
    if($od['od_cart_count'] > 1){
        $ct_price = $ct['ct_price'] * $ct['ct_qty'];
        $osql = " UPDATE {$g5['g5_shop_order_table']} SET
                    od_cart_count = od_cart_count - 1
                    , od_cart_price = od_cart_price - '{$ct_price}'
                WHERE od_id = '{$od_id}'  
        ";
        sql_query($osql,1);

        $csql = " DELETE FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '{$ct_id}' ";
        sql_query($csql,1);
    }
    else{
        $csql = " DELETE FROM {$g5['g5_shop_cart_table']} WHERE ct_id = '{$ct_id}' ";
        sql_query($csql,1);
        $osql = " DELETE FROM {$g5['g5_shop_order_table']} WHERE od_id = '{$od_id}' ";
        sql_query($osql,1);
    }
}

echo $msg;