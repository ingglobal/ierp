<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '프로젝트관리';
include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
"; 

$where = array();
$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만
if (!$member['mb_manager_yn']) {
    $where[] = " prj.com_idx = '".$member['mb_4']."' ";
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


if (!$sst) {
    $sst = "prj_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
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
?>
<style>
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
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
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
</div>


<div id="chart1"></div>

<script>
$(function(e) {

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
        // console.log(this.point.options.content);

        tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">4.12~5.12</span>';
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
});
</script>

<?php
include_once ('./_tail.php');
?>