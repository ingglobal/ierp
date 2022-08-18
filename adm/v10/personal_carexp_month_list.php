<?php
$sub_menu = "960630";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
// $qstr .= ($year_month) ? '&year_month='.$year_month : ''; // 추가로 확장해서 넘겨야 할 변수들
// $qstr .= ($mb_name) ? '&mb_name='.$mb_name : ''; // 추가로 확장해서 넘겨야 할 변수들

$ym2_arr = months_range(G5_TIME_YMD,2); 
// print_r3($ym2_arr);
$apv_ym_arr = explode('-',$ym2_arr[1]);
$apv_y = $apv_ym_arr[0].'년';
$apv_m = $apv_ym_arr[1].'월';
$apv_ym = $apv_y.' '.$apv_m;

$mb_sql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level < 9 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name NOT IN('일정관리','테스트','테스일','최호기','허준영') ORDER BY mb_name ";
// echo $mb_sql;
$mb_result = sql_query($mb_sql,1);
$mb_arr = array();
$ym_arr = months_range(G5_TIME_YMD,12,'asc');
$ym_total_cars = array();
$ym_total_exps = array();
$ym_total_arr = array();
$ym_monthkm_arr = array();
for($m=0;$mrow=sql_fetch_array($mb_result);$m++){
    foreach($ym_arr as $ym){
        $ym_total_cars[$ym] = 0;
        $ym_total_exps[$ym] = 0;
        $ym_total_arr[$ym] = 0;
        $ym_monthkm_arr[$ym] = 0;
    }
    $mb_arr[$mrow['mb_id']];
    $mb_arr[$mrow['mb_id']][$mrow['mb_name']][$ym] = array(
        'mb_name' => ''
        ,'pcu_sum_price' => 0
        ,'pep_sum_price' => 0
        ,'pcu_sum_km' => 0
        ,'p_total_price' => 0
    );
}

// print_r3($mb_arr);


$g5['title'] = '개인경비월별전체통계';
if($super_admin){
    include_once('./_top_menu_personalcaruse.php');
}
include_once('./_head.php');
echo $g5['container_sub_title'];

$show_months = 13; //몇개월치를 볼것인가?


if (!$sst) {
    $sst = "mb_name";
    $sod = "";
}

$sql_group = " GROUP BY MONTH(p_date), mb_id ";

$sql_order = " ORDER BY {$sst} {$sod} ";

$uni_sql = " SELECT pcu_idx AS p_idx
                    ,pcu.mb_id
                    ,mb.mb_name
                    ,pcu_date AS p_date
                    ,pcu_why AS p_topic
                    ,pcu_reason AS p_desc
                    ,pcu_start_km AS p_start_km
                    ,pcu_arrival_km AS p_arrival_km
                    ,pcu_per_price AS p_per_price
                    ,pcu_per_km AS p_per_km
                    ,pcu_oil_type AS p_oil_type
                    ,pcu_price AS p_price
                    ,pcu_price
                    ,'0' AS pep_price
                    ,pcu_status AS p_status
                    ,pcu_reg_dt AS p_reg_dt
                    ,pcu_update_dt AS p_update_dt
                    ,'pcu' AS p_type
            FROM {$g5['personal_caruse_table']} AS pcu
            LEFT JOIN {$g5['member_table']} AS mb ON pcu.mb_id = mb.mb_id
                WHERE pcu_status = 'ok'
                    AND pcu_date >= DATE_SUB(pcu_date, INTERVAL {$show_months} MONTH)

            UNION

            SELECT pep_idx AS p_idx
                    ,pep.mb_id
                    ,mb.mb_name
                    ,pep_date AS p_date
                    ,pep_subject AS p_topic
                    ,pep_content AS p_desc
                    ,'0' AS p_start_km
                    ,'0' AS p_arrival_km
                    ,'0' AS p_per_price
                    ,'0' AS p_per_km
                    ,'' AS p_oil_type
                    ,pep_price AS p_price
                    ,'0' AS pcu_price
                    ,pep_price
                    ,pep_status AS p_status
                    ,pep_reg_dt AS p_reg_dt
                    ,pep_update_dt AS p_update_dt
                    ,'pep' AS p_type
            FROM {$g5['personal_expenses_table']} AS pep
            LEFT JOIN {$g5['member_table']} AS mb ON pep.mb_id = mb.mb_id
                WHERE pep_status = 'ok'
                    AND pep_date >= DATE_SUB(pep_date, INTERVAL {$show_months} MONTH)
