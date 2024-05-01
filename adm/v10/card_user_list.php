<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '카드사용자관리';
include_once('./_top_menu_carduser.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
//$g5['set_card']
//$g5['set_card_status']

$sql_common = " FROM {$g5['card_user_table']} csr
                    LEFT JOIN {$g5['card_table']} crd ON csr.crd_idx = crd.crd_idx
                    LEFT JOIN {$g5['member_table']} mb ON csr.mb_id = mb.mb_id
";

$where = array();
$where[] = " csr_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ($sfl == 'mb_id') :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

if($sch_crd_idx){
    $where[] = " crd.crd_idx = '{$sch_crd_idx}' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "crd_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";


$sql = " select count(csr_idx) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$total_count = $row['cnt'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$sql = " SELECT *
		{$sql_common}
		{$sql_search} {$sql_com_type} {$sql_trm_idx_department}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
//echo $sql;
$result = sql_query($sql,1);


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

$colspan = 11;

// 검색어 확장
$qstr .= $qstr.'&sch_crd_idx='.$sch_crd_idx;
?>
<style>
#fcard_box{padding-bottom:10px;}
.td_chk{}
.td_csr_idx{}
.td_mb_name{width:100px;}
.td_mb_id{width:100px;}
.td_crd_info{width:220px;}
.td_crd_expire{width:100px;}
.td_csr_start_date{width:100px;}
.td_csr_end_date{width:100px;}
.td_csr_memo{}
.td_csr_status{width:80px;}
.td_csr_reg_dt{width:170px;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>
<?php 
$xp_y = ($g5['setting']['set_cardexpire_startyear'])?$g5['setting']['set_cardexpire_startyear']:2020;
$xp_c = ($g5['setting']['set_cardexpire_yearcnt'])?$g5['setting']['set_cardexpire_yearcnt']:20;

//등록된 카드추출 쿼리
$crd_sql = " SELECT * FROM {$g5['card_table']} WHERE
                crd_status = 'ok'
";
$crd_res = sql_query($crd_sql,1);
$crd_arr = array();
for($i=0;$crow=sql_fetch_array($crd_res);$i++){
    $crow['crd_no'] = preg_replace('/^(\d{4})(\d{4})(\d{4})(\d{4})/','$1-$2-$3-$4',$crow['crd_no']);
    $crow['crd_no'] = substr($crow['crd_no'],0,19);
    $crd_arr[$crow['crd_idx']] = $g5['set_card_value'][$crow['crd_code']].'('.$crow['crd_no'].')';
}
//$g5['set_card_value_options']
?>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<select name="sch_crd_idx" id="sch_crd_idx">
    <option value="">::카드선택::</option>
    <?php foreach($crd_arr as $crd_k => $crd_v){ ?>
    <option value="<?=$crd_k?>"><?=$crd_v?></option>
    <?php } ?>
</select>
<script>
$('#sch_crd_idx').val('<?=$sch_crd_idx?>');
</script>

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>사용자명</option>
	<option value="mb_id"<?php echo get_selected($_GET['sfl'], "mb_id"); ?>>사용자ID</option>
    <option value="csr_memo"<?php echo get_selected($_GET['sfl'], "csr_memo"); ?>>메모</option>
</select>
<script>
$('#sfl').val('<?=(($sfl)?$sfl:'mb_name')?>');
</script>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>사용자별 카드를 관리하는 페이지 입니다.</p>
</div>

<div id="fcard_box">
<form name="form_carduserregist" id="form_carduserregist" action="./card_user_regist_update.php" onsubmit="return form_carduserregist_submit(this);" method="post">
<input type="hidden" name="sch_crd_idx" id="sch_crd_idx" value="<?=$sch_crd_idx?>">
<label for="csr_start_date" class="fc_label">
    <input type="hidden" name="mb_id" id="mb_id" value="">
    <input type="text" name="mb_name" value="" id="mb_name" link="./_win_member_select.php" placeholder="사용자" readonly class="frm_input readonly" style="width:100px;">
</label>
<script>
$('#mb_name').on('click',function(){
    var href = $(this).attr('link');
    var win_member_name = window.open(href,"win_member_select","width=400,height=640");
    win_member_name.focus();
    return false;
});
</script>
<select name="crd_idx">
    <option value="">::카드선택::</option>
    <?php foreach($crd_arr as $crd_k => $crd_v){ ?>
    <option value="<?=$crd_k?>"><?=$crd_v?></option>
    <?php } ?>
</select>

<label for="csr_start_date" class="fc_label">
    <input type="text" name="csr_start_date" value="<?php echo $sch_csr_start_date ?>" id="csr_start_date" placeholder="지급일" readonly class="frm_input readonly" style="width:100px;">
</label>
<label for="csr_memo" class="fc_label">
    <input type="text" name="csr_memo" id="csr_memo" placeholder="메모" class="frm_input" value="" style="width:200px;">
</label>
</select>
<input type="submit" class="btn btn_01 btn_register" value="등록">
</form>
</div><!--//#fcard_box-->

<form name="form01" id="form01" action="./card_user_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="sch_crd_idx" id="sch_crd_idx" value="<?=$sch_crd_idx?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col" rowspan="2">
			<label for="chkall" class="sound_only">카드 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
		<th scope="col">번호</th>
		<th scope="col">사용자</th>
		<th scope="col">ID</th>
		<th scope="col">카드정보</th>
		<th scope="col">만기(월/년)</th>
		<th scope="col">지급일</th>
		<th scope="col">반납일</th>
		<th scope="col">메모</th>
		<th scope="col">상태</th>
		<th scope="col">등록일</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
		// 삭제인 경우 그레이 표현
		if($row['csr_status'] == 'expire')
			$row['csr_status_expire_class']	= " tr_expire";

        $row['crd_no'] = preg_replace('/^(\d{4})(\d{4})(\d{4})(\d{4})/','$1-$2-$3-$4',$row['crd_no']);

        $row['crd_no'] = substr($row['crd_no'],0,19);
        
        $bg = 'bg'.($i%2);
    ?>

	<tr class="<?php echo $bg; ?> <?=$row['crd_status_expire_class']?>" tr_id="<?php echo $row['crd_idx'] ?>">
		<td class="td_chk">
			<input type="hidden" name="csr_idx[<?php echo $i ?>]" value="<?php echo $row['csr_idx'] ?>" id="csr_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['csr_idx']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <td class="td_csr_idx"><!-- 번호 -->
            <?=$row['csr_idx']?>
        </td>
        <td class="td_mb_name"><!-- 사용자 -->
            <?=$row['mb_name']?>
        </td>
        <td class="td_mb_id"><!-- 사용자ID -->
            <?=$row['mb_id']?>
        </td>
		<td class="td_crd_info td_left"><!-- 카드정보 -->
            <?=$g5['set_card_value'][$row['crd_code']].'('.$row['crd_no'].')'?>
		</td>
		<td class="td_crd_expire"><!-- 만기 -->
            <?=$row['crd_expire_month']?> / <?=$row['crd_expire_year']?>
		</td>
		<td class="td_csr_start_date"><!-- 지급일 -->
            <input type="text" name="csr_start_date[<?php echo $i ?>]" value="<?=$row['csr_start_date']?>" readonly class="frm_input readonly" style="width:100%;">
		</td>
		<td class="td_csr_end_date"><!-- 반납일 -->
            <input type="text" name="csr_end_date[<?php echo $i ?>]" value="<?=$row['csr_end_date']?>" readonly class="frm_input readonly" style="width:100%;">
		</td>
		<td class="td_csr_memo"><!-- 메모 -->
            <input type="text" name="csr_memo[<?php echo $i ?>]" value="<?=$row['csr_memo']?>" class="frm_input" style="width:100%;">
		</td>
        <td headers="list_csr_status" class="td_csr_status"><!-- 상태 -->
            <select name="csr_status[<?php echo $i ?>]" id="csr_status_<?php echo $i ?>">
                <?=$g5['set_carduser_status_value_options']?>
            </select>
            <script>
            $('#csr_status_<?php echo $i ?>').val('<?=$row['csr_status']?>');
            </script>
		</td>
		<td class="td_csr_reg_dt td_center font_size_14"><!-- 등록일 -->
			<?=$row['csr_reg_dt']?>
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
    <?php //if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <?php if($super_admin) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

<script>
$(function(e) {
    $("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소', onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}}});
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

    //숫자만 입력
    // $('.card_no').on('keyup',function(){
    //     var num = $(this).val().replace(/[^0-9]/g,"").replace(/^(\d{0,4})(\d{0,4})(\d{0,4})$/g,"$1-$2-$3").replace(/(\-{1,2})/g,"");
    //     $(this).val(num);
    // });
});

function card_no_str(inp){
    if(inp.value.length <= 19){
        inp.value = inp.value.replace(/[^0-9]/g, '').replace(/^(\d{4})(\d{4})(\d{4})(\d{4})$/g, "$1-$2-$3-$4").replace(/(\-{1,2})$/g, "");
    }else{
        inp.value = inp.value.substr(0,19);
    }
}


function form_carduserregist_submit(f){
    if(!f.mb_name.value){
        alert('사용자를 선택해 주세요.');
        f.mb_name.focus();
        return false;
    }
    if(!f.crd_idx.value){
        alert('카드를 선택해 주세요.');
        f.crd_idx.focus();
        return false;
    }
    if(!f.csr_start_date.value){
        alert('지급일을 선택해 주세요.');
        f.csr_start_date.focus();
        return false;
    }

    return true;
}

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
        // console.log($('input[name="chk[]"]').length);
        $('input[name="chk[]"]').each(function(){
            if($(this).is(':checked')){
                var tr = $(this).parent().parent();
                var csr_start_date = tr.find('.td_csr_start_date').find('input').val();
                var csr_end_date = tr.find('.td_csr_end_date').find('input').val();
                var csr_status = tr.find('.td_csr_status').find('select option:selected').val();
                
                if(!csr_start_date){
                    alert('카드 지급일을 선택해 주세요');
                    return false;
                }
                if(!csr_end_date) {
                    alert('카드 반납일을 선택해 주세요');
                    return false;
                }
                if(!csr_status) {
                    alert('상태를 선택해 주세요');
                    return false;
                }
            }
        });

		$('input[name="w"]').val('u');
        // return false;
	}

	else if(document.pressed == "선택삭제") {
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
