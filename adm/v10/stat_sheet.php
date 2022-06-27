<?php
$sub_menu = "960500";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '성과시트 상세현황';
include_once('./_top_menu_stat.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


//echo $member['mb_level'].'<br>';	// meta 확장, u.default.php
//echo $member['mb_position'].'<br>';	// meta 확장, u.default.php
//echo $member['mb_2'].'<br>';	// company_member 추출, u.project.php
//echo $member['mb_group_yn'].'<br>';	// meta 확장, u.default.php
//echo $g5['department_uptop_idx'][$member['mb_2']].'<br>'; // 최상위조직코드, u.project.php
//echo $g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]].'<br>';	// 최상위 그룹의 하위 조직코드들
//echo $g5['department_down_idxs'][$member['mb_2']].'<br>'; // 하부조직코드(들), u.project.php


//-- 기본 검색값 할당
$st_date = ($st_date) ? $st_date : date("Y-m",G5_SERVER_TIME-86400).'-01';
$en_date = ($en_date) ? $en_date : G5_TIME_YMD;

$sql_common = " 	FROM {$g5['sales_table']} AS sls ";

$sql_search = " WHERE sls_status IN ('ok') AND sra_type IN ('".implode("','",$g5['set_sales_sra_type_array'])."') ";
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

// 정산권한이 있는 사람은 전부다 보임
if ($member['mb_account_yn']) {
	// 설정값이 있는 경우만
    //print_r2($g5['set_stat_teams_array']);
	if( is_array($g5['set_stat_teams_array']) ) {
		$sql_mygroup = " AND trm_idx_department IN (".implode(",",$g5['set_stat_teams_array']).")";
	}
}
// 정산 권한 없으면 자기 조직 것만 리스트에 나옴
else {
	$sql_mygroup = " AND trm_idx_department IN (".$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]].")";
}


// (권한이 있어도) 법인접근 권한이 없으면 자기 법인만 조회 가능
if(!$member['mb_company_yn']) {
    $sql_search .= " AND sls_company = '".$member['mb_4']."' ";
}

// 기간 검색
if ($st_date)	// 시작일 있는 경우
	$sql_search .= " AND sls_sales_dt >= '{$st_date} 00:00:00' ";
if ($en_date)	// 종료일 있는 경우
	$sql_search .= " AND sls_sales_dt <= '{$en_date} 23:59:59' ";

//-- 조직 검색 설정
if ($_GET[ser_trm_idxs])
    $sql_search .= " AND trm_idx_department in (".$_GET[ser_trm_idxs].") ";
else
    $sql_search .= $sql_mygroup;


$rows = $config['cf_page_rows'];
$rows = 100;
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "
    SELECT SQL_CALC_FOUND_ROWS *
        , (CASE WHEN n='1' THEN CONCAT(mb_id_saler,'_',od_id,'_',ct_id) ELSE CONCAT(mb_id_saler,'_sub_total') END) AS item_name
        , SUM(sls_price_sum) AS sls_price_sum_total
    FROM
    (
            SELECT sls_idx, mb_id_saler, mb_name_saler, trm_idx_department, sls_department_name, sls_emp_enter_date, sls_price_cost
                , od_id, ct_id, it_id, sls_sales_dt
                , sls_emp_rank, sls.com_idx, sls_com_name, sls_status, sls_reg_dt
                , COUNT(ct_id) AS ct_nos
                , SUM(sls_price) AS sls_price_sum
                , GROUP_CONCAT(ct_id) AS ct_ids
                , GROUP_CONCAT(sls_ct_status ORDER BY sls_sales_dt) AS ct_statuses
            {$sql_common}
            {$sql_search}
            GROUP BY mb_id_saler, od_id, ct_id
            ORDER BY sls_emp_rank DESC, sls_emp_enter_date, od_id, sls_sales_dt
    ) AS db_table1, {$g5['tally_table']} AS db_no
    WHERE n <= 2
    GROUP BY item_name
    ORDER BY sls_emp_rank DESC, item_name, sls_emp_enter_date, sls_reg_dt
    LIMIT {$from_record}, {$rows} 
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count[total];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


