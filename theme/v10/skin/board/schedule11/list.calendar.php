<?php
// URL: G5_URL/theme/v10/skin/board/schedule11/list.calendar.php
include_once('./_common.php');

$g5['title'] = $board['bo_subject'];
include_once('./_head.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);

// 초기 입력값 추출 ($month 변수)
$month = ($month)? $month : date('Ym', G5_SERVER_TIME);
$year = substr($month,0,4);
$_month = $year.'-'.substr($month,-2);	// 2018-02와 같이 -가 있는 월변수
$last_month = $month - 1;
$next_month = $month + 1;
// 달력 시작일 (이전달의 마지막주 포함, 1월 재계산 필요)
$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".$year."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".$_month."-01') ORDER BY ymd LIMIT 1 ");
$sql_date_start = $w1['ymd'];
// 달력 종료일 (다음달의 첫주 포함, 12월 재계산 필요)
$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".$year."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = (SELECT LAST_DAY('".$_month."-01')) ) ORDER BY ymd DESC LIMIT 1 ");
$sql_date_end = $w1['ymd'];

// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
$sql_week = " WEEK(ymd,0) AS date_week ";
$sql_group_by = " GROUP BY WEEK(ymd,0) ";
// 01월인 경우
if( substr($month,-2) == '01' ) {
	$last_month = ($year-1).'12';

	// 1월1일의 주차 (보통은 0주차부터 시작, 1월1일이 딱 일요일이면 1주차부터 시작함)
	$w2 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd = '".$year."0101' ");
	if($w2['week_num'] == 1) {
		$sql_date_start = $year.'0101';
	}
	// 1월1일이 일요일 아닌 경우는 작년 마지막 주간의 시작일이 달력 시작일
	else {
		$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".(($year-1))."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".($year-1)."-12-31') ORDER BY ymd LIMIT 1 ");
		$sql_date_start = $w1['ymd'];
	}

	// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
	$sql_week = " if(WEEK(ymd,0) > 50, ".$w2['week_num'].", WEEK(ymd,0)) AS date_week ";
    
    // 작년 마지막 주의 주차수값=52,51 등과 같으므로
    $sql_group_by = " GROUP BY IF(WEEK(ymd,0)<30, WEEK(ymd,0), 0) ";
    
} 
// 12인 경우
if( substr($month,-2) == '12' ) {
	$next_month = ($year+1).'01';

	// 내년 1월1일의 주차 (보통은 0주차부터 시작, 1월1일이 딱 일요일이면 1주차부터 시작함)
	$w2 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd = '".($year+1)."0101' ");
	if($w2['week_num'] == 1) {
		$sql_date_end = $year.'1231';
	}
	// 내년 1월1일이 일요일 아닌 경우는 내년 첫주의 마지막날이 달력 종료일
	else {
		$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".($year+1)."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".($year+1)."-01-01') ORDER BY ymd DESC LIMIT 1 ");
		$sql_date_end = $w1['ymd'];
	}

	// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
	// 12월31일의 주차 (보통은 52주차가 마지막, 1월1일이 딱 일요일이면 마지막 주차가 53)
	$w3 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd LIKE '".$year."1231' ");
	$sql_week = " if(WEEK(ymd,0) < 2, ".$w3['week_num'].", WEEK(ymd,0)) AS date_week ";

    // 내년 첫주의 주차수값=0 이므로 보정해 줘야 함
    $sql_group_by = " GROUP BY IF(WEEK(ymd,0)>30, WEEK(ymd,0), ".$w3['week_num'].") ";
}




