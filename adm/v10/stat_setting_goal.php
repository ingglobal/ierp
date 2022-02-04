<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '매출목표설정';
include_once('./_top_menu_stat.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

//-- 기본 검색값 할당
$ym = ($ym) ? $ym : date("Y-m",G5_SERVER_TIME);

//-- 이전달,다음달 추출
$ym_prev = ((int)substr($ym,-2) == 1) ? 
				(substr($ym,0,4)-1).'-12' 
				: substr($ym,0,4).'-'.sprintf("%02d",((int)substr($ym,-2)-1));
$ym_next = ((int)substr($ym,-2) == 12) ?
				(substr($ym,0,4)+1).'-01'
				: substr($ym,0,4).'-'.sprintf("%02d",((int)substr($ym,-2)+1));

//-- 이전 3개월 & 검색 시작 및 종료일 추출 (이번달꺼는 추출할 필요없음)
$this_year = substr($ym,0,4);
$this_month = (int)substr($ym,5,2);
for($i=-1;$i>-5;$i--) {
	if($this_month == 1) {
		$this_year = $this_year - 1;
		$this_month = 12;
	}
	else {
		$this_year = $this_year;
		$this_month = $this_month - 1;
	}
	${"month".$i} = $this_year.'-'.sprintf("%02d",$this_month);
//	echo ${"month".$i}.'<br>'; 
}
$ym_start = ${"month-3"};	// 3달전 ym


//print_r2($member);
//echo $member['mb_position'].'<br>';	// meta 확장, u.default.php
//echo $member['mb_2'].'<br>';	// company_member 추출, u.project.php
//echo $g5['department_down_idxs'][$member['mb_2']].'<br>'; // 하부조직코드(들), u.project.php
// 나의 조직 코드 배열, 수정,삭제 버튼 노출시 사용
$my_dept_array = explode(",",$g5['department_down_idxs'][$member['mb_2']]);
//print_r2($my_dept_array);


// 관리자 레벨이 아니면 자기 조직 범위 안에서만 리스트에 나옴, 2=회원,4=업체,6=영업자,8=관리자,10=수퍼관리자
if ($is_admin!='super'&&auth_check($auth[$sub_menu],"d",1)) {
	$sql_myteam = " AND mb.mb_id IN ( SELECT mb_id FROM {$g5['member_table']} 
												WHERE mb_2 IN (".$g5['department_down_idxs'][$member['mb_2']].") )
	";
}
//echo $sql_myteam;

//-- 부서 검색 (tmr로 분리된 경우는 term 전체를 가지고 와서 연동해 줘야 함! company_list.php 참조.)
$trm_idxs_search = ($ser_trm_idxs) ? " AND mb_2 IN (".$ser_trm_idxs.") " : "";

$sql_common = " 	FROM {$g5['member_table']} AS mb
							LEFT JOIN {$g5['sales_goal_table']} AS slg ON slg.mb_id_saler = mb.mb_id AND slg.slg_ym = '".$ym."-00'
";

