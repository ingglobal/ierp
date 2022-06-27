<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '프로젝트통계';
include_once('./_top_menu_stat.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

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


// add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/index.css">', 0);
// add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/index.js"></script>', 10);
?>
<style>
.table1 {width:1020px;min-width:1020px;}
.table1 td {vertical-align:top;border:solid 0px red;}
div[class$='_left'] {float:left;}
div[class$='_right'] {float:right;}

.div_main_title {background:#ddd;height:36px;line-height:36px;margin-bottom:5px;padding-left:10px;font-weight:bold;font-size:1.08em;position:relative;}
.div_main01:after{display:block;visibility:hidden;clear:both;content:'';}
div[class^='div_main0'] {margin-bottom:20px;}
.st_more {position: absolute;top: 5px;right: 5px;display: block;width: 40px;line-height: 25px;color: #3a8afd !important;border-radius: 3px;font-weight:normal;font-size:0.85em;}
.ul_list {border:solid 1px #ddd;padding:0 10px 10px;}
.ul_list li {border-bottom:solid 1px #e5ecee;padding:5px 0;}
.ul_list li:last-child {border-bottom:none;}
.ul_list .prj_com_name {font-size:0.9em;color:#818181;}
.ul_list .prj_end_company {font-size:0.9em;color:#a9a9a9;margin-left:5px;}
.ul_calculate {display:block;}
.ul_calculate li {border:solid 1px #ddd;border-radius:5px;padding:10px;background:#5b8c41;}
.ul_calculate li .span1 {display:block;color:#e1e9dc;}
.ul_calculate li .span2 {font-size:2em;color:#fcfe30;}
.ul_calculate li .span3 {color:#e1e9dc;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/modules/gantt.js"></script>
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
Highcharts.setOptions({
	lang: {
  	thousandsSep: ','
  }
})
</script>


<table class="table1">
<tr>
    <td style="width:70%;">

        <div class="div_main_title">
            프로젝트
            <a href="./project_list.php" class="st_more">더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_left">

                <!-- 상태별 -->
                <?php
                $sql = "SELECT (CASE WHEN n='1' THEN prj_status ELSE 'total' END) AS item_name
                            , SUM(count_total) AS count_total
                        FROM
                        (
                            SELECT 
                                prj_status
                                , SUM(count_total) AS count_total
                            FROM
                            (
                                SELECT prj_status
                                    , COUNT(prj_idx) AS count_total
                                FROM g5_1_project
                                WHERE prj_status NOT IN ('trash','delete')
                                GROUP BY prj_status
                            ) AS db_table
                            GROUP BY prj_status
                        ) AS db2, g5_5_tally AS db_no
                        WHERE n <= 2
                        GROUP BY item_name
                        ORDER BY n DESC, item_name
                ";
                // echo $sql;
                $rs = sql_query($sql,1);
                $list = array();
                for ($i=0; $row=sql_fetch_array($rs) ; $i++) {
                    // print_r2($row);
                    if($row['item_name']=='total') {
                        $item_total = $row['count_total'];
                    }
                    else {
                        // 두번째줄부터 배열시작됩니다. (첫줄은 합계)
                        $list[($i-1)]['name'] = $row['item_name']; 
                        $list[($i-1)]['y'] = ($item_total) ? round(($row['count_total']/$item_total*100),1) : 0;
                    }
                }
                // print_r2($list);
                ?>
                <div id="chart01" style="width:250px;height:250px;"></div>
                <script>
                Highcharts.chart('chart01', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: '상태별분포'
                    },
                    tooltip: {
                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                    },
                    accessibility: {
                        point: {
                            valueSuffix: '%'
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            colors: pieColors,
                            dataLabels: {
                                enabled: true,
                                format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                                distance: -50,
                                filter: {
                                    property: 'percentage',
                                    operator: '>',
                                    value: 4
                                }
                            }
                        }
                    },
                    series: [{
                        name: '비율',
                        data: [
                        <?php
                        for ($i=0; $i < sizeof($list); $i++) {
                            if($i>0) echo ', ';
                            echo "{ name: '".$g5['set_prj_status_value'][$list[$i]['name']]."', y: ".$list[$i]['y']." }".PHP_EOL;
                        }
                        ?>
                        ]
                        // data: [
                        //     { name: 'Chrome', y: 61.41 },
                        //     { name: 'Internet Explorer', y: 11.84 },
                        //     { name: 'Firefox', y: 10.85 },
                        //     { name: 'Edge', y: 4.67 },
                        //     { name: 'Safari', y: 4.18 },
                        //     { name: 'Other', y: 7.05 }
                        // ]
                    }]
                });
                removeLogo();
                </script>

            </div>
            <div class="main01_right">

                <!-- 이달 제출금액 -->
                <?php
                //-- 기본 검색값 할당 (당일이므로 같은 값)
                $ym = ($ym) ? $ym:substr(G5_TIME_YMD,0,7);
                $st_date = $ym."-01";
                $en_date = $ym."-31";
                $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                            , SUM(price_total) AS price_total
                        FROM
                        (
                            SELECT 
                                ymd_date
                                , SUM(price_total) AS price_total
                            FROM
                            (
                                (
                                SELECT 
                                    CAST(ymd_date AS CHAR) AS ymd_date
                                    , 0 AS price_total
                                FROM g5_5_ymd AS ymd
                                WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                ORDER BY ymd_date
                                )
                                UNION ALL
                                (
                                SELECT
                                    SUBSTRING(prj_contract_date,1,10) AS ymd_date
                                    , SUM(prp_price) AS price_total
                                FROM g5_1_project AS prj
                                    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
                                WHERE prj_status NOT IN ('trash','delete')
                                    AND prp_type IN ('order')
                                    AND prj_contract_date != '0000-00-00'
                                    AND prj_contract_date >= '".$st_date."'
                                    AND prj_contract_date <= '".$en_date."'
                                GROUP BY ymd_date            
                                )
                            ) AS db_table
                            GROUP BY ymd_date
                        ) AS db2, g5_5_tally AS db_no
                        WHERE n <= 2
                        GROUP BY item_name
                        ORDER BY n DESC, item_name
                ";
                // echo $sql;
                $rs = sql_query($sql,1);
                $list = array();
                for ($i=0; $row=sql_fetch_array($rs) ; $i++) {
                    // print_r2($row);
                    if($row['item_name']=='total') {
                        $item_total = $row['price_total'];
                    }
                    else {
                        // 두번째줄부터 배열시작됩니다. (첫줄은 합계)
                        $list[($i-1)]['day'] = substr($row['item_name'],5); 
                        $list[($i-1)]['y'] = $row['price_total'];
                    }
                }
                // print_r2($list);
                ?>
                <div id="chart02" style="width: 450px; height: 250px;"></div>
                <script>
                Highcharts.chart('chart02', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '월간수주금액'
                    },
                    xAxis: {
                        type: 'category',
                        labels: {
                            rotation: -45,
                            style: {
                                fontSize: '9px',
                                fontFamily: 'Verdana, sans-serif'
                            }
                        }
                    },
                    yAxis: {
                        // min: 0,
                        title: {
                            text: null
                        },
                        labels: {
                            formatter: function () {
                                return Highcharts.numberFormat(this.value, 0) + '원';
                                // return this.value;
                            }
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        // pointFormat: '<b>{point.y:.1f}</b>'
                        // pointFormat: '<b>{point.y}</b>'
                        // pointFormat: '<b>'+Highcharts.numberFormat(point.y, 0)+'</b>'
                        // pointFormat: `<b>${Highcharts.numberFormat(point.y, 0)}</b>`
                        crosshairs: false,
                        shared: true,
                        valuePrefix: '수주금액 ',
                        valueSuffix: ' 원',
                        // borderColor: '#651FFF',                        
                        pointFormat: '<b>{point.y}</b>'
                    },
                    series: [{
                        name: '수주금액',
                        data: [
                        <?php
                        for ($i=0; $i < sizeof($list); $i++) {
                            if($i>0) echo ', ';
                            // echo "{ '".$list[$i]['day']."': ".$list[$i]['y']." }".PHP_EOL;
                            echo "[ '".$list[$i]['day']."', ".$list[$i]['y']." ]".PHP_EOL;
                        }
                        ?>
                        ]
                        // data: [
                        //     ['10-01', 1390000],
                        //     ['10-02', 2012320],
                        //     ['10-03', 2012314.9],
                        //     ['10-04', 2012313.7],
                        //     ['10-05', 2012313.1],
                        //     ['10-06', 2012312.7],
                        //     ['10-07', 2012312.4],
                        //     ['10-08', 2012312.2],
                        //     ['10-09', 2012312.0],
                        //     ['10-10', 2012311.7],
                        //     ['10-11', 2012311.5],
                        //     ['10-12', 2012311.2],
                        //     ['10-13', 2012311.1],
                        //     ['10-14', 2012310.6],
                        //     ['10-15', 2012310.6],
                        //     ['10-16', 2012310.6],
                        //     ['10-17', 2012310.3],
                        //     ['10-18', 201239.8],
                        //     ['10-19', 201239.3],
                        //     ['10-20', 201239.3],
                        //     ['10-21', 2012311.7],
                        //     ['10-22', 2012311.5],
                        //     ['10-23', 2012311.2],
                        //     ['10-24', 2012311.1],
                        //     ['10-25', 2012310.6],
                        //     ['10-26', 2012310.6],
                        //     ['10-27', 2012310.6],
                        //     ['10-28', 2012310.3],
                        //     ['10-29', 201239.8],
                        //     ['10-30', 201239.3],
                        //     ['10-31', 201239.3]
                        // ]
                    }]
                });                    
                </script>


            </div>
        </div>

        <script>
            window.Highcharts = null;
        </script>
        <script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>

        <div class="div_main_title">
            프로젝트일정
            <a href="./project_gantt.php" class="st_more">더보기</a>
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
                <div id="chart03" style="width:710px;"></div>
                <script>
                Highcharts.ganttChart('chart03', {
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

        <div class="div_main01">
            <?php
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
            $prp = sql_fetch($sql,1);
            // print_r2($prp);
            ?>
            <ul class="ul_calculate">
                <li><span class="span1"><?=substr(G5_TIME_YMD,5,2)?>월 매출합계</span>
                    <span class="span2"><?=number_format($prp['price_total'])?></span>
                    <span class="span3">원</span>
                </li>
            </ul>
        </div>

        <div class="div_main01">
            <?php
            $ym = ($ym) ? $ym:substr(G5_TIME_YMD,0,7);
            $sql = "SELECT SUM(prp_price) AS price_total
                    FROM g5_1_project_price
                    WHERE prp_status NOT IN ('trash','delete')
                        AND prp_type IN ('manday','buy','etc')
                        AND prp_pay_date >= '".$st_date."'
                        AND prp_pay_date <= '".$en_date."'
            ";
            // echo $sql;
            $prp5 = sql_fetch($sql,1);
            // print_r2($prp);
            ?>
            <ul class="ul_calculate">
                <li><span class="span1"><?=substr(G5_TIME_YMD,5,2)?>월 매입합계</span>
                    <span class="span2"><?=number_format($prp5['price_total'])?></span>
                    <span class="span3">원</span>
                </li>
            </ul>
        </div>

        <?php
        // A/S 관리
        echo latest10('theme/basic_intra', 'as', 3, 20);
        ?>

        <div class="div_main_title">
            프로젝트관리
            <a href="./project_list.php" class="st_more">더보기</a>
        </div>
        <div class="div_main01">
            <?php
            $sql = " SELECT *
                    , (SELECT com_name FROM {$g5['company_table']} WHERE com_idx = prj.com_idx ) AS prj_com_name
                    FROM {$g5['project_table']} AS prj
                    WHERE prj_status NOT IN ('trash','delete')
                    ORDER BY prj.prj_reg_dt DESC
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

                // 1번 라인 ================================================================================
                echo '<li class="'.$bg.' li_id="'.$row['prj_idx'].'">'.PHP_EOL;
                ?>
                    <a href="./project_form.php?w=u&prj_idx=<?=$row['prj_idx']?>"><?=cut_str($row['prj_name'],25)?></a>
                    <div class="prj_info_sub">
                        <span class="prj_com_name"><?=$row['prj_com_name']?></span>
                        <span class="prj_end_company"><?=$row['prj_end_company']?></span>
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

    </td>
</tr>
</table>



<div style="height:30px;border:solid 0px red;"></div>
<?php
include_once ('./_tail.php');
?>
