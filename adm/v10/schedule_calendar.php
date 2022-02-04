<?php
$sub_menu = "960150";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '일정관리';
// include_once('./_top_menu_schedule.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];


// 디폴트값 설정
$month = ($month)? $month:date('Ym', G5_SERVER_TIME);
$_month = substr($month,0,4).'-'.substr($month,-2);

// include calendar.js
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/calendar.js"></script>', 10);
?>

<!-- 달력 시작 { -->
    <div class="calendar">
    <div class="calendar_title">
        <a href="<?php echo $board_skin_url?>/list.calendar.php?bo_table=<?php echo $bo_table?>" class="btn_mode btn_calendar_skin"><i class="fa fa-calendar"></i> 달력</a>
        <a href="<?php echo G5_BBS_URL?>/board.php?bo_table=<?php echo $bo_table?>" class="btn_mode btn_list_skin"><i class="fa fa-list-alt"></i> 리스트</a>

        <a href="javascript:" class="prev_month" cal_val="-1" title="이전달"><i class="fa fa-arrow-circle-left"></i></a>
        <span class="this_month"><?=$_month?></span>
        <a href="javascript:" class="next_month" cal_val="+1" title="다음달"><i class="fa fa-arrow-circle-right"></i></a>

        <?php if ($member['mb_level']>=$board['bo_write_level']) { ?>
        <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=<?php echo $bo_table;?>" class="btn_mode btn_write">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <span>등록</span>
        </a>
        <?php } ?>

    </div>
	<div class="caution" style="display:<?php if(!$board['set_max_time_apply']&&!$board['set_max_apply']) echo 'none';?>;">
        동일시간대 예약가능 인원은 <span style=""><?php echo $board['set_max_time_apply'];?></span>명까지, 당일 예약가능 인원은 총 <span style=""><?php echo $board['set_max_apply'];?></span>명까지입니다.
	</div>
    <div class="div_calendar">
        <table class="table_calendar">
        <thead>
        <tr>
            <th class="th_sunday">일</th>
            <th>월</th>
            <th>화</th>
            <th>수</th>
            <th>목</th>
            <th>금</th>
            <th class="th_saturday">토</th>
        </tr>
        </thead>
        <tbody><!-- 달력 리스트 --></tbody>
        </table>
    </div>
</div>
<!-- } 달력 종료 -->


<script>
var g5_board_config = 0;
</script>

<?php
include_once ('./_tail.php');
?>
