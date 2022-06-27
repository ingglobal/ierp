<?php
define('G5_IS_ADMIN', true);
define('G5_IS_V01', true);
include_once ('../../../common.php');
//
if ($member['mb_level'] < 4)
    alert('승인된 회원만 접근 가능합니다.',G5_URL);

if (!defined('G5_USE_SHOP') || !G5_USE_SHOP)
    die('<p>쇼핑몰 설치 후 이용해 주십시오.</p>');

include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');

?>