$sql_search = " WHERE mb_level IN (6,7,8) AND mb_2 != '' AND mb_leave_date = '' ".$sql_myteam.$trm_idxs_search;
if ($stx) {
    $sql_search .= " AND ( ";
    switch ($sfl) {
		case ( $sfl == 'mb_id' || $sfl == 'mb_id_saler' || $sfl == 'sls.com_idx' || $sfl == 'od_id' || $sfl == 'it_id' || $sfl == 'ct_id' || $sfl == 'sls_idx' ) :
            $sql_search .= " ({$sfl} = '{$stx}') ";
            break;
        default :
            $sql_search .= " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 나보다 상위 레벨은 안 보임
//if ($is_admin != 'super')
//    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";

if (!$sst) {
    $sst = "mb_2, mb_1";
    $sod = "DESC";
}
else {
    if ($sst == 'mb_1')
		$sst = "convert(mb_1, decimal)";
}
$sql_order = " ORDER BY {$sst} {$sod} ";


//$rows = $config['cf_page_rows'];
$rows = 50;
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS mb.*, slg.slg_ym, slg.slg_sales_goal, slg.slg_cms_goal
			, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
				WHERE mta_db_table = 'member' AND mta_db_id = mb.mb_id ) AS mb_metas
			, ( SELECT slg_sales_goal FROM {$g5['sales_goal_table']} WHERE mb_id_saler = mb.mb_id AND slg_ym = '".${'month-1'}."-00' ) AS sales_goal_m1
			, ( SELECT slg_sales_goal FROM {$g5['sales_goal_table']} WHERE mb_id_saler = mb.mb_id AND slg_ym = '".${'month-2'}."-00' ) AS sales_goal_m2
			, ( SELECT slg_sales_goal FROM {$g5['sales_goal_table']} WHERE mb_id_saler = mb.mb_id AND slg_ym = '".${'month-3'}."-00' ) AS sales_goal_m3
			, ( SELECT slg_sales_goal FROM {$g5['sales_goal_table']} WHERE mb_id_saler = mb.mb_id AND slg_ym = '".${'month-4'}."-00' ) AS sales_goal_m4
		{$sql_common}
		{$sql_search} {$sql_order} 
		LIMIT {$from_record}, {$rows}
";
$result = sql_query($sql,1);
//echo $sql;
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 11;
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

<?php if ($is_admin=='super' || !auth_check($auth[$sub_menu],"d",1) || $member['mb_1'] >= 6) { ?>
<select name="ser_trm_idxs" class="cp_field" title="부서선택">
	<option value="">전체부서</option>
	<?=$department_select_options?>
</select>
<script>$('select[name=ser_trm_idxs]').val('<?=$_GET['ser_trm_idxs']?>').attr('selected','selected');</script>
<?php } ?>
<select name="sfl" id="sfl">
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>이름</option>
    <option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>회원아이디</option>
    <option value="mb_1"<?php echo get_selected($_GET['sfl'], "mb_1"); ?>>직책</option>
    <option value="mb_email"<?php echo get_selected($_GET['sfl'], "mb_email"); ?>>E-MAIL</option>
    <option value="mb_tel"<?php echo get_selected($_GET['sfl'], "mb_tel"); ?>>전화번호</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>휴대폰번호</option>
    <option value="mb_datetime"<?php echo get_selected($_GET['sfl'], "mb_datetime"); ?>>가입일시</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="btn_add01 btn_add">
	<a href="javascript:" data-value="1.1">10%일괄적용</a>
	<a href="javascript:" data-value="1.05">5%일괄적용</a>
	<a href="javascript:" data-value="1">이전달동일적용</a>
</div>

<form name="form01" id="form01" action="./stat_setting_goal_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ym" value="<?php echo $ym; ?>">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs; ?>">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed table-hover">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col">
			<label for="chkall" class="sound_only">사원 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)" checked>
		</th>
		<th scope="col" style="width:110px;"><?php echo subject_sort_link('mb_name','ser_trm_idxs='.$ser_trm_idxs) ?>이름</a></th>
		<th scope="col" style="width:;"><?php echo subject_sort_link('mb_id','ser_trm_idxs='.$ser_trm_idxs) ?>아이디</a></th>
		<th scope="col">소속</th>
		<th scope="col" style="width:70px;"><?php echo subject_sort_link('mb_1','ser_trm_idxs='.$ser_trm_idxs,'desc') ?>직책</a></th>
		<th scope="col" style="width:120px;"><?=${'month-4'}?> 목표</th>
		<th scope="col" style="width:120px;"><?=${'month-3'}?> 목표</th>
		<th scope="col" style="width:120px;"><?=${'month-2'}?> 목표</th>
		<th scope="col" style="width:120px;"><?=${'month-1'}?> 목표</th>
		<th scope="col" style="width:120px;"><?=$ym?> 목표</th>
	</tr>
	</thead>
	<tbody>
	<?php
	for ($i=0; $row=sql_fetch_array($result); $i++) {
		//print_r2($row);
		// 메타 분리
		$pieces = explode(',', $row['mb_metas']);
		foreach ($pieces as $piece) {
			list($key, $value) = explode('=', $piece);
			$row[$key] = $value;
		}
		unset($pieces);unset($piece);
		//print_r2($row);

        // 사이드메뉴
		$row['mb_emp'] = get_sideview($row['mb_id'], get_text($row['mb_name']));
	
	?>
	<tr class="<?php echo $bg; ?>">
		<td headers="list_chk" class="td_chk">
			<input type="hidden" name="mb_id_saler[<?php echo $i ?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_saler_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mb_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>" checked>
		</td>
		<td style="text-align:left;"><!-- 이름 -->
			<?php echo $row['mb_name']; ?><?=$g5['set_mb_ranks_value'][$row['mb_3']]?>
		</td>
		<td><!-- 아이디 -->
			<?php echo get_text($row['mb_id']); ?>
		</td>
		<td style="text-align:left;"><!-- 소속 -->
			<?php echo $g5['department_up_name'][$row['mb_2']]; ?>
		</td>
		<td style="text-align:center;"><!-- 직책 -->
			<?php echo $g5['set_mb_positions_value'][$row['mb_1']]; ?>
		</td>
		<td style="text-align:center;"><!-- 목표 -->
			<?php echo number_format($row['sales_goal_m4']); ?>
		</td>
		<td style="text-align:center;"><!-- 목표 -->
			<?php echo number_format($row['sales_goal_m3']); ?>
		</td>
		<td style="text-align:center;"><!-- 목표 -->
			<?php echo number_format($row['sales_goal_m2']); ?>
		</td>
		<td item="td_total_m1" style="text-align:center;"><!-- 목표 -->
			<?php echo number_format($row['sales_goal_m1']); ?>
		</td>
		<td style="text-align:center;"><!-- 목표 -->
			<input type="text" name="slg_sales_goal[<?php echo $i; ?>]" value="<?=number_format($row['slg_sales_goal'])?>"
				id="sales_goal_<?php echo $i; ?>" class="frm_input" style="width:80px;padding-right:3px;font-size:1.0em;">
		</td>
	</tr>
	<?php
	}
	if ($i == 0)
		echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
	?>
	</tbody>
	</table>
