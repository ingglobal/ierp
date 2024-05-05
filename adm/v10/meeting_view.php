<?php
$sub_menu = '960265';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

// html_purifier(stripslashes($mtg['mtg_content']));
// html_purifier(stripslashes($mtg['mtg_result']));