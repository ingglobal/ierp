<?php
//wr_agd_chkdt
//print_r2($_POST);exit;
//wr_agd_pic
if($w == ''){
    $wr_4 = 'pending';
}
else if($w == 'u'){
    if($wr_4 == 'ok'){
        $wr_6 = G5_TIME_YMDHIS;
        //$wr_4 = 'ok';
    }else{
        $wr_6 = '';
        //$wr_4 = 'check';
    }
    //print_r2($_POST);exit;
}