// 넘겨줄 변수가 추가로 있어서 qstr 별도 설정
$qstr = $qstr."&amp;sfl_date=$sfl_date&amp;st_date=$st_date&amp;en_date=$en_date&amp;sst=$sst&amp;sod=$sod&amp;ser_trm_idxs=$ser_trm_idxs";


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>
.tbl_head01 tbody td {
    height: 25px;
    padding: 5px 2px;
}
.opa_class_pending {color:#adadad;}
.opa_class_cancel {color:red;}
</style>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
기간: 
<input type="text" name="st_date" value="<?php echo $st_date ?>" id="st_date" class="frm_input" style="width:88px;"> ~
<input type="text" name="en_date" value="<?php echo $en_date ?>" id="en_date" class="frm_input" style="width:88px;">
&nbsp;&nbsp;
<?php if ($is_admin=='super' || !auth_check($auth[$sub_menu],"d",1) || $member['mb_1'] >= 6) { ?>
<select name="ser_trm_idxs" title="부서선택">
	<option value="">전체부서</option>
	<?=$department_select_options?>
</select>
<script>$('select[name=ser_trm_idxs]').val('<?=$_GET[ser_trm_idxs]?>').attr('selected','selected');</script>
&nbsp;&nbsp;
<?php } ?>
<select name="sfl" id="sfl">
	<option value="mb_name_saler"<?php echo get_selected($_GET['sfl'], "mb_name_saler"); ?>>담당자명</option>
	<option value="sls_com_name"<?php echo get_selected($_GET['sfl'], "sls_com_name"); ?>>업체명</option>
	<option value="sls_it_name"<?php echo get_selected($_GET['sfl'], "sls_it_name"); ?>>상품명</option>
	<option value="sls_sales_dt"<?php echo get_selected($_GET['sfl'], "sls_sales_dt"); ?>>매출일자</option>
	<option value="mb_id_saler"<?php echo get_selected($_GET['sfl'], "mb_id_saler"); ?>>담당자아이디</option>
	<option value="sls_ct_status"<?php echo get_selected($_GET['sfl'], "sls_ct_status"); ?>>상품상태</option>
	<option value="ct_id"<?php echo get_selected($_GET['sfl'], "ct_id"); ?>>장바구니코드(cd_id)</option>
	<option value="it_id"<?php echo get_selected($_GET['sfl'], "it_id"); ?>>상품코드(it_id)</option>
	<option value="od_id"<?php echo get_selected($_GET['sfl'], "od_id"); ?>>접수번호(od_id)</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
	<p>
		매출 통계를 위한 기본 원장 데이타(들)입니다. 필요한 경우에 한해서 주의해서 수정해 주세요.
	</p>
</div>

<div class="tbl_head01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr>
		<th scope="col">담당자</th>
		<th scope="col">업체명</th>
		<th scope="col">결제방법</th>
		<th scope="col">상품</th>
		<th scope="col">상태</th>
		<th scope="col">단가</th>
		<th scope="col" style="width:50px;">수량</th>
		<th scope="col" style="width:120px;">매출일시</th>
		<th scope="col" style="width:80px;">매출금액</th>
	</tr>
	</thead>
	<tbody>
    <?php
	$bgi = 0;	// 담당자가 바뀔 때마다 배경컬러 변경
    for ($i=0; $row=sql_fetch_array($result); $i++) {
		//print_r2($row);
		
		// 주문에서 정보 추출
        $row['od'] = get_table_meta('g5_shop_order','od_id',$row['od_id'],'shop_order');

        // 이름이 다르면 배경색상 변경
        if($old_mb_id_saler != $row['mb_id_saler']) {
			$bgi++;
		}
		// 이름이 같은 사람이면 표현 안함
		else {
			$row['mb_name_saler'] = '';
			$row['border_top'] = ';border-top:none;';	// 상단 탑 라인 제거
			$row['dept_display'] = ';display:none;';	// 조직 안 보임
		}
		
		$bg = 'bg'.($bgi%2);
		// 맨 위 한 줄 일단 리스트 한 후.. 그 다음 상품 부분은 상품 갯수만큼 반복 tr
		
		// 중간합계일 때는 td 다 합쳐서 한줄로 합계금액만 표시하고 다음으로 넘김
		if(preg_match("/_sub_total/",$row['item_name'])) {
			echo '
			<tr class="'.$bg.'">
				<td style="text-align:center;'.$row['border_top'].'"></td><!--담당자-->
				<td style="text-align:center;background:#ffe9e9;">합계</td><!--합계-->
				<td style="text-align:right;padding-right:7px;font-size:1em;background:#ffe9e9;" colspan="8"><!-- 중간합계-->
					'.number_format($row['sls_price_sum_total']).'
				</td>
			</tr>
			';
			continue;
		}
    ?>
	<tr class="<?php echo $bg; ?>">
		<td style="text-align:center;<?=$row['border_top']?>" rowspan="<?=$row['ct_nos']?>"><!--담당자-->
			<?=$row['mb_name_saler']?>
			<div style="color:#adadad<?=$row['dept_display']?>"><!-- 조직 -->
			<?=$row['sls_department_name']?>
			</div>
		</td>
		<td style="text-align:center;" rowspan="<?=$row['ct_nos']?>"><!-- 업체명 -->
			<a href="./order_list.php?sel_field=od_id&search=<?php echo $row['od']['od_id']; ?>" target="_blank"><?=$row['sls_com_name']?></a>
		</td>
		<td style="text-align:center;" rowspan="<?=$row['ct_nos']?>"><!-- 결제방법 -->
			<?php echo $row['od']['od_settle_case']; ?>
		</td>
		<td style="text-align:center;display:none;" rowspan="<?=$row['ct_nos']?>"><!-- 상품 -->
			<?=preg_replace("/,/","<br>------<br>",$row['ct_info'])?>
		</td>
		
	<?php
		// 상품 부분 반복 tr
		// 다시 분리해서 보여줘야 상태 변화에 따른 매출 내용까지 제대로 보여줄 수 있다.
        $sql = " SELECT mb_id_saler, sra_type, od_id, ct_id, sls_ct_status, it_id, sls_ct_id_values
                    FROM {$g5['sales_table']}
                    {$sql_search}
                        AND mb_id_saler = '{$row['mb_id_saler']}'
                        AND od_id = '{$row['od_id']}'
                        AND ct_id = '{$row['ct_id']}'
                    ORDER BY sls_sales_dt
        ";
        //echo $sql.'<br>';
        $rs = sql_query($sql,1);
        for ($j=0; $row2=sql_fetch_array($rs); $j++) {
			//print_r2($row2);
            // 마이너스 매출인 경우
            if(!in_array($row2['sls_ct_status'], $g5['set_sales_status_array'])) {
                $row2['it_color'] = ';color:red;';	// 취소 색상
                $row2['it_price_unit'] = '-';	// 마이너스
            }

			// 두번째 상품부터 tr 넣어줘야 함 (다음줄), 첫번째 상품은 tr 필요없음
			if($j) {
				echo '</tr><tr class="'.$bg.'">';
				$row2['border_left'] = ';border-left:solid 1px #ddd;';	// td border-left 색상
			}
            
            // 장바구니 정보 분리 unserialize
            //$row2['sls_ct_info'] = unserialize($row2['sls_ct_id_values']);
            $row2['sls_ct_info'] = get_serialized($row2['sls_ct_id_values']);
            if( is_array($row2['sls_ct_info']) ) {
                $row2 = array_merge($row2,$row2['sls_ct_info']);
            }
            //print_r2($row2['sls_ct_info']);
            
            // 추가옵션상품(io_type=1), 선택(필수)옵션(io_type=0) // 추가옵션 상품은 단가=io_price / 선택(필수)옵션 상품은 단가=상품가격+옵션가격
            $row2['opt_price'] = ($row2['io_type']) ?  $row2['io_price'] : $row2['ct_price'] + $row2['io_price'];
            // 소계
            $row2['stotal'] = $row2['opt_price'] * $row2['ct_qty'];	// 상품 sub_total
                
            
			?>
		<td style="text-align:center;<?=$row2['it_color']?><?=$row2['border_left']?>"><!-- 상품 -->
            <?php //echo $row['ct_id']; ?>
			<?=$row2['it_name']?>
		</td>
		<td style="text-align:center;<?=$row2['it_color']?>"><!-- 상태 -->
			<?=$row2['sls_ct_status']?>
		</td>
		<td style="text-align:center;<?=$row2['it_color']?>"><!-- 단가 -->
			<?=$row2['it_price_unit']?><?=number_format($row2['ct_price'])?>
		</td>
		<td style="text-align:center;<?=$row2['it_color']?>"><!-- 수량 -->
			<?=number_format($row2['ct_qty'])?>
		</td>
		<td style="text-align:center;<?=$row2['it_color']?>"><!-- 매출일시 -->
			<?php echo substr($row['sls_sales_dt'],0,16); ?>
		</td>
		<td style="text-align:right;padding-right:7px;font-size:1em;<?=$row2['it_color']?>"><!-- 매출금액 -->
            <?php
			// 정산권한이 있거나 수퍼인 경우는 수정페이지로 링크
			if($is_admin=='super'||!auth_check($auth[$sub_menu],"d",1))
				echo '<a href="./sales_form.php?w=u&sls_idx='.$row['sls_idx'].'" target="_blank">'.$row2['it_price_unit'].number_format($row['sls_price_sum']).'</a>';
			else
				echo $row2['it_price_unit'].number_format($row['sls_price_sum']);
			?>
		</td>
			<?php
		}
    ?>
	</tr>

    <?php
		$old_mb_id_saler = $row['mb_id_saler'];
    }
    if ($i == 0)
        echo "<tr><td colspan='15' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
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
	$(document).on( 'keyup','input[name^=sls_share], input[name^=sls_price_cost]',function(e) {
		//alert( $(this).val().replace(/,/g,'') );
		$(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

	$("#st_date,#en_date,input[name^=sls_sales_dt]").datepicker({
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

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
    }
    
    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
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
