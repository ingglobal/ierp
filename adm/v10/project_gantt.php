<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '프로젝트일정';
include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$week = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
$week2 = array('일', '월', '화', '수', '목', '금', '토');

// 시작일 (D-10)
$w1 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval -10 day) AS start_day ",1);//처음 -10 //2번째 수정 -15
$sql_date_start = $w1['start_day'];
$st_date = $st_date ?: $sql_date_start;
$st_date1 = date_parse($st_date);

//echo $week2[date("w",strtotime($st_date))]."<br>";
// print_r2($st_date1);
// 종료일 (D+30, 실제는 31일간 추출)
$w2 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval +51 day) AS end_day ",1);//처음 +31 //2번째수정 +46
$sql_date_end = $w2['end_day'];
//echo $sql_date_end."<br>";
$en_date = $en_date ?: $sql_date_end;
//echo $en_date."<br>";
$en_date1 = date_parse($en_date);

//echo $week2[date("w",strtotime($en_date))];
// print_r2($en_date1);
// echo $sql_date_start.'~'.$sql_date_end.'<br>';
$stdate = $st_date;
$endate = $en_date;

$we_arr = array();
while(strtotime($stdate) <= strtotime($endate)){
    //echo $stdate."<br>";
    if($week[date("w",strtotime($stdate))] == 'sat' || $week[date("w",strtotime($stdate))] == 'sun'){
        array_push($we_arr,$stdate);
    }
    $stdate = date("Y-m-d",strtotime("+1 day",strtotime($stdate)));
}

$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 

$where = array();
$where[] = " prs.prs_status NOT IN ('trash','delete','end','hide') ";  // 디폴트 검색조건

// 운영권한이 없으면 자기 것만
// if (!$member['mb_manager_yn']) {
//     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
// }
if ($st_date && $en_date) {
    $where[] = " prs.prs_start_date <= '$en_date' AND prs.prs_end_date >= '$st_date' ";
}
else if ($st_date) {
    $where[] = " prs.prs_start_date >= '$st_date' ";
}
else if ($en_date) {
    $where[] = " prs.prs_end_date <= '$en_date' ";
}

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_worker' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'prs_role' ) :
            $where[] = " ({$sfl} LIKE '".$g5['set_worker_type_reverse'][$stx]."%') ";
            break;
        case ($sfl == 'prs_department') :
            $where[] = " prs_department IN ('{$g5['set_department_name_reverse'][$stx]}','') ";
            $where[] = " prj.prj_idx IN (SELECT prj_idx FROM {$g5['project_schedule_table']} WHERE prs_department = '{$g5['set_department_name_reverse'][$stx]}' GROUP BY prj_idx HAVING COUNT(*) >= 1 ) ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// if (!$sst) {
//     $sst = "prj_idx";
//     $sod = "DESC";
// }
// $sql_order = " ORDER BY {$sst} {$sod} ";
// prs_role 이 먼저 나와야 함, PM이 나오고 그 다음..
$sql_order = " ORDER BY com.com_idx, prj_idx DESC, prs_role, mb_id_worker, prs_start_date ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS prs.*
            , com.com_name AS com_name
            , prs.prj_idx AS prj_idx
            , prj.prj_name AS prj_name
            , mb.mb_name AS mb_name
            , GREATEST('".$st_date."', prs_start_date ) AS st_date
            , LEAST('".$en_date."', prs_end_date ) AS en_date
        {$sql_common}
		{$sql_search}
        {$sql_order}
";
//echo $sql.'<br>';
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

//print gettype($g5['set_prs_type_arr']);

