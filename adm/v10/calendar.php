<?php
$sub_menu = "960150";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '일정관리';
// include_once('./_top_menu_schedule.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];


// 초기 입력값 추출 ($month 변수)
$month = ($month)? $month : date('Ym', G5_SERVER_TIME);
$year = substr($month,0,4);
$_month = $year.'-'.substr($month,-2);	// 2018-02와 같이 -가 있는 월변수
$last_month = $month - 1;
$next_month = $month + 1;
// 달력 시작일 (이전달의 마지막주 포함, 1월 재계산 필요)
$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".$year."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".$_month."-01') ORDER BY ymd LIMIT 1 ");
$sql_date_start = $w1['ymd'];
// 달력 종료일 (다음달의 첫주 포함, 12월 재계산 필요)
$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".$year."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = (SELECT LAST_DAY('".$_month."-01')) ) ORDER BY ymd DESC LIMIT 1 ");
$sql_date_end = $w1['ymd'];

// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
$sql_week = " WEEK(ymd,0) AS date_week ";
$sql_group_by = " GROUP BY WEEK(ymd,0) ";
// 01월인 경우
if( substr($month,-2) == '01' ) {
	$last_month = ($year-1).'12';

	// 1월1일의 주차 (보통은 0주차부터 시작, 1월1일이 딱 일요일이면 1주차부터 시작함)
	$w2 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd = '".$year."0101' ");
	if($w2['week_num'] == 1) {
		$sql_date_start = $year.'0101';
	}
	// 1월1일이 일요일 아닌 경우는 작년 마지막 주간의 시작일이 달력 시작일
	else {
		$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".(($year-1))."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".($year-1)."-12-31') ORDER BY ymd LIMIT 1 ");
		$sql_date_start = $w1['ymd'];
	}

	// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
	$sql_week = " if(WEEK(ymd,0) > 50, ".$w2['week_num'].", WEEK(ymd,0)) AS date_week ";
    
    // 작년 마지막 주의 주차수값=52,51 등과 같으므로
    $sql_group_by = " GROUP BY IF(WEEK(ymd,0)<30, WEEK(ymd,0), 0) ";
    
} 
// 12인 경우
if( substr($month,-2) == '12' ) {
	$next_month = ($year+1).'01';

	// 내년 1월1일의 주차 (보통은 0주차부터 시작, 1월1일이 딱 일요일이면 1주차부터 시작함)
	$w2 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd = '".($year+1)."0101' ");
	if($w2['week_num'] == 1) {
		$sql_date_end = $year.'1231';
	}
	// 내년 1월1일이 일요일 아닌 경우는 내년 첫주의 마지막날이 달력 종료일
	else {
		$w1 = sql_fetch(" SELECT ymd FROM g5_5_ymd WHERE ymd LIKE '".($year+1)."%' AND WEEK(ymd,0) = (SELECT WEEK(ymd,0) FROM g5_5_ymd WHERE ymd_date = '".($year+1)."-01-01') ORDER BY ymd DESC LIMIT 1 ");
		$sql_date_end = $w1['ymd'];
	}

	// 첫주 또는 마지막 주간의 WEEK(주차값), 1월 or 12월은 걸쳐 있으므로 재계산 필요
	// 12월31일의 주차 (보통은 52주차가 마지막, 1월1일이 딱 일요일이면 마지막 주차가 53)
	$w3 = sql_fetch(" SELECT WEEK(ymd,0) AS week_num FROM g5_5_ymd WHERE ymd LIKE '".$year."1231' ");
	$sql_week = " if(WEEK(ymd,0) < 2, ".$w3['week_num'].", WEEK(ymd,0)) AS date_week ";

    // 내년 첫주의 주차수값=0 이므로 보정해 줘야 함
    $sql_group_by = " GROUP BY IF(WEEK(ymd,0)>30, WEEK(ymd,0), ".$w3['week_num'].") ";
}



                    
//-- 달력 디비 추출 --//
// 달력 추출 ======================================================
$sql = "SELECT max( if(DATE_FORMAT(ymd, '%w') = 0, ymd_date, '') ) as day0,
		       max( if(DATE_FORMAT(ymd, '%w') = 0, ymd_more, '') ) as day0more,
		       max( if(DATE_FORMAT(ymd, '%w') = 1, ymd_date, '') ) as day1,
		       max( if(DATE_FORMAT(ymd, '%w') = 1, ymd_more, '') ) as day1more,
		       max( if(DATE_FORMAT(ymd, '%w') = 2, ymd_date, '') ) as day2,
		       max( if(DATE_FORMAT(ymd, '%w') = 2, ymd_more, '') ) as day2more,
		       max( if(DATE_FORMAT(ymd, '%w') = 3, ymd_date, '') ) as day3,
		       max( if(DATE_FORMAT(ymd, '%w') = 3, ymd_more, '') ) as day3more,
		       max( if(DATE_FORMAT(ymd, '%w') = 4, ymd_date, '') ) as day4,
		       max( if(DATE_FORMAT(ymd, '%w') = 4, ymd_more, '') ) as day4more,
		       max( if(DATE_FORMAT(ymd, '%w') = 5, ymd_date, '') ) as day5,
		       max( if(DATE_FORMAT(ymd, '%w') = 5, ymd_more, '') ) as day5more,
		       max( if(DATE_FORMAT(ymd, '%w') = 6, ymd_date, '') ) as day6,
		       max( if(DATE_FORMAT(ymd, '%w') = 6, ymd_more, '') ) as day6more
		FROM g5_5_ymd
		WHERE ymd BETWEEN '".$sql_date_start."' AND '".$sql_date_end."'
		{$sql_group_by}
