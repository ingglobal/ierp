<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';

// 최고관리자인 경우만
if($member['mb_level']>=9) {
    // $sub_title_list = '
    //     <a href="./stat_setting_goal.php" class="btn_top_menu '.$active_stat_setting_goal.'">영업목표설정</a>
    // ';
}

$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./stat_project.php" class="btn_top_menu '.$active_stat_list.'">프로젝트통계</a>
	<a href="./stat_as.php" class="btn_top_menu '.$active_stat_as_list.'">A/S통계</a>
	<a href="./stat_project_price.php" class="btn_top_menu '.$active_stat_project_price_list.'">수입지출통계</a>
	<a href="./stat_sales.php" class="btn_top_menu '.$active_stat_sales_list.'">영업통계</a>
	<a href="./stat_quot.php" class="btn_top_menu '.$active_stat_quot_list.'">견적통계</a>
	<a href="./stat_purchase_sales.php" class="btn_top_menu '.$active_stat_quot_list.'">매입매출통계</a>
	'.$sub_title_list.'
</h2>
';
?>
