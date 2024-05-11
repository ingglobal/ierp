<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

include_once(G5_LIB_PATH.'/latest10.lib.php');

// 게시판을 관리자단에서도 봐야 되서 추가 설정: 디폴트는 _common.php단에서 admin.lib.php 호출
if( $board['gr_id']=='intra') {
    
    // auth_check 같은 함수 때문에 관리자단 admin.lib.php 추가함 (또 다른 문제는 $qstr을 admin.lib.php 에서 초기화한다는 게 문제다. 그래서 하단에 재설정)
    include_once(G5_ADMIN_PATH.'/admin.lib.php');
    include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');
    // 게시판 sub_menu 할당 (원래 게시판에는 sub_menu 변수가 없음)
    $sub_menu = $board['bo_1'];
    // 인트라 게시판은 직원전용
    if ($member['mb_level'] < 4)
        alert('접근이 불가능한 게시판입니다.',G5_URL);
    

    //qstr 조건 추가 { -------------------
    // 공통 qstr
    $qstr .= '&ser_com_idx='.$ser_com_idx.'&fr_date='.$fr_date.'&to_date='.$to_date.'&ser_wr_1='.$ser_wr_1.'&ser_wr_2='.$ser_wr_2.'&ser_wr_10='.$ser_wr_10;
    // 관리자단에서는 admin.lib.php에서 초기화 되므로 common.php에 있었던 부분 재선언
    if (isset($_REQUEST['sca']))  {
        $sca = clean_xss_tags(trim($_REQUEST['sca']));
        if ($sca) {
            $sca = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $sca);
            $qstr .= '&amp;sca=' . urlencode($sca);
        }
    } else {
        $sca = '';
    }

    // AS게시판 관련
    $qstr_tables1 = array('contract');
    if( in_array($bo_table,$qstr_tables1) ) {
        $qstr .= '&ser_wr_5='.$ser_wr_5.'&ser_wr_6='.$ser_wr_6.'&ser_wr_7='.$ser_wr_7;
    }
    // 수정, 간수게시판 관련
    $qstr_tables1 = array('maintain1','maintain2');
    if( in_array($bo_table,$qstr_tables1) ) {
        $qstr .= '&pl_date='.$pl_date.'&ser_mb_name_worker='.$ser_mb_name_worker.'&ser_wr_5='.$ser_wr_5;
    }
    // 작업게시판 관련
    $qstr_tables2 = array('cart1');
    if( in_array($bo_table,$qstr_tables2) ) {
        $qstr .= '&ser_ct_id='.$ser_ct_id;
    }
    // } qstr 조건 추가 -------------------

}
if(G5_IS_MOBILE){
    include_once(G5_USER_ADMIN_MOBILE_PATH.'/admin.head.php');   // 당장은 분리해서 관리할 필요 없음
}else{
    include_once(G5_ADMIN_PATH.'/admin.head.php');
    //include_once(G5_USER_ADMIN_PATH.'/admin.head.php');
}

//안건 알람추가
$alrm_agd = '';
$altable = $g5['write_prefix'].'agenda';
$alsql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$altable} WHERE wr_2 = '{$member['mb_id']}' AND wr_4 NOT IN ('ok') ");
$alcnt = $alsql['cnt'];
if($alcnt){
    $alrm_agd .= '<li id="alrm_agd" class="blink_a" style="display:none;">'.PHP_EOL;
    $alrm_agd .= '<a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=agenda" class="txt_redblink" style="color:orange;">'.PHP_EOL;
    $alrm_agd .= '안건확인요망';
    $alrm_agd .= '</a>'.PHP_EOL;
    $alrm_agd .= '</li>'.PHP_EOL;
}
echo $alrm_agd;

//차량정비 알람추가(cmbn = car_mng_board_notice)
$cmbn_result = sql_fetch(" SELECT bo_notice FROM {$g5['board_table']} WHERE bo_table = 'car_mng' ");
$cmbn = explode(',',$cmbn_result['bo_notice']);
$cmtable = $g5['write_prefix'].'car_mng';
foreach($cmbn as $cnv){
    $ncmd_sql = " SELECT wr_id,ca_name,wr_1 FROM {$cmtable} WHERE wr_id = '{$cnv}' ";
    $ncmd_rst = sql_fetch($ncmd_sql);
    if($ncmd_rst['wr_1']){
        $cmdt_alarm = strtotime(get_dayAddDate($ncmd_rst['wr_1'],-7));
        $cmdt_next = strtotime(get_dayAddDate($ncmd_rst['wr_1'],7));
        $cmdt_today = strtotime(G5_TIME_YMD);
        if($cmdt_today >= $cmdt_alarm && $cmdt_today <= $cmdt_next){
            echo '<li class="car_alm blink_a" style="display:none;">'.PHP_EOL;
            echo '<a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=car_mng&wr_id='.$ncmd_rst['wr_id'].'" class="txt_redblink" style="color:skyblue;">'.PHP_EOL;
            echo $ncmd_rst['ca_name'].'(관리요망)';
            echo '</a>'.PHP_EOL;
            echo '</li>'.PHP_EOL;
        }
    }
}

//특근 알람추가
$alrm_ovt = '';
$ovt_table = $g5['write_prefix'].'overtime';
$ovtsql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$ovt_table} WHERE wr_mb_id_approver = '{$member['mb_id']}' AND wr_apply_status = 'pending'  ");
$ovtcnt = $ovtsql['cnt'];
if($ovtcnt){
    $alrm_ovt .= '<li id="alrm_ovt" class="blink_a" style="display:none;">'.PHP_EOL;
    $alrm_ovt .= '<a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=overtime" class="txt_redblink" style="color:yellow;">'.PHP_EOL;
    $alrm_ovt .= '특근신청확인';
    $alrm_ovt .= '</a>'.PHP_EOL;
    $alrm_ovt .= '</li>'.PHP_EOL;
}
echo $alrm_ovt;

