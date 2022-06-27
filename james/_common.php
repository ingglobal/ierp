<?php
include_once('../common.php');

// 쇼핑몰 사용 true (기본적으로 쇼핑몰 환경이라고 정의)
define('_SHOP_', true);

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
while( list($key, $val) = each($_REQUEST) ) {
	${$key} = $_REQUEST[$key];
}

// 모바일 접속인 경우
if (G5_IS_MOBILE) {
	$u_skin_path = G5_THEME_PATH.'/mobile';
	$u_skin_url = G5_THEME_URL.'/mobile';
}
// PC인 경우
else {
	$u_skin_path = G5_THEME_PATH;
	$u_skin_url = G5_THEME_URL;
}

// 스킨 경로 정의
$user_skin_path		= $u_skin_path.'/'.G5_USER_DIR;
$user_skin_url		= $u_skin_url.'/'.G5_USER_DIR;
$user_skin_img_path	= $u_skin_path.'/'.G5_USER_DIR.'/img';
$user_skin_img_url	= $u_skin_url.'/'.G5_USER_DIR.'/img';

?>