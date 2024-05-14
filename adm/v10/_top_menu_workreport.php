<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./workreport_calendar.php" class="btn_top_menu '.$active_workreport_calendar.'">업무보고달력</a>
	<a href="./workreport_list.php" class="btn_top_menu '.$active_workreport_list.'">업무보고리스트</a>
</h2>
';
?>