</div>

<?php
// 팀장 이상인 경우만 선택수정 버튼이 나타남
if($is_admin=='super' || $member['mb_1'] >= 6) { ?>
<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <!--<input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">-->
</div>
<?php } ?>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_trm_idxs='.$ser_trm_idxs.'&ym='.$ym.'&amp;page='); ?>

<script>
var g5_decimals  = "0";
var g5_dec_point = ".";
var g5_thousands_sep = ",";

$(function() {
	// 부서 검색 추출, 해당 부서가 아닌 정보들은 숨김 (mb_level<8 이면서 팀장 이상인 경우)
	<?php if (auth_check($auth[$sub_menu],"d",1) && $member['mb_1'] >= 6) { ?>
	var dept_array = [<?=$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]]?>];
	$('select[name=ser_trm_idxs] option').each(function(e) {
		//alert( $(this).val() );
		if($(this).val() !='') {
			var this_option = $(this);
			var dept_option_array = $(this).val().split(',');
			dept_option_array.forEach( function (value) {
				//console.log( value + ' / ' + this_option.val() + ' / ' + this_option.text() );
				//console.log( dept_array.indexOf( parseInt(value) ) );
				//console.log( '---' );
				// 배열 안에 해당 값이 없으면 옵션값 숨김
				if( dept_array.indexOf( parseInt(value) ) == -1 ) {
					//console.log( this_option.val() );
					//console.log( '제거' );
					this_option.remove();
				}
			});
		}
	});
	<?php } ?>
	
	// 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name^=slg_sales_goal]',function(e) {
		//alert( $(this).val().replace(/,/g,'') );
		$(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});
	
	// 퍼센터 일괄 적용
	$(document).on('click','a[data-value]',function(e) {
		e.preventDefault();
		//console.log($(this).attr('data-value'));
		applyPercent($(this).attr('data-value'));
	});
	
	
});


//-- 해당 % 적용
function applyPercent(percent_no) {
	var this_data_value = parseFloat(percent_no);
	$('#form01 table tbody tr').each(function() {
		$(this).find('input[name^=slg_sales_goal]').val( strtonumber( parseInt($(this).find("td[item=td_total_m1]").text().replace(/,/g,'')) * this_data_value ) );
	});
}

// 금액을 숫자로 변경
function strtonumber(n) {
	n = Number(n).toFixed(g5_decimals);
	nArray = n.split('.');	

	var reg = /(^[+-]?\d+)(\d{3})/;
	nArray[0] +='';
	while(reg.test(nArray[0]))
	nArray[0] = nArray[0].replace(reg, '$1' + g5_thousands_sep + '$2');
	n = (nArray[1] == undefined) ? nArray[0] : nArray[0] + g5_dec_point + nArray[1]; 
	return n;
}


function form01_submit(f) {
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
    }
    
    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 항목을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
        	return false;
        }
        else {
			$('input[name="w"]').val('d');
        } 
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
