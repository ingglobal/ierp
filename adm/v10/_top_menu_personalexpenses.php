me<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./personal_expenses_list.php" class="btn_top_menu '.$active_personal_expenses_list.'">개인지출내역</a>
	<a href="./personal_expenses_month_list.php" class="btn_top_menu '.$active_personal_expenses_month_list.'">개인지출월별통계</a>
	<a href="./personal_expcar_month_list.php" class="btn_top_menu '.$active_personal_expcar_month_list.'">개인월별전체통계</a>
</h2>
';
?>
