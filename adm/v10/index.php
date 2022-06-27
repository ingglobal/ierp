<?php
$sub_menu = "960100";
include_once('./_common.php');

$g5['title'] = '대시보드';
include_once('./_top_menu_default.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


//##################### 나의 간트일정 : 시작 ###############################
$week = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
$week2 = array('일', '월', '화', '수', '목', '금', '토');

// 시작일 (D-10)
$w1 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval -15 day) AS start_day ",1);//처음 -10
$sql_date_start = $w1['start_day'];
$st_date = $sql_date_start;
$st_date1 = date_parse($st_date);

//echo $week2[date("w",strtotime($st_date))]."<br>";
// print_r2($st_date1);
// 종료일 (D+30, 실제는 31일간 추출)
$w2 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval +46 day) AS end_day ",1);//처음 +31
$sql_date_end = $w2['end_day'];
//echo $sql_date_end."<br>";
$en_date = $sql_date_end;
//echo $en_date."<br>";
$en_date1 = date_parse($en_date);

//echo $week2[date("w",strtotime($en_date))];
// print_r2($en_date1);
// echo $sql_date_start.'~'.$sql_date_end.'<br>';
$stdate = $st_date;
$endate = $en_date;

$we_arr = array();
while(strtotime($stdate) <= strtotime($endate)){
    //echo $stdate."<br>";
    if($week[date("w",strtotime($stdate))] == 'sat' || $week[date("w",strtotime($stdate))] == 'sun'){
        array_push($we_arr,$stdate);
    }
    $stdate = date("Y-m-d",strtotime("+1 day",strtotime($stdate)));
}

$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 

$where = array();
$where[] = " prs.prs_status NOT IN ('trash','delete','end','hide') ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 것만
// if (!$member['mb_manager_yn']) {
//     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
// }
if ($st_date && $en_date) {
    $where[] = " prs.prs_start_date <= '$en_date' AND prs.prs_end_date >= '$st_date' ";
}
else if ($st_date) {
    $where[] = " prs.prs_start_date >= '$st_date' ";
}
else if ($en_date) {
    $where[] = " prs.prs_end_date <= '$en_date' ";
}

$where[] = " (mb.mb_name LIKE '%{$member['mb_name']}%') ";

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// if (!$sst) {
//     $sst = "prj_idx";
//     $sod = "DESC";
// }
// $sql_order = " ORDER BY {$sst} {$sod} ";
// prs_role 이 먼저 나와야 함, PM이 나오고 그 다음..
$sql_order = " ORDER BY prj_idx DESC, prs_role, mb_id_worker, prs_start_date ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

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
//echo $sql.'<br>';
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

//print gettype($g5['set_prs_type_arr']);

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
        }else{
            if($mb_role_old != $row['prs_role']) {
                $gantt_y++;
            }
        }
        /*
        if(($mb_id_worker_old == $row['mb_id_worker'] && $mb_role_old != $row['prs_role']) || ($mb_id_worker_old != $row['mb_id_worker'] && $mb_role_old == $row['prs_role'])) {
            $gantt_y++;
        }
        */
        
    }
    
    if(G5_IS_MOBILE)
        $list[$i]['name'] =  ($prj_idx_old != $row['prj_idx']) ? '['.$row['prj_idx'].']'.cut_str($row['prj_name'],10) : '';
    else
        $list[$i]['name'] =  ($prj_idx_old != $row['prj_idx']) ? '['.$row['prj_idx'].']'.cut_str($row['prj_name'],20) : '';
    $list[$i]['prj_idx'] = $row['prj_idx'];
    $list[$i]['role'] = $g5['set_worker_type_value'][$row['prs_role']];
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
    $list[$i]['completed'] = (in_array($row['prs_type'], $g5['set_prs_type_arr'])) ? $row['prs_percent']/100 : 0;
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
    $mb_role_old = $row['prs_role'];

}

