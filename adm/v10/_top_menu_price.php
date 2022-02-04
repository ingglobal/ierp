<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./project_group_price_list.php" class="btn_top_menu '.$active_project_group_price_list.'">수입지출관리</a>
	<a href="./project_price_list.php" class="btn_top_menu '.$active_project_price_list.'">수입지출항목관리</a>
</h2>
';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./project_group_price_list.php" class="btn_top_menu '.$active_project_group_price_list.'">수입지출관리</a>
</h2>
';
?>
