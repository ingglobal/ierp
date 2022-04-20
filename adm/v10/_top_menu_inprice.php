<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./project_income_list.php" class="btn_top_menu '.$active_project_income_list.'">기타수입(과제별)</a>
	<a href="./project_incomedivid_list.php" class="btn_top_menu '.$active_project_incomedivid_list.'">기타수입(각수입별)</a>
</h2>
';
?>