";
// echo $uni_sql;
$sql = " SELECT mb_id
                ,mb_name
                , SUM(p_arrival_km - p_start_km) AS p_sum_km
                , CONCAT(YEAR(p_date),'-',LPAD(MONTH(p_date),'2','0')) AS p_month
                , CONCAT(YEAR(p_date),'년',LPAD(MONTH(p_date),'2','0'),'월') AS p_month2
                , CONCAT(YEAR(p_date),'-',LPAD(MONTH(p_date),'2','0'),'%') AS p_month_sch
                , SUM(pcu_price) AS sum_pcu_price
                , SUM(pep_price) AS sum_pep_price
                , SUM(p_price) AS sum_person_price
                , ( SELECT SUM(pcu_price)
                    FROM {$g5['personal_caruse_table']}
                    WHERE pcu_status = 'ok'
                        AND pcu_date LIKE p_month_sch
                ) AS month_pcu_price
                , ( SELECT SUM(pcu_arrival_km - pcu_start_km)
                    FROM {$g5['personal_caruse_table']}
                    WHERE pcu_status = 'ok'
                        AND pcu_date LIKE p_month_sch
                ) AS month_km
                , ( SELECT SUM(pep_price)
                    FROM {$g5['personal_expenses_table']}
                    WHERE pep_status = 'ok'
                        AND pep_date LIKE p_month_sch
                ) AS month_pep_price
            FROM ( {$uni_sql} ) AS pun
        {$sql_group}
        {$sql_order}
";

// echo $sql;
$result = sql_query($sql,1);
/*
$mb_arr(
     [ing25481444] => Array
        [권순우] => Array
             [2022-07] => Array
                [mb_name] => 
                [pcu_sum_price] => 0
                [pep_sum_price] => 0
                [pcu_sum_km] => 0
                [p_total_price] => 0
)
$row(
    [mb_id] => ing25481444
    [mb_name] => 권순우
    [p_sum_km] => 0
    [p_month] => 2022-06
    [p_month2] => 2022년06월
    [p_month_sch] => 2022-06%
    [sum_pcu_price] => 0
    [sum_pep_price] => 55440
    [sum_person_price] => 55440
)
*/

// print_r2($mb_arr);
for($i=0;$row=sql_fetch_array($result);$i++){
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['p_month']]['mb_name'] = $row['mb_name'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['p_month']]['pcu_sum_price'] = $row['sum_pcu_price'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['p_month']]['pep_sum_price'] = $row['sum_pep_price'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['p_month']]['pep_sum_km'] = $row['p_sum_km'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['p_month']]['p_total_price'] = $row['sum_person_price'];
    // print_r2($row);
    $ym_total_cars[$row['p_month']] = $row['month_pcu_price'];
    $ym_total_exps[$row['p_month']] = $row['month_pep_price'];
    $ym_total_arr[$row['p_month']] = $row['month_pcu_price'] + $row['month_pep_price'];
    $ym_monthkm_arr[$row['p_month']] = $row['month_km'];
}
// print_r3($ym_total_cars);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';


$colspan = count($ym_arr) + 2;
$total_price = 0;
?>
<style>
#container{min-width:1800px !important;}

#tot_box{position:absolute;display:none;top:10px;right:10px;font-size:1.3em;}
#tot_box:after{display:block;visibility:hidden;clear:both;content:'';}
#tot_box strong{color:#555;float:left;font-weight:500;}
#tot_box #tot_price{float:left;margin-left:10px;font-weight:700;color:darkblue;font-size:1.2em;}