$list = array();
$gantt_y = -1;
for($i=0;$row=sql_fetch_array($result);$i++) {
    $row['start1'] = date_parse($row['st_date']);
    $row['start2'] = date_parse($row['prs_start_date']);
    $row['end1'] = date_parse($row['en_date']);
    $row['end2'] = date_parse($row['prs_end_date']);
    $row['pointwidth'] = $g5['setting']['set_gantt_thickness_'.$row['prs_type']] ?: 5;
    $row['color'] = $g5['setting']['set_gantt_color_'.$row['prs_type']] ?: '';
    // print_r2($row);
    // If same project, same worker, schedules should be located at the same line.
    if($prj_idx_old != $row['prj_idx']) {
        $gantt_y++;
    }
    else {
        if($mb_id_worker_old != $row['mb_id_worker']) {
            $gantt_y++;
        }else{
           if($mb_role_old != $row['prs_role']) {
                $gantt_y++;
           }
        }
        /*
        if(($mb_id_worker_old == $row['mb_id_worker'] && $mb_role_old != $row['prs_role']) || ($mb_id_worker_old != $row['mb_id_worker'] && $mb_role_old == $row['prs_role'])) {
            $gantt_y++;
        }
        */
        
    }
	
    if(G5_IS_MOBILE)
	    $list[$i]['name'] =  ($prj_idx_old != $row['prj_idx']) ? '['.$row['prj_idx'].']'.cut_str($row['prj_name'],10) : '';
    else
        $list[$i]['name'] =  ($prj_idx_old != $row['prj_idx']) ? '['.$row['prj_idx'].']'.cut_str($row['prj_name'],20) : '';
    $list[$i]['com_name'] = $row['com_name'];
    $list[$i]['prj_idx'] = $row['prj_idx'];
    $list[$i]['role'] = $g5['set_worker_type_value'][$row['prs_role']];
    $list[$i]['assignee'] = $row['mb_name'];
    $list[$i]['content'] = $row['prs_task'];
    // $list[$i]['start'] = 'Date.UTC('.$row['start1']['year'].', '.$row['start1']['month'].', '.$row['start1']['day'].')';
    // $list[$i]['end'] = 'Date.UTC('.$row['end1']['year'].', '.$row['end1']['month'].', '.$row['end1']['day'].')';
    $list[$i]['start_year'] = $row['start1']['year'];
    $list[$i]['start_month'] = $row['start1']['month'];
    $list[$i]['start_day'] = $row['start1']['day'];
    $list[$i]['prs_start_month'] = $row['start2']['month'];
    $list[$i]['prs_start_day'] = $row['start2']['day'];
    $list[$i]['end_year'] = $row['end1']['year'];
    $list[$i]['end_month'] = $row['end1']['month'];
    $list[$i]['end_day'] = $row['end1']['day'];
    $list[$i]['prs_end_month'] = $row['end2']['month'];
    $list[$i]['prs_end_day'] = $row['end2']['day'];
    $list[$i]['completed'] = (in_array($row['prs_type'], $g5['set_prs_type_arr'])) ? $row['prs_percent']/100 : 0;
    $list[$i]['pointWidth'] = $row['pointwidth'];
    $list[$i]['color'] = $row['color'];
    // $list[$i]['mb_id_worker'] = $row['mb_id_worker'];
    // $list[$i]['prs_type'] = $row['prs_type'];
    $list[$i]['prs_idx'] = $row['prs_idx']; // for url link 
    // $list[$i]['test'] = $prj_idx_old .'?='. $row['prj_idx'] .' / '. $mb_id_worker_old .'?='. $row['mb_id_worker'];
    $list[$i]['y'] = $gantt_y;

    // save old value for proj and mb_id_worker
    $prj_idx_old = $row['prj_idx'];
    $mb_id_worker_old = $row['mb_id_worker'];
    $mb_role_old = $row['prs_role'];

}
// print_r2($list);
// echo $gantt_y.'<br>';

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>
<style>
#fsearch{position:relative;}
#fsearch .btn_s_add{height:30px;line-height:30px;border:1px solid #ddd;background:#efefef;position:absolute;top:0;right:0;}

#fsearch .date_show{display:inline-block;margin-right:10px;font-size:1.2em;}
#fsearch .date_today{color:red;}
#fsearch .date_target{color:blue;}
#fsearch #depart_box{position:absolute;left:10px;bottom:-50px;z-index:100;}
#fsearch #depart_box .btn_depart{margin-left:5px;}
#fsearch #depart_box .btn_depart.focus{background:#4744bf;}

