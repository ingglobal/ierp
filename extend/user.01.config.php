<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


/***************************
    환경설정 변수, 상수
***************************/
define('G5_USER_VER', '1.0');

// 공통 변수, 상수 선언
define('G5_USER_DIR',               'user');
define('G5_USER_ADMIN_DIR',         'v10');
define('G5_AJAX_DIR',               'ajax');
define('G5_HOOK_DIR',               'hook');
define('G5_DEFAULT_COUNTRY',        'ko_KR');   //디폴트 국가_언어
define('G5_USER_PATH',              G5_PATH.'/'.G5_USER_DIR);
define('G5_USER_URL',               G5_URL.'/'.G5_USER_DIR);
define('G5_USER_THEME_PATH',        G5_THEME_PATH.'/'.G5_USER_DIR);
define('G5_USER_THEME_URL',         G5_THEME_URL.'/'.G5_USER_DIR);
define('G5_USER_THEME_CSS_PATH',    G5_THEME_PATH.'/'.G5_USER_DIR.'/'.G5_CSS_DIR);
define('G5_USER_THEME_CSS_URL',     G5_THEME_URL.'/'.G5_USER_DIR.'/'.G5_CSS_DIR);
define('G5_USER_THEME_IMG_PATH',    G5_THEME_PATH.'/'.G5_USER_DIR.'/'.G5_IMG_DIR);
define('G5_USER_THEME_IMG_URL',     G5_THEME_URL.'/'.G5_USER_DIR.'/'.G5_IMG_DIR);
define('G5_USER_THEME_JS_PATH',     G5_THEME_PATH.'/'.G5_USER_DIR.'/'.G5_JS_DIR);
define('G5_USER_THEME_JS_URL',      G5_THEME_URL.'/'.G5_USER_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_PATH',        G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR);
define('G5_USER_ADMIN_URL',         G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR);
define('G5_USER_ADMIN_AJAX_URL',    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_AJAX_DIR);
define('G5_USER_ADMIN_CSS_PATH',    G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_CSS_URL',     G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_IMG_PATH',    G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_IMG_URL',     G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_JS_PATH',     G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_JS_URL',      G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_SQLS_PATH',   G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.'sqls');
define('G5_USER_ADMIN_SQLS_URL',    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.'sqls');
define('G5_USER_ADMIN_MOBILE_PATH', G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR);
define('G5_USER_ADMIN_MOBILE_URL',  G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR);
define('G5_USER_ADMIN_MOBILE_AJAX_PATH', G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_AJAX_DIR);
define('G5_USER_ADMIN_MOBILE_AJAX_URL',  G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_AJAX_DIR);
define('G5_USER_ADMIN_MOBILE_CSS_PATH', G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_MOBILE_CSS_URL',  G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_MOBILE_IMG_PATH', G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_MOBILE_IMG_URL',  G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_MOBILE_JS_PATH', G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_MOBILE_JS_URL',  G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_MOBILE_LIB_PATH', G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_LIB_DIR);
define('G5_USER_ADMIN_MOBILE_LIB_URL',  G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_LIB_DIR);
define('G5_USER_CSS_PATH',          G5_USER_PATH.'/'.G5_CSS_DIR);
define('G5_USER_CSS_URL',           G5_USER_URL.'/'.G5_CSS_DIR);
define('G5_USER_JS_PATH',           G5_USER_PATH.'/'.G5_JS_DIR);
define('G5_USER_JS_URL',            G5_USER_URL.'/'.G5_JS_DIR);
define('G5_USER_IMG_URL',           G5_USER_URL.'/'.G5_IMG_DIR);
define('G5_USER_AJAX_URL',          G5_USER_URL.'/'.G5_AJAX_DIR);
define('G5_USER_MOBILE_PATH',       G5_USER_PATH.'/'.G5_MOBILE_DIR);
define('G5_USER_MOBILE_URL',        G5_USER_URL.'/'.G5_MOBILE_DIR);
define('G5_USER_MOBILE_SKIN_PATH',  G5_USER_PATH.'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR);
define('G5_USER_MOBILE_SKIN_URL',   G5_USER_URL.'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR);


// 테이블 정의
define('USER_TABLE_PREFIX',         G5_TABLE_PREFIX.'5_');
$g5['setting_table']                = USER_TABLE_PREFIX.'setting';
$g5['meta_table']                   = USER_TABLE_PREFIX.'meta';
$g5['tally_table']                  = USER_TABLE_PREFIX.'tally';
$g5['term_table']                   = USER_TABLE_PREFIX.'term';
$g5['term_relation_table']          = USER_TABLE_PREFIX.'term_relation';
$g5['ymd_table']                    = USER_TABLE_PREFIX.'ymd';
$g5['file_table']                   = USER_TABLE_PREFIX.'file';
$g5['user_log_table']               = USER_TABLE_PREFIX.'user_log';

?>
