<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

// 최고관리자인 경우만
if($member['mb_6']==1) {
    // $sub_title_list = '
    //     <a href="./configform.php" class="btn_top_menu '.$active_term_list.'">쇼핑몰설정</a>
    // ';
    $sub_title_list = '';
}

// $g5['container_sub_title'] = '
// <h2 id="container_sub_title">
// 	<a href="./item_sell_list.php" class="btn_top_menu '.$active_item_sell_list.'">판매상품관리</a>
// 	<a href="./item_order_cart.php" class="btn_top_menu '.$active_item_order_cart.'">주문바구니</a>
// 	<a href="./item_order_list.php" class="btn_top_menu '.$active_item_order_list.'">판매내역관리</a>
// 	'.$sub_title_list.'
// </h2>
// ';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./item_sell_list.php" class="btn_top_menu '.$active_item_sell_list.'">판매상품관리</a>
	<a href="./item_order_list.php" class="btn_top_menu '.$active_item_order_list.'">판매내역관리</a>
	<a href="./categorylist.php" class="btn_top_menu '.$active_categorylist.'">상품분류관리</a>
	'.$sub_title_list.'
</h2>
';
?>