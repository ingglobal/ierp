<?php
$sub_menu = "960300";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");


if($type == '') $type = 'day';
if($yy == '') $yy = date('Y');
if($mm == '') $mm = date('m');

$mm = (int)$mm;

$down = 1;
$up = 2;
$ymin = date('Y') - $down;
$ymax = date('Y') + $up;

if($yy < $ymin || $yy >= $ymax){
    alert('연도의 검색범위를 벗어 났습니다.');
}

$yy_opts = '';
for($i=$ymin;$i<$ymax;$i++){
    $yy_opts .= '<option value="'.$i.'">'.$i.'</option>';
}
$mm_opts = '';
for($i=1;$i<13;$i++){
    $mm_opts .= '<option value="'.$i.'">'.$i.'</option>';
}


function get_report_list($yy,$mm,$dd,$type) {
    global $super_ceo_admin, $g5, $member;
	$date_str = $yy."-".$mm."-".$dd;

    $sql_writer = '';
    if(!$super_ceo_admin){
        $sql_writer = " AND wrp.mb_id = '{$member['mb_id']}' ";
    }

	$sql = " SELECT wrp_idx, wrp_date, wrp.mb_id, mb_name, mb_2, mb_3 FROM {$g5['workreport_table']} wrp
                LEFT JOIN {$g5['member_table']} mb ON wrp.mb_id = mb.mb_id
            WHERE wrp_month = '{$mm}'
                AND wrp_type = '{$type}'
                AND wrp_date = '{$date_str}'
                {$sql_writer}
            ORDER BY wrp_date, wrp_reg_dt
    ";
	$res = sql_query($sql,1);
    $str = '';
    if($res->num_rows){
        $str .= '<ul>'.PHP_EOL;
        for($i=0;$row=sql_fetch_array($res);$i++) {
            $str .= '<li><a class="a_'.$type.'" href="'.G5_USER_ADMIN_URL.'/workreport_view.php?wrp_idx='.$row['wrp_idx'].'&type='.$type.'&yy='.$yy.'&mm='.$mm.'&date='.$row['wrp_date'].'">'.$row['mb_name'].'('.(($g5['set_mb_ranks_value'][$row['mb_3']])?$g5['set_mb_ranks_value'][$row['mb_3']]:'직함없음').')</a><i class="fa fa-times del_rep" wrp_idx="'.$row['wrp_idx'].'" type="'.$type.'" yy="'.$yy.'" mm="'.$mm.'" aria-hidden="true"></i></li>';
        }
        $str .= '</ul>'.PHP_EOL;
    }
	return $str;
}

function get_monthAddDate($dateInfo,$monNum){//임채완이 재정의 한 함수(월수계산)
	$dtArr = explode('-',$dateInfo);
	$year_ = $dtArr[0];
	$month_ = $dtArr[1];
	$day_ = $dtArr[2];

	$dt = @mktime(0,0,0,$month_+$monNum,$day_,$year_);

	return date("Y-m-d",$dt);
} 

// 1. 총일수 구하기
$last_day = date("t", strtotime($yy."-".$mm."-01"));
// 2. 시작요일 구하기
$start_week = date("w", strtotime($yy."-".$mm."-01"));
// 3. 총 몇 주인지 구하기
$total_week = ceil(($last_day + $start_week) / 7);
// 4. 마지막 요일 구하기
$last_week = date('w', strtotime($yy."-".$mm."-".$last_day));



$g5['title'] = '업무보고달력';
//include_once('./_top_menu_company.php');
include_once('./_head.php');
$cmonth = $yy.'-'.sprintf("%02d",$mm).'-01';
$cyy = substr(G5_TIME_YMD,0,4);
$cmm = (int)substr(G5_TIME_YMD,5,2);
$pmonth = get_monthAddDate($cmonth,-1);
$pyy = substr($pmonth,0,4);
$pmm = (int)substr($pmonth,5,2);
$nmonth = get_monthAddDate($cmonth,1);
$nyy = substr($nmonth,0,4);
$nmm = (int)substr($nmonth,5,2);
?>
<form name="form" method="get" id="cldform">
<div id="date_box">
    <div id="date_con">
        <div class="box_d box_t">
            <a href="javascript:" class="t_btn t_day<?=(($type == 'day')?' focus':'')?>" type="day">일일보고</a>
            <a href="javascript:" class="t_btn t_week<?=(($type == 'week')?' focus':'')?>" type="week">주간보고</a>
            <a href="javascript:" class="t_btn t_month<?=(($type == 'month')?' focus':'')?>" type="month">월간보고</a>
        </div>
        <select name="yy" id="yy">
            <?=$yy_opts?>
        </select>
        <span>년</span> 
        <select name="mm" id="mm">
            <?=$mm_opts?>
        </select>
        <span>월</span>
        <script>
        $('#yy').val('<?=$yy?>');
        $('#mm').val('<?=$mm?>');
        </script>
        <div class="box_d box_m">
            <a href="javascript:" class="m_btn m_prev" to_month="prev" yy="<?=$pyy?>" mm="<?=$pmm?>"><span class="sound_only">이전달</span><i class="fa fa-angle-left" aria-hidden="true"></i></a>
            <a href="javascript:" class="m_btn m_curr" to_month="curr" yy="<?=$cyy?>" mm="<?=$cmm?>">이번달</a>
            <a href="javascript:" class="m_btn m_next" to_month="next" yy="<?=$nyy?>" mm="<?=$nmm?>"><span class="sound_only">다음달</span><i class="fa fa-angle-right" aria-hidden="true"></i></a>
        </div>
    </div>
