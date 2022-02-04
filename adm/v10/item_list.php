<?php
$sub_menu = '960220';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '부품관리';
include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


// 분류
$ca_list  = '<option value="">선택</option>'.PHP_EOL;
$sql = " select * from {$g5['g5_shop_category_table']} ";
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
$sql .= " order by ca_order, ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;
    $nbsp = '';
    for ($i=0; $i<$len; $i++) {
        $nbsp .= '&nbsp;&nbsp;&nbsp;';
    }
    $ca_list .= '<option value="'.$row['ca_id'].'">'.$nbsp.$row['ca_name'].'</option>'.PHP_EOL;
}

$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

if ($sfl == "")  $sfl = "it_name";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where (a.ca_id = b.ca_id";
if ($is_admin != 'super')
    $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";


$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;save_stx='.$stx;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>
.start_end_date {margin-left:10px;color:#b7b7b7;font-size:0.7rem;font-family:tahoma;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">등록된 상품</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sca" class="sound_only">분류선택</label>
<select name="sca" id="sca">
    <option value="">전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} order by ca_order, ca_id ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=sql_fetch_array($result1); $i++) {
        $len = strlen($row1['ca_id']) / 2 - 1;
        $nbsp = '';
        for ($i=0; $i<$len; $i++) $nbsp .= '&nbsp;&nbsp;&nbsp;';
        echo '<option value="'.$row1['ca_id'].'" '.get_selected($sca, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
    }
    ?>
</select>

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
    <option value="it_basic" <?php echo get_selected($sfl, 'it_basic'); ?>>기본설명</option>
    <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>상품코드</option>
    <option value="it_price" <?php echo get_selected($sfl, 'it_price'); ?>>가격</option>
    <option value="it_maker" <?php echo get_selected($sfl, 'it_maker'); ?>>제조사</option>
    <option value="it_origin" <?php echo get_selected($sfl, 'it_origin'); ?>>원산지</option>
    <option value="it_sell_email" <?php echo get_selected($sfl, 'it_sell_email'); ?>>판매자 e-mail</option>
</select>

<label for="stx" class="sound_only">검색어</label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
<input type="submit" value="검색" class="btn_submit">
</form>

<form name="form01" method="post" action="./item_list_update.php" onsubmit="return form01_submit(this);" autocomplete="off" id="form01">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="3">
            <label for="chkall" class="sound_only">상품 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" colspan="2" id="th_pc_title"><?php echo subject_sort_link('it_name', 'sca='.$sca); ?>상품명</a></th>
        <th scope="col" colspan="3">분류</th>
        <th scope="col"><?php echo subject_sort_link('it_use', 'sca='.$sca, 1); ?>판매</a></th>
        <th scope="col" id="th_amt"><?php echo subject_sort_link('it_price', 'sca='.$sca); ?>판매가격</a></th>
        <th scope="col" rowspan="3">분배</th>
        <th scope="col" rowspan="3">관리</th>
    </tr>
    <tr>
        <th scope="col" style="width:140px;"><?php echo subject_sort_link('it_id', 'sca='.$sca); ?>상품코드</a></th>
        <th scope="col" style="width:60px;"><?php echo subject_sort_link('it_order', 'sca='.$sca); ?>출력순서</a></th>
        <th scope="col">상품분리</th>
        <th scope="col">제작여부</th>
        <th scope="col">매출0</th>
        <th scope="col" style="width:50px;"><?php echo subject_sort_link('it_soldout', 'sca='.$sca, 1); ?>품절</a></th>
        <th scope="col" id="th_pt"><?php echo subject_sort_link('it_point', 'sca='.$sca); ?>원가</a></th>
    </tr>
    <tr>
        <th scope="col" colspan="7">기본설명</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $row3 = get_meta('shop_item',$row['it_id']);
        if(is_array($row3))
            $row =  array_merge($row, $row3);
		//print_r2($row);
		// 분배 내용 
		$sql2 = " SELECT *
					FROM {$g5['share_rate_table']} AS sra
						LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = sra.mb_id_saler
						LEFT JOIN {$g5['g5_shop_item_table']} AS it ON it.it_id = sra.it_id
					WHERE sra.it_id = '".$row['it_id']."' AND sra_status NOT IN ('trash')
		";
        //echo $sql2.'<br>';
		$rs2 = sql_query($sql2,1);
		for($j=0;$row2=sql_fetch_array($rs2);$j++) {
			//print_r2($row2);
			// 직원/조직
			$row2['mb_item'] = (preg_match("/team/",$row2['sra_type'])) ? $g5['department_name'][$row2['trm_idx_department']] : $row2['mb_name'];
			$row2['mb_item'] = ($row2['sra_type']=='order_join_member') ? '추천인전체' : $row2['mb_item'] ;
			
			// 시작일~종료일
			$row2['sra_start_date'] = $row2['sra_start_date'].' ~ ';
			$row2['sra_end_date'] = ($row2['sra_end_date']!='9999-12-31') ? $row2['sra_end_date'] : '';

			$row2['sra_price_text'] = number_format($row2['sra_price']);
			if($row2['sra_price_type']=='rate')
				$row2['sra_price_text'] .= '%';
			
			// 분배 표현
			//$row['share_list'] = $row2;
			$row['share'] .= $row2['mb_item'].'('.$g5['set_sra_type_value'][$row2['sra_type']].') '.$row2['sra_price_text'];
			$row['share'] .= '<span class="start_end_date">'.$row2['sra_start_date'].$row2['sra_end_date'].'</span>';
			$row['share'] .= ' <a href="./item_share_setting.php?w=u&sra_idx='.$row2['sra_idx'].'" class="btn_setting"><i class="fa fa-edit fa1"></i></a>';
			$row['share'] .= '<br>';
		}

        $it_point = $row['it_point'];
        if($row['it_point_type'])
            $it_point .= '%';
		
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['it_id']; ?>">
        <td rowspan="3" class="td_chk" style="width:30px;">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
        </td>
        <td colspan="2" headers="th_pc_title" class="td_input">
            <label for="name_<?php echo $i; ?>" class="sound_only">상품명</label>
            <input type="text" name="it_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>" id="name_<?php echo $i; ?>" required class="tbl_input required" size="30">
        </td>
        <td class="td_sort" colspan="3"><!-- 분류 -->
            <label for="ca_id_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?>분류</label>
            <select name="ca_id[<?php echo $i; ?>]" id="ca_id_<?php echo $i; ?>" style="width:170px;">
                <?php echo conv_selected_option($ca_list, $row['ca_id']); ?>
            </select>
        </td>
        <td>
            <label for="use_<?php echo $i; ?>" class="sound_only">판매여부</label>
            <input type="checkbox" name="it_use[<?php echo $i; ?>]" <?php echo ($row['it_use'] ? 'checked' : ''); ?> value="1" id="use_<?php echo $i; ?>">
        </td>
        <td headers="th_amt" class="td_numbig td_input">
            <label for="price_<?php echo $i; ?>" class="sound_only">판매가격</label>
            <input type="text" name="it_price[<?php echo $i; ?>]" value="<?php echo $row['it_price']; ?>" id="price_<?php echo $i; ?>" class="tbl_input sit_amt" size="7">
        </td>
        <td rowspan="3" style="text-align:left;vertical-align:top;"><!-- 분배 -->
			<?=$row['share']?><?=print_r2($row['share_list'])?>
        </td>
        <td rowspan="3" class="td_mng td_mng_s">
            <a href="./item_form.php?w=u&it_id=<?php echo $row['it_id']; ?>&ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_02">수정</a>
            <a href="./item_share_setting.php?it_id=<?php echo $row['it_id']; ?>" class="btn btn_03 btn_setting"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>설정</a>
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['it_id']; ?>">
        <td class="td_num"><!-- 상품코드 -->
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <?php echo $row['it_id']; ?>
        </td>
        <td class="td_num"><!-- 출력순서 -->
            <input type="text" name="it_order[<?php echo $i; ?>]" value="<?php echo $row['it_order']; ?>" id="it_order_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:50px;">
        </td>
        <td class="td_num"><!-- 상품분리 -->
            <label for="it_cart_separate_yn_<?php echo $i; ?>" class="sound_only">상품분리</label>
            <input type="checkbox" name="it_cart_separate_yn[<?php echo $i; ?>]" <?php echo ($row['it_cart_separate_yn'] ? 'checked' : ''); ?> value="1" id="it_cart_separate_yn_<?php echo $i; ?>">
        </td>
        <td class="td_num"><!-- 제작여부 -->
            <label for="it_make_yn_<?php echo $i; ?>" class="sound_only">제작여부</label>
            <input type="checkbox" name="it_make_yn[<?php echo $i; ?>]" <?php echo ($row['it_make_yn'] ? 'checked' : ''); ?> value="1" id="it_make_yn_<?php echo $i; ?>">
        </td>
        <td class="td_num"><!-- 매출0 -->
            <label for="it_sales_zero_<?php echo $i; ?>" class="sound_only">제작여부</label>
            <input type="checkbox" name="it_sales_zero[<?php echo $i; ?>]" <?php echo ($row['it_sales_zero'] ? 'checked' : ''); ?> value="1" id="it_sales_zero_<?php echo $i; ?>">
        </td>
        <td>
            <label for="soldout_<?php echo $i; ?>" class="sound_only">품절</label>
            <input type="checkbox" name="it_soldout[<?php echo $i; ?>]" <?php echo ($row['it_soldout'] ? 'checked' : ''); ?> value="1" id="soldout_<?php echo $i; ?>">
        </td>
        <td class=""><!-- 원가 -->
            <input type="text" name="it_price_cost_rate[<?php echo $i; ?>]" value="<?php echo $row['it_price_cost_rate']; ?>" id="price_cost_rate_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:23px;">%
            <input type="text" name="it_price_cost[<?php echo $i; ?>]" value="<?php echo $row['it_price_cost']; ?>" id="price_cost_<?php echo $i; ?>" class="tbl_input sit_amt" style="width:50px;">
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['it_id']; ?>">
        <td class="td_num td_left" colspan="7"><!-- 기본설명 -->
            <?=($row['it_basic'])?$row['it_basic']:'&nbsp;'?>
        </td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if ($is_admin == 'super') { ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02" style="display:none;">
    <?php } ?>
</div>
<!-- <div class="btn_confirm01 btn_confirm">
    <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
</div> -->
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : 5, $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            //stuff to do on mouse enter
            //console.log($(this).attr('od_id')+' mouseenter');
            //$(this).find('td').css('background','red');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#e6e6e6 ');
            
        },
        mouseleave: function () {
            //stuff to do on mouse leave
            //console.log($(this).attr('od_id')+' mouseleave');
            //$(this).find('td').css('background','unset');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });
    
	$(".btn_setting").click(function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		window.open(url, "win_item_setting", "left=50,top=50,width=520,height=600,scrollbars=1");
	});
});

function form01_submit(f)
{
    if(document.pressed == "수당일괄설정") {
        self.location="./item_share_setting_all.php";
        return false;
    }

    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
