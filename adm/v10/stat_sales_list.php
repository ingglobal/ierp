<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '영업매출';
include_once('./_top_menu_stat.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


//echo $member['mb_group_yn'].'<br>';	// meta 확장, u.default.php
//echo $g5['department_uptop_idx'][$member['mb_2']].'<br>'; // 최상위조직코드, u.project.php
//echo $g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]].'<br>';	// 최상위 그룹의 하위 조직코드들
//echo $g5['department_down_idxs'][$member['mb_2']].'<br>'; // 하부조직코드(들), u.project.php


//-- 디폴트 년월(ym)
$ym = ($ym) ? $ym : date("Y-m",G5_SERVER_TIME);

//-- 이전달,다음달 추출
$ym_prev = ((int)substr($ym,-2) == 1) ? 
				(substr($ym,0,4)-1).'-12' 
				: substr($ym,0,4).'-'.sprintf("%02d",((int)substr($ym,-2)-1));
$ym_next = ((int)substr($ym,-2) == 12) ?
				(substr($ym,0,4)+1).'-01'
				: substr($ym,0,4).'-'.sprintf("%02d",((int)substr($ym,-2)+1));
				
$st_date = $ym.'-01';
$en_date = $ym.'-31';


// 관리자 레벨이 아니면 자기 조직 것만 리스트에 나옴
// 스토어: 196,197,198,203,204,229,243,240
// 충무로: 145,157,242,250
// 전략기획실: 222,224,223,164
// 운영&정산 권한이 있는 경우 (매출통계 노출 가능 팀만 추출)
if ($member['mb_manager_account_yn']) {
	// 설정값이 있는 경우만
	if( $g5['set_stat_teams_array'] ) {
		$sql_trmgroup = " AND term.trm_idx IN (".implode(",",$g5['set_stat_teams_array']).") "
									." AND parent.trm_idx IN (".implode(",",$g5['set_stat_teams_array']).") ";
		$sql_mygroup = " AND trm_idx_department IN (".implode(",",$g5['set_stat_teams_array']).")";
	}
}
// 직원인 경우(자기 그룹만 보여주면 됨)
else {
	$sql_trmgroup = " AND term.trm_idx IN (".$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]].") "
								." AND parent.trm_idx IN (".$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]].") ";
	$sql_mygroup = " AND trm_idx_department IN (".$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]].")";
}


// 검색 조건
$sql_search = " WHERE sls_sales_dt >= '".$st_date." 00:00:00'
                        AND sls_sales_dt <= '".$en_date." 23:59:59'
                        AND sls_status IN ('ok')
                        AND sra_type IN ('".implode("','",$g5['set_sales_sra_type_array'])."')
";


//-- 매출 목표 추출
$sql = "	SELECT parent.trm_idx
				, parent.trm_left
				, SUM(slg_sales_goal_sum) slg_sales_goal_sum
				, SUM(slg_cms_goal_total_1) slg_cms_goal_total_1
			FROM {$g5['term_table']} AS term, 
				{$g5['term_table']} AS parent,
				(
				SELECT 
					trm_idx_department
					, SUM( slg_sales_goal ) slg_sales_goal_sum
					, SUM( slg_cms_goal ) slg_cms_goal_total_1
				FROM {$g5['sales_goal_table']}
				WHERE slg_ym = '".$ym."-00'
					AND slg_status IN ('ok')
                    AND mb_id_saler IN ( SELECT mb_id FROM {$g5['member_table']} WHERE mb_level >= 6 )
				GROUP BY trm_idx_department
				) ord_opa
			WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
				AND term.trm_taxonomy = 'department'
				AND parent.trm_taxonomy = 'department'
				AND term.trm_status = 'ok'
				AND parent.trm_status = 'ok'
				AND term.trm_idx = trm_idx_department
			GROUP BY parent.trm_idx
			ORDER BY parent.trm_left
";
//echo $sql;
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
	//print_r2($row);
	$sales_goal[$row['trm_idx']] = $row['slg_sales_goal_sum'];	// 매출 목표값
}
//print_r2($sales_goal);


// 목표 매출 합계
$sql = "	SELECT 
				trm_idx_department
				, SUM( slg_sales_goal ) slg_sales_goal_sum
				, SUM( slg_cms_goal ) slg_cms_goal_total_1
			FROM {$g5['sales_goal_table']}
			WHERE slg_ym = '".$ym."-00'
				AND slg_status IN ('ok')
                AND mb_id_saler IN ( SELECT mb_id FROM {$g5['member_table']} WHERE mb_level >= 6 )
				{$sql_mygroup}
";
$goal1 = sql_fetch($sql,1);
$sales_goal_total = $goal1['slg_sales_goal_sum'];


