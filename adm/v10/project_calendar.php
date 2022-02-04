<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '프로젝트 달력보기';
include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

?>
<style>
</style>


달력보기


<?php
include_once ('./_tail.php');
?>