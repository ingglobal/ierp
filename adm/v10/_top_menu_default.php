<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 운영권한이 있는 사람에게만 보임
if($member['mb_manager_yn']) {
    $sub_title_list = '
        <a href="./stat_list.php" class="btn_top_menu '.$active_stat_list.'">통계</a>
        <a href="'.G5_USER_ADMIN_URL.'/config_form.php" class="btn_top_menu '.$active_config_form.'">솔루션설정</a>
    ';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
    <a href="'.G5_BBS_URL.'/board.php?bo_table=notice1" class="btn_top_menu '.$active_board.'">공지사항</a>
    '.$sub_title_list.'
    <a href="./item_list.php" class="btn_top_menu '.$active_item_list.'">부품관리</a>
</h2>
';
$g5['container_sub_title'] = '';
?>
