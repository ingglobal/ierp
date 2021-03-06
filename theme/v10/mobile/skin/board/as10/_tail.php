<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

if (!defined('G5_IS_ADMIN'))
    include_once(G5_PATH.'/_tail.php'); // 관리자단이 아닐 때만 추가

// 게시판 관리의 하단 파일 경로
if (G5_IS_MOBILE) {
    echo html_purifier(stripslashes($board['bo_mobile_content_tail']));
    // 모바일의 경우 설정을 따르지 않는다.
    include_once(G5_BBS_PATH.'/_tail.php');
} else {
    // 관리자단이면 경로 재설정
    if (defined('G5_IS_ADMIN')) {
        //echo substr($board['bo_include_tail'], strpos($board['bo_include_tail'],'/adm/')+4).'<br>';
        $board['bo_include_tail'] = G5_ADMIN_PATH.substr($board['bo_include_tail'], strpos($board['bo_include_tail'],'/adm/')+4);
        
    }
    echo html_purifier(stripslashes($board['bo_content_tail']));
    if(is_include_path_check($board['bo_include_tail'])) {  //파일경로 체크
        @include ($board['bo_include_tail']);
    } else {    //파일경로가 올바르지 않으면 기본파일을 가져옴
        include_once(G5_BBS_PATH.'/_tail.php');
    }
}
?>