#target_date_box{position:fixed;right:10px;bottom:60px;z-index:1000;}
#target_date_box span#target_date{color:#0000ff;font-size:1.2em;font-weight:500;}
.pj_a{position:relative;top:8px;}
.pj_c{position:absolute;left:3px;top:-14px;font-size:0.6em;background:#def9de;height:16px;line-height:16px;padding:0 6px;border-radius:8px;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<p><b>검색기간은</b> 되도록 최대 <b>3개월정도</b>의 기간으로 지정하여 검색해 주세요.</p>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get" autocomplete="off">
<label for="sfl" class="sound_only">검색대상</label>
<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" style="width:80px;" placeholder="시작일">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" style="width:80px;" placeholder="종료일">
<select name="sfl" id="sfl">
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
	<option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option>
	<option value="prs_role"<?php echo get_selected($_GET['sfl'], "prs_role"); ?>>역할</option>
	<option value="prj.prj_idx"<?php echo get_selected($_GET['sfl'], "prj.prj_idx"); ?>>프로젝트번호</option>
	<option value="prs_department"<?php echo get_selected($_GET['sfl'], "prs_department"); ?>>부서명</option>
    <!--
	<option value="prs_task"<?php //echo get_selected($_GET['sfl'], "prs_task"); ?>>업무내용</option>
	<option value="prs_content"<?php //echo get_selected($_GET['sfl'], "prs_content"); ?>>상세설명</option>
	<option value="prj_name"<?php //echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
    -->
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
<div class="btn_fixed_top" style="top:55px;">
    <span class="date_show date_target"></span>
    <span class="date_show date_today"></span>
    <?php if($member['mb_manager_yn']) { ?>
    <a href="./project_gantt_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <?php } ?>
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <a href="./_win_sms_project_schedule.php" class="btn btn_03 btn_sms_project_schedule" style="margin-right:20px;">문자통지</a>
    <?php } ?>
    <a href="./project_schedule_form.php?gant=1" class="btn btn_01">일정등록</a>
</div>
<div id="depart_box">
    <?php if(count($g5['set_department_name_value'])){ 
    echo '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="btn btn_02 btn_depart'.(($sfl != 'prs_department')?' focus':'').'">전체</a>';
    foreach($g5['set_department_name_value'] as $key => $val){
    ?>
    <button type="button" class="btn btn_02 btn_depart" onclick="javascript:go_sch(this.value);" key="<?=$key?>" value="<?=$val?>"><?=$val?></a>
    <?php } ?>
    <?php } ?>
</div>
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
</div>

<div id="target_date_box"><span id="target_date"></span></div>
<div id="chart1"></div>

<script>
if($('#sfl').val() == 'prs_department'){
    $('.btn_depart').removeClass('focus');
    $('.btn_depart[key="<?=$g5['set_department_name_reverse'][$stx]?>"]').addClass('focus');
    $('option[value="prs_department"]').remove();
    $('#stx').val('');
}
function go_sch(d){
    $('#fsearch').attr('onsubmit','return sch_submit(this);');
    $('#sfl option').attr('selected',false);
    $('<option value="prs_department" selected="selected">부서명</option>').appendTo('#sfl');
    $('#stx').val(d);
    $('#fsearch .btn_submit').trigger('click');
}

// var data_arr = <?=json_encode($list)?>;
// console.log(JSON.stringify(data_arr));
var week = ['일','월','화','수','목','금','토'];
var we_arr = <?=json_encode($we_arr)?>;
$(function(e) {

    // 문자통지
    $(document).on('click','.btn_sms_project_schedule',function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
        winSMSProject = window.open(href,"winSMSProject","left=50,top=100,width=520,height=600,scrollbars=1");
        winSMSProject.focus();
    });


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

/*
height: 165+35*<?=($gantt_y+1)?>
*/
console.log(<?=($gantt_y+1)?>);
Highcharts.ganttChart('chart1', {
chart: {
    height: 165+35*<?=($gantt_y+1)?>,
    events: {
        load: function(){
            var st = Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']+5)?>, 0);
            var ed = Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']+40)?>, 0);//
            this.xAxis[0].setExtremes(st,ed);
            for(var idx in we_arr){
                //console.log(we_arr[idx]);
                var d = new Date(we_arr[idx]);
                var x_val = d.getTime();
                var x_axi = this.xAxis[0].axis;
                //console.log(x_val);
                this.xAxis[0].addPlotLine({
                    value:(x_val + (1000*60*60*12)),
                    width:16,
                    color:'#efefef'
                });
            }
            var t = new Date(getToday());
            //var t = new Date('2021-03-21');
            var today = t.getTime() + (1000*60*60*12);
            //console.log(today);
            $('.date_today').text(getToday()+'('+(week[new Date(getToday()).getDay()])+')');
            this.xAxis[0].addPlotLine({
                value: today,
                width:2,
                color: 'red',
                label: {
                    text: getToday(),
                    align:'right',
                    rotation: 0,
                    y: 14,
                    x: 80,
                    style: {
                        color: 'red'
                    }
                }
            });
        },
        click: function(e) {
            var xValue = e.xAxis[0].value;
            var xAxis = e.xAxis[0].axis;
            //console.log(xValue);
            var dt = new Date(formatDate(xValue));
            var pt = dt.getTime() + (1000*60*60*12);
            //$('.date_today').text(getToday()+'('+(week[new Date(getToday()).getDay()])+')');
            $('.date_target').text(formatDate(pt)+'('+(week[new Date(formatDate(pt)).getDay()])+')');
            //console.log(formatDate(xValue));
            $.each(xAxis.plotLinesAndBands,function(){
                if(this.id === 'myPlotLineId'){
                    this.destroy();
                }
            });
            xAxis.addPlotLine({
                value: pt,//xValue,//pt
                width:2,
                color: 'blue',
                label: {
                    text: formatDate(pt),//formatDate(xValue - (60*60*12)),//pt
                    align:'right',
                    rotation: 0,
                    y: 14,
                    x: 80,
                    style: {
                        color: 'blue'
                    }
                },
                id: 'myPlotLineId'
            });
        }
    }
},
xAxis: [
    { // day display, first x-axis from bottom
        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']-10)?>, 0),
        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=($en_date1['day'])?>, 23, 59, 59),
        tickInterval: 1000 * 60 * 60 * 24 // 1 day
        ,labels: {
            format: '{value:%d}' // day of the week
        },
        grid: {
            cellHeight: 30
        }
    }
    ,{ // month display, 2nd x-axis from bottom
        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=($st_date1['day']-10)?>, 0),
        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=($en_date1['day'])?>, 23, 59, 59),
        tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
        ,labels: {
            format: '{value:%Y-%m}'
        },
        grid: {
            cellHeight: 30
        },
        id: 'bottom-datetime-axis'
        /*
        currentDateIndicator: {
            width: 2,
            dashStyle: 'solid',
            color: 'red',
            label: {
                format: '%Y-%m-%d'
            }
        }*/
    }
],
yAxis: {
    uniqueNames: true,
    staticScale: 20
},
navigator: {
    enabled: true,
    liveRedraw: true,
    series: {
        lineColor: '#f2f2f2',
        type: 'spline', // 'gantt' - bar is showing outside of the navigator, not good, 
        // ^ but 'spline', it showw warning #15, but neglect it, no problem for running.
        pointPlacement: 0.5,
        pointPadding: 0.25
    },
    xAxis: {
        type: 'datetime',
        dateTimeLabelFormats: {
            second: '%H:%M:%S',
            minute: '%H:%M',
            hour: '%H:%M',
            day: '%m-%d',
            week: '%m-%d',
            month: '%Y-%m',
            year: '%Y-%m'
        }
    },
    yAxis: {
        min: 0,
        max: 3,
        reversed: true,
        categories: []
    }
},
scrollbar: {
    enabled: true
},
rangeSelector: {
    enabled: true,
    selected: 0
},
tooltip: {
    useHTML: true,
    formatter: function(tooltip) {
        // console.log(this);
        // console.log(this.point.options);
        // console.log(this.point.options.content);

        // tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">4.12~5.12</span>';
        tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">'+this.point.options.start_dt+'~'+this.point.options.end_dt+'</span>';
        if(this.point.options.content) {
            tooltip1 += '<br/><span style="font-size:0.9em;">'+this.point.options.content+'</span>';
        }
        return tooltip1;
        // // If not null, use the default formatter
        // return tooltip.defaultFormatter.call(this, tooltip);
    }
},
yAxis: {
    type: 'category',
    grid: {
        borderColor: '#dddddd',       
        style: {
            
        },
        columns: [
        {
            title: {
                text: '프로젝트',
                rotation: 0,
                y: -0,
                x: -15
            },
            labels: {
                //format: '{point.name}',
                align:'left',
                formatter: function(){
                    if(this.point.name && this.point.com_name){
                        return (this.point.name) ? '<a href="javascript:" class="pj_a" onclick="sch_project('+this.point.prj_idx+');"><span class="pj_c">'+this.point.com_name+'</span>'+this.point.name+'</a>' : "";
                    }else{
                        return (this.point.name) ? '<a href="javascript:" class-"pj_a1" onclick="sch_project('+this.point.prj_idx+');">'+this.point.name+'</a>' : "";
                    }  
                },
                useHTML: true,
                style: {
                    fontSize:'1em',
                    fontWeight: 'bold'
                }
            }
        },
        {
            title: {
                text: '역할', // 역할
                rotation: 0,
                y: -0,
                x: 0
            },
            labels: {
                format: '{point.role}'
            }
        },
        {
            title: {
            text: '담당자',  // 담당자이름
            rotation: 0,
                y: 0,
                x: 0
            },
            labels: {
                format: '{point.assignee}',
                formatter: function(){
                    return '<a href="javascript:" onclick="sch_worker(\''+this.point.assignee+'\');">'+this.point.assignee+'</a>';
                },
                useHTML: true,
            }
        }
        ]
    }
},
plotOptions: {
    series: {
        // opacity:0.8,
        cursor: 'pointer',
        point: {
            events: {
                click: function () {
                    // console.log(this.options);
                    location.href = './project_schedule_form.php?gant=1&w=u&prs_idx=' +
                        this.options.prs_idx + '&sst=<?=$sst?>&sod=<?=$sod?>&sfl=<?=$sfl?>&stx=<?=$stx?>&page=<?=$page?>';
                }
            }
        }/*,
        animation: false, // Do not animate dependency connectors
        dragDrop: {
          draggableX: true,
          draggableY: true,
          dragMinY: 0,
          dragMaxY: 2,
          //dragPrecisionX: day / 3 // Snap to eight hours
        }*/
    }
},
series: [
{
    name: 'Projects',
    opacity:0.85,
    data: [
        <?php
        for($i=0;$i<sizeof($list);$i++) {
            echo "
            {   name: '".$list[$i]['name']."',
                prj_idx: '".$list[$i]['prj_idx']."',
                role: '".$list[$i]['role']."',
                com_name: '".$list[$i]['com_name']."',
                assignee: '".$list[$i]['assignee']."',
                content: '".$list[$i]['content']."',
                start: Date.UTC(".$list[$i]['start_year'].", ".($list[$i]['start_month']-1).", ".$list[$i]['start_day']."),
                start_dt: '".$list[$i]['prs_start_month'].".".$list[$i]['prs_start_day']."',
                end: Date.UTC(".$list[$i]['end_year'].", ".($list[$i]['end_month']-1).", ".$list[$i]['end_day'].", 23, 59, 59),
                end_dt: '".$list[$i]['prs_end_month'].".".$list[$i]['prs_end_day']."',
                completed: ".$list[$i]['completed'].",
                pointWidth: ".$list[$i]['pointWidth'].",
                color: '".$list[$i]['color']."',
                prs_idx: ".$list[$i]['prs_idx'].",
                y: ".$list[$i]['y']."
            },
            ";
        }
        ?>
    ]
    // data: [
    // {
    //     name: '아진산업',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#7882a6',
    //     prs_idx: 4,
    //     y: 0
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2017, 12, 20),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0,
    //     pointWidth: 8,
    //     color: '#cccccc',
    //     y: 1
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0,
    //     pointWidth: 2,
    //     color: '#ff6361',
    //     y: 1
    // },
    // {
    //     name: '세림산업',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#525b7e',
    //     prs_idx: 4,
    //     y: 2
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2018, 1, 2),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     prs_idx: 4,
    //     y: 3
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 4
    // },
    // {
    //     name: '아강테크',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#525b7e',
    //     y: 5
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2018, 1, 2),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 6
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 6
    // },
    // {
    //     name: '세림산업',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#525b7e',
    //     y: 7
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2018, 1, 2),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 8
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 9
    // },
    // ]
}
]
});
removeLogo();
// highchart.com이라는 로고 제거
function removeLogo() {
    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },10);
}

