<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

print_r2($_POST);