//-- 영업일수 계산 (토, 일 제외, 공휴일 제외)
$sql = "	SELECT count(ymd) AS day_ym FROM g5_5_ymd 
			WHERE ymd_date LIKE '".$ym."%'
				AND dayofweek(ymd) NOT IN (1,7)
				AND ymd_holiday = 0
";
$day1 = sql_fetch($sql,1);
$day_ym['total'] = $day1['day_ym'];

// 지난 일수
$sql = "	SELECT count(ymd) AS day_ym FROM g5_5_ymd 
			WHERE ymd_date LIKE '".$ym."%'
				AND dayofweek(ymd) NOT IN (1,7)
				AND ymd_holiday = 0
				AND ymd_date <= '".G5_TIME_YMD."';
";
$day2 = sql_fetch($sql,1);
$day_ym['day_spent'] = $day2['day_ym'];
$day_ym['day_spent_rate'] = round($day_ym['day_spent']/$day_ym['total']*100,1);
//print_r2($day_ym);


$sql =	"SELECT 
				trm_idx
				, term_name AS item_name
				, depth
				, trm_left
				, sls_price_sum
				, sls_share_sum
				, sls_price_cost_sum
			FROM (	
				(
				SELECT 
					0 AS trm_idx
					, 'total' AS term_name
					, 0 AS depth
					, 0 AS trm_left
					, SUM( sls_price ) sls_price_sum
					, SUM( sls_share ) sls_share_sum
					, SUM( sls_price_cost ) sls_price_cost_sum
				FROM {$g5['sales_table']}
                    {$sql_search}
					{$sql_mygroup}
				)
			UNION ALL
				(
				SELECT 
					trm_idx
					, GROUP_CONCAT(name) term_name
					, GROUP_CONCAT(cast(depth as char)) depth
					, trm_left
					, SUM(sls_price_sum) sls_price_sum
					, SUM(sls_share_sum) sls_share_sum
					, SUM(sls_price_cost_sum) sls_price_cost_sum
				FROM (	(
						SELECT term.trm_idx
							, CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
							, (COUNT(parent.trm_idx) - 1) AS depth
							, term.trm_left
							, 0 sls_price_sum
							, 0 sls_share_sum
							, 0 sls_price_cost_sum
						FROM {$g5['term_table']} AS term,
								{$g5['term_table']} AS parent
						WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
							AND term.trm_taxonomy = 'department'
							AND parent.trm_taxonomy = 'department'
							AND term.trm_status = 'ok'
							AND parent.trm_status = 'ok'
							{$sql_trmgroup}
						GROUP BY term.trm_idx
						ORDER BY term.trm_left
						)
					UNION ALL
						(
						SELECT parent.trm_idx
							, NULL name
							, NULL depth
							, parent.trm_left
							, SUM(sls_price_sum) sls_price_sum
							, SUM(sls_share_sum) sls_share_sum
							, SUM(sls_price_cost_sum) sls_price_cost_sum
						FROM {$g5['term_table']} AS term, 
							{$g5['term_table']} AS parent,
							(
							SELECT 
								trm_idx_department
								, SUM( sls_price ) sls_price_sum
								, SUM( sls_share ) sls_share_sum
								, SUM( sls_price_cost ) sls_price_cost_sum
							FROM {$g5['sales_table']}
                                {$sql_search}
							GROUP BY trm_idx_department
							) ord_opa
						WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
							AND term.trm_taxonomy = 'department'
							AND parent.trm_taxonomy = 'department'
							AND term.trm_status = 'ok'
							AND parent.trm_status = 'ok'
							AND term.trm_idx = trm_idx_department
							{$sql_mygroup}
						GROUP BY parent.trm_idx
						ORDER BY parent.trm_left
						) 
					) db_table
				GROUP BY trm_idx
				ORDER BY trm_left
				) 
			) db_table1
			ORDER BY trm_left
";
//echo $sql;
$result = sql_query($sql,1);
?>
<style>
.icon_prev_next {font-size:2em;position:absolute;top:2px;cursor:pointer;}
</style>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>

<div style="display:inline-block;position:relative;padding:0 35px;">
	<a href="?ym=<?=$ym_prev?>&ser_trm_idxs=<?=$ser_trm_idxs?>"><i class="fa fa-chevron-circle-left icon_prev_next" style="left:5px"></i></a>
	<input type="text" name="ym" value="<?=$ym?>" id="ym" class="frm_input" style="width:70px;text-align:center;">
	<a href="?ym=<?=$ym_next?>&ser_trm_idxs=<?=$ser_trm_idxs?>"><i class="fa fa-chevron-circle-right icon_prev_next" style="right:5px"></i></a>
</div>
&nbsp;&nbsp;
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
	<p>
		매출 통계입니다.
	</p>
</div>


