<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");


//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들
$month_m_cnt = 2;
$month_p_cnt = 2;

$g5['title'] = '프로젝트 참여일수 및 인건비 통계';
include_once('./_top_menu_stat.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$field_common = " 
    prj.prj_idx
    ,prs.prs_idx
    ,com.com_name
    ,prj.prj_name
    ,(SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_status NOT IN ('trash','delete')  AND prp_type = 'order') AS order_price
    ,prs.prs_role
    ,mb.mb_name
    ,prs.prs_start_date
    ,prs.prs_end_date
    ,(mb.mb_7) AS hour_price
    ,(mb.mb_7 * 8) AS day_price
    ,(ABS(DATEDIFF(prs.prs_start_date,prs.prs_end_date))+1) AS day_cnt
    ,((mb.mb_7 * 8) * (ABS(DATEDIFF(prs.prs_start_date,prs.prs_end_date))+1)) AS stot_price
    ,((DATEDIFF(prs.prs_end_date, prs.prs_start_date) + 1) - ((WEEK(prs.prs_end_date) - WEEK(prs.prs_start_date)) * 2) - (case when weekday(prs.prs_end_date) = 6 then 1 else 0 end) - (case when weekday(prs.prs_end_date) = 5 then 1 else 0 end)) AS week_minus_cnt
    ,((mb.mb_7 * 8) * ((DATEDIFF(prs.prs_end_date, prs.prs_start_date) + 1) - ((WEEK(prs.prs_end_date) - WEEK(prs.prs_start_date)) * 2) - (case when weekday(prs.prs_end_date) = 6 then 1 else 0 end) - (case when weekday(prs.prs_end_date) = 5 then 1 else 0 end))) AS stot_w_price
    ,prj.prj_status
    ,prs.prs_status
";
//echo $field_common;
$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['project_schedule_table']} AS prs ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 
//echo $sql_common;
$where = array();
$where[] = " prj.prj_status = 'ok' ";   // 디폴트 검색조건
$where[] = " prs.prs_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " mb.mb_id NOT IN ('iljung') ";   // 디폴트 검색조건

if ($st_date && $en_date) {
    $where[] = " prs.prs_start_date <= '$en_date' AND prs.prs_end_date >= '$st_date' ";
}
else if ($st_date && !$en_date) {
    $where[] = " prs.prs_start_date >= '$st_date' ";
}
else if (!$st_date && $en_date) {
    $where[] = " prs.prs_end_date <= '$en_date' ";
}
else{
    $month_minus = strtotime("-".$month_m_cnt." months");
    $month_plus = strtotime("+".$month_p_cnt." months");
    $date_minus = date("Y-m-d",$month_minus);
    $date_plus = date("Y-m-d",$month_plus);
    $where[] = " prs.prs_start_date <= '$date_plus' AND prs.prs_end_date >= '$date_minus' ";
}

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        case ( $sfl == 'prj.prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb.mb_name' ) :
            $where[] = " (mb.mb_name LIKE '%{$stx}%') ";
            break;
		case ($sfl == 'prj.prj_name') :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
        case ($sfl == 'prj.prj_status') :
            $stx = $g5['set_prj_status_reverse'][$stx];
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        case ($sfl == 'prs.prs_role') :
            $stx = $g5['set_worker_type_reverse'][$stx];
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "prj.prj_idx DESC, prs.prs_role, prs.mb_id_worker, prs.prs_start_date";
    $sod = "";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " SELECT 
        {$field_common}
        {$sql_common}
		{$sql_search}
        {$sql_order}
";

//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
//$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
$list = array();
$cur_prj_idx = 0;
if($result->num_rows){
    for($k=0;$row=sql_fetch_array($result);$k++){
        if($cur_prj_idx != $row['prj_idx']) {
            $list[$row['prj_idx']] = array();
            $cur_prj_idx = $row['prj_idx'];
        }
        array_push($list[$row['prj_idx']],$row);
    }
}

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';


