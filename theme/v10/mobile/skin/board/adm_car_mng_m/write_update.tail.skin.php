<?php

//delete_cache_latest($bo_table);
if($notice){
    $ntc = sql_fetch(" SELECT bo_notice FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}' ");
    $ntc_arr = ($ntc['bo_notice']) ? explode(',',$ntc['bo_notice']) : array();
    $cat_noti_arr = array();
    foreach($ntc_arr as $nv){
        $csql = " SELECT COUNT(*) AS cnt FROM {$write_table} WHERE wr_id = '{$nv}' AND ca_name = '{$ca_name}' ";
        $carr = sql_fetch($csql);
        //echo $carr['cnt']."<br>";//$csql."<br>";//(int)$csql['cnt']."<br>";
        if($carr['cnt']) array_push($cat_noti_arr,$nv);
    }
    //print_r2($ntc_arr);
    //($cat_noti_arr);
    //array_push($cat_noti_arr,3);
    $dff_arr = array_diff($ntc_arr,$cat_noti_arr);
    array_push($dff_arr,$wr_id);
    $add_noti_str = implode(',', $dff_arr);
    sql_query(" UPDATE {$g5['board_table']} SET bo_notice = '{$add_noti_str}' WHERE bo_table = '{$bo_table}' ");
}