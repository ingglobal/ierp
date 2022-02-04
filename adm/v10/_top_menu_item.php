<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
if(!G5_IS_MOBILE){
	$g5['container_sub_title'] = '
	<h2 id="container_sub_title">
		<a href="./order_cart.php" class="btn_top_menu '.$active_order_cart.'">견적목록보기</a>
		<a href="./itemlist.php" class="btn_top_menu '.$active_itemlist.'">부품목록</a>
		<a href="./order_list.php" class="btn_top_menu '.$active_order_list.'">부품견적내역</a>
	</h2>
	';
}else{
	$g5['container_sub_title'] = '';
}
?>