//연차 알람추가
$alrm_dof = '';
$dof_table = $g5['write_prefix'].'dayoff';
$dofsql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$dof_table} WHERE wr_mb_id_approver = '{$member['mb_id']}' AND wr_apply_status = 'pending'  ");
$dofcnt = $dofsql['cnt'];
if($dofcnt){
    $alrm_dof .= '<li id="alrm_dof" class="blink_a" style="display:none;">'.PHP_EOL;
    $alrm_dof .= '<a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=dayoff" class="txt_redblink" style="color:pink;">'.PHP_EOL;
    $alrm_dof .= '연차신청확인';
    $alrm_dof .= '</a>'.PHP_EOL;
    $alrm_dof .= '</li>'.PHP_EOL;
}
echo $alrm_dof;

//공지 알람추가
$alrm_not = '';
$not_table = $g5['write_prefix'].'notice1';
$notsql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$not_table} WHERE wr_datetime >= date_add(now(), interval -2 day)  ");
$notcnt = $notsql['cnt'];
if($notcnt){
    $alrm_not .= '<li id="alrm_not" class="blink_a" style="display:none;">'.PHP_EOL;
    $alrm_not .= '<a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=notice1" class="txt_redblink" style="color:pink;">'.PHP_EOL;
    $alrm_not .= '공지확인';
    $alrm_not .= '</a>'.PHP_EOL;
    $alrm_not .= '</li>'.PHP_EOL;
}
echo $alrm_not;

//AS 알람추가
$alrm_as = '';
$as_table = $g5['write_prefix'].'as';
$assql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$as_table} WHERE wr_6 = '{$member['mb_id']}' AND wr_10 = 'receipt'  ");
$ascnt = $assql['cnt'];
if($ascnt){
    $alrm_as .= '<li id="alrm_as" class="blink_a" style="display:none;">'.PHP_EOL;
    $alrm_as .= '<a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=as" class="txt_redblink" style="color:yellow;">'.PHP_EOL;
    $alrm_as .= 'AS관리확인';
    $alrm_as .= '</a>'.PHP_EOL;
    $alrm_as .= '</li>'.PHP_EOL;
}
echo $alrm_as;

//기안서 알람추가
$alrm_drf = '';
$drf_table = $g5['draft_table'];
$drfsql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$drf_table} WHERE mb_id_approval = '{$member['mb_id']}' AND drf_status IN ('pending','repending')  ");
$drfcnt = $drfsql['cnt'];
if($drfcnt){
    $alrm_drf .= '<li id="alrm_drf" class="blink_a" style="display:none;">'.PHP_EOL;
    $alrm_drf .= '<a href="'.G5_USER_ADMIN_URL.'/draft_list.php" class="txt_redblink" style="color:pink;">'.PHP_EOL;
    $alrm_drf .= '기안서신청확인';
    $alrm_drf .= '</a>'.PHP_EOL;
    $alrm_drf .= '</li>'.PHP_EOL;
}
echo $alrm_drf;

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// jquery-ui css 
add_stylesheet('<link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" rel="stylesheet" />', 0);

echo PHP_EOL;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>
var g5_user_admin_url = "<?php echo G5_USER_ADMIN_URL; ?>";
var g5_user_admin_ajax_url = "<?php echo G5_USER_ADMIN_AJAX_URL; ?>";
var dta_types = ['타입명','온도','토크','전류','전압','진동','소리','습도','압력','속도'];

$(function(){
    // Test db display, Need to know what DB is using.
    <?php
    if(G5_MYSQL_DB!='ingglobal_erp') {
        echo "$('#ft p').prepend('<span style=\"color:darkorange;\">".G5_MYSQL_DB."</span>');";
    }
    ?>
    if($('.blink_a').length > 0){
        $('<div id="notice_box"><div id="bell"><i class="fa fa-bell-o" aria-hidden="true"></i><span>'+$('.blink_a').length+'</span></div><ul class="notice_ul"></ul></div>').appendTo('body');
        $('#bell').on('click',function(){$('#notice_box').toggleClass('focus');});
    }


    if($('#alrm_agd').length > 0){
        $('#alrm_agd').prependTo('#notice_box > ul.notice_ul').show();
    }
	
	if($('#alrm_ovt').length > 0){
        $('#alrm_ovt').prependTo('#notice_box > ul.notice_ul').show();
    }
	
	if($('#alrm_as').length > 0){
        $('#alrm_as').prependTo('#notice_box > ul.notice_ul').show();
    }

    if($('#alrm_dof').length > 0){
        $('#alrm_dof').prependTo('#notice_box > ul.notice_ul').show();
    }
	
    if($('.car_alm').length > 0){
        $('.car_alm').prependTo('#notice_box > ul.notice_ul').show();
    }

    if($('#alrm_not').length > 0){
        $('#alrm_not').prependTo('#notice_box > ul.notice_ul').show();
    }

    if($('#alrm_drf').length > 0){
        $('#alrm_drf').prependTo('#notice_box > ul.notice_ul').show();
    }
});
</script>
<?php
//모달관련
if(is_file(G5_USER_ADMIN_MODAL_CSS_PATH.'/default_modal.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_MODAL_CSS_URL.'/default_modal.css">',0);
if(is_file(G5_USER_ADMIN_MODAL_CSS_PATH.'/'.$g5['file_name'].'_modal.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_MODAL_CSS_URL.'/'.$g5['file_name'].'_modal.css">',0);
@include_once(G5_USER_ADMIN_MODAL_PATH.'/'.$g5['file_name'].'_modal.php');