</div>
<table id="tbl_cld">
    <thead>
        <tr>
            <th class="th_cld th_sun"><b>일</b></th>
            <th class="th_cld th_nml"><b>월</b></th>
            <th class="th_cld th_nml"><b>화</b></th>
            <th class="th_cld th_nml"><b>수</b></th>
            <th class="th_cld th_nml"><b>목</b></th>
            <th class="th_cld th_nml"><b>금</b></th>
            <th class="th_cld th_sat"><b>토</b></th>
        </tr>
    </thead>
    <tbody>
        <?
        $today_yy = date('Y');
        $today_mm = date('m');
        // 5. 화면에 표시할 화면의 초기값을 1로 설정
        $day=1;
        
        // 6. 총 주 수에 맞춰서 세로줄 만들기
        for($i=1; $i <= $total_week; $i++){?>
        <tr>
        <?
            // 7. 총 가로칸 만들기
            for ($j=0; $j<7; $j++){
                $day_class = '';
                if($j == 0){
                    // 9. $j가 0이면 일요일이므로 빨간색
                    $day_class = 'day_red';
                }else if($j == 6){
                    // 10. $j가 0이면 일요일이므로 파란색
                    $day_class = 'day_blue';
                }
        ?>
        <td class="<?=(($today_yy == $yy && $today_mm == $mm && $day == date("j"))?'td_current':'')?>">
        <?
            // 8. 첫번째 주이고 시작요일보다 $j가 작거나 마지막주이고 $j가 마지막 요일보다 크면 표시하지 않아야하므로
            //    그 반대의 경우 -  ! 으로 표현 - 에만 날자를 표시한다.
            if (!(($i == 1 && $j < $start_week) || ($i == $total_week && $j > $last_week))){
                $omm = sprintf("%02d",$mm);
                $odd = sprintf("%02d",$day);
                $date = $yy.'-'.$omm.'-'.$odd;// 각 td의 날짜 2024-05-03
                // echo $date;
                // 12. 오늘 날자면 굵은 글씨
                if($today_yy == $yy && $today_mm == $mm && $day == date("j")){
                    echo '<div class="day_num day_current">';
                } else { echo '<div class="day_num day_nml">';}
                
                // 13. 날자 출력
                echo '<span class="'.$day_class.'">'.$day.'</span></div>'.PHP_EOL;
        
                echo '<div class="day_con"><i class="fa fa-plus-circle add_rep" type="'.$type.'" yy="'.$yy.'" mm="'.$mm.'" date="'.$date.'" aria-hidden="true"></i>';
                
                //스케줄 출력
                $schstr = get_report_list($yy,$omm,$odd,$type);
                echo $schstr;
                echo '</div>'.PHP_EOL;
        
                // 14. 날자 증가
                $day++;
            }
            ?>
        </td>
        <?}?>
        </tr>
        <?}?>

    </tbody>
</table> 
</form>
<script>
$('.day_con').on('mouseenter', function(){
    $(this).find('.add_rep').addClass('focus');
});
$('.day_con').on('mouseleave', function(){
    $(this).find('.add_rep').removeClass('focus');
});

$('#yy').on('change', function(){
    let page_url = '<?=$_SERVER['SCRIPT_NAME']?>';
    let type = '';
    let yy = $(this).val();
    let mm = $('#mm').val();
    $('.t_btn').each(function(){
        if($(this).hasClass('focus')){
            type = $(this).attr('type');
        }
    });
    let to_url = page_url + '?type=' + type + '&yy=' + yy + '&mm=' + mm;
    location.href = to_url;
});

$('#mm').on('change', function(){
    let page_url = '<?=$_SERVER['SCRIPT_NAME']?>';
    let type = '';
    let yy = $('#yy').val();
    let mm = $(this).val();
    $('.t_btn').each(function(){
        if($(this).hasClass('focus')){
            type = $(this).attr('type');
        }
    });
    let to_url = page_url + '?type=' + type + '&yy=' + yy + '&mm=' + mm;
    location.href = to_url;
});

$('.t_btn').on('click', function(){
    let page_url = '<?=$_SERVER['SCRIPT_NAME']?>';
    let type = $(this).attr('type');
    let yy = $('#yy').val();
    let mm = $('#mm').val();
    let to_url = page_url + '?type=' + type + '&yy=' + yy + '&mm=' + mm;
    location.href = to_url;
});

$('.m_btn').on('click', function(){
    let page_url = '<?=$_SERVER['SCRIPT_NAME']?>';
    let type = '';
    let yy = $(this).attr('yy');
    let mm = $(this).attr('mm');
    $('.t_btn').each(function(){
        if($(this).hasClass('focus')){
            type = $(this).attr('type');
        }
    });
    let to_url = page_url + '?type=' + type + '&yy=' + yy + '&mm=' + mm;
    location.href = to_url;
});

$('.add_rep').on('click', function(){
    let type = $(this).attr('type');
    let yy = $(this).attr('yy');
    let mm = $(this).attr('mm');
    let date = $(this).attr('date');
    let page_url = '<?=G5_USER_ADMIN_URL?>/workreport_form.php?type=' + type + '&yy=' + yy + '&mm=' + mm + '&date=' + date;
    // alert(page_url);
    location.href = page_url;
});

$('.del_rep').on('click', function(){
    if(!confirm("복구가 불가능 합니다. 정말 삭제하시겠습니까?")){
        return false;
    }
    let wrp_idx = $(this).attr('wrp_idx');
    let type = $(this).attr('type');
    let yy = $(this).attr('yy');
    let mm = $(this).attr('mm');
    let page_url = '<?=G5_USER_ADMIN_URL?>/workreport_form_update.php?w=d&type=' + type + '&yy=' + yy + '&mm=' + mm + '&wrp_idx=' + wrp_idx;
    // alert(page_url);
    location.href = page_url;
});
</script>
<?php
include_once ('./_tail.php');