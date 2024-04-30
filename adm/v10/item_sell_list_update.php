<?php
$sub_menu = '960226';
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


        $p_ca_id = is_array($_POST['ca_id']) ? strip_tags($_POST['ca_id'][$k]) : '';
        $p_ca_id2 = is_array($_POST['ca_id2']) ? strip_tags($_POST['ca_id2'][$k]) : '';
        $p_ca_id3 = is_array($_POST['ca_id3']) ? strip_tags($_POST['ca_id3'][$k]) : '';
        $p_com_id = is_array($_POST['com_id']) ? strip_tags($_POST['com_id'][$k]) : '';
        $p_it_name = is_array($_POST['it_name']) ? strip_tags(clean_xss_attributes($_POST['it_name'][$k])) : '';
        $p_it_cust_price = is_array($_POST['it_cust_price']) ? preg_replace("/,/","",$_POST['it_cust_price'][$k]) : '';
        $p_it_price = is_array($_POST['it_price']) ? preg_replace("/,/","",$_POST['it_price'][$k]) : '';
        $p_it_buy_price = is_array($_POST['it_buy_price']) ? preg_replace("/,/","",$_POST['it_buy_price'][$k]) : '';
        $p_it_stock_qty = is_array($_POST['it_stock_qty']) ? preg_replace("/,/","",$_POST['it_stock_qty'][$k]) : '';
        $p_it_skin = is_array($_POST['it_skin']) ? strip_tags($_POST['it_skin'][$k]) : '';
        $p_it_mobile_skin = is_array($_POST['it_mobile_skin']) ? strip_tags($_POST['it_mobile_skin'][$k]) : '';
        $p_it_use = is_array($_POST['it_use']) ? strip_tags($_POST['it_use'][$k]) : '';
        $p_it_soldout = is_array($_POST['it_soldout']) ? strip_tags($_POST['it_soldout'][$k]) : '';
        $p_it_order = is_array($_POST['it_order']) ? strip_tags($_POST['it_order'][$k]) : '';
        $p_it_notax = is_array($_POST['it_notax']) ? strip_tags($_POST['it_notax'][$k]) : 0;

        $sql = "update {$g5['g5_shop_item_table']}
                   set ca_id          = '".sql_real_escape_string($p_ca_id)."',
                       ca_id2         = '".sql_real_escape_string($p_ca_id2)."',
                       ca_id3         = '".sql_real_escape_string($p_ca_id3)."',
                       com_idx        = '".sql_real_escape_string($p_com_id)."',
                       it_name        = '".$p_it_name."',
                       it_price       = '".sql_real_escape_string($p_it_price)."',
                       it_cust_price  = '".sql_real_escape_string($p_it_cust_price)."',
                       it_buy_price  = '".sql_real_escape_string($p_it_buy_price)."',
                       it_skin        = '".sql_real_escape_string($p_it_skin)."',
                       it_mobile_skin = '".sql_real_escape_string($p_it_mobile_skin)."',
                       it_use         = '".sql_real_escape_string($p_it_use)."',
                       it_notax         = '".sql_real_escape_string($p_it_notax)."',
                       it_stock_qty     = '".sql_real_escape_string($p_it_stock_qty)."',
                       it_soldout     = '".sql_real_escape_string($p_it_soldout)."',
                       it_order       = '".sql_real_escape_string($p_it_order)."',
                       it_update_time = '".G5_TIME_YMDHIS."'
                 where it_id   = '".preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k])."' ";

        sql_query($sql,1);
        // echo $sql.'<br>';

		if( function_exists('shop_seo_title_update') ) shop_seo_title_update(preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]), true);
    }
    // exit;
    goto_url("./item_sell_list.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
} else if ($_POST['act_button'] == "선택삭제") {
    // alert($super_admin.'입니다.');
    if (!$super_admin)
        alert('제품 삭제는 최고관리자만 가능합니다.');

    auth_check($auth[$sub_menu], 'w');

    // _ITEM_DELETE_ 상수를 선언해야 itemdelete.inc.php 가 정상 작동함
    define('_ITEM_DELETE_', true);

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        // include 전에 $it_id 값을 반드시 넘겨야 함
        $it_id = preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]);
        include ('./itemdelete.inc.php');
    }
    
    goto_url("./item_sell_list.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
} else if ($_POST['act_button'] == "선택담기") {

    auth_check($auth[$sub_menu], 'w');
    // exit;
    // 보관기간이 지난 상품 삭제
	cart_item_clean();
    
    
    for ($i=0; $i<count($_POST['chk']); $i++){
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        
        $p_sell_qty = is_array($_POST['sell_qty']) ? preg_replace("/,/","",$_POST['sell_qty'][$k]) : '';

        if( !$_POST['ca_id'][$k] || !$_POST['seller_idx'][$k])
            continue;
        
        
            
        // cart id 설정
        set_cart_id($sw_direct);

        if($sw_direct)
            $tmp_cart_id = get_session('ss_cart_direct');
        else
            $tmp_cart_id = get_session('ss_cart_id');

        // 상품정보
        $it1 = get_table('g5_shop_item','it_id',$_POST['it_id'][$k]);

        // 동일옵션의 상품이 있으면 에러
        $sql2 = " select ct_id, ct.com_idx, cr.com_name, io_type, ct_qty
            from {$g5['g5_shop_cart_table']} ct
            left join {$g5['companyreseller_table']} cr ON ct.com_idx = cr.com_idx
            where od_id = '$tmp_cart_id'
                    and it_id = '{$_POST['it_id'][$k]}'
                    and io_id = '$io_id'
                    and ct.com_idx != '0'
                    and ct_status = '쇼핑' ";
        $row2 = sql_fetch($sql2,1);

        if($row2['com_idx'] && $_POST['seller_idx'][$k] != $row2['com_idx']){
            alert('['.$row2['com_name'].'] 업체에 대한 주문만 담아 주세요.');
            exit;
        }

        if($row2['ct_id']) {
            continue;
            //$response->msg = "장바구니에 동일 상품이 존재합니다.";
        }
        else {

            // 장바구니 입력
            $sql = "INSERT INTO {$g5['g5_shop_cart_table']} SET
                od_id = '".$tmp_cart_id."'
                , com_idx = '".$_POST['seller_idx'][$k]."'
                , mb_id = '".$member['mb_id']."'
                , it_id = '".$_POST['it_id'][$k]."'
                , it_name = '".$it1['it_name']."'
                , ct_status = '쇼핑'
                , ct_price = '".$it1['it_price']."'
                , ct_option = '".$it1['it_name']."'
                , ct_qty = '1'
                , ct_notax = '".$it1['it_notax']."'
                , ct_time = '".G5_TIME_YMDHIS."'
                , ct_ip = '".$_SERVER['HTTP_X_FORWARDED_FOR']."'
                , ct_select = '0'
                , ct_select_time = '".G5_TIME_YMDHIS."'
            ";
            sql_query($sql,1);
            $ct_id = sql_insert_id();
            
            //$response->result = true;
            //$response->msg = "장바구니에 담기 성공";
        }
    }
    
    alert('주문목록에 담기 완료, 담긴 제품은 주문바구니보기에서 확인하세요.',"./item_sell_list.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
}

// exit;

?>
