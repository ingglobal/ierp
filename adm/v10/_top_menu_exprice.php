<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./project_expense_list.php" class="btn_top_menu '.$active_project_expense_list.'">지출관리(과제별)</a>
	<a href="./project_expensedivid_list.php" class="btn_top_menu '.$active_project_expensedivid_list.'">지출관리(각지출별)</a>
</h2>
';
?>
