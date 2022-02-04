<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// 후킹 부분을 정의합니다

add_event('member_login_check','u_member_login_check',10);
function u_member_login_check(){
    global $g5, $mb, $member;

    // for a manager without mb_4, then assign default_com_idx
    if($mb['mb_level']>=6 && !$mb['mb_4']) {
        $com_idx = $g5['setting']['set_com_idx'];
    }
    // for normal member 
    else {
        $com_idx = $mb['mb_4'];
    }

    set_session('ss_com_idx', $com_idx);

    // 로그인 기록을 남겨요.
    $tmp_sql = " insert into {$g5['login_table']} ( lo_ip, mb_id, lo_datetime, lo_location, lo_url ) values ( '".G5_SERVER_TIME."', '{$mb['mb_id']}', '".G5_TIME_YMDHIS."', '".$mb['mb_name']."',  '".$_SERVER['REMOTE_ADDR']."' ) ";
    sql_query($tmp_sql, FALSE);

    send_kosmo_log();
}

?>