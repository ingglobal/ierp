<?php
/*
$notice = '';
if (isset($_POST['notice']) && $_POST['notice']) {
    $notice = $_POST['notice'];
}
//회원 자신이 쓴글을 수정할 경우 공지가 풀리는 경우가 있음 
if($w =='u' && !$is_admin && $board['bo_notice'] && in_array($wr['wr_id'], $notice_array)){
    $notice = 1;
}

if ($w == '') {
    if ($notice) {
        $bo_notice = $wr_id.($board['bo_notice'] ? ",".$board['bo_notice'] : '');
        sql_query(" update {$g5['board_table']} set bo_notice = '{$bo_notice}' where bo_table = '{$bo_table}' ");
    }
}else if($w == 'u') {
    $bo_notice = board_notice($board['bo_notice'], $wr_id, $notice);
    sql_query(" update {$g5['board_table']} set bo_notice = '{$bo_notice}' where bo_table = '{$bo_table}' ");
}

// 게시판의 공지사항을 , 로 구분하여 업데이트 한다.
function board_notice($bo_notice, $wr_id, $insert=false)
{
    $notice_array = explode(",", trim($bo_notice));

    if($insert && in_array($wr_id, $notice_array))
        return $bo_notice;

    $notice_array = array_merge(array($wr_id), $notice_array);
    $notice_array = array_unique($notice_array);
    foreach ($notice_array as $key=>$value) {
        if (!trim($value))
            unset($notice_array[$key]);
    }
    if (!$insert) {
        foreach ($notice_array as $key=>$value) {
            if ((int)$value == (int)$wr_id)
                unset($notice_array[$key]);
        }
    }
    return implode(",", $notice_array);
}

echo $ca_name;
echo "<br>";
echo $wr_id;
echo "<br>";
echo $notice;
echo "<br>";
*/
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
//exit;