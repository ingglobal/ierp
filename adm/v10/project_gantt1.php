<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '프로젝트일정';
include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


//쿼리수정
$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 

$where = array();
$where[] = " prs.prs_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만
//if (!$member['mb_manager_yn']) {
//    $where[] = " prs.prs_status = 'ok' ";
//}
if ($start_fr_date && $start_to_date) {
    $where[] = " prs.prs_start_date between '$start_fr_date' and '$start_to_date' ";
}

if ($end_fr_date && $end_to_date) {
    $where[] = " prs.prs_end_date between '$end_fr_date' and '$end_to_date' ";
}


if ($stx){
    switch($sfl){
		case ( $sfl == 'prj.com_idx' || $sfl == 'prs_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        case($sfl == 'prj.prj_name'):
            $where[] = " (prj.prj_name LIKE '%{$stx}%') ";
            break;
        case($sfl == 'mb.mb_name'):
            $where[] = " (mb.mb_name LIKE '%{$stx}%') ";
            break;
        case($sfl == 'prs.prs_content'):
            $where[] = " (prs.prs_content LIKE '%{$stx}%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


$sst = "prs.prj_idx DESC, prs.prs_type ASC";
$sod = '';

$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS prs.prj_idx,prs.prs_type,prs.prs_content,prs.prs_start_date,prs.prs_end_date,prs.prs_graph_type,prs.prs_graph_color,prs.prs_graph_thickness,prs.prs_percent,prj.prj_name, mb.mb_name
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
// echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$schd_arr = ['name' => 'Projects', 'data' => array()];
//echo $result->num_rows."<br>";
if($result->num_rows){
    $y = 0;
    $y_arr = array();
    $prj_idx = 0;
    for($i=0;$row=sql_fetch_array($result);$i++){
        // print_r2($row);
        //if($com_name != $srow['prj_name']) $com_name = $srow['prj_name'];
        //else $srow['prj_name'] = '';
        $rarr = array();
        
        $rarr['name'] = $row['prj_name'];
        $rarr['role'] = strtoupper($row['prs_type']);
        $rarr['assignee'] = $row['mb_name'];
        $rarr['content'] = $row['prs_content'];
        $rarr['start'] = strtotime($row['prs_start_date']." 00:00:01") * 1000;
        $rarr['end'] = strtotime($row['prs_end_date']." 23:59:59") * 1000;
        $rarr['completed'] = $row['prs_percent']/100;
        $rarr['pointWidth'] = $row['prs_graph_thickness'];
        $rarr['color'] = $row['prs_graph_color'];
        
        if($prj_idx != $row['prj_idx']){
            $y_arr = array();
            $prj_idx = $row['prj_idx'];
            $y_arr[$row['prs_type']] = $y;
            $rarr['y'] = $y;
            $y++;
        }else{
            $rarr['name'] = '';
            if(array_key_exists($row['prs_type'],$y_arr)){
                $rarr['y'] = $y_arr[$row['prs_type']];
                //$y--;
            }else{
                $y_arr[$row['prs_type']] = $y;
                $rarr['y'] = $y;
                $y++;
            }
        }
        //echo $row['prj_idx'].':'.$row['prs_type'].':'.$y."<br>";
        array_push($schd_arr['data'],$rarr);
    }
}
//print_r2($schd_arr);
?>
<style>
#fsearch{position:relative;}
#fsearch .btn_s_add{height:30px;line-height:30px;border:1px solid #ddd;background:#efefef;position:absolute;top:0;right:0;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<!--form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    //$skips = array('prj_idx','prj_status','prj_set_output','prj_image','trm_idx_category','prj_idx2','prj_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item');
    //if(is_array($items)) {
    //    foreach($items as $k1 => $v1) {
    //        if(in_array($k1,$skips)) {continue;}
    //        echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
    //    }
    //}
    ?>
	<option value="prj.com_idx"<?php //echo get_selected($_GET['sfl'], "prj.com_idx"); ?>>업체번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form-->
<?php
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="prj.prj_name"<?php echo get_selected($_GET['sfl'], "prj.prj_name"); ?>>프로젝트명</option>
	<option value="prs.prj_idx"<?php echo get_selected($_GET['sfl'], "prs.prj_idx"); ?>>프로젝트번호</option>
	<option value="mb.mb_name"<?php echo get_selected($_GET['sfl'], "mb.mb_name"); ?>>담당자명</option>
	<option value="prs.prs_content"<?php echo get_selected($_GET['sfl'], "prs.prs_content"); ?>>일정내용</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
<a href="./project_schedule_form.php?gant=1" class="btn btn_s_add">일정등록</a>
</form>
<form class="local_sch03 local_sch">
    <strong>시작일자</strong>
    <input type="text" id="start_fr_date"  name="start_fr_date" value="<?php echo $start_fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="start_to_date"  name="start_to_date" value="<?php echo $start_to_date; ?>" class="frm_input" size="10" maxlength="10">
    &nbsp;&nbsp;&nbsp;&nbsp;
    <strong>종료일자</strong>
    <input type="text" id="end_fr_date"  name="end_fr_date" value="<?php echo $end_fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="end_to_date"  name="end_to_date" value="<?php echo $end_to_date; ?>" class="frm_input" size="10" maxlength="10">
    <input type="submit" value="검색" class="btn_submit" style="height:30px;line-height:30px;">
</form>
<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
</div>


<div id="chart1"></div>


<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>
<script>
var schd_arr = <?php echo json_encode($schd_arr); ?>;
console.log(JSON.stringify(schd_arr));
$(function(e) {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
});

Highcharts.ganttChart('chart1', {
// chart: {
//     height: 1
// },
xAxis: [
    { // day display, first x-axis from bottom
        tickInterval: 1000 * 60 * 60 * 24 // 1 day
        ,labels: {
            format: '{value:%d}' // day of the week
        },
        grid: {
            cellHeight: 30
        }
    }
    ,{ // month display, 2nd x-axis from bottom
        tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
        ,labels: {
            format: '{value:%Y-%m}'
        },
        grid: {
            cellHeight: 30
        }
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
    enabled: false,
    selected: 0
},
tooltip: {
    useHTML: true,
    formatter: function(tooltip) {
        // console.log(this);
        // console.log(this.point.options);
        //console.log(this.point.options.content);
        //var start_date = new Date(this.point.start);
        //var end_date = new Date(this.point.end);
        //var start_dt = start_date.format('yyyy-MM-dd');
        //var end_dt = end_date.format('yyyy-MM-dd');
        var start_dt = formatDate(this.point.options.start);
        var end_dt = formatDate(this.point.options.end);

        tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">'+start_dt+'~'+end_dt+'</span>';
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
series: [schd_arr]
});

/*
series: [
{
    name: 'Projects',
    data: [
    {
        name: '아진산업',
        role: 'PM',
        assignee: '김청탁',
        content: '로봇 티칭 완료',
        start: Date.UTC(2017, 11, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.55,
        color: 'rgb(124, 181, 236)',
        y: 0
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        content: '현대 로봇 셋업 함',
        start: Date.UTC(2017, 12, 20),
        end: Date.UTC(2018, 1, 5),
        completed: 0.5,
        color: 'rgb(67, 67, 72)',
        y: 1
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        start: Date.UTC(2017, 12, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.5,
        pointWidth: 7,
        color: '#dddddd',
        y: 1
    },
    {
        name: '세림산업',
        role: 'PM',
        assignee: '김청탁',
        content: '로봇 티칭 완료',
        start: Date.UTC(2017, 11, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.55,
        color: 'rgb(124, 181, 236)',
        y: 2
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        content: '현대 로봇 셋업 함',
        start: Date.UTC(2018, 1, 2),
        end: Date.UTC(2018, 1, 5),
        completed: 0.5,
        color: 'rgb(124, 181, 236)',
        y: 3
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        start: Date.UTC(2017, 12, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.5,
        color: 'rgb(124, 181, 236)',
        y: 4
    },
    {
        name: '아강테크',
        role: 'PM',
        assignee: '김청탁',
        content: '로봇 티칭 완료',
        start: Date.UTC(2017, 11, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.55,
        color: 'rgb(124, 181, 236)',
        y: 5
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        content: '현대 로봇 셋업 함',
        start: Date.UTC(2018, 1, 2),
        end: Date.UTC(2018, 1, 5),
        completed: 0.5,
        color: 'rgb(124, 181, 236)',
        y: 6
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        start: Date.UTC(2017, 12, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.5,
        color: 'rgb(124, 181, 236)',
        y: 6
    },
    {
        name: '세림산업',
        role: 'PM',
        assignee: '김청탁',
        content: '로봇 티칭 완료',
        start: Date.UTC(2017, 11, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.55,
        color: 'rgb(124, 181, 236)',
        y: 7
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        content: '현대 로봇 셋업 함',
        start: Date.UTC(2018, 1, 2),
        end: Date.UTC(2018, 1, 5),
        completed: 0.5,
        color: 'rgb(124, 181, 236)',
        y: 8
    },
    {
        name: '',
        role: 'SUB1',
        assignee: '최치환',
        start: Date.UTC(2017, 12, 1),
        end: Date.UTC(2018, 1, 2),
        completed: 0.5,
        color: 'rgb(124, 181, 236)',
        y: 9
    }
    ]
}
]
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
</script>

<?php
include_once ('./_tail.php');
?>