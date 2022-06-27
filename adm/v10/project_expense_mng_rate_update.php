<?php
include_once('./_common.php');

if($rate > 100) $rate = 100;
else if($rate <= 0) $rate = 0;
else if(!isset($rate)) $rate = 10;
//$prj_idx, $rate
$sql = " UPDATE {$g5['project_table']} SET prj_mng_rate = '{$rate}' WHERE prj_idx = '{$prj_idx}' ";
sql_query($sql);