$colspan = 11;
?>
<style>
.prs_hide td{color:#7c7aec;}
.prs_hide td.td_prs_status{color:#00f;}
.prs_end td{color:#f79359;}
.prs_end td.td_prs_status{color:orange;}

th {
    background: #54565a;
    border: 1px solid #6f7177;
    color: #fff;
    font-weight: normal;
    text-align: center;
    padding: 8px 5px;
    font-size: 0.92em;
}
.td_right{text-align:right;}
.td_cnt{background:#fbfeb1;}
.td_dprice{background:#daf7f9;}
.td_sprice{background:#bffbd4;}
.td_scnt{background:#f7fb80;}
.td_sdprice{background:#9ff2fa;}
.td_ssprice{background:#65fe9b;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<input type="text" name="st_date" value="<?=(($st_date) ? $st_date : $date_minus)?>" id="st_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="시작일">
~
<input type="text" name="en_date" value="<?=(($en_date) ? $en_date : $date_plus)?>" id="en_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="종료일">
<select name="sfl" id="sfl">
	<option value="com.com_name"<?php echo get_selected($_GET['sfl'], "com.com_name"); ?>>업체명</option>
	<option value="mb.mb_name"<?php echo get_selected($_GET['sfl'], "mb.mb_name"); ?>>담당자</option>
	<option value="prs.prs_role"<?php echo get_selected($_GET['sfl'], "prs.prs_role"); ?>>역할</option>
	<option value="prj.prj_idx"<?php echo get_selected($_GET['sfl'], "prj.prj_idx"); ?>>프로젝트번호</option>
	<option value="prj.prj_name"<?php echo get_selected($_GET['sfl'], "prj.prj_name"); ?>>프로젝트명</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
</div>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<!--input type="hidden" name="sst" value="<?php //echo $sst ?>"-->
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<?php

?>
<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <!-- 테이블 항목명 1번 라인 -->
    <!--
	<tr>
		<th scope="col">번호</th>
		<th scope="col">업체명</th>
		<th scope="col">프로젝트명</th>
		<th scope="col">소번호</th>
		<th scope="col">작업자</th>
		<th scope="col">역할</th>
		<th scope="col">시작일</th>
		<th scope="col">종료일</th>
		<th scope="col">투입일</th>
		<th scope="col">인건비</th>
		<th scope="col">소계</th>
	</tr>
    -->
	</thead>
	<tbody>
    <?php
    $i = 0;
    foreach($list as $pk => $pv){
        $s_cnt = count($pv);
        $sum_day = 0;
        $sum_day_price = 0;
        $sum_stot_price = 0;
        for($j=0;$j<$s_cnt;$j++){
            $first_idx = '';
            if($j == 0){
                $first_idx = 'first_idx';
                $sum_day = $pv[$j]['day_cnt'];
                $sum_day_price = $pv[$j]['day_price'];
                $sum_stot_price = $pv[$j]['stot_price'];
                echo '<tr>'.PHP_EOL;
                    echo '<th scope="col">번호</th>'.PHP_EOL;
                    echo '<th scope="col">업체명</th>'.PHP_EOL;
                    echo '<th scope="col">프로젝트명</th>'.PHP_EOL;
                    echo '<th scope="col">소번호</th>'.PHP_EOL;
                    echo '<th scope="col">작업자</th>'.PHP_EOL;
                    echo '<th scope="col">역할</th>'.PHP_EOL;
                    echo '<th scope="col">시작일</th>'.PHP_EOL;
                    echo '<th scope="col">종료일</th>'.PHP_EOL;
                    echo '<th scope="col">투입일</th>'.PHP_EOL;
                    echo '<th scope="col">인건비</th>'.PHP_EOL;
                    echo '<th scope="col">소계</th>'.PHP_EOL;
                echo '</tr>'.PHP_EOL;
            }
            else{
                $sum_day += $pv[$j]['day_cnt'];
                $sum_day_price += $pv[$j]['day_price'];
                $sum_stot_price += $pv[$j]['stot_price'];
            }
            echo '<tr class="'.$first_idx.'">'.PHP_EOL;
                echo '<td class="td_prj_idx" val="'.$pv[$j]['prj_idx'].'">'.(($first_idx) ? $pv[$j]['prj_idx'] : '').'</td>'.PHP_EOL;
                echo '<td class="td_com_name" val="'.$pv[$j]['com_name'].'">'.(($first_idx) ? $pv[$j]['com_name'] : '').'</td>'.PHP_EOL;
                echo '<td class="td_prj_name" val="'.$pv[$j]['prj_name'].'">'.(($first_idx) ? $pv[$j]['prj_name'] : '').'</td>'.PHP_EOL;
                echo '<td class="td_prs_idx" val="'.$pv[$j]['prs_idx'].'">'.$pv[$j]['prs_idx'].'</td>'.PHP_EOL;
                echo '<td class="td_mb_name" val="'.$pv[$j]['mb_name'].'">'.$pv[$j]['mb_name'].'</td>'.PHP_EOL;
                echo '<td class="td_prs_role" val="'.$g5['set_worker_type_value'][$pv[$j]['prs_role']].'">'.$g5['set_worker_type_value'][$pv[$j]['prs_role']].'</td>'.PHP_EOL;
                echo '<td class="td_prs_start_date" val="'.$pv[$j]['prs_start_date'].'">'.$pv[$j]['prs_start_date'].'</td>'.PHP_EOL;
                echo '<td class="td_prs_end_date" val="'.$pv[$j]['prs_end_date'].'">'.$pv[$j]['prs_end_date'].'</td>'.PHP_EOL;
                echo '<td class="td_right td_cnt">'.number_format($pv[$j]['day_cnt']).'</td>'.PHP_EOL;
                echo '<td class="td_right td_dprice">'.number_format($pv[$j]['day_price']).'</td>'.PHP_EOL;
                echo '<td class="td_right td_sprice">'.number_format($pv[$j]['stot_price']).'</td>'.PHP_EOL;
            if($s_cnt == ($j+1)){
            echo '</tr><tr>'.PHP_EOL;
                //echo '<td></td>'.PHP_EOL;  
                //echo '<td></td>'.PHP_EOL;  
                //echo '<td></td>'.PHP_EOL;  
                //echo '<td></td>'.PHP_EOL;  
                //echo '<td></td>'.PHP_EOL;  
                //echo '<td></td>'.PHP_EOL;  
                //echo '<td></td>'.PHP_EOL;  
                echo '<td colspan="8" style="background:#f1f1f1;">합 계</td>'.PHP_EOL;  
                echo '<td class="td_right td_scnt">'.number_format($sum_day).'</td>'.PHP_EOL;  
                echo '<td class="td_right td_sdprice">'.number_format($sum_day_price).'</td>'.PHP_EOL;  
                echo '<td class="td_right td_ssprice">'.number_format($sum_stot_price).'</td>'.PHP_EOL;
    
                echo '</tr><tr><td colspan="'.$colspan.'"></td>'.PHP_EOL;
            }
            echo '</tr>'.PHP_EOL;
        }
        $i++;
    }
	if ($i == 0)
		echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

</form>

<?php //echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>

<script>
$(function(e) {
    $("input[name$=_date]").datepicker({
        closeText: "닫기",
        currentText: "오늘",
        monthNames: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
        monthNamesShort: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
        dayNamesMin:['일','월','화','수','목','금','토'],
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        //maxDate: "+0d"
    });
	
});

function form01_submit(f)
{
    /*
	if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/code/form.php');
        return false;
	}

    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}

    if(document.pressed == "선택표시") {
		$('input[name="w"]').val('s');
	}

    if(document.pressed == "선택종료") {
		$('input[name="w"]').val('e');
	}

    if(document.pressed == "선택숨김") {
		$('input[name="w"]').val('h');
	}

	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		} 
	}
    return true;
    */
}
</script>

<?php
include_once ('./_tail.php');
?>