<!-- 리스트 테이블 -->
<div class="tbl_head01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr>
		<th scope="col" style="width:200px;">조직명</th>
		<th scope="col" style="width:150px;">매출목표</th>
		<th scope="col" style="width:150px;">매출합계</th>
		<th scope="col" style="width:80px;">목표달성율</th>
		<th scope="col">그래프</th>
		<th scope="col" style="width:85px;">영업일진척율</th>
		<th scope="col" style="width:80px;">성과比</th>
	</tr>
	</thead>
	<tbody class="tbl_body">
	<?php
	for ($i=0; $row=sql_fetch_array($result); $i++) {
		//print_r2($row);

		//-- 들여쓰기
		$row['indent'] = $row['depth']*20;
		
		// 합계인 경우
		if($row['item_name'] == 'total') {
			$row['item_name'] = '합계';
			$row['tr_class'] = 'stat_total';
			$sales_goal[$row['trm_idx']] = $sales_goal_total;
		}
		else {
			$row['tr_class'] = 'stat_normal';
		}
		
		// 상세보기
		if($row['depth'] >= 1)
			$row['btn_sheet2'] = '<a href="./sales_list.php?ser_trm_idxs='.$g5['department_down_idxs'][$row['trm_idx']].'&st_date='.$st_date.'&en_date='.$en_date.'"><span class="btn_sheet2" style="font-size:0.65rem;border:solid 1px #ccc;padding:1px 4px 1px 2px;">상세</span></a>';

		// 매출목표 기준값 (없는 경우는 0)
		//if($row['item_name'] == 'total')
		//	$amount_total = $row['sls_price_sum'];
		$amount_total = ($sales_goal[$row['trm_idx']]) ? $sales_goal[$row['trm_idx']] : 0;
		
		// 비율
		$row['rate'] = ($amount_total) ? $row['sls_price_sum'] / $amount_total * 100 : 0 ;
		$row['rate_cancel'] = ($amount_total) ? abs($row['sls_minus_1']) / $amount_total * 100 : 0 ;
		$row['rate_color'] = ($row['rate']>=100) ? 'deepskyblue' : '';

		// 그래프
		if($row['sls_price_sum'] > 0) {
			$row['rate_graph'] = ($row['rate']<100) ? $row['rate'] : 100;
			$row['graph'] = '<img src="./img/graph.gif" width="'.$row['rate_graph'].'%" height="18">';
		}

		// 취소가 있는 경우
		if($row['sls_minus_1'] != 0) {
//			$row['sls_minus_1_text'] = '<br><span style="font-size:0.8em;">(취소: '.number_format($row['sls_minus_1']/1.1).')</span>';
//			$row['graph_cancel'] = '<br><img src="'.G5_USER_URL.'/img/graph_cancel.gif" width="'.number_format($row['rate_cancel'], 1).'%" height="10">';
		}

		// 성과比 계산
		$row['rate_ratio'] = $row['rate'] - $day_ym['day_spent_rate'];
		$row['rate_ratio_color'] = ($row['rate_ratio']>=0) ? '' : 'red';

		echo '
			<tr class="'.$row['tr_class'].'">
				<td style="text-align:left;padding-left:'.(15+$row['indent']).'px;">'.$row['item_name'].' '.$row['btn_sheet2'].'</td>
				<td class=""style="text-align:right;">'.number_format($sales_goal[$row['trm_idx']]).'</td>
				<td class=""style="text-align:right;">'.number_format($row['sls_price_sum']).$row['sls_minus_1_text'].'</td><!-- 매출합계 -->
				<td class="" style="text-align:right;font-size:1.0em;color:'.$row['rate_color'].';">'.number_format($row['rate'], 1).'%</td><!-- 목표달성율 -->
				<td class="" style="text-align:left;padding-left:0;">'.$row['graph'].$row['graph_cancel'].'</td>
				<td class="" style="text-align:;">'.number_format($day_ym['day_spent_rate'], 1).'%</td>
				<td class="" style="text-align:;color:'.$row['rate_ratio_color'].'">'.number_format($row['rate_ratio'], 1).'%</td>
			</tr>
			';
	
	}
	if ($i == 0)
		echo '<tr class="no-data" style="display:none"><td colspan="8" class="text-center">등록(검색)된 자료가 없습니다.</td></tr>';
	?>
    </tbody>
    </table>
</div>
<!-- //리스트 테이블 -->

<script>

//-- $(document).ready 페이지로드 후 js실행 --//
$(document).ready(function(){

    $("#st_date,#en_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        //maxDate: "+0d"
    });	 

	$( "#fsearch" ).submit(function(e) {
		if($('input[name=st_date]').val() > $('input[name=en_date]').val()) {
			alert('시작일이 종료일보다 큰 값이면 안 됩니다.');
			e.preventDefault();
		}
	});

});
//-- //$(document).ready 페이지로드 후 js실행 --//


</script>

<?php
include_once ('./_tail.php');
?>
