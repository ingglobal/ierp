<?php
$sub_menu = '960226';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '주문상세내용';
include_once('./_top_menu_reseller.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$tot_point = 0;
$tot_sell_price = 0;

// $s_cart_id 로 현재 장바구니 자료 쿼리
$sql = " SELECT a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty, a.ct_status, a.ct_send_cost, a.it_sc_type,
				b.ca_id, b.ca_id2, b.ca_id3, b.it_tel_inq, c.ca_name, d.com_name
				,( SELECT ca_name FROM {$g5['g5_shop_category_table']} WHERE ca_id = SUBSTRING(c.ca_id,1,2) ) as ca_p_name
		FROM {$g5['g5_shop_cart_table']} a 
			LEFT JOIN {$g5['g5_shop_item_table']} b ON ( a.it_id = b.it_id )
			LEFT JOIN {$g5['companyreseller_table']} d ON ( d.com_idx = a.com_idx )
			LEFT JOIN {$g5['g5_shop_category_table']} c ON ( c.ca_id = b.ca_id )
		WHERE a.od_id = '$od_id' ";
$sql .= " GROUP BY a.it_id ";
$sql .= " ORDER BY a.ct_id ";
//echo $sql.'<br>';
$result = sql_query($sql);

$it_send_cost = 0;
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>

<div class="local_desc01 local_desc">
	<p>우선 아래 이메일로 전송하실 고객업체의 업체명,업체담당자명,이메일을 입력하세요.</p>
	<p>"전송처선택"을 클릭하면, 기존 등록된 회원업체와 담당자를 선택할 수 있습니다. </p>
</div>

<!-- 장바구니 시작 { -->
<script src="<?php echo G5_USER_ADMIN_URL; ?>/js/shop.js"></script>	<!-- 원본위치: /js/shop.js -->
<style>
.top_inf{position:relative;padding:5px;}
.top_inf .mng_com_mb{position:absolute;bottom:0;right:0;}
.com_box{padding:10px 0;}
.com_box table th{background:#f1f1f1;}
.com_box table .td_radio{text-align:left;}
.com_box table .td_radio label{display:inline-block;font-size:1.2em;height:40px;line-height:40px;margin-left:20px;}
.com_box table .td_radio label:first-child{margin-left:0;}
.com_box table .td_radio label input{}
.com_box table .td_radio label span{margin-left:5px;}
.com_box table input[type="text"]{padding:0 10px;}
</style>
<div id="sod_bsk">
<form name="frmcartlist" id="sod_bsk_list" method="post" action="./order_result.php">
	<div class="tbl_head01 tbl_wrap tbl_cart">
		<div class="top_inf">
			<div class="top_c top_no">접수번호: <b><?=$od_id?></b></div>
			<a href="./companylistmng.win.php" class="mng_com_mb btn btn_02">전송처선택</a>
		</div>
		<div class="com_box">
			<input type="hidden" name="com_idx" id="com_idx" class="frm_input">
			<input type="hidden" name="mng_id" id="mng_id" class="frm_input">
			<table>
			<tbody>
			<tr>
				<th>전송양식</th>
				<td colspan="3" class="td_radio">
					<label><input type="radio" name="doc_form" value="quot" checked><span>견적서</span></label>
					<label><input type="radio" name="doc_form" value="order"><span>발주서</span></label>
					<label><input type="radio" name="doc_form" value="deal"><span>거래명세서</span></label>
				</td>
			</tr>
			<tr>
				<th>제목</th>
				<td colspan="3"><input type="text" name="orl_subject" placeholder="제목" id="od_subject" required class="frm_input required"></td>
			</tr>
			<tr>
				<th>업체명</th>
				<td><input type="text" name="com_name" placeholder="업체명" id="com_name" required class="frm_input required"></td>
				<th>담당자명</th>
				<td><input type="text" name="mng_name" placeholder="담당자명" id="mng_name" required class="frm_input required"></td>
			</tr>
			<tr>
				<th>담당자Email</th>
				<td><input type="text" name="mng_email" placeholder="담당자Email" id="mng_email" required class="frm_input required"></td>
				<th>사업자등록번호</th>
				<td><input type="text" name="com_biz_no" placeholder="사업자등록번호" id="com_biz_no" class="frm_input"></td>
			</tr>
			<tr>
				<th>업체전화번호</th>
				<td><input type="text" name="com_tel" placeholder="업체전화번호" id="com_tel" class="frm_input"></td>
				<th>업체팩스번호</th>
				<td><input type="text" name="com_fax" placeholder="업체팩스번호" id="com_fax" class="frm_input"></td>
			</tr>
			<tr>
				<th>업체주소</th>
				<td colspan="3"><input type="text" name="com_addr" placeholder="업체주소" id="com_addr" class="frm_input"></td>
			</tr>
			<tr>
				<th>추가메세지</th>
				<td colspan="3">
					<textarea name="od_memo" id="od_memo" rows="5" style="padding:10px;"></textarea>
				</td>
			</tr>
			</tbody>
			</table>
		</div>
		<table>
		<thead>
		<tr>
			<th scope="col">부품명</th>
			<th scope="col">총수량</th>
			<th scope="col" style="width:110px;">판매가</th>
			<th scope="col">소계</th>
		</tr>
		</thead>
		<tbody>
		<?php
		
		for ($i=0; $row=sql_fetch_array($result); $i++) {
			//print_r2($row);
			// 합계금액
			$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
							SUM(ct_point * ct_qty) as point,
							SUM(ct_qty) as qty
						from {$g5['g5_shop_cart_table']}
						where it_id = '{$row['it_id']}'
						and od_id = '$od_id' ";
			$sum = sql_fetch($sql);
	
			if ($i==0) { // 계속쇼핑
				$continue_ca_id = $row['ca_id'];
			}
	
			//$a1 = '<a href="'.G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'" target="_blank"><b>';
			//$a2 = '</b></a>';
			$a1 = '<span>';
			$a2 = '</span>';
			$image = get_it_image($row['it_id'], 70, 70);
	
			$ca_str = ($row['ca_p_name'] == $row['ca_name']) ? '' : $row['ca_p_name'].'&nbsp;&nbsp;>&nbsp;&nbsp;'.$row['ca_name'];
			//$it_name = $a1 .'('.$row['com_name'].')&nbsp;&nbsp;'. stripslashes($row['it_name']).'&nbsp;&nbsp;<span style="color:#0000ff;">['.(($row['ca_p_name'] == $row['ca_name']) ? '' : $row['ca_p_name'].'&nbsp;&nbsp;>&nbsp;&nbsp;'.$row['ca_name']).']</span>'. $a2;
			$it_name = $a1 .stripslashes($row['it_name']).'&nbsp;&nbsp;<span style="color:#0000ff;">['.(($row['ca_p_name'] == $row['ca_name']) ? '' : $row['ca_p_name'].'&nbsp;&nbsp;>&nbsp;&nbsp;'.$row['ca_name']).']</span>'. $a2;
			
			
			// 판매가
			$row['ct_buy_price'] = $row['ct_price'];
			$row['ct_price'] = ($row['ct_price']==0 && $row['it_tel_inq']==1) ? '<span class="color_red">입력대기</span>' : number_format($row['ct_price']) ;
			$sell_price = $sum['price'];
		?>
	
		<tr>
			<td style="text-align:left;padding-left:15px;"><!-- 부품명 -->
				<input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
				<input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
				<input type="hidden" name="ca_str[<?php echo $i; ?>]" value="<?php echo $ca_str; ?>">
				<input type="hidden" name="it_name[<?php echo $i; ?>]" value="<?php echo get_text($row['it_name']); ?>">
				<?php echo $it_name; ?>
			</td>
			<td class="td_qty">
				<input type="hidden" name="it_qty[<?php echo $i; ?>]" value="<?php echo $row['ct_qty']; ?>">
				<span id="ct_qty_<?php echo $i; ?>" class="ct_qty"><?php echo number_format($sum['qty']); ?></span>
			</td><!-- 총수량 -->
			<td class="td_price">
				<input type="hidden" name="it_buy_price[<?php echo $i; ?>]" value="<?php echo $row['ct_buy_price']; ?>">
				<span id="buy_price_<?php echo $i; ?>" class="buy_price"><?php echo $row['ct_price']?></span>
			</td><!-- 판매가 -->
			<td class="td_subtotal">
				<input type="hidden" name="it_tot_buy_price[<?php echo $i; ?>]" value="<?php echo ($row['ct_buy_price'] * $row['ct_qty']); ?>">
				<span id="sell_price_<?php echo $i; ?>" class="sell_price"><?php echo number_format($sell_price); ?></span>
			</td><!-- 소계 -->
		</tr>
	
		<?php
			$tot_sell_price += $sell_price;
		} // for 끝
	
		if ($i == 0) {
			echo '<tr><td colspan="4" class="empty_table">장바구니에 담긴 부품이 없습니다.</td></tr>';
		} else {
			// 배송비 계산
			$send_cost = get_sendcost($s_cart_id, 0);
		}
		?>
		</tbody>
		</table>
	</div>
	
	<?php
	$tot_price = $tot_sell_price; // 총계 = 주문부품금액합계 + 배송비
	if ($tot_price > 0) {
	?>
	<div id="sod_bsk_tot" style="padding-bottom:40px;">
		<?php
		// 합계 금액이 있다면.
		if ($tot_price > 0) {
		?>
		<dt class="sod_bsk_cnt">총 가격</dt>
		<dd class="sod_bsk_cnt">
			<input type="hidden" name="total_price" value="<?php echo $tot_price; ?>">
			<strong style="font-size:1.2em;"><?php echo number_format($tot_price); ?></strong>원
		</dd>
		<?php } ?>
	
	</div>
	<?php } ?>
    <div class="btn_fixed_top btn_confirm">
		<?php if ($i > 0) { ?>
		<input type="hidden" name="url" value="./orderform.php">
		<input type="hidden" name="records" value="<?php echo $i; ?>">
		<input type="hidden" name="od_id" value="<?=$od_id?>">
		<input type="hidden" name="mb_id" value="<?=$member['mb_id']?>">
		<input type="hidden" name="od_name" value="<?=$member['mb_name']?>">
		<input type="hidden" name="od_email" value="<?=$member['mb_email']?>">
		<input type="hidden" name="act" value="">
		<a href="./order_excel_down.php?od_id=<?=$od_id?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
		<a href="javascript:" link="./_win_preview.php" class="btn btn_02 btn_preview">양식미리보기</a>
		<button type="button" onclick="return form_check('email');" class="btn btn_02">이메일전송</button>
		<?php } ?>
	</div>
	
</form>
</div>

<script>
/*
// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name*=_price]',function(e) {
	if(!isNaN($(this).val().replace(/,/g,'')))
		$(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});
*/
$(function() {
    var close_btn_idx;
	var num_reg = /^(\s|\d)+$/;
	$('.mng_com_mb').on('click',function(){
		var win_com_list = window.open(this.href, "win_com_list", "left=10,top=10,width=400,height=600");
		win_com_list.focus();
		return false;
	});

	$('.btn_preview').on('click',function(){
		if(!$('#sod_bsk_list [name="orl_subject"]').val()){
			alert('제목을 입력해 주세요.');
			$('#sod_bsk_list [name="orl_subject"]').focus();
			return false;
		}
		if(!$('#sod_bsk_list [name="com_name"]').val()){
			alert('업체명을 입력해 주세요.');
			$('#sod_bsk_list [name="com_name"]').focus();
			return false;
		}
		if(!$('#sod_bsk_list [name="mng_name"]').val()){
			alert('담당자명을 입력해 주세요.');
			$('#sod_bsk_list [name="mng_name"]').focus();
			return false;
		}
		if(!$('#sod_bsk_list [name="mng_email"]').val()){
			alert('담당자Email을 입력해 주세요.');
			$('#sod_bsk_list [name="mng_email"]').focus();
			return false;
		}
		var child_href = $(this).attr('link');
		//alert(child_href);return;
		var win_preview_ttl = 'win_preview';
		var win_preview = window.open(child_href, win_preview_ttl, "left=10,top=10,width=800,height=800");
		$('#sod_bsk_list').attr({'action':child_href,'target':win_preview_ttl}).submit();
		win_preview.focus();
		return false;
	});
});

function num_comma(qty,price,tprice,mg){
	var qty_input = qty.find('input');
	var qty_num = Number(qty.find('input').val());
	var qty_txt = qty.find('.ct_qty');
	
	var price_input = price.find('input');
	var price_num = Number(price.find('input').val());
	var price_txt = price.find('.buy_price');
	
	var total_input = tprice.find('input');
	var total_num = Number(tprice.find('input').val());
	var total_txt = tprice.find('.sell_price');

	var mag = Number(mg);
	
	var price_m = price_num + price_num/100 * mag
	price_input.val(price_m);
	price_txt.text(set_comma(price_m));
	
	var total_m = price_m * qty_num;
	total_input.val(total_m);
	total_txt.text(set_comma(total_m));
}

function set_comma(n){
	var reg = /(^[+-]?\d+)(\d{3})/;

	n += '';

	while (reg.test(n))
		n = n.replace(reg, '$1' + ',' + '$2');
	
	return n; 
}

function form_check(act) {
    var f = document.frmcartlist;
    var cnt = f.records.value;

    if (act == "exel"){
        f.act.value = act;
        f.submit();
    }
	else if (act == 'email'){
		if(!confirm('해당 양식을 이메일로 전송하시겠습니까?')){
			return false;
		}
		if(f.orl_subject.value == ''){
			alert('제목을 입력해 주세요.');
			f.orl_subject.focus();
			return false;
		}

		if(f.com_name.value == ''){
			alert('업체명을 입력해 주세요.');
			f.com_name.focus();
			return false;
		}
		
		if(f.mng_name.value == ''){
			alert('담당자명을 입력해 주세요.');
			f.mng_name.focus();
			return false;
		}
	
		if(f.mng_email.value == ''){
			alert('담당자의 email정보가 없습니다.');
			f.mng_email.focus();
			return false;
		}
		
		f.act.value = act;
        f.submit();
	}
	/*
    else if (act == "alldelete")
    {
        f.act.value = act;
        f.submit();
    }
    else if (act == "seldelete")
    {
        if($("input[name^=ct_chk]:checked").size() < 1) {
            alert("삭제하실 부품을 하나이상 선택해 주십시오.");
            return false;
        }

        f.act.value = act;
        f.submit();
    }
	*/
    return true;
}
</script>
<!-- } 장바구니 끝 -->
<?php
include_once ('./_tail.php');
?>