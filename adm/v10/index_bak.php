<?php
$sub_menu = "960100";
include_once('./_common.php');

$g5['title'] = '대시보드';
include_once('./_top_menu_default.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// print_r2($member);
// mb_3=140(대표)
// mb_2=조직코드

//-- 기본 검색값 할당 (당일이므로 같은 값)
$ym = ($ym) ? $ym:substr(G5_TIME_YMD,0,7);
$st_date = $ym."-01";
$en_date = $ym."-31";

// 지난달 추출
$sql = " SELECT DATE_ADD(now( ) , INTERVAL -1 MONTH) AS ym_1 ";
$ym_01 = sql_fetch($sql,1);
$ym01 = substr($ym_01['ym_1'],0,7);
$st_date01 = $ym01."-01";
$en_date01 = $ym01."-31";

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
// add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/index.css">', 0);
// add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/index.js"></script>', 10);
?>
<style>
.table1 {width:1020px;min-width:1020px;}
.table1 td {vertical-align:top;border:solid 0px red;}
div[class$='_left'] {float:left;}
div[class$='_right'] {float:right;}

.div_main_title {background:#ddd;height:36px;line-height:36px;margin-bottom:5px;padding-left:10px;font-weight:bold;font-size:1.08em;position:relative;}
.div_main_title01 {font-weight:bold;font-size:1.08em;position:relative;}
.main_more {
    position: absolute;
    display: block;
    width: 40px;
    line-height: 25px;
    color: #3a8afd !important;
    font-weight:normal;
    font-size:0.9em;
    border-radius: 3px;
    text-align: center;
}
.div_main_title01 .main_more {top: 2px;right: 5px;}
.div_main_title .main_more {top: 5px;right: 5px;}

.div_main01:after{display:block;visibility:hidden;clear:both;content:'';}
.st_more {position: absolute;top: 5px;right: 5px;display: block;width: 40px;line-height: 25px;color: #3a8afd !important;border-radius: 3px;font-weight:normal;}

.ul_calculate {display:block;}
.ul_calculate:after {display:block;visibility: hidden;clear:both;content:'';}
.ul_calculate li {float:left;border:solid 1px #ddd;border-radius:5px;padding:10px;background:#5b8c41;width:32%;margin-right:14px;}
.ul_calculate li:last-child {float:right;margin-right:0;}
.ul_calculate li .span1 {display:block;color:#e1e9dc;}
.ul_calculate li .span2 {font-size:2em;color:#fcfe30;}
.ul_calculate li .span3 {color:#e1e9dc;}
div[class^='div_main0'] {margin-bottom:20px;}
.ul_list {border:solid 1px #ddd;padding:0 10px 10px;}
.ul_list li {border-bottom:solid 1px #e5ecee;padding:5px 0;}
.ul_list li:last-child {border-bottom:none;}
.ul_list .prj_com_name {font-size:0.9em;color:#818181;}
.ul_list .prj_end_company {font-size:0.9em;color:#a9a9a9;margin-left:5px;}
.ul_list .prp_com_name {font-size:0.9em;color:#818181;}
.ul_list .prp_end_company {font-size:0.9em;color:#a9a9a9;margin-left:5px;}
.ul_list .prp_price {float:right;font-size:0.9em;margin-left:5px;}

</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script>
// highchart.com이라는 로고 제거
function removeLogo() {
    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },10);
}
// Make monochrome colors
var pieColors = (function () {
    var colors = [],
        base = Highcharts.getOptions().colors[0],
        i;

    for (i = 0; i < 10; i += 1) {
        // Start out with a darkened base color (negative brighten), and end
        // up with a much brighter color
        colors.push(Highcharts.color(base).brighten((i - 3) / 7).get());
    }
    return colors;
}());
</script>

<table class="table1">
<tr style="display:none;">
    <td colspan="3" style="padding-bottom:10px;">
        <?php
        // 달력 시작일
        $sql_date_start = G5_TIME_YMD;
        // 달력 종료일 (오늘 +6일, 실제는 7일간 추출)
        $w1 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval +6 day) AS end_day ",1);
        $sql_date_end = $w1['end_day'];
        // echo $sql_date_start.'~'.$sql_date_end.'<br>';


        // 수입지출 일정 [ ======================================================
        if( in_array($member['mb_2'], $g5['set_team_account_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
            $sql = "SELECT prp_idx, prp.prj_idx, prp_type, prp_price, prp_pay_no, prp_plan_date, prp_status
                        , prj.prj_name, prj.prj_percent
                        , com.com_name
                    FROM {$g5['project_price_table']} AS prp
                        LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prp.prj_idx
                        LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    WHERE prp_status NOT IN ('trash','delete')
                        AND prp_plan_date BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
            ";
            // echo $sql.'<br>';
            $rs = sql_query($sql,1);
            for ($i=0; $row=sql_fetch_array($rs); $i++) {
                // echo $row['prp_plan_date'].'<br>';	// 2018-02-03
                $row['href'] = G5_USER_ADMIN_URL.'/project_group_price_list.php?sfl=prj_idx&stx='.$row['prj_idx'];
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
                        AND prs_start_date <= '".$sql_date_end."'
                        AND prs_end_date >= '".$sql_date_start."'
            ";
            // echo $sql.'<br>';
            $rs = sql_query($sql,1);
            for ($i=0; $row=sql_fetch_array($rs); $i++) {
                // echo $row['mb_name'].': '.$row['prs_start_date'].'~'.$row['prs_end_date'].'<br>';	// 2018-02-03
                $row['href'] = G5_USER_ADMIN_URL.'/project_gantt.php?sfl=prs.prj_idx&stx='.$row['prj_idx'];
                // 시작일~종료일 돌면서 해당 일자 만들어줌
                // $row['prj_start_time'] = strtotime($row['prs_start_date']);
                $row['prj_start_time'] = (!$row['prs_start_date']||$row['prs_start_date']<$_month.'-01') ? strtotime($_month.'-01') : strtotime($row['prs_start_date']);
                $row['prj_month_count'] = date('t', strtotime($_month."-01"));
                // $row['prj_end_time'] = (!$row['prs_end_date']||$row['prs_end_date']>$_month.'-31') ? strtotime($_month.'-'.$row['prj_month_count']) : strtotime($row['prs_end_date']);
                $row['prj_end_time'] = strtotime($row['prs_end_date']);
                // echo $row['mb_name'].': '.$row['prs_start_date'].'('.$row['prj_start_time'].')~'.$row['prs_end_date'].'('.date("Y-m-d",$row['prj_end_time']).'/'.$row['prj_end_time'].')<br>';	// 2018-02-03
                for($j=$row['prj_start_time'];$j<=$row['prj_end_time'];$j+=86400) {
                    $row['prj_start_dyas'] = (int)( (($j-strtotime($row['prs_start_date'])) / 86400)+1 ).'일차';
                    // echo $row['mb_name'].': '.date("Y-m-d",$j).' '.$row['prj_start_dyas'].'<br>';	// 2018-02-03
                    $row['prj_name'] = cut_str($row['prj_name'],7,'..');
    
                    $day_content[date("Y-m-d",$j)] .= '<div class="prs_item prs_'.$row['prs_status'].'" prs_idx="'.$row['prs_idx'].'">'
                        .'<span class="prs_days"><i class="fa fa-circle"></i> '.$row['prj_start_dyas'].'</span>'
                        .'<span class="prs_prj_name">'.$row['prj_name'].'</span>'
                        .'<div class="prs_name_content"><a href="'.$row['href'].'"><span class="prs_name">'.$row['mb_name'].'</span><span class="prs_content">'.cut_str($row['prs_content'],24).'</span></a></div>'
                    .'</div>';
                }
            }
        }


        // 엽업관리 다음일정 추출
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
                WHERE wr_6!='' AND wr_6 BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
                ORDER BY STR_TO_DATE(wr_6, '%Y-%m-%d %H:%i:%s')
            ";
            // echo $sql.'<br>';
            $rs1 = sql_query($sql,1);
            for ($i=0; $row=sql_fetch_array($rs1); $i++) {
                //echo $row['crj_date'].'<br>';	// 2018-02-03
                $row['mb'] = get_member($row['mb_id'],'mb_name');
                $row['href'] = get_pretty_url('sales', $row['wr_id']);
                // $row['wr_subject'] = cut_str($row['wr_subject'],10,'..');
                $day_content[substr($row['wr_6'],0,10)] .= '<div class="sales_item sales_'.$row['wr_10'].'" wr_id="'.$row['wr_id'].'">'
                    .'<span class="sales_company"><i class="fa fa-circle"></i> '.$row['wr_1'].'</span>'
                    .'<span class="sales_status">'.$g5['set_sales_status_value'][$row['wr_10']].'</span>'
                    .'<div class="sales_name_subject"><a href="'.$row['href'].'"><span class="sales_name">'.$row['mb']['mb_name'].'</span><span class="sales_subject">'.$row['wr_subject'].'</span></a></div>'
                .'</div>';
            }
        }



        // 일정리스트 { -------
        $sql  = "SELECT *
            FROM ".$g5['write_prefix']."schedule
            WHERE wr_2 BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
                OR wr_6 BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
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

            // // 일정표현
            // $day_content[substr($row['wr_2'],0,10)] .= '<div class="schedule_item schedule_'.$row['wr_9'].'" wr_id="'.$row['wr_id'].'">'
            //     .'<span class="schedule_range"><i class="fa fa-circle"></i>'.$row['wr_range'].'</span>'
            //     .'<span class="schedule_ymd">'.$row['wr_ymd'].'</span>'
            //     //.'<span class="schedule_ampm">'.$row['wr_ampm'].'</span>'
            //     .'<span class="schedule_hi">'.$row['wr_hi'].'</span>'
            //     .'<span class="schedule_his">'.$row['wr_his'].'</span>'
            //     .'<span class="schedule_subject"><a href="'.$row['href'].'">'.$row['wr_subject'].'</a></span>'
            // .'</div>';
        }
        //print_r2($day_content);
        // } 일정리스트 -------


        // 달력 추출 ======================================================
        // 0 = Monday, 1 = Tuesday, 2 = Wednesday, 3 = Thursday, 4 = Friday, 5 = Saturday, 6 = Sunday
        $sql1 = "SELECT *
                    , WEEKDAY(ymd_date) AS ymd_week
                FROM g5_5_ymd
                WHERE ymd_date BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
        ";
        // echo $sql1;
        $rs1 = sql_query($sql1,1);
        ?>
        <div class="div_main_title01">
            <a href="<?=G5_THEME_URL?>/skin/board/schedule11/list.calendar.php?bo_table=schedule" class="main_more">더보기</a>
            <?=$sql_date_start.' ~ '.$sql_date_end?>
        </div>
        <table class="table_calendar">
        <caption></caption>
            <thead>
            <tr>
            <?php
            for($i=0;$row=sql_fetch_array($rs1);$i++) {
                // print_r2($row);
                echo '<th class="th_'.strtolower(date("l",strtotime($row['ymd_date']))).'">'.$g5['week_names2'][$row['ymd_week']].'</th>';
            }
            ?>
            </tr>
            </thead>
            <tbody>
                <tr class=" ">
                    <?php 
                    // 쿼리를 한번 더 불러야 리스트됨
                    $rs2 = sql_query($sql1,1);
                    for($i=0;$row=sql_fetch_array($rs2);$i++) {
                        // print_r2($row);
                        $row['dates'] = explode("-",$row['ymd_date']);    // 날짜값 분리 배열

                        // 해당 날짜의 개별 설정 unserialize 추출
                        if($row['ymd_more']) {
                            $unser = unserialize(stripslashes($row['ymd_more']));
                            if( is_array($unser) ) {
                                foreach ($unser as $key=>$value) {
                                    $row[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
                                }    
                            }
                        }
                        //print_r2($row);

                        // 요일별 클래스
                        if( $row['ymd_week']==6 )
                            $row['day_week'] = " day_sunday";
                        else if( $row['ymd_week']==5 )
                            $row['day_week'] = " day_saturday";
                        else
                            $row['day_week'] = " day_weekday";

                        // 오늘
                        $row['day_today'] = ( $row['ymd_date'] == G5_TIME_YMD )? " day_today" : "";
                        // 오늘 이전
                        $row['day_oldday'] = ( $row['ymd_date'] < G5_TIME_YMD )? " day_oldday":"";
                        // 공휴일
                        $row['day_holiday'] = ( $row['holiday_name'] )? " day_holiday":"";
//                            // 이전달
//                            $row['day_prev_month'] = ( substr($row['ymd_date'],0,7) < $_month )? " day_prev_month":"";
//                            // 다음달
//                            $row['day_next_month'] = ( substr($row['ymd_date'],0,7) > $_month )? " day_next_month":"";
                        // 날짜값이 없는 경우
                        $row['day_null'] = ( !$row['ymd_date'] )? " day_null":"";


                        // td 시작
                        echo '<td td_date="'.$row['ymd_date'].'" class="td_day '
                                .$row['day_week']
                                .$row['day_today']
                                .$row['day_holiday']
                                .$row['day_oldday']
                                .$row['day_prev_month']
                                .$row['day_next_month']
                                .$row['day_null'].'"';
                        echo '>';	// end of <td

                        // 날짜 & 공휴일명
                        echo '<div class="day_no_holiday">';
                        echo '<div class="day_no">'.number_format($row['dates'][2]).'</div>';
                        echo ($row['holiday_name']) ? '<div class="day_holiday_text" title="'.$row['holiday_description'].'">'.$row['holiday_name'].'</div>' : '' ;   // 공휴일 내용이 있으면 표현
                        echo '</div>';

                        // 일정내용
                        // echo $row['ymd_date'].'<br>';
                        echo ($day_content[$row['ymd_date']]) ? $day_content[$row['ymd_date']] : '' ;

                        // td 닫기
                        echo '</td>';
                    }
                    ?>
                </tr>
            </tbody>
        </table>


    </td>
</tr><!--//display:none 달력 비표시-->
<tr><!--//나의 간트일정 표시하기-->
   <td>

   </td> 
</tr>
<tr>
    <td style="width:70%;">

        <?php
        // 회계팀과 대표님만 보임
        //if( in_array($member['mb_2'], $g5['set_team_account_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
        if($member['mb_2'] == 2 || $member['mb_level'] >= 8){
        ?>
        <div class="div_main01" style="width:100%;">

            <?php
            // 매출합계 (수주완료일자의 수주금액)
            $ym = ($ym) ? $ym:substr(G5_TIME_YMD,0,7);
            $st_date = $ym."-01";
            $en_date = $ym."-31";
            $sql = "SELECT SUM(prp_price) AS price_total
                    FROM g5_1_project AS prj
                        LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
                    WHERE prj_status NOT IN ('trash','delete')
                        AND prp_type IN ('order')
                        AND prj_contract_date != '0000-00-00'
                        AND prj_contract_date >= '".$st_date."'
                        AND prj_contract_date <= '".$en_date."'
            ";
            // echo $sql;
            $prp1 = sql_fetch($sql,1);
            // print_r2($prp1);

            // 매입합계 (타입이 매입이면서 적용일자가 이달)
            $sql = "SELECT SUM(prp_price) AS price_total
                    FROM g5_1_project AS prj
                        LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
                    WHERE prj_status NOT IN ('trash','delete')
                        AND prp_type IN ('".implode("','",$g5['set_purchase_type_array'])."')
                        AND prp_pay_date != '0000-00-00'
                        AND prp_pay_date >= '".$st_date."'
                        AND prp_pay_date <= '".$en_date."'
            ";
            // echo $sql;
            $prp4 = sql_fetch($sql,1);

            // 수금예정 (수금예정일자의 금액)
            $sql = "SELECT SUM(prp_price) AS price_total
                    FROM g5_1_project_price AS prp
                    WHERE prp_planpay_date != '0000-00-00'
                        AND prp_planpay_date >= '".$st_date."'
                        AND prp_planpay_date <= '".$en_date."'
            ";            
            // echo $sql;
            $prp2 = sql_fetch($sql,1);
            // print_r2($prp2);

            // 수금완료 (수금예정일자의 금액)
            $sql = "SELECT SUM(prp_price) AS price_total
                    FROM g5_1_project_price AS prp
                    WHERE prp_pay_date != '0000-00-00'
                        AND prp_pay_date >= '".$st_date."'
                        AND prp_pay_date <= '".$en_date."'
            ";
            // echo $sql;
            $prp3 = sql_fetch($sql,1);
            // print_r2($prp2);
            ?>
            <ul class="ul_calculate">
                <li><span class="span1"><?=substr(G5_TIME_YMD,5,2)?>월 매출합계</span>
                    <span class="span2"><?=number_format($prp1['price_total'])?></span>
                    <span class="span3">원</span>
                </li>
                <li><span class="span1"><?=substr(G5_TIME_YMD,5,2)?>월 매입합계</span>
                    <span class="span2"><?=number_format($prp4['price_total'])?></span>
                    <span class="span3">원</span>
                </li>
                <li><span class="span1"><?=substr(G5_TIME_YMD,5,2)?>월 수금완료</span>
                    <span class="span2"><?=number_format($prp3['price_total'])?></span>
                    <span class="span3">원</span>
                </li>
            </ul>

        </div>
        <?php
        //// 회계팀만 보임
        }
        ?>

        <!-- 프로젝트일정 -->
        <div class="div_main_title">
            <a href="./project_gantt.php" class="main_more">더보기</a>
            프로젝트일정
        </div>
        <div class="div_main01" style="width:100%;">
            <div class="main01_left" style="width:100%;">
                <?php
                // 시작일 (D-7)
                $w1 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval -7 day) AS start_day ",1);
                $sql_date_start = $w1['start_day'];
                // $st_date = $st_date ?: $sql_date_start;
                $st_date = $sql_date_start;
                $st_date1 = date_parse($st_date);
                // print_r2($st_date1);
                // 종료일 (D+10, 실제는 11일간 추출)
                $w2 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval +11 day) AS end_day ",1);
                $sql_date_end = $w2['end_day'];
                // $en_date = $en_date ?: $sql_date_end;
                $en_date = $sql_date_end;
                $en_date1 = date_parse($en_date);
                // print_r2($en_date1);
                // echo $sql_date_start.'~'.$sql_date_end.'<br>';


                $sql_common = " FROM {$g5['project_schedule_table']} AS prs
                                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
                "; 

                $where = array();
                $where[] = " prs.prs_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

                // 운영권한이 없으면 자기 것만
                // if (!$member['mb_manager_yn']) {
                //     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
                // }
                $where[] = " prs.prs_start_date <= '$en_date' AND prs.prs_end_date >= '$st_date' ";

                // 최종 WHERE 생성
                if ($where)
                    $sql_search = ' WHERE '.implode(' AND ', $where);

                $sql_order = " ORDER BY prj_idx, prs_role, mb_id_worker, prs_start_date ";

                $sql = " SELECT SQL_CALC_FOUND_ROWS prs.*
                            , com.com_name AS com_name
                            , prs.prj_idx AS prj_idx
                            , prj.prj_name AS prj_name
                            , mb.mb_name AS mb_name
                            , GREATEST('".$st_date."', prs_start_date ) AS st_date
                            , LEAST('".$en_date."', prs_end_date ) AS en_date
                        {$sql_common}
                        {$sql_search}
                        {$sql_order}
                ";
                // echo $sql.'<br>';
                $result = sql_query($sql,1);

                $list = array();
                $gantt_y = -1;
                for($i=0;$row=sql_fetch_array($result);$i++) {
                    $row['start1'] = date_parse($row['st_date']);
                    $row['start2'] = date_parse($row['prs_start_date']);
                    $row['end1'] = date_parse($row['en_date']);
                    $row['end2'] = date_parse($row['prs_end_date']);
                    $row['pointwidth'] = $g5['setting']['set_gantt_thickness_'.$row['prs_type']] ?: 5;
                    $row['color'] = $g5['setting']['set_gantt_color_'.$row['prs_type']] ?: '';
                    // print_r2($row);
                    // If same project, same worker, schedules should be located at the same line.
                    if($prj_idx_old != $row['prj_idx']) {
                        $gantt_y++;
                    }
                    else {
                        if($mb_id_worker_old != $row['mb_id_worker']) {
                            $gantt_y++;
                        }
                    }
                    $list[$i]['name'] = ($prj_idx_old != $row['prj_idx']) ? cut_str($row['prj_name'],10) : '';
                    $list[$i]['role'] = strtoupper($row['prs_role']);
                    $list[$i]['assignee'] = $row['mb_name'];
                    $list[$i]['content'] = $row['prs_task'];
                    // $list[$i]['start'] = 'Date.UTC('.$row['start1']['year'].', '.$row['start1']['month'].', '.$row['start1']['day'].')';
                    // $list[$i]['end'] = 'Date.UTC('.$row['end1']['year'].', '.$row['end1']['month'].', '.$row['end1']['day'].')';
                    $list[$i]['start_year'] = $row['start1']['year'];
                    $list[$i]['start_month'] = $row['start1']['month'];
                    $list[$i]['start_day'] = $row['start1']['day'];
                    $list[$i]['prs_start_month'] = $row['start2']['month'];
                    $list[$i]['prs_start_day'] = $row['start2']['day'];
                    $list[$i]['end_year'] = $row['end1']['year'];
                    $list[$i]['end_month'] = $row['end1']['month'];
                    $list[$i]['end_day'] = $row['end1']['day'];
                    $list[$i]['prs_end_month'] = $row['end2']['month'];
                    $list[$i]['prs_end_day'] = $row['end2']['day'];
                    $list[$i]['completed'] = (in_array($row['prs_type'],$g5['setting']['set_prs_rate_display_array'])) ? $row['prs_percent']/100 : 0;
                    $list[$i]['pointWidth'] = $row['pointwidth'];
                    $list[$i]['color'] = $row['color'];
                    // $list[$i]['mb_id_worker'] = $row['mb_id_worker'];
                    // $list[$i]['prs_type'] = $row['prs_type'];
                    $list[$i]['prs_idx'] = $row['prs_idx']; // for url link 
                    // $list[$i]['test'] = $prj_idx_old .'?='. $row['prj_idx'] .' / '. $mb_id_worker_old .'?='. $row['mb_id_worker'];
                    $list[$i]['y'] = $gantt_y;

                    // save old value for proj and mb_id_worker
                    $prj_idx_old = $row['prj_idx'];
                    $mb_id_worker_old = $row['mb_id_worker'];

                }
                // print_r2($list);
                ?>
                <div id="chart01" style="width:710px;"></div>
                <script>
                Highcharts.ganttChart('chart01', {
                chart: {
                    height: 85+35*<?=($gantt_y+1)?>
                },
                xAxis: [
                    { // day display, first x-axis from bottom
                        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=$st_date1['day']?>, 0),
                        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=$en_date1['day']?>, 23, 59, 59),
                        tickInterval: 1000 * 60 * 60 * 24 // 1 day
                        ,labels: {
                            format: '{value:%d}' // day of the week
                        },
                        grid: {
                            cellHeight: 30
                        }
                    }
                    ,{ // month display, 2nd x-axis from bottom
                        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=$st_date1['day']?>, 0),
                        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=$en_date1['day']?>, 23, 59, 59),
                        tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
                        ,labels: {
                            format: '{value:%Y-%m}'
                        },
                        grid: {
                            cellHeight: 30
                        }
                    },
                ],
                yAxis: {
                    uniqueNames: true,
                    staticScale: 20
                },
                navigator: {
                    enabled: false,
                    liveRedraw: true,
                    series: {
                        lineColor: '#f2f2f2',
                        type: 'spline', // 'gantt' - bar is showing outside of the navigator, not good, 
                        // ^ but 'spline', it showw warning #15, but neglect it, no problem for running.
                        pointPlacement: 0.5,
                        pointPadding: 0.25
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            second: '%H:%M:%S',
                            minute: '%H:%M',
                            hour: '%H:%M',
                            day: '%m-%d',
                            week: '%m-%d',
                            month: '%Y-%m',
                            year: '%Y-%m'
                        }
                    },
                    yAxis: {
                        min: 0,
                        max: 3,
                        reversed: true,
                        categories: []
                    }
                },
                scrollbar: {
                    enabled: false
                },
                rangeSelector: {
                    enabled: false,
                    selected: 0
                },
                tooltip: {
                    useHTML: true,
                    formatter: function(tooltip) {
                        // console.log(this);
                        // console.log(this.point.options);
                        // console.log(this.point.options.content);

                        // tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">4.12~5.12</span>';
                        tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">'+this.point.options.start_dt+'~'+this.point.options.end_dt+'</span>';
                        if(this.point.options.content) {
                            tooltip1 += '<br/><span style="font-size:0.9em;">'+this.point.options.content+'</span>';
                        }
                        return tooltip1;
                        // // If not null, use the default formatter
                        // return tooltip.defaultFormatter.call(this, tooltip);
                    }
                },
                yAxis: {
                    type: 'category',
                    grid: {
                        borderColor: '#dddddd',
                        columns: [
                        {
                            title: {
                            text: '프로젝트',
                            rotation: 0,
                                y: -5,
                                x: -15
                            },
                            labels: {
                                format: '{point.name}'
                            }
                        },
                        {
                            title: {
                            text: '담당자', // 역할
                            rotation: 0,
                                y: -5,
                                x: 0
                            },
                            labels: {
                                format: '{point.role}'
                            }
                        },
                        {
                            title: {
                            text: ' ',  // 담당자이름
                            rotation: 0,
                                y: -5,
                                x: -15
                            },
                            labels: {
                                format: '{point.assignee}'
                            }
                        }
                        ]
                    }
                },
                plotOptions: {
                    series: {
                        // opacity:0.8,
                        cursor: 'pointer',
                        point: {
                            events: {
                                click: function () {
                                    // console.log(this.options);
                                    location.href = './project_schedule_form.php?w=u&prs_idx=' +
                                        this.options.prs_idx;
                                }
                            }
                        }
                    }
                },
                series: [
                {
                    name: 'Projects',
                    opacity:0.85,
                    data: [
                        <?php
                        for($i=0;$i<sizeof($list);$i++) {
                            echo "
                            {   name: '".$list[$i]['name']."',
                                role: '".$list[$i]['role']."',
                                assignee: '".$list[$i]['assignee']."',
                                content: '".$list[$i]['content']."',
                                start: Date.UTC(".$list[$i]['start_year'].", ".($list[$i]['start_month']-1).", ".$list[$i]['start_day']."),
                                start_dt: '".$list[$i]['prs_start_month'].".".$list[$i]['prs_start_day']."',
                                end: Date.UTC(".$list[$i]['end_year'].", ".($list[$i]['end_month']-1).", ".$list[$i]['end_day'].", 23, 59, 59),
                                end_dt: '".$list[$i]['prs_end_month'].".".$list[$i]['prs_end_day']."',
                                completed: ".$list[$i]['completed'].",
                                pointWidth: ".$list[$i]['pointWidth'].",
                                color: '".$list[$i]['color']."',
                                prs_idx: ".$list[$i]['prs_idx'].",
                                y: ".$list[$i]['y']."
                            },
                            ";
                        }
                        ?>
                    ]
                }
                ]
                });
                removeLogo();
                // highchart.com이라는 로고 제거
                function removeLogo() {
                    //Highcharts.com 로고 제거
                    setTimeout(function(e){
                        $('.highcharts-credits').remove();
                    },10);
                }
                </script>


            </div>
        </div>

    </td>
    <td><img src="<?=G5_USER_ADMIN_IMG_URL?>/dot.png" style="width:10px;"></td>
    <td style="width:30%;">

        <?php
        // 시스템팀만 보임
        if( in_array($member['mb_2'], $g5['set_team_system_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
            // A/S 관리
            echo latest10('theme/basic_intra', 'as', 3, 20);
        }
        ?>

        <?php
        // 영업팀만 보임
        if( in_array($member['mb_2'], $g5['set_team_sales_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
            // 영업진행현황
            echo latest10('theme/basic_intra', 'sales', 3, 20);
        }
        ?>

        <?php
        // 회계팀만 보임
        if( in_array($member['mb_2'], $g5['set_team_account_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
        ?>
        <div class="div_main_title">
            수입지출항목관리
            <a href="./project_price_list.php" class="st_more">더보기</a>
        </div>
        <div class="div_main01">
            <?php
            $sql = "SELECT *
                    FROM {$g5['project_price_table']} AS prp
                        LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prp.prj_idx
                        LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    WHERE prp_status NOT IN ('trash','delete')
                        AND prp.prp_type NOT IN ('submit','nego','order') 
                    ORDER BY prp.prp_idx DESC
                    LIMIT 5
            ";
            // echo $sql;
            $result = sql_query($sql,1);
            ?>
            <ul class="ul_list">
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                //print_r2($row);
                $bg = 'bg'.($i%2);

                echo '<li class="'.$bg.' li_id="'.$row['prp_idx'].'">'.PHP_EOL;
                ?>
                    <a href="./project_price_form.php?w=u&prp_idx=<?=$row['prp_idx']?>"><?=cut_str($row['prj_name'],25)?></a>
                    <div class="prp_info_sub">
                        <span class="prp_com_name"><?=$row['com_name']?></span>
                        <span class="prp_end_company"><?=$row['prj_end_company']?></span>
                        <span class="prp_price"><?=number_format($row['prp_price'])?></span>
                    </div>
                <?php
                //echo $td_items[$i];
                echo '</li>'.PHP_EOL;
            }
            if ($i == 0)
                echo '<tr><td class="empty_table">자료가 없습니다.</td></tr>';
            ?>
            </ul>
        </div>
        <?php
        } // 회계팀만 보임
        ?>

        <?php
        // 공지사항
        echo latest10('theme/basic_intra', 'notice1', 3, 20);
        ?>


    </td>
</tr>
</table>




<div style="height:30px;border:solid 0px red;"></div>
<?php
include_once ('./_tail.php');
?>
