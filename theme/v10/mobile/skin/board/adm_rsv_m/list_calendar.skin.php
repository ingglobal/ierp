<?php
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style_calendar.css">', 0);
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.0/main.min.css">', 1);
add_javascript('<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.0/main.min.js"></script>',1);
add_javascript('<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.0/locales/ko.js"></script>',1);
add_javascript('<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>',1);
add_javascript('<script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script>',1);

//차량종류( carname )
if($board['bo_2_subj'] && $board['bo_2'] && preg_match("/,/",$board['bo_2']) && preg_match("/=/",$board['bo_2'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_2']));
    $valname = $board['bo_2_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
    }
}

//막대기 배경색( carbgcolor ) bo_2
if($board['bo_5_subj'] && $board['bo_5'] && preg_match("/,/",$board['bo_5']) && preg_match("/=/",$board['bo_5'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_5']));
    $valname = $board['bo_5_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
    }
}
//막대기 폰트색( carftcolor ) bo_2
if($board['bo_6_subj'] && $board['bo_6'] && preg_match("/,/",$board['bo_6']) && preg_match("/=/",$board['bo_6'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_6']));
    $valname = $board['bo_6_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
    }
}

$thismonth = strtotime(substr(G5_TIME_YMD,0,8).'01 00:00:00');
$thisdt = substr(G5_TIME_YMD,0,8).'01 00:00:00';
$startdt = date("Y-m-d H:i:s",strtotime("-1year",$thismonth));
$enddt = date("Y-m-d H:i:s",strtotime("+1year",$thismonth));

$cld_sql = " SELECT wr_id,wr_subject,wr_content,wr_1,wr_2,wr_3 FROM {$write_table} WHERE wr_1 <= '{$enddt}' AND wr_2 >= '{$startdt}' ";
$cld_rst = sql_query($cld_sql,1);
$cal_arr = [];
for($i=0;$scd=sql_fetch_array($cld_rst);$i++){
    $scd['wr_1'] = str_replace(' ','T',$scd['wr_1']);
    $scd['wr_2'] = str_replace(' ','T',$scd['wr_2']);
    $scd_arr = array(
        'id' => $scd['wr_id']
        ,'title' => $bo_carname_value[$scd['wr_subject']].'-'.$scd['wr_content']
        ,'start' => $scd['wr_1']
        ,'end' => $scd['wr_2']
        ,'url' => G5_USER_ADMIN_URL.'/bbs_write.php?bo_table=car_rsv&w=u&wr_id='.$scd['wr_id'].'&calendar=1'
        ,'classNames' => 'scd_'.$scd['wr_subject']
        ,'backgroundColor' => $bo_carbgcolor_value[$scd['wr_subject']]
        ,'borderColor' => $bo_carbgcolor_value[$scd['wr_subject']]
        ,'textColor' => $bo_carftcolor_value[$scd['wr_subject']]
    );
    array_push($cal_arr,$scd_arr);
}

//$wr_1_t = substr_replace($write['wr_1'],' ','T');
?>
<p style="padding-bottom:10px;padding-top:10px;color:red;">새로운 일정을 등록하려면 해당<b style="color:blue;">날짜의 빈공간</b>을 클릭하세요.</p>
<div id='calendar'></div>
<div class="btn_fixed_top">
    <a href="<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=<?=$bo_table?>" id="" class="btn btn_02">목록</a>
</div>
<script>
var evt_arr = <?php echo json_encode($cal_arr); ?>;
//console.log(evt_arr);
document.addEventListener('DOMContentLoaded', function() {
    $('.fc-today-button').off('click');
    $('.fc-daygrid-day-number').off('click');
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ko',
        initialView: 'dayGridMonth',
        //initialDate: '2021-05-25',
        headerToolbar: {
            left: 'prev,next today',
            //left: '',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
            //right: ''
        },
        timeFormat:{agenda:'h(:mm)t'},
        displayEventEnd: true,
        //editable: true,
        events: evt_arr,
        eventClick: function(info){
            var eventObj = info.event;
            if(eventObj.url){
                //window.open(eventObj.url);
                //info.jsEvent.preventDefault();//prevents browser from following link in current tab.
                location.href = eventObj.url;
            }
            else {
                ;//alert(eventObj.id);
            }
        },
        /*
        eventDidMount: function(info,el,jsEvent,view){
            var eventObj = info.event;
            var tooltip = new Tooltip(info.el, {
                title: eventObj.end,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        eventMouseEnter: function(info,el,jsEvent,view){
            var eventObj = info.event;
            console.log(eventObj.title);
        },
        eventMouseLeave: function(info,el,jsEvent,view){
            var eventObj = info.event;
            console.log(eventObj.title);
        },
        */
        
        dateClick: function(date,jsEvent,view){
            //console.log(date.dateStr);
            location.href = '<?=G5_USER_ADMIN_URL?>/bbs_write.php?bo_table=car_rsv&calendar=1&target_dt='+date.dateStr;
        }
    });
    calendar.render();
    <?php if($target_dt){ //카렌터를 원하는 날짜(월)로 이동 ?>
    calendar.gotoDate('<?=$target_dt?>');
    <?php } ?>
    //calendar.gotoDate('<?php //echo G5_TIME_YMD; ?>');
    //#external-events .fc-event
    
    $('.fc-event').each(function() {
        /*
        // store data so the calendar knows to render an event upon drop
        $(this).data('event', {
            title: $.trim($(this).text()), // use the element's text as the event title
            stick: true // maintain when user navigates (see docs on the renderEvent method)
        });

        // make the event draggable using jQuery UI
        
        $(this).draggable({
            zIndex: 999,
            revert: true,      // will cause the event to go back to its
            revertDuration: 0  //  original position after the drag
        });
        */
    });

    if($('.fc-today-button').length){
        $('.fc-today-button').attr('disabled',false);
        $('.fc-today-button').on('click',function(){
            location.href = '<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=car_rsv&calendar=1';
        });
    }

});
</script>