<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '프로젝트관리';
include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// 시작일 (D-10)
$w1 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval -10 day) AS start_day ",1);
$sql_date_start = $w1['start_day'];
$st_date = $st_date ?: $sql_date_start;
$st_date1 = date_parse($st_date);
// print_r2($st_date1);
// 종료일 (D+30, 실제는 31일간 추출)
$w2 = sql_fetch(" SELECT date_add('".G5_TIME_YMD."', interval +31 day) AS end_day ",1);
$sql_date_end = $w2['end_day'];
$en_date = $en_date ?: $sql_date_end;
$en_date1 = date_parse($en_date);
// print_r2($en_date1);
// echo $sql_date_start.'~'.$sql_date_end.'<br>';


$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 

$where = array();
$where[] = " prs.prs_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

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
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'prj_name' || $sfl == 'prj_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
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
$sql_order = " ORDER BY prj_idx, prs_role, mb_id_worker, prs_start_date ";

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
		LIMIT {$from_record}, {$rows} 
";
// echo $sql.'<br>';
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$list = array();
$gantt_y = -1;
for($i=0;$row=sql_fetch_array($result);$i++) {
    $row['date1'] = date_parse($row['st_date']);
    $row['date2'] = date_parse($row['en_date']);
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
        }
    }
    $list[$i]['name'] = ($prj_idx_old != $row['prj_idx']) ? cut_str($row['prj_name'],10) : '';
    $list[$i]['role'] = strtoupper($row['prs_role']);
    $list[$i]['assignee'] = $row['mb_name'];
    $list[$i]['content'] = $row['prs_task'];
    // $list[$i]['start'] = 'Date.UTC('.$row['date1']['year'].', '.$row['date1']['month'].', '.$row['date1']['day'].')';
    // $list[$i]['end'] = 'Date.UTC('.$row['date2']['year'].', '.$row['date2']['month'].', '.$row['date2']['day'].')';
    $list[$i]['start_year'] = $row['date1']['year'];
    $list[$i]['start_month'] = $row['date1']['month'];
    $list[$i]['start_day'] = $row['date1']['day'];
    $list[$i]['end_year'] = $row['date2']['year'];
    $list[$i]['end_month'] = $row['date2']['month'];
    $list[$i]['end_day'] = $row['date2']['day'];
    $list[$i]['completed'] = ($row['prs_type']=='mine') ? $row['prs_percent']/100 : 0;
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

}
// print_r2($list);
// echo $gantt_y.'<br>';

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>
#fsearch{position:relative;}
#fsearch .btn_s_add{height:30px;line-height:30px;border:1px solid #ddd;background:#efefef;position:absolute;top:0;right:0;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="시작일">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="종료일">
<select name="sfl" id="sfl">
    <?php
    $skips = array('prj_idx','prj_status','prj_set_output','prj_image','trm_idx_category','prj_idx2','prj_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item');
    if(is_array($items)) {
        foreach($items as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
	<option value="prj.com_idx"<?php echo get_selected($_GET['sfl'], "prj.com_idx"); ?>>업체번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
<a href="./project_schedule_form.php?gant=1" class="btn btn_s_add">일정등록</a>
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
</div>


<div id="chart1"></div>

<script>
// var data_arr = <?=json_encode($list)?>;
// console.log(JSON.stringify(data_arr));
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


Highcharts.ganttChart('chart1', {
chart: {
    height: 50+45*<?=($gantt_y+1)?>
},
xAxis: [
    { // day display, first x-axis from bottom
        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=$st_date1['day']?>, 0),
        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=$en_date1['day']?>, 23, 59, 59),
        tickInterval: 1000 * 60 * 60 * 24 // 1 day
        ,labels: {
            format: '{value:%d}' // day of the week
        },
        grid: {
            cellHeight: 30
        }
    }
    ,{ // month display, 2nd x-axis from bottom
        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=$st_date1['day']?>, 0),
        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=$en_date1['day']?>, 23, 59, 59),
        tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
        ,labels: {
            format: '{value:%Y-%m}'
        },
        grid: {
            cellHeight: 30
        }
    },
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
    enabled: false,
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
        columns: [
        {
            title: {
            text: '프로젝트',
            rotation: 0,
                y: -5,
                x: -15
            },
            labels: {
                format: '{point.name}'
            }
        },
        {
            title: {
            text: '담당자', // 역할
            rotation: 0,
                y: -5,
                x: 0
            },
            labels: {
                format: '{point.role}'
            }
        },
        {
            title: {
            text: ' ',  // 담당자이름
            rotation: 0,
                y: -5,
                x: -15
            },
            labels: {
                format: '{point.assignee}'
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
                    location.href = './project_schedule_form.php?w=u&prs_idx=' +
                        this.options.prs_idx;
                }
            }
        }
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
                role: '".$list[$i]['role']."',
                assignee: '".$list[$i]['assignee']."',
                content: '".$list[$i]['content']."',
                start: Date.UTC(".$list[$i]['start_year'].", ".($list[$i]['start_month']-1).", ".$list[$i]['start_day']."),
                start_dt: '".$list[$i]['start_month'].".".$list[$i]['start_day']."',
                end: Date.UTC(".$list[$i]['end_year'].", ".($list[$i]['end_month']-1).", ".$list[$i]['end_day'].", 23, 59, 59),
                end_dt: '".$list[$i]['end_month'].".".$list[$i]['end_day']."',
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
</script>

<?php
include_once ('./_tail.php');
?>