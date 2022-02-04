<?php
$wr_1 = ($wr_1_date) ? $wr_1_date : '0000-00-00';
$wr_1 .= ' '.$wr_1_time;

$wr_2 = ($wr_2_date) ? $wr_2_date : '0000-00-00';
$wr_2 .= ' '.$wr_2_time;


if($calendar){
    $calendar_url = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;calendar='.$calendar.'&amp;target_dt='.$target_dt;
}else{
    ;
}