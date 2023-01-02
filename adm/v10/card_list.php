<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '카드관리';
include_once('./_top_menu_carduser.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
//$g5['set_card']
//$g5['set_card_status']

$sql_common = " FROM {$g5['card_table']}
";

$where = array();
$where[] = " crd_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ($sfl == 'crd_name') :
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
    $sst = "crd_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT *
		{$sql_common}
		{$sql_search} {$sql_com_type} {$sql_trm_idx_department}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['card_table']} WHERE crd_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}

$colspan = 7;

// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_com_type" class="cp_field" title="업종선택">
	<option value="">전체업종</option>
	<?=$g5['set_com_type_options_value']?>
</select>
<script>$('select[name=ser_com_type]').val('<?=$_GET['ser_com_type']?>').attr('selected','selected');</script>
<select name="sfl" id="sfl">
	<option value="crd_name"<?php echo get_selected($_GET['sfl'], "crd_name"); ?>>카드사명</option>
    <option value="com_status"<?php echo get_selected($_GET['sfl'], "com_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>카드사별 카드를 관리하는 페이지 입니다.</p>
</div>

<form name="form01" id="form01" action="./card_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ser_com_type" value="<?php echo $ser_com_type; ?>">
<input type="hidden" name="ser_trm_idx_salesarea" value="<?php echo $ser_trm_idx_salesarea; ?>">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col" rowspan="2">
			<label for="chkall" class="sound_only">카드 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
		<th scope="col" class="td_left">카드사명</th>
		<th scope="col">카드번호</th>
		<th scope="col">유효기간</th>
		<th scope="col">상태</th>
		<th scope="col" style="width:80px;">메모</th>
		<th scope="col">등록일</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
		// 삭제인 경우 그레이 표현
		if($row['crd_status'] == 'expire')
			$row['crd_status_expire_class']	= " tr_expire";

        $bg = 'bg'.($i%2);
    ?>

	<tr class="<?php echo $bg; ?> <?=$row['crd_status_expire_class']?>" tr_id="<?php echo $row['crd_idx'] ?>">
		<td class="td_chk">
			<input type="hidden" name="crd_idx[<?php echo $i ?>]" value="<?php echo $row['crd_idx'] ?>" id="crd_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['crd_code']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
		<td class="td_crd_name td_left" colspan="2"><!-- 업체명 -->
            <input type="hidden" name="crd_code[<?php echo $i ?>]" value="<?php echo $row['crd_code'] ?>" id="crd_idx_<?php echo $i ?>" class="crd_code">
            <select name="crd_name">
               <?=$g5['set_card_value_options']?> 
            </select>
		</td>
		<td class="td_crd_no"><!-- 카드번호 -->
            <input type="text" name="crd_no[<?php echo $i ?>]" value="<?=$row['crd_no']?>" class="frm_input">
		</td>
		<td class="td_crd_expire"><!-- 만기일 -->
            <input type="text" name="crd_expire[<?php echo $i ?>]" value="<?=$row['crd_expire']?>" class="frm_input">
		</td>
		<td class="td_com_type"><!-- 메모 -->
            <input type="text" name="crd_memo[<?php echo $i ?>]" value="<?=$row['crd_memo']?>" class="frm_input" style="width:100%;">
		</td>
        <td headers="list_crd_status" class="td_crd_status"><!-- 상태 -->
			<?php echo $g5['set_com_status_value'][$row['com_status']] ?>
            <select name="crd_status[<?php echo $i ?>]">
                <?=$g5['set_com_status_value_options']?>
            </select>
		</td>
		<td class="td_com_reg_dt td_center font_size_8"><!-- 등록일 -->
			<?php echo substr($row['com_reg_dt'],0,10) ?>
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

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

<script>
$(function(e) {
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

    // 담당자 클릭
    $(".btn_manager").click(function(e) {
        var href = "./company_member_list.php?com_idx="+$(this).attr('com_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=700,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_company_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_company_board = window.open(this_href,'win_company_board','left=100,top=100,width=770,height=650');
        win_company_board.focus();
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
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
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
