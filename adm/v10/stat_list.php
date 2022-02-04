<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '통계';
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
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.js"></script>
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
            영업통계
            <a href="./stat_list.php?ym=<?=$ym01?>" class="st_more"><span class="sound_only">매출</span>더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_left">

                <!-- 상태별 -->
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
                        text: ''
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
            <div class="main01_right">
                <!-- 이달의 상담수 -->
                <?php
                $sql = "SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
                            , SUM(count_total) AS count_total
                        FROM
                        (
                            SELECT 
                                ymd_date
                                , SUM(count_total) AS count_total
                            FROM
                            (
                                (
                                SELECT 
                                    CAST(ymd_date AS CHAR) AS ymd_date
                                    , 0 AS count_total
                                FROM g5_5_ymd AS ymd
                                WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                                ORDER BY ymd_date
                                )
                                UNION ALL
                                (
                                SELECT
                                    SUBSTRING(wr_datetime,1,10) AS ymd_date
                                    , COUNT(wr_id) AS count_total
                                FROM g5_write_sales
                                WHERE wr_is_comment = 1
                                    AND wr_datetime >= '".$st_date." 00:00:00'
                                    AND wr_datetime <= '".$en_date." 23:59:59'
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
                        $item_total = $row['count_total'];
                    }
                    else {
                        // 두번째줄부터 배열시작됩니다. (첫줄은 합계)
                        $list[($i-1)]['day'] = substr($row['item_name'],5); 
                        $list[($i-1)]['y'] = $row['count_total'];
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
                                return Highcharts.numberFormat(this.value, 0);
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
                        valuePrefix: '상담 ',
                        valueSuffix: ' 건',
                        // borderColor: '#651FFF',                        
                        pointFormat: '<b>{point.y}</b>'
                    },
                    series: [{
                        name: '상담건수',
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


        <div class="div_main_title">
            견적통계 및 수주금액통계
            <a href="./stat_list.php?ym=<?=$ym01?>" class="st_more"><span class="sound_only">견적통계</span>더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_left">

                <!-- 상태별 -->
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
                        text: ''
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
                                    SUBSTRING(prj_submit_date,1,10) AS ymd_date
                                    , SUM(prp_price) AS price_total
                                FROM g5_1_project AS prj
                                    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
                                WHERE prj_status NOT IN ('trash','delete')
                                    AND prp_type IN ('submit')
                                    AND prj_submit_date != '0000-00-00'
                                    AND prj_submit_date >= '".$st_date."'
                                    AND prj_submit_date <= '".$en_date."'
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
                <div id="chart04" style="width: 450px; height: 250px;"></div>
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
                                return Highcharts.numberFormat(this.value, 0);
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


        <div class="div_main_title">
            수입지출통계
            <a href="./stat_list.php?ym=<?=$ym01?>" class="st_more"><span class="sound_only">매출</span>더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_left">

            </div>
            <div class="main01_right">
				<div id="columnchart_values200" style="width: 450px; height: 235px;"></div>
            </div>
        </div>


        <div class="div_main_title">
            부품견적통계
            <a href="./stat_list.php?ym=<?=$ym01?>" class="st_more"><span class="sound_only">매출</span>더보기</a>
        </div>
        <div class="div_main01">
            <div class="main01_left">

            </div>
            <div class="main01_right">
				<div id="columnchart_values200" style="width: 450px; height: 235px;"></div>
            </div>
        </div>



    </td>
    <td><img src="<?=G5_USER_ADMIN_IMG_URL?>/dot.png" style="width:10px;"></td>
    <td style="width:30%;">

		<div class="div_main_title">
            프로젝트
            <a href="./stat_list.php?ym=<?=$ym01?>" class="st_more"><span class="sound_only">매출</span>더보기</a>
        </div>
        <div class="div_main01">
			진행중인 프로젝트의 진행율을 표시합니다.
			<br>
			테이블 형태
        </div>

		<div class="div_main_title">
            A/S
            <a href="./stat_list.php?ym=<?=$ym01?>" class="st_more"><span class="sound_only">매출</span>더보기</a>
        </div>
        <div class="div_main01">
			진행중인 A/S 진행 상황을 표시합니다.
			<br>
			테이블 형태
        </div>


    </td>
</tr>
</table>



<div style="height:30px;border:solid 0px red;"></div>
<?php
include_once ('./_tail.php');
?>