/*
if($('#sfl').val() == 'prs_department'){
    $('.btn_depart').removeClass('focus');
    $('.btn_depart[key="<?php //$g5['set_department_name_reverse'][$stx]?>"]').addClass('focus');
    $('option[value="prs_department"]').remove();
    $('#stx').val('');
}
function go_sch(d){
    $('#fsearch').attr('onsubmit','return sch_submit(this);');
    $('#sfl option').attr('selected',false);
    $('<option value="prs_department" selected="selected">부서명</option>').appendTo('#sfl');
    $('#stx').val(d);
    $('#fsearch .btn_submit').trigger('click');
}
*/

function sch_project(prj_idx){
    $('#sfl option').attr('selected',false);
    $('#sfl option[value="prj.prj_idx"]').attr('selected',true);
    $('#stx').val(prj_idx);
    $('#fsearch .btn_submit').trigger('click');
}


function sch_worker(wname){
    $('#sfl option').attr('selected',false);
    $('#sfl option[value="mb_name"]').attr('selected',true);
    $('#stx').val(wname);
    $('#fsearch .btn_submit').trigger('click');
}
/*
$('#chart1').bind('click', function (e) {
    var chart = $(this).highcharts();
    var rect = $('.highcharts-plot-background').offset();
    var rect_wd = $('.highcharts-plot-background').width();
    var rect_ht = $('.highcharts-plot-background').height();
    var minX = rect.left;
    var minY = rect.top;
    var maxX = minX + rect_wd;
    var maxY = minY + rect_ht;
    e = chart.pointer.normalize(e);
 
   //console.log(chart.xAxis[0].toValue(e.chartX));
   if(e.pageX > minX && e.pageY > minY && e.pageX < maxX && e.pageY < maxY){
       if(e.ctrlKey){
           //console.log(formatDate(chart.xAxis[0].toValue(e.chartX))+':'+e.pageX);
           $('.highcharts-plot-lines-0').attr('id','click_bar1');
           if($('#click_bar').length > 0) $('#click_bar').remove();
           $('<g id="click_bar" class="" data-z-index="0"><path fill="none" class="highcharts-plot-line " stroke="red" stroke-width="2" stroke-dasharray="none" d="M '+e.pageX+' 70 L '+e.pageX+' 2066"></path></g>').insertAfter('#click_bar1');
           //$('#click_bar').css('left',(e.pageX - minX));
       }
   }
});
*/
function formatDate(date) {
    var d = new Date(date),
    month = '' + (d.getMonth() + 1),
    day = '' + d.getDate(),
    year = d.getFullYear();
    
    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    return [year, month, day].join('-');   
}

function getToday(){
    var date = new Date();
    var year = date.getFullYear();
    var month = ("0" + (1 + date.getMonth())).slice(-2);
    var day = ("0" + date.getDate()).slice(-2);

    return year + "-" + month + "-" + day;
}
</script>

<?php
include_once ('./_tail.php');
?>