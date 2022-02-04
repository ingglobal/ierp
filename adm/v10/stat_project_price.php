<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '수입지출통계';
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
.ul_list .prp_com_name {font-size:0.9em;color:#818181;}
.ul_list .prp_end_company {font-size:0.9em;color:#a9a9a9;margin-left:5px;}
.ul_list .prp_price {float:right;font-size:0.9em;margin-left:5px;}
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
            수입지출 현황
            <a href="./project_group_price_list.php" class="st_more">더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_left">

                <!-- 날짜명칭별 -->
                <?php
                // 올해 분기별 접수 건수
                $year = ($year) ? $year : substr(G5_TIME_YMD,0,4);
                $ym = ($ym) ? $ym:substr(G5_TIME_YMD,0,7);
                $st_date = $year."-01-01";
                $en_date = $year."-12-31";
                $sql = "SELECT (CASE WHEN n='1' THEN prp_name ELSE 'total' END) AS item_name
                            , SUM(count_total) AS count_total
                        FROM
                        (
                            SELECT *
                            FROM (
                                    (
                                    SELECT 
                                        'prp_plan_date' AS prp_name
                                        , COUNT(prp_idx) AS count_total
                                    FROM g5_1_project_price
                                    WHERE prp_status NOT IN ('trash','delete')
                                        AND prp_plan_date != '0000-00-00'
                                        AND prp_plan_date >= '".$st_date."'
                                        AND prp_plan_date <= '".$en_date."'
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        'prp_issue_date' AS prp_name
                                        , COUNT(prp_idx) AS count_total
                                    FROM g5_1_project_price
                                    WHERE prp_status NOT IN ('trash','delete')
                                        AND prp_issue_date != '0000-00-00'
                                        AND prp_issue_date >= '".$st_date."'
                                        AND prp_issue_date <= '".$en_date."'
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        'prp_planpay_date' AS prp_name
                                        , COUNT(prp_idx) AS count_total
                                    FROM g5_1_project_price
                                    WHERE prp_status NOT IN ('trash','delete')
                                        AND prp_planpay_date != '0000-00-00'
                                        AND prp_planpay_date >= '".$st_date."'
                                        AND prp_planpay_date <= '".$en_date."'
                                    )
                                    UNION ALL
                                    (
                                    SELECT 
                                        'prp_pay_date' AS prp_name
                                        , COUNT(prp_idx) AS count_total
                                    FROM g5_1_project_price
                                    WHERE prp_status NOT IN ('trash','delete')
                                        AND prp_pay_date != '0000-00-00'
                                        AND prp_pay_date >= '".$st_date."'
                                        AND prp_pay_date <= '".$en_date."'
                                    )
                            ) AS db_table
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
                            echo "{ name: '".$g5['set_prp_data_name_value'][$list[$i]['name']]."', y: ".$list[$i]['y']." }".PHP_EOL;
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

                <?php
                // 올해 분기별 매출
                $year = ($year) ? $year : substr(G5_TIME_YMD,0,4);
                $ym = ($ym) ? $ym:substr(G5_TIME_YMD,0,7);
                $st_date = $year."-01-01";
                $en_date = $year."-12-31";
                $sql = "SELECT (CASE WHEN n='1' THEN ymd_quarter ELSE 'total' END) AS item_name
                            , SUM(price_total) AS price_total
                        FROM
                        (
                            SELECT 
                                ymd_quarter
                                , SUM(price_total) AS price_total
                            FROM
                            (
                                (
                                SELECT 
                                    n AS ymd_quarter
                                    , 0 AS price_total
                                FROM g5_5_tally AS ymd
                                WHERE n < 5
                                ORDER BY ymd_quarter
                                )
                                UNION ALL
                                (
                                SELECT
                                    QUARTER(prj_contract_date) AS ymd_quarter
                                    , SUM(prp_price) AS price_total
                                FROM g5_1_project AS prj
                                    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
                                WHERE prj_status NOT IN ('trash','delete')
                                    AND prp_type IN ('order')
                                    AND prj_contract_date != '0000-00-00'
                                    AND prj_contract_date >= '".$st_date."'
                                    AND prj_contract_date <= '".$en_date."'
                                GROUP BY ymd_quarter
                                )
                            ) AS db_table
                            GROUP BY ymd_quarter
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
                        $list[($i-1)]['quarter'] = $row['item_name']; 
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
                        text: '분기별 매출'
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
                                return Highcharts.numberFormat(this.value, 0)+'원';
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
                        valuePrefix: '',
                        valueSuffix: ' 원',
                        // borderColor: '#651FFF',                        
                        pointFormat: '<b>{point.y}</b>'
                    },
                    series: [{
                        name: '분기',
                        data: [
                        <?php
                        for ($i=0; $i < sizeof($list); $i++) {
                            if($i>0) echo ', ';
                            // echo "{ '".$list[$i]['day']."': ".$list[$i]['y']." }".PHP_EOL;
                            echo "[ '".$list[$i]['quarter']."분기', ".$list[$i]['y']." ]".PHP_EOL;
                        }
                        ?>
                        ]
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
            월별 매출 현황
            <a href="./project_group_price_list.php" class="st_more">더보기</a>
        </div>
        <div class="div_main01" style="width:100%;">

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
                                prj_contract_date AS ymd_date
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
            <div id="chart04" style="width: 710px; height: 250px;"></div>
            <script>
            Highcharts.chart('chart04', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: ''
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
                            return Highcharts.numberFormat(this.value, 0)+'원';
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
                    valuePrefix: '매출 ',
                    valueSuffix: ' 원',
                    // borderColor: '#651FFF',                        
                    pointFormat: '<b>{point.y}</b>'
                },
                series: [{
                    name: '매출',
                    data: [
                    <?php
                    for ($i=0; $i < sizeof($list); $i++) {
                        if($i>0) echo ', ';
                        // echo "{ '".$list[$i]['day']."': ".$list[$i]['y']." }".PHP_EOL;
                        echo "[ '".$list[$i]['day']."', ".$list[$i]['y']." ]".PHP_EOL;
                    }
                    ?>
                    ]
                }]
            });                    
            </script>


        </div>

    </td>
    <td><img src="<?=G5_USER_ADMIN_IMG_URL?>/dot.png" style="width:10px;"></td>
    <td style="width:30%;">

        <!-- 매출합계 -->
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
