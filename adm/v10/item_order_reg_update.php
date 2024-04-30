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
// print_r2($_POST);exit;
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
$od_pwd = $member['mb_password'];
$od_name = $member['mb_name'];
$a_od_price = preg_replace("/,/","",$a_od_price);
// 주문서에 입력
$sql = " INSERT {$g5['g5_shop_order_table']}
set od_id             = '$a_od_id',
    mb_id             = '{$member['mb_id']}',
    od_pwd            = '$od_pwd',
    od_name           = '$od_name',
    od_email          = '',
    od_tel            = '',
    od_hp             = '',
    od_zip1           = '',
    od_zip2           = '',
    od_addr1          = '',
    od_addr2          = '',
    od_addr3          = '',
    od_addr_jibeon    = '',
    od_b_name         = '',
    od_b_tel          = '',
    od_b_hp           = '',
    od_b_zip1         = '',
    od_b_zip2         = '',
    od_b_addr1        = '',
    od_b_addr2        = '',
    od_b_addr3        = '',
    od_b_addr_jibeon  = '',
    od_deposit_name   = '',
    od_memo           = '',
    od_cart_count     = '$a_ct_cnt',
    od_cart_price     = '$a_od_price ',
    od_cart_coupon    = '',
    od_send_cost      = '',
    od_send_coupon    = '',
    od_send_cost2     = '',
    od_coupon         = '',
    od_receipt_price  = '',
    od_receipt_point  = '',
    od_bank_account   = '',
    od_receipt_time   = '',
    od_misu           = '',
    od_pg             = '',
    od_tno            = '',
    od_app_no         = '',
    od_escrow         = '',
    od_tax_flag       = '',
    od_tax_mny        = '',
    od_vat_mny        = '',
    od_free_mny       = '',
    od_status         = '주문',
    od_shop_memo      = '',
    od_hope_date      = '',
    od_time           = '".G5_TIME_YMDHIS."',
    od_ip             = '$REMOTE_ADDR',
    od_settle_case    = '',
    od_test           = '',
    mb_id_saler       = '{$member['mb_id']}',
    com_idx           = '{$a_com_idx}'
";
sql_query($sql, false);



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
}
else{
    if($od['od_id'])
        alert('주문정보가 존재하지 않습니다.');
    
    if($it['it_id'])
        alert('제품정보가 존재하지 않습니다.');
}



goto_url('./item_order_list.php?'.$qstr);