//##################### 나의 간트일정 : 종료 ###############################


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
.ul_calculate li {float:left;border:solid 1px #ddd;border-radius:5px;padding:10px;background:#5b8c41;width:32%;margin-right:2%;}
.ul_calculate li:last-child {margin-right:0;}
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

/*
#latest_box{}
#latest_box:after{display:block;visibility:hidden;clear:both;content:'';}
#latest_box .dash_lst{float:left;width:24%;}
*/

#chart1_empty{text-align:center;padding:100px 0;border:1px solid #eee;}


</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<section id="gantt_me">
    <div class="div_main_title">
        <a href="./project_gantt.php" class="main_more">더보기</a>
        <?=$member['mb_name']?>님의 프로젝트일정
    </div>
    <div class="div_main01" style="width:100%;">
        <div class="main01_left" style="width:100%;">
            <?php if(count($list)){ ?>
            <div id="chart1"></div>
            <?php } else { ?>
            <div id="chart1_empty"><?=$member['mb_name']?>님의 등록된 일정이 없습니다.</div>
            <?php } ?>
            
            <script>
            // var data_arr = <?=json_encode($list)?>;
            // console.log(JSON.stringify(data_arr));
            var week = ['일','월','화','수','목','금','토'];
            var we_arr = <?=json_encode($we_arr)?>;
            $(function(e) {

                // 문자통지
                $(document).on('click','.btn_sms_project_schedule',function(e) {
                    e.preventDefault();
                    var href = $(this).attr('href');
                    winSMSProject = window.open(href,"winSMSProject","left=50,top=100,width=520,height=600,scrollbars=1");
                    winSMSProject.focus();
                });

            });


            Highcharts.ganttChart('chart1', {
            chart: {
                height: 165+35*<?=($gantt_y+1)?>,
                events: {
                    load: function(){
                        var st = Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']+5)?>, 0);
                        var ed = Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']+40)?>, 0);//
                        this.xAxis[0].setExtremes(st,ed);
                        for(var idx in we_arr){
                            //console.log(we_arr[idx]);
                            var d = new Date(we_arr[idx]);
                            var x_val = d.getTime();
                            var x_axi = this.xAxis[0].axis;
                            //console.log(x_val);
                            this.xAxis[0].addPlotLine({
                                value:(x_val + (1000*60*60*12)),
                                width:16,
                                color:'#efefef'
                            });
                        }
                        var t = new Date(getToday2());
                        //var t = new Date('2021-03-21');
                        var today = t.getTime() + (1000*60*60*12);
                        //console.log(today);
                        //$('.date_today').text(getToday()+'('+(week[new Date(getToday()).getDay()])+')');
                        //alert(getToday());
                        this.xAxis[0].addPlotLine({
                            value: today,
                            width:2,
                            color: 'red',
                            label: {
                                text: getToday2(),
                                align:'right',
                                rotation: 0,
                                y: 14,
                                x: 80,
                                style: {
                                    color: 'red'
                                }
                            }
                        });
                    },
                    click: function(e) {
                        /*
                        var xValue = e.xAxis[0].value;
                        var xAxis = e.xAxis[0].axis;
                        //console.log(xValue);
                        var dt = new Date(formatDate(xValue));
                        var pt = dt.getTime() + (1000*60*60*12);
                        //$('.date_today').text(getToday()+'('+(week[new Date(getToday()).getDay()])+')');
                        $('.date_target').text(formatDate(pt)+'('+(week[new Date(formatDate(pt)).getDay()])+')');
                        //console.log(formatDate(xValue));
                        $.each(xAxis.plotLinesAndBands,function(){
                            if(this.id === 'myPlotLineId'){
                                this.destroy();
                            }
                        });
                        xAxis.addPlotLine({
                            value: pt,//xValue,//pt
                            width:2,
                            color: 'blue',
                            label: {
                                text: formatDate(pt),//formatDate(xValue - (60*60*12)),//pt
                                align:'right',
                                rotation: 0,
                                y: 14,
                                x: 80,
                                style: {
                                    color: 'blue'
                                }
                            },
                            id: 'myPlotLineId'
                        });
                        */
                    }
                }
            },
            xAxis: [
                { // day display, first x-axis from bottom
                    min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']-10)?>, 0),
                    max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=($en_date1['day'])?>, 23, 59, 59),
                    tickInterval: 1000 * 60 * 60 * 24 // 1 day
                    ,labels: {
                        format: '{value:%d}' // day of the week
                    },
                    grid: {
                        cellHeight: 30
                    }
                }
                ,{ // month display, 2nd x-axis from bottom
                    min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']-10)?>, 0),
                    max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=($en_date1['day'])?>, 23, 59, 59),
                    tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
                    ,labels: {
                        format: '{value:%Y-%m}'
                    },
                    grid: {
                        cellHeight: 30
                    },
                    id: 'bottom-datetime-axis'
                }
            ],
            yAxis: {
                uniqueNames: true,
                staticScale: 20
            },
            navigator: {
                enabled: true,
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
                enabled: true
            },
            rangeSelector: {
                enabled: true,
                selected: 0
            },
            tooltip: {
                useHTML: true,
                formatter: function(tooltip) {
                    tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">'+this.point.options.start_dt+'~'+this.point.options.end_dt+'</span>';
                    if(this.point.options.content) {
                        tooltip1 += '<br/><span style="font-size:0.9em;">'+this.point.options.content+'</span>';
                    }
                    return tooltip1;
                }
            },
            yAxis: {
                type: 'category',
                grid: {
                    borderColor: '#dddddd',       
                    style: {
                        
                    },
                    columns: [
                    {
                        title: {
                            text: '프로젝트',
                            rotation: 0,
                            y: -0,
                            x: -15
                        },
                        labels: {
                            //format: '{point.name}',
                            align:'left',
                            formatter: function(){
                                //return '<a href="javascript:" onclick="sch_project('+this.point.prj_idx+');">'+this.point.name+'</a>';
                                return '<a href="javascript:">'+this.point.name+'</a>';
                            },
                            useHTML: true,
                            style: {
                                fontSize:'1em',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    {
                        title: {
                            text: '역할', // 역할
                            rotation: 0,
                            y: -0,
                            x: 0
                        },
                        labels: {
                            format: '{point.role}'
                        }
                    },
                    {
                        title: {
                        text: '담당자',  // 담당자이름
                        rotation: 0,
                            y: 0,
                            x: 0
                        },
                        labels: {
                            format: '{point.assignee}',
                            formatter: function(){
                                //return '<a href="javascript:" onclick="sch_worker(\''+this.point.assignee+'\');">'+this.point.assignee+'</a>';
                                return '<a href="javascript:">'+this.point.assignee+'</a>';
                            },
                            useHTML: true,
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
                            /*
                            click: function () {
                                // console.log(this.options);
                                location.href = './project_schedule_form.php?gant=1&w=u&prs_idx=' +
                                    this.options.prs_idx + '&sst=<?=$sst?>&sod=<?=$sod?>&sfl=<?=$sfl?>&stx=<?=$stx?>&page=<?=$page?>';
                            }
                            */
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
                            prj_idx: '".$list[$i]['prj_idx']."',
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
            function getToday2(){
                var date = new Date();
                var year = date.getFullYear();
                var month = ("0" + (1 + date.getMonth())).slice(-2);
                var day = ("0" + date.getDate()).slice(-2);

                return year + "-" + month + "-" + day;
            }
            </script>
        </div>
    </div>
</section>
<?php
// 회계팀과 대표님만 보임
//if( in_array($member['mb_2'], $g5['set_team_account_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
if($member['mb_2'] == 2 || $member['mb_level'] >= 8){
?>
<section id="price">
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
</section>
<?php
//// 회계팀만 보임
}
?>

<section id="latest_box">
    <?php
    // 시스템팀만 보임
    if( in_array($member['mb_2'], $g5['set_team_system_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
        // A/S 관리
        echo "<div class='dash_lst'>".PHP_EOL;
        echo latest10('theme/basic_intra', 'as', 3, 80);
        echo "</div>".PHP_EOL;
    }
    ?> 

    <?php
    // 영업팀만 보임
    if( in_array($member['mb_2'], $g5['set_team_sales_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
        // 영업진행현황
        echo "<div class='dash_lst'>".PHP_EOL;
        echo latest10('theme/basic_intra', 'sales', 3, 80);
        echo "</div>".PHP_EOL;
    }
    ?>

    <?php
    // 회계팀만 보임
    if( in_array($member['mb_2'], $g5['set_team_account_array']) || $member['mb_level']>=8 || $member['mb_3']>=140 ) {
    ?>
    <div class="dash_lst">
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
                    <a href="./project_price_form.php?w=u&prp_idx=<?=$row['prp_idx']?>"><?=cut_str($row['prj_name'],80)?></a>
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
    </div>
    <?php
    } // 회계팀만 보임
    ?>

    <?php
    // 공지사항
    echo "<div class='dash_lst'>".PHP_EOL;
    echo latest10('theme/basic_intra', 'notice1', 3, 80);
    echo "</div>".PHP_EOL;
    ?>
</section>

<?php
//##################### 나의 간트일정 : 시작 ###############################
$week = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
$week2 = array('일', '월', '화', '수', '목', '금', '토');

// 시작일 (D-10)
$w1 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval -15 day) AS start_day ",1);//처음 -10
$sql_date_start = $w1['start_day'];
$st_date = $sql_date_start;
$st_date1 = date_parse($st_date);

//echo $week2[date("w",strtotime($st_date))]."<br>";
// print_r2($st_date1);
// 종료일 (D+30, 실제는 31일간 추출)
$w2 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval +46 day) AS end_day ",1);//처음 +31
$sql_date_end = $w2['end_day'];
//echo $sql_date_end."<br>";
$en_date = $sql_date_end;
//echo $en_date."<br>";
$en_date1 = date_parse($en_date);

//echo $week2[date("w",strtotime($en_date))];
// print_r2($en_date1);
// echo $sql_date_start.'~'.$sql_date_end.'<br>';
$stdate = $st_date;
$endate = $en_date;

$we_arr = array();
while(strtotime($stdate) <= strtotime($endate)){
    //echo $stdate."<br>";
    if($week[date("w",strtotime($stdate))] == 'sat' || $week[date("w",strtotime($stdate))] == 'sun'){
        array_push($we_arr,$stdate);
    }
    $stdate = date("Y-m-d",strtotime("+1 day",strtotime($stdate)));
}

$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 

$where = array();
$where[] = " prs.prs_status NOT IN ('trash','delete','end','hide') ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 것만
// if (!$member['mb_manager_yn']) {
//     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
// }
if ($st_date && $en_date) {
    $where[] = " prs.prs_start_date <= '$en_date' AND prs.prs_end_date >= '$st_date' ";
}
else if ($st_date) {
    $where[] = " prs.prs_start_date >= '$st_date' ";
}
else if ($en_date) {
    $where[] = " prs.prs_end_date <= '$en_date' ";
}

//$where[] = " (mb.mb_name LIKE '%{$member['mb_name']}%') ";

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// if (!$sst) {
//     $sst = "prj_idx";
//     $sod = "DESC";
// }
// $sql_order = " ORDER BY {$sst} {$sod} ";
// prs_role 이 먼저 나와야 함, PM이 나오고 그 다음..
$sql_order = " ORDER BY prj_idx DESC, prs_role, mb_id_worker, prs_start_date ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

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
//echo $sql.'<br>';
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

//print gettype($g5['set_prs_type_arr']);

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
        }else{
            if($mb_role_old != $row['prs_role']) {
                $gantt_y++;
            }
        }
        /*
        if(($mb_id_worker_old == $row['mb_id_worker'] && $mb_role_old != $row['prs_role']) || ($mb_id_worker_old != $row['mb_id_worker'] && $mb_role_old == $row['prs_role'])) {
            $gantt_y++;
        }
        */
        
    }
    
    if(G5_IS_MOBILE)
        $list[$i]['name'] =  ($prj_idx_old != $row['prj_idx']) ? '['.$row['prj_idx'].']'.cut_str($row['prj_name'],10) : '';
    else
        $list[$i]['name'] =  ($prj_idx_old != $row['prj_idx']) ? '['.$row['prj_idx'].']'.cut_str($row['prj_name'],20) : '';
    $list[$i]['prj_idx'] = $row['prj_idx'];
    $list[$i]['role'] = $g5['set_worker_type_value'][$row['prs_role']];
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
    $list[$i]['completed'] = (in_array($row['prs_type'], $g5['set_prs_type_arr'])) ? $row['prs_percent']/100 : 0;
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
    $mb_role_old = $row['prs_role'];

}

//##################### 나의 간트일정 : 종료 ###############################
?>
<section id="gantt_all">
    <div class="div_main_title">
        <a href="./project_gantt.php" class="main_more">더보기</a>
        프로젝트일정
    </div>
    <div class="div_main01" style="width:100%;">
        <div class="main01_left" style="width:100%;">
            <div id="chart2"></div>
            <script>
            // var data_arr = <?=json_encode($list)?>;
            // console.log(JSON.stringify(data_arr));
            var week = ['일','월','화','수','목','금','토'];
            var we_arr = <?=json_encode($we_arr2)?>;
            $(function(e) {

                // 문자통지
                $(document).on('click','.btn_sms_project_schedule',function(e) {
                    e.preventDefault();
                    var href = $(this).attr('href');
                    winSMSProject = window.open(href,"winSMSProject","left=50,top=100,width=520,height=600,scrollbars=1");
                    winSMSProject.focus();
                });

            });


            Highcharts.ganttChart('chart2', {
            chart: {
                height: 165+35*<?=($gantt_y+1)?>,
                events: {
                    load: function(){
                        var st = Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']+5)?>, 0);
                        var ed = Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']+40)?>, 0);//
                        this.xAxis[0].setExtremes(st,ed);
                        for(var idx in we_arr){
                            //console.log(we_arr[idx]);
                            var d = new Date(we_arr[idx]);
                            var x_val = d.getTime();
                            var x_axi = this.xAxis[0].axis;
                            //console.log(x_val);
                            this.xAxis[0].addPlotLine({
                                value:(x_val + (1000*60*60*12)),
                                width:16,
                                color:'#efefef'
                            });
                        }
                        var t = new Date(getToday());
                        //var t = new Date('2021-03-21');
                        var today = t.getTime() + (1000*60*60*12);
                        //console.log(today);
                        //$('.date_today').text(getToday()+'('+(week[new Date(getToday()).getDay()])+')');
                        this.xAxis[0].addPlotLine({
                            value: today,
                            width:2,
                            color: 'red',
                            label: {
                                text: getToday(),
                                align:'right',
                                rotation: 0,
                                y: 14,
                                x: 80,
                                style: {
                                    color: 'red'
                                }
                            }
                        });
                    },
                    click: function(e) {
                        /*
                        var xValue = e.xAxis[0].value;
                        var xAxis = e.xAxis[0].axis;
                        //console.log(xValue);
                        var dt = new Date(formatDate(xValue));
                        var pt = dt.getTime() + (1000*60*60*12);
                        //$('.date_today').text(getToday()+'('+(week[new Date(getToday()).getDay()])+')');
                        $('.date_target').text(formatDate(pt)+'('+(week[new Date(formatDate(pt)).getDay()])+')');
                        //console.log(formatDate(xValue));
                        $.each(xAxis.plotLinesAndBands,function(){
                            if(this.id === 'myPlotLineId'){
                                this.destroy();
                            }
                        });
                        xAxis.addPlotLine({
                            value: pt,//xValue,//pt
                            width:2,
                            color: 'blue',
                            label: {
                                text: formatDate(pt),//formatDate(xValue - (60*60*12)),//pt
                                align:'right',
                                rotation: 0,
                                y: 14,
                                x: 80,
                                style: {
                                    color: 'blue'
                                }
                            },
                            id: 'myPlotLineId'
                        });
                        */
                    }
                }
            },
            xAxis: [
                { // day display, first x-axis from bottom
                    min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']-10)?>, 0),
                    max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=($en_date1['day'])?>, 23, 59, 59),
                    tickInterval: 1000 * 60 * 60 * 24 // 1 day
                    ,labels: {
                        format: '{value:%d}' // day of the week
                    },
                    grid: {
                        cellHeight: 30
                    }
                }
                ,{ // month display, 2nd x-axis from bottom
                    min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']-10)?>, 0),
                    max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=($en_date1['day'])?>, 23, 59, 59),
                    tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
                    ,labels: {
                        format: '{value:%Y-%m}'
                    },
                    grid: {
                        cellHeight: 30
                    },
                    id: 'bottom-datetime-axis'
                }
            ],
            yAxis: {
                uniqueNames: true,
                staticScale: 20
            },
            navigator: {
                enabled: true,
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
                enabled: true
            },
            rangeSelector: {
                enabled: true,
                selected: 0
            },
            tooltip: {
                useHTML: true,
                formatter: function(tooltip) {
                    tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">'+this.point.options.start_dt+'~'+this.point.options.end_dt+'</span>';
                    if(this.point.options.content) {
                        tooltip1 += '<br/><span style="font-size:0.9em;">'+this.point.options.content+'</span>';
                    }
                    return tooltip1;
                }
            },
            yAxis: {
                type: 'category',
                grid: {
                    borderColor: '#dddddd',       
                    style: {
                        
                    },
                    columns: [
                    {
                        title: {
                            text: '프로젝트',
                            rotation: 0,
                            y: -0,
                            x: -15
                        },
                        labels: {
                            //format: '{point.name}',
                            align:'left',
                            formatter: function(){
                                //return '<a href="javascript:" onclick="sch_project('+this.point.prj_idx+');">'+this.point.name+'</a>';
                                return '<a href="javascript:">'+this.point.name+'</a>';
                            },
                            useHTML: true,
                            style: {
                                fontSize:'1em',
                                fontWeight: 'bold'
                            }
                        }
                    },
                    {
                        title: {
                            text: '역할', // 역할
                            rotation: 0,
                            y: -0,
                            x: 0
                        },
                        labels: {
                            format: '{point.role}'
                        }
                    },
                    {
                        title: {
                        text: '담당자',  // 담당자이름
                        rotation: 0,
                            y: 0,
                            x: 0
                        },
                        labels: {
                            format: '{point.assignee}',
                            formatter: function(){
                                //return '<a href="javascript:" onclick="sch_worker(\''+this.point.assignee+'\');">'+this.point.assignee+'</a>';
                                return '<a href="javascript:">'+this.point.assignee+'</a>';
                            },
                            useHTML: true,
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
                            /*
                            click: function () {
                                // console.log(this.options);
                                location.href = './project_schedule_form.php?gant=1&w=u&prs_idx=' +
                                    this.options.prs_idx + '&sst=<?=$sst?>&sod=<?=$sod?>&sfl=<?=$sfl?>&stx=<?=$stx?>&page=<?=$page?>';
                            }
                            */
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
                            prj_idx: '".$list[$i]['prj_idx']."',
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

            removeLogo2();
            // highchart.com이라는 로고 제거
            function removeLogo2() {
                //Highcharts.com 로고 제거
                setTimeout(function(e){
                    $('.highcharts-credits').remove();
                },10);
            }

            function formatDate(date) {
                var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
                
                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;
                return [year, month, day].join('-');   
            }

            function getToday(){
                var date = new Date();
                var year = date.getFullYear();
                var month = ("0" + (1 + date.getMonth())).slice(-2);
                var day = ("0" + date.getDate()).slice(-2);

                return year + "-" + month + "-" + day;
            }
            </script>
        </div>
    </div>
</section>
<?php
include_once ('./_tail.php');
?>
