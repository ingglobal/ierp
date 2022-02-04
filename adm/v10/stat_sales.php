<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '영업통계';
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
.main01_column1{display:inline-block;width:33%;}
.main01_column2{display:inline-block;width:33%;}
.main01_column3{display:inline-block;width:33%;}
div[class^='div_main0'] {margin-bottom:20px;}
.st_more {position: absolute;top: 5px;right: 5px;display: block;width: 40px;line-height: 25px;color: #3a8afd !important;border-radius: 3px;font-weight:normal;font-size:0.85em;}
.ul_list {border:solid 1px #ddd;padding:0 10px 10px;}
.ul_list li {border-bottom:solid 1px #e5ecee;padding:5px 0;}
.ul_list li:last-child {border-bottom:none;}
.ul_list .prj_com_name {font-size:0.9em;color:#818181;}
.ul_list .prj_end_company {font-size:0.9em;color:#a9a9a9;margin-left:5px;}
.ul_list .com_president {font-size:0.9em;color:#818181;}
.ul_list .com_reg_dt {float:right;font-size:0.9em;color:#a9a9a9;margin-left:5px;}
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
            영업 현황
            <a href="<?=G5_BBS_URL?>/board.php?bo_table=sales" class="st_more">더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_column1">
                <!-- 영업상태별 분포 -->
                <?php
                $bo = get_table_meta('board','bo_table','sales');
                // 상태값
                $set_values = explode(',', preg_replace("/\s+/", "", $bo['bo_9']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', $set_value);
                    $g5['set_sales_status_value'][$key] = $value;
                }
                // print_r2($g5['set_sales_status_value']);
                unset($set_values);unset($set_value);
                $sql = "SELECT (CASE WHEN n='1' THEN wr_10 ELSE 'total' END) AS item_name
                            , SUM(count_total) AS count_total
                        FROM
                        (
                            SELECT 
                                wr_10
                                , SUM(count_total) AS count_total
                            FROM
                            (
                                SELECT wr_10
                                    , COUNT(wr_id) AS count_total
                                FROM g5_write_sales
                                WHERE wr_is_comment = 0
                                    AND wr_10 != ''
                                GROUP BY wr_10
                            ) AS db_table
                            GROUP BY wr_10
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
                        text: '영업상태별'
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
                            echo "{ name: '".$g5['set_sales_status_value'][$list[$i]['name']]."', y: ".$list[$i]['y']." }".PHP_EOL;
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
            <div class="main01_column2">
                <!-- 관심등급별 분포 -->
                <?php
                // 관심등급배열
                $set_values = explode(',', preg_replace("/\s+/", "", $bo['bo_8']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', $set_value);
                    $g5['set_interest_value'][$key] = $value;
                }
                // print_r2($g5['set_interest_value']);
                unset($set_values);unset($set_value);
                $sql = "SELECT (CASE WHEN n='1' THEN wr_5 ELSE 'total' END) AS item_name
                            , SUM(count_total) AS count_total
                        FROM
                        (
                            SELECT 
                                wr_5
                                , SUM(count_total) AS count_total
                            FROM
                            (
                                SELECT wr_5
                                    , COUNT(wr_id) AS count_total
                                FROM g5_write_sales
                                WHERE wr_is_comment = 0
                                    AND wr_5 != ''
                                GROUP BY wr_5
                            ) AS db_table
                            GROUP BY wr_5
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
                <div id="chart02" style="width:250px;height:250px;"></div>
                <script>
                Highcharts.chart('chart02', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: '관심등급별'
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
                            echo "{ name: '".cut_str($g5['set_interest_value'][$list[$i]['name']],6)."', y: ".$list[$i]['y']." }".PHP_EOL;
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
            <div class="main01_column3">
                <!-- 영업자별 분포 -->
                <?php
                $sql = "SELECT (CASE WHEN n='1' THEN wr_name ELSE 'total' END) AS item_name
                            , SUM(count_total) AS count_total
                        FROM
                        (
                            SELECT 
                                wr_name
                                , SUM(count_total) AS count_total
                            FROM
                            (
                                SELECT wr_name
                                    , COUNT(wr_id) AS count_total
                                FROM g5_write_sales
                                WHERE wr_is_comment = 0
                                    AND wr_name != ''
                                GROUP BY wr_name
                            ) AS db_table
                            GROUP BY wr_name
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
                <div id="chart03" style="width:250px;height:250px;"></div>
                <script>
                Highcharts.chart('chart03', {
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                        type: 'pie'
                    },
                    title: {
                        text: '영업자별'
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
                            echo "{ name: '".$list[$i]['name']."', y: ".$list[$i]['y']." }".PHP_EOL;
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
        </div>

        <script>
            window.Highcharts = null;
        </script>
        <script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>

        <div class="div_main_title">
            월별 수주 현황
            <a href="<?=G5_BBS_URL?>/board.php?bo_table=sales" class="st_more">더보기</a>
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
                                ctr_sales_date AS ymd_date
                                , SUM(ctr_price) AS price_total
                            FROM g5_1_contract
                            WHERE ctr_status IN ('ok')
                                AND ctr_sales_date >= '".$st_date."'
                                AND ctr_sales_date <= '".$en_date."'
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



        <?php
        // 영업진행현황
        echo latest10('theme/basic_intra', 'sales', 3, 20);
        ?>
        <div class="div_main_title">
            업체관리
            <a href="./company_list.php" class="st_more">더보기</a>
        </div>
        <div class="div_main01">
            <?php
            $sql = "SELECT *
                    FROM {$g5['company_table']} AS com
                    WHERE com_status NOT IN ('trash','delete')
                    ORDER BY com_reg_dt DESC
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

                echo '<li class="'.$bg.' li_id="'.$row['com_idx'].'">'.PHP_EOL;
                ?>
                    <a href="./company_form.php?w=u&com_idx=<?=$row['com_idx']?>"><?=cut_str($row['com_name'],25)?></a>
                    <div class="com_info_sub">
                        <span class="com_president"><?=$row['com_president']?></span>
                        <span class="com_reg_dt"><?=substr($row['com_reg_dt'],0,10)?></span>
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
