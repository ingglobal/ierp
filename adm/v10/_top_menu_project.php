<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
if(G5_IS_MOBILE){
	$g5['container_sub_title'] = '
	<h2 id="container_sub_title">
		<a href="./project_gantt.php" class="btn_top_menu '.$active_project_gantt.'">프로젝트일정</a>
		<a href="./project_schedule_list.php" class="btn_top_menu '.$active_project_list.'">프로젝트일정항목관리</a>
	</h2>
	';
}else{
	$g5['container_sub_title'] = '
	<h2 id="container_sub_title">
		<a href="./project_gantt.php" class="btn_top_menu '.$active_project_gantt.'">프로젝트일정</a>
		<a href="./project_schedule_list.php" class="btn_top_menu '.$active_project_list.'">프로젝트일정항목관리</a>
	</h2>
	';
	/*
	$g5['container_sub_title'] = '
	<h2 id="container_sub_title">
		<a href="./project_gantt.php" class="btn_top_menu '.$active_project_gantt.'">프로젝트일정</a>
		<a href="./project_schedule_list.php" class="btn_top_menu '.$active_project_list.'">프로젝트일정항목관리</a>
		<a href="./project_search.php" class="btn_top_menu '.$active_project_search.'">일정미할당사원검색</a>
	</h2>
	';
	*/
}
?>
