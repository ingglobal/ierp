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

if($act_button == '일괄수정'){ //############################################# 일괄수정 #######################

    foreach($od_id as $i=>$odid){
        $od_price = preg_replace("/,/","", $od_cart_price[$i]);
        $od_time = $od_date[$i].' '.$od_times[$i];
        $osql = " UPDATE {$g5['g5_shop_order_table']} SET
                    od_cart_price = '{$od_price}'
                    , od_time = '{$od_time}'
                WHERE od_id = '{$odid}'
        ";
        sql_query($osql,1);
    }

    foreach($ct_id as $j=>$ctid){
        $ctprice = preg_replace("/,/","", $ct_price[$j]);
        $ctqty = $ct_qty[$j];
        $oi = array_search($ct_od_id[$j],$od_id);
        $ct_time = $od_date[$oi].' '.$od_times[$oi];
        $csql = "UPDATE {$g5['g5_shop_cart_table']} SET
                ct_price = '{$ctprice}'
                , ct_qty = '{$ctqty}'
                , ct_time = '{$ct_time}'
                , ct_select_time = '{$ct_time}'
            WHERE ct_id = '{$ctid}'
        ";
        sql_query($csql,1);
    }
}
else if($act_button == '선택삭제'){ //######################################## 선택삭제 #######################
    foreach($chk as $n=>$i){
        $csql = " DELETE FROM {$g5['g5_shop_cart_table']} WHERE od_id = '{$od_id[$i]}' ";
        sql_query($csql,1);
        $osql = " DELETE FROM {$g5['g5_shop_order_table']} WHERE od_id = '{$od_id[$i]}' ";
        sql_query($osql,1);
    }
}


goto_url('./item_order_list.php?'.$qstr);