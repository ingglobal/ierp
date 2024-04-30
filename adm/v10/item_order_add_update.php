<?php
$sub_menu = '960226';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

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

/*
[a_od_id] => 2024042620072437
[a_com_idx] => 1
[a_com_name] => TESTTECH
[a_it_id] => 1713342982
[a_it_price] => 2,000,000
[a_it_rate] => 8
[a_ct_price] => 1,840,000
[a_ct_cnt] => 2
[a_od_price] => 3,680,000
*/

$osql = " SELECT * FROM {$g5['g5_shop_order_table']} WHERE od_id = '{$a_od_id}' ";
$od = sql_fetch($osql);

$isql = " SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = '{$a_it_id}' ";
$it = sql_fetch($isql);



if($od['od_id'] && $it['it_id']){
    $a_ct_price = preg_replace("/,/","",$a_ct_price);

    // 장바구니 입력
    $sql = " INSERT INTO {$g5['g5_shop_cart_table']} SET
                od_id = '".$a_od_id."'
                , com_idx = '".$a_com_idx."'
                , mb_id = '".$member['mb_id']."'
                , it_id = '".$a_it_id."'
                , it_name = '".$it['it_name']."'
                , ct_status = '주문'
                , ct_price = '".$a_ct_price."'
                , ct_option = '".$it['it_name']."'
                , ct_qty = '".$a_ct_cnt."'
                , ct_notax = '".$it['it_notax']."'
                , ct_time = '".$od['od_time']."'
                , ct_ip = '".$_SERVER['HTTP_X_FORWARDED_FOR']."'
                , ct_select = '1'
                , ct_select_time = '".$od['od_time']."'
    ";
    // echo $sql;exit;
    sql_query($sql,1);
    $ct_id = sql_insert_id();

    $od_price = $a_ct_price * $a_ct_cnt;

    $osql = " UPDATE {$g5['g5_shop_order_table']} SET 
                od_cart_count = od_cart_count + 1
                , od_cart_price = od_cart_price + '{$od_price}'
            WHERE od_id = '{$a_od_id}'
    ";
    sql_query($osql,1);
}
else{
    if($od['od_id'])
        alert('주문정보가 존재하지 않습니다.');
    
    if($it['it_id'])
        alert('제품정보가 존재하지 않습니다.');
}



goto_url('./item_order_list.php?'.$qstr);