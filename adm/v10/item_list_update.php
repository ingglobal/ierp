<?php
$sub_menu = '910200';
include_once('./_common.php');

check_demo();

check_admin_token();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택수정") {

    auth_check($auth[$sub_menu], 'w');

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        if( ! $_POST['ca_id'][$k]) {
            alert("기본분류는 반드시 선택해야 합니다.");
        }
        
        $it = get_table_meta('g5_shop_item','it_id',$_POST['it_id'][$k],'shop_item');

        $sql = "UPDATE {$g5['g5_shop_item_table']} SET
					   ca_id          = '{$_POST['ca_id'][$k]}',
                       it_name        = '{$_POST['it_name'][$k]}',
                       it_price       = '{$_POST['it_price'][$k]}',
                       it_use         = '{$_POST['it_use'][$k]}',
                       it_soldout     = '{$_POST['it_soldout'][$k]}',
                       it_order       = '{$_POST['it_order'][$k]}',
                       it_update_time = '".G5_TIME_YMDHIS."'
                 WHERE it_id = '{$_POST['it_id'][$k]}'
		";
        sql_query($sql,1);
        
        // 상품분리, 제작여부, 원가
        $it_more = serialized_update('it_cart_separate_yn',$_POST['it_cart_separate_yn'][$k],$it['it_more']);
        $it_more = serialized_update('it_make_yn',$_POST['it_make_yn'][$k],$it_more);
        $it_more = serialized_update('it_sales_zero',$_POST['it_sales_zero'][$k],$it_more);
        $it_more = serialized_update('it_price_cost_rate',$_POST['it_price_cost_rate'][$k],$it_more);
        $it_more = serialized_update('it_price_cost',$_POST['it_price_cost'][$k],$it_more);

        $ar['mta_db_table'] = 'shop_item';
        $ar['mta_db_id'] = $_POST['it_id'][$k];
        $ar['mta_key'] = 'it_more';
        $ar['mta_value'] = $it_more;
        meta_update($ar);
        unset($ar);
        
    }
}

goto_url("./item_list.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
?>
