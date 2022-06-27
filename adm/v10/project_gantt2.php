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

    <div id="chart1">
    </div>


<script>
$(function(e) {

});

Highcharts.ganttChart('chart1', {
      navigator: {
        enabled: true,
        // liveRedraw: true,
        series: {
          type: 'areaspline',
          fillOpacity: 0.05,
          dataGrouping: {
            smoothed: true
          },
          color: '#FF00FF',
          lineWidth: 1,
          marker: {
            enabled: true
          }
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
      // Zoom starts something and Date range selection setting.
      rangeSelector: {
        enabled: false,
        // 0-1m; 1-3m; 2-6m; 3-YTD(Year to date); 4-1y; 5-all
        selected: 5
      },
      xAxis: [
        {
          labels: {
            format: '{value:%d}' // day of the week
          },
          grid: {
            // default setting
            enabled: true
          },
          tickInterval: 1000 * 60 * 60 * 24 // Day
        },
        {
          labels: {
            format: 'Week{value:%W}'
          },
          tickInterval: 1000 * 60 * 60 * 24 * 7 // week
        }
      ],
      yAxis: {
        type: 'category',
        grid: {
          borderColor: '#dddddd',
          columns: [
            {
              title: {
                text: '프로젝트',
                rotation: 0,
                y: -15,
                x: -15
              },
              labels: {
                format: '{point.name}'
              }
            },
            {
              title: {
                text: '담당자',
                rotation: 0,
                y: -15,
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
              name: 'Project 6',
              start: Date.UTC(2014, 10, 18),
              end: Date.UTC(2014, 10, 25),
              completed: 0.55,
              assignee: 'Richards',
              y: 0
            },
            {
              name: 'Project 2',
              start: Date.UTC(2014, 10, 27),
              end: Date.UTC(2014, 10, 29),
              assignee: 'Marc',
              y: 1,
              completed: 0.65
            },
            {
              name: 'Project 3',
              start: Date.UTC(2014, 10, 20),
              end: Date.UTC(2014, 10, 25),
              assignee: 'Thomas',
              y: 2,
              completed: 0.45
            },
            {
              name: 'Project 4',
              start: Date.UTC(2014, 10, 23),
              end: Date.UTC(2014, 10, 26),
              assignee: 'Benjamin',
              y: 3,
              completed: 0.35
            },
            {
              name: 'Project 5',
              start: Date.UTC(2014, 10, 23),
              end: Date.UTC(2014, 10, 26),
              assignee: 'Benjamin5',
              y: 4,
              completed: 0.35
            },
            {
              name: 'Project 6',
              start: Date.UTC(2014, 10, 23),
              end: Date.UTC(2014, 10, 26),
              assignee: 'Benjamin5',
              y: 5,
              completed: 0.35
            },
            {
              name: 'Project 6',
              start: Date.UTC(2014, 10, 20),
              end: Date.UTC(2014, 10, 21),
              assignee: 'Benjamin6',
              y: 5,
              completed: 0.35
            }
          ],
          type: null
        }
      ]
    });
</script>

<?php
include_once ('./_tail.php');
?>