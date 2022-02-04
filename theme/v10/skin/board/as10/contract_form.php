<?php
// 
include_once('./_common.php');

if($member['mb_level']<6)
    alert('접근이 불가능한 메뉴입니다.');

$wr = get_table_meta($bo_table,'wr_id',$wr_id);
if(!$wr['wr_id'])
    alert('관련 영업정보가 존재하지 않습니다.');
else {
    // 고객 정보
    if($wr['wr_3']) {
        // 고객
        $mb1 = get_saler($wr['wr_3']);
        //print_r3($mb1);
        $wr['mb_name_customer'] = $mb1['mb_name'].$g5['set_mb_ranks_value'][$mb1['mb_3']];
    }
    // 업체정보
    if($wr['wr_2']) {
        $com1 = get_table_meta('company','com_idx',$wr['wr_2']);
        //print_r3($com1);
    }
}

if(!$ctr_idx) {
    $ctr['ctr_sales_date'] = G5_TIME_YMD;
    $ctr['ctr_status'] = 'ok';
}
else {
    $ctr = get_table_meta('contract','ctr_idx',$ctr_idx);
//    print_r2($ctr);
}


$g5['title'] = '수주 정보 입력';
include_once(G5_PATH.'/head.sub.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>
<style>
</style>
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div id="menu_frm" class="new_win">
<h1 id="win_title"><?php echo $g5['title']; ?></h1>
<div class="new_win_con">

    <form name="form01" id="form01" action="./contract_form_update.php" onsubmit="return form01_check(this);" method="post">
	<input type="hidden" name="w" value="<?php echo $w ?>">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
	<input type="hidden" name="ctr_idx" value="<?php echo $ctr_idx ?>">
	<input type="hidden" name="token" value="">
    <div class="win_content">
        <div class="tbl_frm01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_1" style="width:28%;">
                <col class="grid_3">
            </colgroup>
            <tbody>
			<tr>
				<th scope="row">제목</th>
				<td>
                    <?=$wr['wr_subject']?>
				</td>
			</tr>
			<tr>
				<th scope="row">업체정보</th>
				<td>
                    <div><b><?php echo $com1['com_name']?></b></div>
                    <div><b>대표자</b> <?php echo $com1['com_president']?></div>
                    <div><b>대표전화</b> <?php echo $com1['com_tel']?></div>
                    <div><b>이메일</b> <?php echo $com1['com_email']?></div>
				</td>
			</tr>
			<tr>
				<th scope="row">고객명</th>
				<td>
                    <?=$wr['mb_name_customer']?>
				</td>
			</tr>
			<tr>
				<th scope="row">수주상품</th>
				<td>
					<input type="text" name="ctr_item" value="<?php echo $ctr['ctr_item'] ?>" id="ctr_item" required class="required frm_input" style="width:100%;">
				</td>
			</tr>
			<tr>
				<th scope="row">수주금액</th>
				<td>
					<input type="text" name="ctr_price" value="<?php echo number_format($ctr['ctr_price']) ?>" id="ctr_price" required class="required frm_input" size="15">
				</td>
			</tr>
			<tr>
				<th scope="row">기여도(%)</th>
				<td>
					<input type="text" name="ctr_percent" value="<?php echo $ctr['ctr_percent'] ?>" id="ctr_percent" required class="required frm_input" style="width:40px;"> %
				</td>
			</tr>
			<tr>
				<th scope="row">수주일자</th>
				<td>
					<input type="text" name="ctr_sales_date" value="<?php echo $ctr['ctr_sales_date'] ?>" id="ctr_sales_date" required class="required frm_input" style="width:90px;">
					<input type="checkbox" value="<?php echo date("Y-m-d"); ?>" id="ctr_sales_date_set_today" onclick="if (this.form.ctr_sales_date.value==this.form.ctr_sales_date.defaultValue) {
		this.form.ctr_sales_date.value=this.value; } else { this.form.ctr_sales_date.value=this.form.ctr_sales_date.defaultValue; }">
					<label for="ctr_sales_date_set_today">오늘로 지정</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="ctr_memo">메모</label></th>
				<td colspan="3"><textarea name="ctr_memo" id="ctr_memo"><?php echo $ctr['ctr_memo'] ?></textarea></td>
			</tr>
			<tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>">
				<th scope="row">상태</th>
				<td>
					<select name="ctr_status">
						<option value="">상태값선택</option>
						<?=$g5['set_status_options_value']?>
					</select>
					<script>$('select[name=ctr_status]').val('<?=$ctr['ctr_status']?>').attr('selected','selected');</script>
				</td>
			</tr>
            </tbody>
            </table>
        </div>
    </div>
    <div class="win_btn ">
        <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
        <input type="button" class="btn_close btn" value="창닫기" onclick="window.close();">
        <input type="button" class="btn_delete btn" value="삭제" style="display:<?=(!$ctr_idx||$member['mb_level']<8)?'none':'';?>;">
    </div>

    </form>
        
        
</div>
</div>
<script>
$(function() {
    $("#ctr_sales_date, #ctr_end_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		alert( $(this).val().replace(/,/g,'') );
        if( $(this).val() != 0 )
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

    $("#btn_item").click(function() {
        var href = $(this).attr("href");
        itemwin = window.open(href, "itemwin", "left=50,top=50,width=520,height=600,scrollbars=1");
        itemwin.focus();
        return false;
    });
    $("#btn_member").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        memberwin.focus();
        return false;
    });
	$(".btn_delete").click(function() {
		if(confirm('수주 정보를 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./contract_form_update.php?token="+token+"&w=d&ctr_idx=<?=$ctr_idx?>&bo_table=<?=$bo_table?>&wr_id=<?=$wr_id?>";
		}
	});
});

function form01_check(f)
{
	// 팀개별분배는 아이디 제거해야 함
	if (f.ctr_type.value=='team'&&f.mb_id_saler.value!='') {
		alert("팀개별분배인 경우 직원아이디값이 공백이어야 합니다.");
		f.mb_id_saler.select();
		return false;
	}
	// 개인분배는 아이디값이 반드시 있어야 함
	if (f.ctr_type.value=='member'&&f.mb_id_saler.value=='') {
		alert("개인분배인 경우 직원아이디값이 존재해야 합니다.");
		f.mb_id_saler.select();
		return false;
	}
	if (isNaN(f.ctr_price.value)==true) {
		alert("금액은 숫자만 가능합니다.");
		f.ctr_price.focus();
		return false;
	}

    return true;
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