";
// echo $sql.'<br>';
$result = sql_query($sql,1);


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// include calendar.js
// add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/calendar.js"></script>', 10);
?>

<!-----------------------------------  CALENDAR 버튼 -------------------------------------->
<div class="btn_add01 btn_add">
	<div style="display:inline-block;" class="float_left">
		<a href="<?=$_SERVER['PHP_SELF']?>?month=<?=$last_month?>&amp;itm_idx=<?=$itm_idx?>&amp;pgm_idx=<?=$pgm_idx?>&apm;stx=<?=$stx?>&apm;stx=<?=$stx?>" class="">이전달</a>
	    <a style="font-size:25px;font-weight:bold;font-family: tahoma;background:white;color:black;cursor:default;"><?=$_month?></a>
		<a href="<?=$_SERVER['PHP_SELF']?>?month=<?=$next_month?>&amp;itm_idx=<?=$itm_idx?>&amp;pgm_idx=<?=$pgm_idx?>&apm;stx=<?=$stx?>" class="">다음달</a>
	</div>
	<a href="./work_list.php?fr_date=<?=$_month?>-01&to_date=<?=$_month?>-31&search_ct_status=촬영대기" style="display:none;">목록보기</a>
</div>

<div id="sct" class="tbl_head02 tbl_wrap">
<table id="table01_list">
<caption><?php echo $g5['title']; ?> 달력</caption>
<thead>
<tr>
    <th scope="col" style="width:14%">일</th>
    <th scope="col" style="width:14%">월</th>
    <th scope="col" style="width:14%">화</th>
    <th scope="col" style="width:14%">수</th>
    <th scope="col" style="width:14%">목</th>
    <th scope="col" style="width:14%">금</th>
    <th scope="col" style="width:14%">토</th>
</tr>
</thead>
<tbody>
<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	echo '<tr class=" ">';
	
	// 일 ~ 토 한 주간 표현
	for($j=0;$j<7;$j++) {
        //echo $row['day'.$j].'<br>';	// 2018-02-03
        $row[$i][$j]['dates'] = explode("-",$row['day'.$j]);    // 날짜값 분리 배열
        $row[$i][$j]['day_no'] = number_format($row[$i][$j]['dates'][2]);    // 날짜만 숫자로

		// 해당 날짜의 개별 설정 unserialize 추출
        if($row["day".$j."more"]) {
            $unser = unserialize(stripslashes($row["day".$j."more"]));
            if( is_array($unser) && substr($row['day'.$j],0,7) == $_month ) {
                foreach ($unser as $key=>$value) {
                    $row[$i][$j][$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
                }    
            }
        }
        //print_r2($row[$i][$j]);

		// 요일별 클래스
		if( $j==0 )
			$day_style_week[$i][$j] = " day_sunday";
		else if( $j==6 )
			$day_style_week[$i][$j] = " day_saturday";
		else
			$day_style_week[$i][$j] = " day_weekday";

        // 날짜별 스타일 설정
		if( $j==0 )
			$day_style = "day_sunday";
		else if( $j==6 )
			$day_style = "day_saturday";
		else 
			$day_style = "day_normal";

		// 오늘
        $day_today[$i][$j] = ( $row['day'.$j] == G5_TIME_YMD )? " day_today" : "";
		// 오늘 이전
		$day_oldday[$i][$j] = ( $row['day'.$j] < G5_TIME_YMD )? " day_oldday":"";
		// 공휴일
		$day_holiday[$i][$j] = ( $row[$i][$j]['holiday_name'] )? " day_holiday":"";
		// 이전달
		$day_prev_month[$i][$j] = ( substr($row['day'.$j],0,7) < $_month )? " day_prev_month":"";
		// 다음달
		$day_next_month[$i][$j] = ( substr($row['day'.$j],0,7) > $_month )? " day_next_month":"";
		// 날짜값이 없는 경우
		$day_null[$i][$j] = ( !$row['day'.$j] )? " day_null":"";

		echo '<td td_date="'.$row['day'.$j].'" class="td_day '
                .$day_style_week[$i][$j]
                .$day_today[$i][$j]
                .$day_holiday[$i][$j]
                .$day_oldday[$i][$j]
                .$day_prev_month[$i][$j]
                .$day_next_month[$i][$j]
                .$day_null[$i][$j].'"';
        echo '>';	// end of <td
        // 날짜 & 공휴일명
		if($row["day".$j]) {
            echo '<div class="day_no_holiday">';
            echo '<div class="day_no">'.number_format($row[$i][$j]['dates'][2]).'</div>';
            echo ($row[$i][$j]['holiday_name']) ? '<div class="day_holiday_text" title="'.$row[$i][$j]['holiday_description'].'">'.$row[$i][$j]['holiday_name'].'</div>' : '' ;   // 공휴일 내용이 있으면 표현
            echo '</div>';
        }

        // 일정내용
        echo ($day_content[$row['day'.$j]]) ? $day_content[$row['day'.$j]] : '' ;

        // 관리자인 경우 통계 보임
        if($is_admin && $member['mb_manager_yn']) {
            echo ($day_status_stat[$row['day'.$j]]) ? $day_status_stat[$row['day'.$j]] : '' ;
        }
		echo '</td>';
    }
    
	echo '</tr>';
}
?>
</tbody>
</table>
</div>

<script>
$(function(){

});

</script>


<?php
include_once ('./_tail.php');
?>