.td_pcu_date{width:90px;}
.td_pcu_why{width:170px;}
.td_pcu_reason{width:400px;}
.td_pcu_start_km{width:100px;}
.td_pcu_arrival_km{width:100px;}
.td_pcu_diff_km{width:80px;text-align:right !important;}
.td_pcu_oil_type{width:130px;}
.td_pcu_per_price{width:100px;}
.td_pcu_per_km{width:30px;}
.td_pcu_price{width:100px;text-align:right !important;}
.td_caruse_sum{position:relative;padding-top:14px !important;padding-bottom:6px !important;}
.pers_km{position:absolute;top:0;left:3px;color:blue;font-size:0.9em;}
.month_km{position:absolute;top:-4px;left:3px;color:darkred;font-size:0.8em;}
.tr_even{background:#efefef !important;}
.tot_cars{position:absolute;top:-4px;left:4px;font-size:0.6em;color:darkred;}
.tot_exps{position:absolute;top:-4px;right:4px;font-size:0.6em;color:blue;}
.tot_km{position:absolute;bottom:-4px;left:4px;font-size:0.6em;color:darkred;}
.tot_ttl{}
.dv_cars{position:absolute;top:-4px;left:4px;font-size:0.7em;color:darkred;}
.dv_exps{position:absolute;top:-4px;right:4px;font-size:0.7em;color:blue;}
.dv_km{position:absolute;bottom:-4px;left:4px;font-size:0.7em;color:darkred;}
.dv_ttl{}
#dv_approval{text-align:right;position:relative;}
#dv_approval h1{position:absolute;top:10px;left:10px;font-size:3em;}
#dv_approval p{position:absolute;top:90px;left:10px;font-size:1.6em;}
#dv_approval ul{display:inline-block;padding-bottom:10px;}
#dv_approval ul li{float:left;border:1px solid #333;width:120px;text-align:center;}
#dv_approval ul li .dv_mgr{}
#dv_approval ul li .dv_sign{height:100px;border-top:1px solid #333;}
</style>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<div class="local_ov01 local_ov" style="display:none;">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>
<div id="pdf_box">
    <div id="dv_approval">
        <h1><?=$g5['title']?>(<?=$apv_ym?>)</h1>
        <p><?=G5_TIME_YMD?></p>
        <ul>
            <li>
                <div class="dv_mgr">담당자</div>
                <div class="dv_sign"></div>
            </li>
            <li style="margin-left:-1px;">
                <div class="dv_mgr">대표</div>
                <div class="dv_sign"></div>
            </li>
        </ul>
    </div>
    <div class="local_desc01 local_desc" style="display:none;position:relative;">
        <p><?php if(!$super_admin){ echo '<span style="color:blue;">'.$member['mb_name'].'</span>님의 '; } ?>개인차량사용내역을 관리하는 페이지입니다.</p>
    </div>
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
        <tr>
            <th scope="col">번호</th>
            <th scope="col">이름</th>
            <?php foreach($ym_arr as $ym_v){ ?>
            <th scope="col"><?=$ym_v?>월</th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <tr style="background:#eff1cc;">
            <td colspan="2" class="td_total_ttl">월별총합계</td>
            <?php foreach($ym_total_arr as $mk => $mv){ ?>
            <td class="td_caruse_sum" style="text-align:right;font-weight:700;">
                <?php if($ym_total_cars[$mk]){ ?><div class="tot_cars"><?=number_format($ym_total_cars[$mk])?>(차)</div><?php } ?>
                <?php if($ym_total_exps[$mk]){ ?><div class="tot_exps"><?=number_format($ym_total_exps[$mk])?>(지)</div><?php } ?>
                <?php if($ym_monthkm_arr[$mk]){ ?><div class="tot_km"><?=number_format($ym_monthkm_arr[$mk])?>(k)</div><?php } ?>
                <?php if($mv){ ?><div class="tot_arr"><?=number_format($mv)?><span>원</span></div><?php } ?>
            </td>
            <?php } ?>
        </tr>
        <?php
        $no = 0;
        foreach($mb_arr as $k => $v){
            $no++;
            $tr_bg = ($no % 2 == 0)?'tr_even':'';
        ?>
        <tr class="<?=$tr_bg?>">
            <td class="td_no"><?=$no?></td>
            <td class="td_mb_name">
            <?php
            $va = array_keys($v);
            echo $va[0];
            ?>
            </td>
            <?php foreach($ym_arr as $ymv){ ?>
            <td class="td_caruse_sum" style="text-align:right;">
                <?=(($v[$va[0]][$ymv]['pcu_sum_price'])?'<div class="dv_cars">'.number_format($v[$va[0]][$ymv]['pcu_sum_price']).'(차)</div>':'')?>
                <?=(($v[$va[0]][$ymv]['pep_sum_price'])?'<div class="dv_exps">'.number_format($v[$va[0]][$ymv]['pep_sum_price']).'(지)</div>':'')?>
                <?=(($v[$va[0]][$ymv]['pep_sum_km'])?'<div class="dv_km">'.number_format($v[$va[0]][$ymv]['pep_sum_km']).'(k)</div>':'')?>
                <?=(($v[$va[0]][$ymv]['p_total_price'])?'<div class="dv_ttl">'.number_format($v[$va[0]][$ymv]['p_total_price']).'<span>원</span></div>':'')?>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div><!--//.tbl_head01-->
</div><!--//#pdf_box-->
<div class="btn_fixed_top">
    <a href="javascript:" class="btn btn_02 pdf_btn">PDF다운로드</a>
</div>
<?php if($total_price){ ?>
<script>
$('#tot_box').css('display','block');
$('#tot_price').text('<?=number_format($total_price)?>원');
</script>
<?php } ?>


<script>
//pdf다운로드 버튼을 클릭하면
$('.pdf_btn').on('click',function(){
    //pdf_wrap을 canvas객체로 변환
    html2canvas($('#pdf_box')[0]).then(function(canvas) {
        var doc = new jsPDF('p', 'mm', 'a4'); //jspdf객체 생성
        var imgData = canvas.toDataURL('image/png'); //캔버스를 이미지로 변환
        var imgWidth = 200;//pageHeight * 3; // 이미지 가로 210길이(mm) A4 기준
        var pageHeight = imgWidth * 1.414;//imgWidth * 1.414;  // 출력 페이지 세로 길이 계산 A4 기준
        var imgHeight = canvas.height * imgWidth / canvas.width;
        var heightLeft = imgHeight;
        var pos_x = 5;
        var pos_y = 5;

        doc.addImage(imgData, 'PNG', pos_x, pos_y, imgWidth, imgHeight); //이미지를 기반으로 pdf생성

        doc.save('<?php echo get_text($g5['title'].'_'.$apv_ym) ?>.pdf'); //pdf저장
    });
});
</script>
<?php
include_once ('./_tail.php');
?>
