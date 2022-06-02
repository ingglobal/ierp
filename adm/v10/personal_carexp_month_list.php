<?php
$sub_menu = "960630";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'personal_caruse';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
// $qstr .= ($year_month) ? '&year_month='.$year_month : ''; // 추가로 확장해서 넘겨야 할 변수들
// $qstr .= ($mb_name) ? '&mb_name='.$mb_name : ''; // 추가로 확장해서 넘겨야 할 변수들


$mb_sql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level < 8 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name NOT IN('일정관리','테스트','테스일','최호기','허준영') ORDER BY mb_name ";
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
        ,'pcu_sum' => 0
        ,'pcu_total' => 0
        ,'pcu_total_km' => 0
    );
}

// print_r3($mb_arr);


$g5['title'] = '개인월별전체통계';
if($super_admin){
    include_once('./_top_menu_personalcaruse.php');
}
include_once('./_head.php');
echo $g5['container_sub_title'];

$show_months = 13; //몇개월치를 볼것인가?

$sql_common = " FROM {$g5['personal_caruse_table']} AS pcu
                    LEFT JOIN {$g5['member_table']} AS mb ON pcu.mb_id = mb.mb_id
";


$where = array();
//$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " pcu_status = 'ok' ";   // 디폴트 검색조건
$where[] = " pcu_date >= DATE_SUB(pcu_date, INTERVAL {$show_months} MONTH) ";   // 디폴트 검색조건


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "pcu_date";
    $sod = "";
}

if (!$sst2) {
    $sst2 = ", mb_name";
    $sod2 = "";
}

$sql_group = " GROUP BY MONTH(pcu_date), pcu.mb_id ";

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = 100;//25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT (ROW_NUMBER() OVER(ORDER BY pcu_date)) AS num
            , pcu.mb_id
            , mb_name
            , SUM(pcu_arrival_km - pcu_start_km) AS pcu_total_km
            , CONCAT(YEAR(pcu_date),'-',LPAD(MONTH(pcu_date),'2','0')) AS pcu_month
            , CONCAT(YEAR(pcu_date),'년',LPAD(MONTH(pcu_date),'2','0'),'월') AS pcu_month2
            , CONCAT(YEAR(pcu_date),'-',LPAD(MONTH(pcu_date),'2','0'),'%') AS pcu_month_sch
            , SUM(pcu_price) AS pcu_sum
            , ( SELECT COUNT(*)
                FROM {$g5['personal_caruse_table']}
                WHERE pcu_status = 'ok'
                    AND pcu_date LIKE pcu_month_sch
            ) AS pcu_cnt
            , ( SELECT SUM(pcu_price)
                FROM {$g5['personal_caruse_table']}
                WHERE pcu_status = 'ok'
                    AND pcu_date LIKE pcu_month_sch
            ) AS pcu_sum2
            , ( SELECT SUM(pcu_arrival_km - pcu_start_km)
                FROM {$g5['personal_caruse_table']}
                WHERE pcu_status = 'ok'
                    AND pcu_date LIKE pcu_month_sch
            ) AS pcu_month_km
            , (
                SELECT SUM(pep_price) 
                        FROM {$g5['personal_expenses_table']}
                    WHERE pep_status = 'ok'
                        AND pep_date LIKE pcu_month_sch
                        AND mb_id = pcu.mb_id
            ) AS pep_sum
            , ( SELECT SUM(pep_price)
                FROM {$g5['personal_expenses_table']}
                WHERE pep_status = 'ok'
                    AND pep_date LIKE pcu_month_sch
            ) AS pep_sum2
        {$sql_common}
        {$sql_search}
        {$sql_group}
        {$sql_order}
";
        //LIMIT {$from_record}, {$rows}

// echo $sql;
$result = sql_query($sql,1);
/*
$mb_arr(
    [tomasjoa](
        [임채완](
            [2022-04](
                'mb_name' => ''
                ,'pcu_sum' => 0
                ,'pcu_total' => 0
                ,'pcu_total_km' => 0
            )
        )
    )
)
$row(
    [num] => 2
    [mb_id] => tomasjoa
    [mb_name] => 임채완
    [pcu_month] => 2022-05
    [pcu_month2] => 2022년05월
    [pcu_month_sch] => 2022-05%
    [pcu_sum] => 62720
    [pcu_cnt] => 3
    [pcu_sum2] => 67220
    [pcu_total_km] => 23432
)
*/


for($i=0;$row=sql_fetch_array($result);$i++){
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['mb_name'] = $row['mb_name'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['pcu_sum'] = $row['pcu_sum'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['pep_sum'] = $row['pep_sum'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['pcu_total'] = $row['pcu_sum'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['pep_total'] = $row['pep_sum'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['p_total'] = $row['pcu_sum'] + $row['pep_sum'];
    $mb_arr[$row['mb_id']][$row['mb_name']][$row['pcu_month']]['pcu_total_km'] = $row['pcu_total_km'];
    $ym_total_cars[$row['pcu_month']] = $row['pcu_sum2'];
    $ym_total_exps[$row['pcu_month']] = $row['pep_sum2'];
    $ym_total_arr[$row['pcu_month']] = $row['pcu_sum2'] + $row['pep_sum2'];
    // echo ($row['pep_sum2'])."<br>";
    $ym_monthkm_arr[$row['pcu_month']] = $row['pcu_month_km'];
    // print_r3($row['pcu_sum2']);
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
.td_caruse_sum{position:relative;padding-top:14px !important;}
.pers_km{position:absolute;top:0;left:3px;color:blue;font-size:0.9em;}
.month_km{position:absolute;top:-4px;left:3px;color:darkred;font-size:0.8em;}
.tr_even{background:#efefef !important;}
.tot_cars{position:absolute;top:-4px;left:4px;font-size:0.8em;color:darkred;}
.tot_exps{position:absolute;top:-4px;right:4px;font-size:0.8em;color:blue;}
.tot_ttl{}
.dv_cars{position:absolute;top:-4px;left:4px;font-size:0.8em;color:darkred;}
.dv_exps{position:absolute;top:-4px;right:4px;font-size:0.8em;color:blue;}
.dv_ttl{}
</style>
<div class="local_ov01 local_ov" style="display:none;">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
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
            <?=(($v[$va[0]][$ymv]['pcu_total'])?'<div class="dv_cars">'.number_format($v[$va[0]][$ymv]['pcu_total']).'(차)</div>':'')?>
            <?=(($v[$va[0]][$ymv]['pep_total'])?'<div class="dv_exps">'.number_format($v[$va[0]][$ymv]['pep_total']).'(지)</div>':'')?>
            <?=(($v[$va[0]][$ymv]['p_total'])?'<div class="dv_ttl">'.number_format($v[$va[0]][$ymv]['p_total']).'<span>원</span></div>':'')?>
        </td>
        <?php } ?>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div><!--//.tbl_head01-->
<?php if($total_price){ ?>
<script>
$('#tot_box').css('display','block');
$('#tot_price').text('<?=number_format($total_price)?>원');
</script>
<?php } ?>


<?php ;//echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;from_date='.$from_date.'&amp;to_date='.$to_date.'&amp;page='); ?>


<script>

</script>
<?php
include_once ('./_tail.php');
?>
