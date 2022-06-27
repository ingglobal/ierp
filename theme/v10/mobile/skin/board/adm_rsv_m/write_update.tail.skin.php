<?php
delete_cache_latest($bo_table);
//print_r2($_POST);
//exit;
if($calendar){
    $target_dt = $_POST['wr_1_date'];
    $calendar_url = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;calendar='.$calendar.'&amp;target_dt='.$target_dt;
    goto_url($calendar_url);
}
else{
    //$redirect_url = run_replace('write_update_move_url', short_url_clean(G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;'.$qstr), $board, $wr_id, $w, $qstr, $file_upload_msg);
    $redirect_url = run_replace('write_update_move_url', short_url_clean(G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;'.$qstr), $board, $wr_id, $w, $qstr, $file_upload_msg);
    if ($file_upload_msg)
        alert($file_upload_msg, $redirect_url);
    else
        goto_url($redirect_url);
}
exit;