// 수입지출 일정 [ ======================================================
if( in_array($member['mb_2'], $g5['set_team_account_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
    $sql = "SELECT prp_idx, prp.prj_idx, prp_type, prp_price, prp_pay_no, prp_plan_date, prp_status
                , prj.prj_name, prj.prj_percent
                , com.com_name
            FROM {$g5['project_price_table']} AS prp
                LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prp.prj_idx
                LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_plan_date BETWEEN '".$_month."-01' AND '".$_month."-31'
    ";
    // echo $sql.'<br>';
    $rs = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($rs); $i++) {
        // echo $row['prp_plan_date'].'<br>';	// 2018-02-03
        $row['href'] = 'javascript:';//G5_USER_ADMIN_URL.'/project_group_price_list.php?sfl=prj_idx&stx='.$row['prj_idx'];
        $day_content[$row['prp_plan_date']] .= '<div class="prp_item prp_'.$row['prp_status'].'" prp_idx="'.$row['prp_idx'].'">'
            .'<span class="prp_type"><i class="fa fa-circle"></i> '.$g5['set_price_type2_value'][$row['prp_type']].'</span>'
            .'<span class="prp_price">'.number_format($row['prp_price']).'</span>'
            .'<div class="prp_com_prj_name"><span class="prp_com_name">'.$row['com_name'].'</span><span class="prp_prj_name"><a href="'.$row['href'].'">'.$row['prj_name'].'</a></span></div>'
        .'</div>';
    }
}

// 프로젝트 일정 [ ======================================================
if( in_array($member['mb_2'], $g5['set_team_system_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
    $sql = "SELECT prs.*
                , mb.mb_name
                , prj.prj_name, prj.prj_percent
                , com.com_name
            FROM {$g5['project_schedule_table']} AS prs
                LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
                LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
            WHERE prs_status NOT IN ('trash','delete','cancel')
                AND (
                    prs_start_date BETWEEN '".$_month."-01' AND '".$_month."-31'
                    OR prs_end_date BETWEEN '".$_month."-01' AND '".$_month."-31'
                    )
    ";
    // echo $sql.'<br>';
    $rs = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($rs); $i++) {
        // echo $row['mb_name'].': '.$row['prs_start_date'].'~'.$row['prs_end_date'].'<br>';	// 2018-02-03
        $row['href'] = 'javascript:';//G5_USER_ADMIN_URL.'/project_gantt.php?sfl=prs.prj_idx&stx='.$row['prj_idx'];
        // 시작일~종료일 돌면서 해당 일자 만들어줌
        // $row['prj_start_time'] = strtotime($row['prs_start_date']);
        $row['prj_start_time'] = (!$row['prs_start_date']||$row['prs_start_date']<$_month.'-01') ? strtotime($_month.'-01') : strtotime($row['prs_start_date']);
        $row['prj_month_count'] = date('t', strtotime($_month."-01"));
        $row['prj_end_time'] = (!$row['prs_end_date']||$row['prs_end_date']>$_month.'-31') ? strtotime($_month.'-'.$row['prj_month_count']) : strtotime($row['prs_end_date']);
        // echo $row['mb_name'].': '.$row['prs_start_date'].'('.$row['prj_start_time'].')~'.$row['prs_end_date'].'('.$row['prj_end_time'].')<br>';	// 2018-02-03
        for($j=$row['prj_start_time'];$j<=$row['prj_end_time'];$j+=86400) {
            $row['prj_start_dyas'] = (int)( (($j-strtotime($row['prs_start_date'])) / 86400)+1 ).'일차';
            // echo $row['mb_name'].': '.date("Y-m-d",$j).' '.$row['prj_start_dyas'].'<br>';	// 2018-02-03

            $day_content[date("Y-m-d",$j)] .= '<div class="prs_item prs_'.$row['prs_status'].'" prs_idx="'.$row['prs_idx'].'">'
                .'<span class="prs_days"><i class="fa fa-circle"></i> '.$row['prj_start_dyas'].'</span>'
                .'<span class="prs_prj_name" title="'.$row['prj_name'].'">'.cut_str($row['prj_name'],9).'</span>'
                .'<div class="prs_name_content"><a href="'.$row['href'].'"><span class="prs_name">'.$row['mb_name'].'</span><span class="prs_content">'.cut_str($row['prs_content'],30).'</span></a></div>'
            .'</div>';
        }
    }
}

// 영업관리 다음일정 추출
if( in_array($member['mb_2'], $g5['set_team_sales_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
    $bo = get_table_meta('board','bo_table','sales');
    // 상태값
    $set_values = explode(',', preg_replace("/\s+/", "", $bo['bo_9']));
    foreach ($set_values as $set_value) {
        list($key, $value) = explode('=', $set_value);
        $g5['set_sales_status_value'][$key] = $value;
    }
    unset($set_values);unset($set_value);

    $sql  = "SELECT *
        FROM g5_write_sales
        WHERE wr_6!='' AND wr_6 BETWEEN '".$_month."-01' AND '".$_month."-31'
        ORDER BY STR_TO_DATE(wr_6, '%Y-%m-%d %H:%i:%s')
    ";
    // echo $sql.'<br>';
    $rs1 = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($rs1); $i++) {
        //echo $row['crj_date'].'<br>';	// 2018-02-03
        $row['mb'] = get_member($row['mb_id'],'mb_name');
        $row['href'] = 'javascript:';//get_pretty_url('sales', $row['wr_id']);
        // $row['wr_subject'] = cut_str($row['wr_subject'],10,'..');
        $day_content[substr($row['wr_6'],0,10)] .= '<div class="sales_item sales_'.$row['wr_10'].'" wr_id="'.$row['wr_id'].'">'
            .'<span class="sales_company"><i class="fa fa-circle"></i> '.$row['wr_1'].'</span>'
            .'<span class="sales_status">['.$g5['set_sales_status_value'][$row['wr_10']].']</span>'
            .'<div class="sales_name_subject"><a href="'.$row['href'].'"><span class="sales_name">'.$row['mb']['mb_name'].'</span><span class="sales_subject">'.$row['wr_subject'].'</span></a></div>'
        .'</div>';
    }
}

// AS관리 다음일정 추출
if( $member['mb_level']>=6 ) {
    $bo = get_table_meta('board','bo_table','as');
    // 상태값
    $set_values = explode(',', preg_replace("/\s+/", "", $bo['bo_10']));
    foreach ($set_values as $set_value) {
        list($key, $value) = explode('=', $set_value);
        $g5['set_as_status_value'][$key] = $value;
    }
    unset($set_values);unset($set_value);

    $sql  = "SELECT *
        FROM g5_write_as
        WHERE wr_facebook_user!='' AND wr_facebook_user BETWEEN '".$_month."-01' AND '".$_month."-31'
        ORDER BY STR_TO_DATE(wr_facebook_user, '%Y-%m-%d %H:%i:%s')
    ";
    // echo $sql.'<br>';
    $rs1 = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($rs1); $i++) {
        //echo $row['crj_date'].'<br>';	// 2018-02-03
        $row['mb'] = get_member($row['mb_id'],'mb_name');
        $row['href'] = 'javascript:';//get_pretty_url('as', $row['wr_id']);
        // $row['wr_subject'] = cut_str($row['wr_subject'],10,'..');
        $day_content[substr($row['wr_facebook_user'],0,10)] .= '<div class="as_item as_'.$row['wr_10'].'" wr_id="'.$row['wr_id'].'">'
            .'<span class="as_company"><i class="fa fa-circle"></i> (AS) '.$row['wr_1'].'</span>'
            .'<span class="as_status">['.$g5['set_as_status_value'][$row['wr_10']].']</span>'
            .'<div class="as_name_subject"><a href="'.$row['href'].'"><span class="as_name">('.$row['wr_5'].')</span><span class="as_subject">'.cut_str($row['wr_subject'],8,'...').'</span></a></div>'
        .'</div>';
    }
}

// 일정리스트 { -------
$sql  = "SELECT *
    FROM ".$g5['write_prefix'].$bo_table."
    WHERE wr_2 BETWEEN '".$_month."-01' AND '".$_month."-31'
        OR wr_6 BETWEEN '".$_month."-01' AND '".$_month."-31'
    ORDER BY STR_TO_DATE(wr_2, '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(wr_3, '%H:%i:%s')
";


//    echo $sql.'<br>';
$result = sql_query($sql,1);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // 날짜 분리
    $row['wr_2_arr'] = date_parse($row['wr_2']);
    $row['wr_6_arr'] = date_parse($row['wr_6']);
    $row['wr_ymd'] = sprintf("%04d",$row['wr_2_arr']['year']).'-'.sprintf("%02d",$row['wr_2_arr']['month']).'-'.sprintf("%02d",$row['wr_2_arr']['day']);
    $row['wr_hi'] = sprintf("%02d",$row['wr_2_arr']['hour']).':'.sprintf("%02d",$row['wr_2_arr']['minute']);
    $row['wr_his'] = sprintf("%02d",$row['wr_2_arr']['hour']).':'.sprintf("%02d",$row['wr_2_arr']['minute']).':'.sprintf("%02d",$row['wr_2_arr']['second']);
    $row['wr_ampm'] = date("A h:i ", strtotime($row['wr_3']));
    $row['wr_ampm2'] = date("A h:i ", strtotime($row['wr_4']));
    // $row['wr_range'] = date("A h:i",strtotime($row['wr_3']));
    // $row['wr_range'] .= ($row['wr_4'])?'~'.date("A h:i",strtotime($row['wr_4'])):'';
    $row['href'] = get_pretty_url($bo_table, $row['wr_id']);

    // 시작일~종료일 돌면서 해당 일자 만들어줌
    // $row['sch_start_time'] = strtotime($row['sch_start_date']);
    $row['sch_start_time'] = strtotime($row['wr_2']);  // timestamp of start day
    $row['sch_end_time'] = ($row['wr_6']) ? strtotime($row['wr_6']) : $row['sch_start_time']+86400-1;
    // echo $row['wr_subject'].': '.$row['wr_2'].'('.$row['sch_start_time'].')~'.$row['wr_6'].'('.$row['sch_end_time'].')<br>';
    for($j=$row['sch_start_time'];$j<=$row['sch_end_time'];$j+=86400) {
        // 시간범위
        $row['wr_range'] = date("A h:i",strtotime($row['wr_3']));
        $row['wr_range'] .= ($row['wr_4'])?'~'.date("A h:i",strtotime($row['wr_4'])):'';
        // 시작일~종료일인 경우 (범위가 존재하는 경우)
        if($row['wr_6']&&$row['wr_2']!=$row['wr_6']) {
            // 일차 표현
            $row['sch_start_dyas'] = (int)( (($j-strtotime($row['wr_2'])) / 86400)+1 ).'일차';
            $row['wr_range'] = $row['sch_start_dyas'];
        }

        $day_content[date("Y-m-d",$j)] .= '<div class="schedule_item schedule_'.$row['wr_9'].'" wr_id="'.$row['wr_id'].'">'
            .'<span class="schedule_range"><i class="fa fa-circle"></i>'.$row['wr_range'].'</span>'
            .'<span class="schedule_subject"><a href="'.$row['href'].'">'.$row['wr_subject'].'</a></span>'
            .'</div>';
    }

    // 일정표현
    // $day_content[substr($row['wr_2'],0,10)] .= '<div class="schedule_item schedule_'.$row['wr_9'].'" wr_id="'.$row['wr_id'].'">'
    //     .'<span class="schedule_range"><i class="fa fa-circle"></i>'.$row['wr_range'].'</span>'
    //     .'<span class="schedule_ymd">'.$row['wr_ymd'].'</span>'
    //     .'<span class="schedule_hi">'.$row['wr_hi'].'</span>'
    //     .'<span class="schedule_his">'.$row['wr_his'].'</span>'
    //     .'<span class="schedule_subject"><a href="'.$row['href'].'">'.$row['wr_subject'].'</a></span>'
    // .'</div>';
}
//print_r2($day_content);
// } 일정리스트 -------





                    
//-- 달력 디비 추출 --//
// 달력 추출 ======================================================
$sql = "SELECT max( if(DATE_FORMAT(ymd, '%w') = 0, ymd_date, '') ) as day0,
		       max( if(DATE_FORMAT(ymd, '%w') = 0, ymd_more, '') ) as day0more,
		       max( if(DATE_FORMAT(ymd, '%w') = 1, ymd_date, '') ) as day1,
		       max( if(DATE_FORMAT(ymd, '%w') = 1, ymd_more, '') ) as day1more,
		       max( if(DATE_FORMAT(ymd, '%w') = 2, ymd_date, '') ) as day2,
		       max( if(DATE_FORMAT(ymd, '%w') = 2, ymd_more, '') ) as day2more,
		       max( if(DATE_FORMAT(ymd, '%w') = 3, ymd_date, '') ) as day3,
		       max( if(DATE_FORMAT(ymd, '%w') = 3, ymd_more, '') ) as day3more,
		       max( if(DATE_FORMAT(ymd, '%w') = 4, ymd_date, '') ) as day4,
		       max( if(DATE_FORMAT(ymd, '%w') = 4, ymd_more, '') ) as day4more,
		       max( if(DATE_FORMAT(ymd, '%w') = 5, ymd_date, '') ) as day5,
		       max( if(DATE_FORMAT(ymd, '%w') = 5, ymd_more, '') ) as day5more,
		       max( if(DATE_FORMAT(ymd, '%w') = 6, ymd_date, '') ) as day6,
		       max( if(DATE_FORMAT(ymd, '%w') = 6, ymd_more, '') ) as day6more
		FROM g5_5_ymd
		WHERE ymd BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
		{$sql_group_by}
";
// echo $sql.'<br>';
$result = sql_query($sql,1);

?>
<?php if(G5_IS_MOBILE){ ?>
<style>
#container{margin-top:0px;}
#container_title{padding-left:10px;}
.container_wr{padding-top:0px;overflow-x:auto !important;min-height:800px;}
.container_wr .calendar{min-width:1000px;}
.calendar_title{margin-top:10px;}
.calendar_title .btn_write{right:auto;left:52px;}
.calendar_title .btn_list_skin{left:98px;}
.calendar_title .btn_mode{top:10px;}
.table_calendar .prs_days i{color:#3c7ff9;}
.table_calendar .sales_days i{color:#05e241;}
.table_calendar .prp_type i{color:#ff0481;}
.table_calendar .schedule_range i{color:#000000;}
.btn_fixed_top a{display:inline-block;width:30px;height:30px;line-height:30px;background:#ddd;text-align:center;font-size:1.2em;}
</style>
<div class="btn_fixed_top">
    <a href="<?php echo $calendar_url?>" class="btn_mode btn_calendar_skin"><i class="fa fa-calendar"></i><span class="sound_only">달력</span></a>
    <a href="<?php echo G5_BBS_URL?>/board.php?bo_table=<?php echo $bo_table?>" class="btn_mode btn_list_skin"><i class="fa fa-list-alt"></i><span class="sound_only">리스트</span></a>
    <?php if ($member['mb_level']>=$board['bo_write_level']) { ?>
    <a href="<?=G5_BBS_URL?>/write.php?bo_table=schedule" class="btn_mode btn_write" style="background:#03c75a !important;color:#fff;">
        <i class="fa fa-plus" aria-hidden="true"></i>
        <span class="sound_only">등록</span>
    </a>
    <?php } ?>
</div>
<?php }else{ ?>
<style>
.btn_fixed_top{}
.btn_fixed_top:after{display:block;visibility:hidden;clear:both;content:'';}
.btn_fixed_top a{display:block;float:left;background:#9eacc6;color:#fff;height:30px;line-height:30px;padding:0 10px;margin-left:5px;border-radius:5px;}
.btn_fixed_top a.btn_calendar_skin{}
.btn_fixed_top a.btn_list_skin{}
.btn_fixed_top a.btn_write{}
.btn_fixed_top a span{margin-left:5px;}
</style>
<div class="btn_fixed_top">
        <a href="<?php echo $calendar_url?>" class="btn_mode btn_calendar_skin"><i class="fa fa-calendar"></i><span>달력</span></a>
        <a href="<?php echo G5_BBS_URL?>/board.php?bo_table=<?php echo $bo_table?>" class="btn_mode btn_list_skin"><i class="fa fa-list-alt"></i><span>리스트</span></a>
        <?php if($member['mb_6'] <= 2){ //if ($member['mb_level']>=$board['bo_write_level']) { ?>
        <a href="<?=G5_BBS_URL?>/write.php?bo_table=schedule" class="btn_mode btn_write" style="background:#ff4081 !important;color:#fff;">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <span>등록</span>
        </a>
        <?php } ?>
</div>
<?php } ?>
<!-- 달력 시작 { -->
<div class="calendar">
    <div class="calendar_title">
        <!--
        <a href="<?php //echo $board_skin_url?>/list.calendar.php?bo_table=<?php //echo $bo_table?>" class="btn_mode btn_calendar_skin"><i class="fa fa-calendar"></i> 달력</a>
        -->
        <?php
        $calendar_url = G5_THEME_URL.'/skin/board/schedule11/list.calendar.php?bo_table=schedule';
        ?>
        <a href="<?=$_SERVER['PHP_SELF']?>?month=<?=$last_month?>&bo_table=<?=$bo_table?>" class="prev_month" title="이전달"><i class="fa fa-arrow-circle-left"></i></a>
        <span class="this_month"><?=$_month?></span>
        <a href="<?=$_SERVER['PHP_SELF']?>?month=<?=$next_month?>&bo_table=<?=$bo_table?>" class="next_month" title="다음달"><i class="fa fa-arrow-circle-right"></i></a>


    </div>
	<div class="caution" style="display:none;">
        동일시간대 예약가능 인원은 <span style=""><?php echo $board['set_max_time_apply'];?></span>명까지, 당일 예약가능 인원은 총 <span style=""><?php echo $board['set_max_apply'];?></span>명까지입니다.
	</div>
    <div class="div_calendar">
        <table class="table_calendar">
        <thead>
        <tr>
            <th class="th_sunday">일</th>
            <th>월</th>
            <th>화</th>
            <th>수</th>
            <th>목</th>
            <th>금</th>
            <th class="th_saturday">토</th>
        </tr>
        </thead>
        <tbody>
            <!-- 달력 리스트 -->
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++)
            {
                echo '<tr class=" ">';
                
                // 일 ~ 토 한 주간 표현
                for($j=0;$j<7;$j++) {
                    //echo $row['day'.$j].'<br>';	// 2018-02-03
                    $row[$i][$j]['dates'] = explode("-",$row['day'.$j]);    // 날짜값 분리 배열
                    $row[$i][$j]['day_no'] = number_format($row[$i][$j]['dates'][2]);    // 날짜만 숫자로

                    // 해당 날짜의 개별 설정 unserialize 추출
                    if($row["day".$j."more"]) {
                        $unser = unserialize(stripslashes($row["day".$j."more"]));
                        if( is_array($unser) && substr($row['day'.$j],0,7) == $_month ) {
                            foreach ($unser as $key=>$value) {
                                $row[$i][$j][$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
                            }    
                        }
                    }
                    //print_r2($row[$i][$j]);

                    // 요일별 클래스
                    if( $j==0 )
                        $day_style_week[$i][$j] = " day_sunday";
                    else if( $j==6 )
                        $day_style_week[$i][$j] = " day_saturday";
                    else
                        $day_style_week[$i][$j] = " day_weekday";

                    // 날짜별 스타일 설정
                    if( $j==0 )
                        $day_style = "day_sunday";
                    else if( $j==6 )
                        $day_style = "day_saturday";
                    else 
                        $day_style = "day_normal";

                    // 오늘
                    $day_today[$i][$j] = ( $row['day'.$j] == G5_TIME_YMD )? " day_today" : "";
                    // 오늘 이전
                    $day_oldday[$i][$j] = ( $row['day'.$j] < G5_TIME_YMD )? " day_oldday":"";
                    // 공휴일
                    $day_holiday[$i][$j] = ( $row[$i][$j]['holiday_name'] )? " day_holiday":"";
                    // 이전달
                    $day_prev_month[$i][$j] = ( substr($row['day'.$j],0,7) < $_month )? " day_prev_month":"";
                    // 다음달
                    $day_next_month[$i][$j] = ( substr($row['day'.$j],0,7) > $_month )? " day_next_month":"";
                    // 날짜값이 없는 경우
                    $day_null[$i][$j] = ( !$row['day'.$j] )? " day_null":"";

                    echo '<td td_date="'.$row['day'.$j].'" class="td_day '
                            .$day_style_week[$i][$j]
                            .$day_today[$i][$j]
                            .$day_holiday[$i][$j]
                            .$day_oldday[$i][$j]
                            .$day_prev_month[$i][$j]
                            .$day_next_month[$i][$j]
                            .$day_null[$i][$j].'"';
                    echo '>';	// end of <td
                    // 날짜 & 공휴일명
                    if($row["day".$j]) {
                        echo '<div class="day_no_holiday">';
                        echo '<div class="day_no">'.number_format($row[$i][$j]['dates'][2]).'</div>';
                        echo ($row[$i][$j]['holiday_name']) ? '<div class="day_holiday_text" title="'.$row[$i][$j]['holiday_description'].'">'.$row[$i][$j]['holiday_name'].'</div>' : '' ;   // 공휴일 내용이 있으면 표현
                        echo '</div>';
                    }

                    // 일정내용
                    // echo $row['day'.$j].'<br>';
                    echo ($day_content[$row['day'.$j]]) ? $day_content[$row['day'.$j]] : '' ;

                    // 관리자인 경우 통계 보임
                    if($is_admin && $member['mb_manager_yn']) {
                        echo ($day_status_stat[$row['day'.$j]]) ? $day_status_stat[$row['day'.$j]] : '' ;
                    }
                    echo '</td>';
                }
                
                echo '</tr>';
            }
            ?>
        </tbody>
        </table>
    </div>
</div>
<!-- } 달력 종료 -->


<?php
include_once('./_tail.php');
?>