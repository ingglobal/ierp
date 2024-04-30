<?php
$sub_menu = '960226';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '주문바구니';
include_once('./_top_menu_reseller.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// cart id 설정
set_cart_id($sw_direct);

$s_cart_id = get_session('ss_cart_id');
//echo $s_cart_id;
// 선택필드 초기화
$sql = " UPDATE {$g5['g5_shop_cart_table']} SET ct_select = '0' WHERE od_id = '$s_cart_id' ";
sql_query($sql,1);

// $cart_com_idx = 0;
$cart_com_name = '';
$cart_com_dc_rate = 0;
?>
<style>
.td_s_price{width:110px;text-align:right !important;}
.mag_cancel{background:darkred;color:#fff !important;}
</style>
<div class="local_desc01 local_desc">
	<p>제품의 수량 및 갯수를 수정하시려면 리스트 내부에 있는 [선택사항수정] 버튼을 클릭하여 내용을 수정하세요.</p>
	<p>수량 및 갯수 조정이 끝났으면 오른편 위 [주문완료] 버튼을 클릭하세요.</p>
</div>

<!-- 장바구니 시작 { -->
<script src="<?php echo G5_USER_ADMIN_URL; ?>/js/shop.js"></script>	<!-- 원본위치: /js/shop.js -->

<div id="sod_bsk">
<form name="frmcartlist" id="sod_bsk_list" method="post" action="./item_order_cart_update.php">
	<div class="tbl_head01 tbl_wrap tbl_cart">
		<div class="top_inf">
			<div class="top_c top_no">접수번호: <b><?=$s_cart_id?></b></div>
			<div class="top_c top_mag">
				<a href="javascript:" class="mag_com" rate="0">할인율셋팅</a>
				<input type="text" id="mag_act_txt" class="frm_input"><span>%</span>
				<a href="javascript:" class="mag_act">일괄할인율적용</a>
				<a href="javascript:" class="mag_cancel">할인취소</a>
			</div>
		</div>
		<table>
		<thead>
		<tr>
			<th scope="col">
				<label for="ct_all" class="sound_only">부품 전체</label>
				<input type="checkbox" name="ct_all" value="1" id="ct_all" checked="checked">
			</th>
			<th scope="col">부품명</th>
			<th scope="col">기준판매가</th>
			<th scope="col">할인률</th>
			<th scope="col">총수량</th>
			<th scope="col" style="width:110px;">판매가</th>
			<th scope="col">소계</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$tot_point = 0;
		$tot_sell_price = 0;
	
		// $s_cart_id 로 현재 장바구니 자료 쿼리
		$sql = " SELECT a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty, a.ct_status, a.ct_send_cost, a.it_sc_type,
						b.ca_id, b.ca_id2, b.ca_id3, b.it_price, b.it_tel_inq, d.com_name
						, e.com_idx, e.com_level, e.com_name as seller_name
						,( SELECT ca_name FROM {$g5['g5_shop_category_table']} WHERE ca_id = SUBSTRING(c.ca_id,1,2) ) as ca_p_name
				FROM {$g5['g5_shop_cart_table']} a 
					LEFT JOIN {$g5['g5_shop_item_table']} b ON ( a.it_id = b.it_id )
					LEFT JOIN {$g5['company_table']} d ON ( d.com_idx = b.com_idx )
					LEFT JOIN {$g5['g5_shop_category_table']} c ON ( c.ca_id = b.ca_id )
					LEFT JOIN {$g5['companyreseller_table']} e ON a.com_idx = e.com_idx
				WHERE a.od_id = '$s_cart_id' 
					AND c.ca_id IN ('7m','8m')  ";
		$sql .= " GROUP BY a.it_id ";
		$sql .= " ORDER BY a.ct_id ";
        // echo $sql.'<br>';
		$result = sql_query($sql);
	
		$it_send_cost = 0;
		$scom_idx = 0;
		for ($i=0; $row=sql_fetch_array($result); $i++) {
			if($i == 0){
				$cart_com_name = $row['seller_name'];
				$cart_com_dc_rate = $g5['set_com_dc_rate_value'][$row['com_level']];
				$scom_idx = $row['com_idx'];
			}
			// print_r2($row);
			// 합계금액
			$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
							SUM(ct_point * ct_qty) as point,
							SUM(ct_qty) as qty
						from {$g5['g5_shop_cart_table']}
						where it_id = '{$row['it_id']}'
						and od_id = '$s_cart_id' ";
			$sum = sql_fetch($sql);
	
			if ($i==0) { // 계속쇼핑
				$continue_ca_id = $row['ca_id'];
			}
	
			//$a1 = '<a href="'.G5_SHOP_URL.'/item.php?it_id='.$row['it_id'].'" target="_blank"><b>';
			//$a2 = '</b></a>';
			$a1 = '';
			$a2 = '';
			$image = get_it_image($row['it_id'], 70, 70);
	
			// $it_name = $a1 .'('.(($row['com_name']) ? $row['com_name'] : 'INGGLOBAL').')&nbsp;&nbsp;'. stripslashes($row['it_name']).'&nbsp;&nbsp;<span style="color:#0000ff;">['.(($row['ca_p_name'] == $row['ca_name']) ? '' : $row['ca_p_name'].'&nbsp;&nbsp;>&nbsp;&nbsp;'.$row['ca_name']).']</span>'. $a2;
			$it_name = $a1 .'('.(($row['com_name']) ? $row['com_name'] : 'INGGLOBAL').')&nbsp;&nbsp;'. stripslashes($row['it_name']).'&nbsp;&nbsp;<span style="color:#0000ff;">['.(($row['ca_p_name'] == $row['ca_name']) ? '' : $row['ca_p_name'].''.$row['ca_name']).']</span>'. $a2;
			$it_options = print_item_options2($row['it_id'], $s_cart_id);	// 함수위치 extend/u.project.php, 원본 lib/shop.lib.php
			if($it_options) {
				$mod_options = '<div class="sod_option_btn"><button type="button" class="mod_options btns btn_02" com_idx="'.$row['com_idx'].'">선택사항수정</button></div>';
				//$mod_options = '';
				$it_name .= '<div class="sod_opt">'.$it_options.'</div>';
			}
	
			// 배송비
			switch($row['ct_send_cost'])
			{
				case 1:
					$ct_send_cost = '착불';
					break;
				case 2:
					$ct_send_cost = '무료';
					break;
				default:
					$ct_send_cost = '선불';
					break;
			}
	
			// 조건부무료
			if($row['it_sc_type'] == 2) {
				$sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $s_cart_id);
	
				if($sendcost == 0)
					$ct_send_cost = '무료';
			}
			
			// 판매가
			$row['ct_buy_price'] = $row['ct_price'];
			$row['ct_price'] = ($row['ct_price']==0 && $row['it_tel_inq']==1) ? '<span class="color_red">입력대기</span>' : number_format($row['ct_price']) ;
	
			$point      = $sum['point'];
			$sell_price = $sum['price'];
		?>
	
		<tr>
			<td class="td_chk"><!-- 체크박스 -->
				<label for="ct_chk_<?php echo $i; ?>" class="sound_only">부품</label>
				<input type="checkbox" name="ct_chk[<?php echo $i; ?>]" value="1" id="ct_chk_<?php echo $i; ?>" checked="checked">
			</td>
			<td style="text-align:left;padding-left:15px;"><!-- 부품명 -->
				<input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
				<input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
				<input type="hidden" name="it_name[<?php echo $i; ?>]" value="<?php echo get_text($row['it_name']); ?>">
				<?php echo $it_name.$mod_options; ?>
			</td>
			<td class="td_s_price">
				<span id="ct_s_price_<?php echo $i; ?>" class="ct_s_price" it_price="<?=$row['it_price']?>"><?php echo number_format($row['it_price']); ?></span>
			</td>
			<td class="td_rate">
				<?php
					$dif_price = $row['it_price'] - $row['ct_buy_price'];
					$dc_rate = $dif_price / $row['it_price'] * 100;
				?>
				<span id="ct_dc_rate_<?php echo $i; ?>" class="ct_dc_rate"><?php echo number_format($dc_rate,0,'',''); ?>%</span>
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
				<input type="hidden" value="<?php echo ($row['ct_buy_price'] * $row['ct_qty']); ?>">
				<span id="sell_price_<?php echo $i; ?>" class="sell_price"><?php echo number_format($sell_price); ?></span>
			</td><!-- 소계 -->
		</tr>
	
		<?php
			$tot_point      += $point;
			$tot_sell_price += $sell_price;
		} // for 끝
	
		if ($i == 0) {
			echo '<tr><td colspan="8" class="empty_table">장바구니에 담긴 부품이 없습니다.</td></tr>';
		} else {
			// 배송비 계산
			$send_cost = get_sendcost($s_cart_id, 0);
		}
		?>
		</tbody>
		</table>
	</div>
	
	<?php
	$tot_price = $tot_sell_price + $send_cost; // 총계 = 주문부품금액합계 + 배송비
	if ($tot_price > 0 || $send_cost > 0) {
	?>
	<div id="sod_bsk_tot" style="padding-bottom:40px;">
		<?php
		// 합계 금액이 있다면.
		if ($tot_price > 0) {
		?>
		<dt class="sod_bsk_cnt">총 가격</dt>
		<dd class="sod_bsk_cnt">
			<input type="hidden" value="<?php echo $tot_price; ?>">
			<strong style="font-size:1.2em;"><?php echo number_format($tot_price); ?></strong>원
		</dd>
		<?php } ?>
	
	</div>
	<?php } ?>
	
    <div class="btn_fixed_top btn_confirm">
		<?php if ($i > 0) { ?>
		<input type="hidden" name="url" value="./item_order_form.php">
		<input type="hidden" name="records" value="<?php echo $i; ?>">
		<input type="hidden" name="mb_id" value="<?=$member['mb_id']?>">
		<input type="hidden" name="od_name" value="<?=$member['mb_name']?>">
		<input type="hidden" name="scom_idx" value="<?=$scom_idx?>">
		<input type="hidden" name="act" value="">
		<button type="button" onclick="return form_check('order');" class="btn btn_01">주문완료</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<!--button type="button" onclick="return form_check('modify');" class="btn btn_02">선택수정</button-->
		<button type="button" onclick="return form_check('seldelete');" class="btn btn_02">선택삭제</button>
		<!-- <button type="button" onclick="return form_check('alldelete');" class="btn btn_02">비우기</button> -->
		<?php } ?>
	</div>
	
</form>
</div>

<script>
let cart_com_name = '<?=$cart_com_name?>';
let cart_com_dc_rate = '<?=$cart_com_dc_rate?>';
let mag_btn_name = (cart_com_name != '') ? '[' + cart_com_name + '] 할인율셋팅' : '할인율셋팅';
$('.mag_com').text(mag_btn_name);
$('.mag_com').attr('rate', cart_com_dc_rate);
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
	$('.mag_com').on('click',function(){
		let rate = $(this).attr('rate');
		$('#mag_act_txt').val(rate);
	});
	//일괄할인율적용
	$('.mag_act').on('click',function(){
		if($('#mag_act_txt').val() == '' || $('#mag_act_txt').val() == '0'){
			alert('할인율정보가 없습니다.');
			return false;
		}
		if(isNaN($('#mag_act_txt').val())){
			alert('숫자 데이터가 아닙니다.');
			return false;
		}
		
		var mag = $('#mag_act_txt').val();
		
		var big_total_input = $('#sod_bsk_tot').find('dd.sod_bsk_cnt').find('input');
		var big_total_num = Number($('#sod_bsk_tot').find('dd.sod_bsk_cnt').find('input').val());
		var big_total_obj = $('#sod_bsk_tot').find('dd.sod_bsk_cnt').find('strong');
		var big_total_mag = big_total_num - big_total_num/100 * mag;
		//alert($('.td_qty').length);
		$('.td_qty').each(function(){
			var qty_obj = $(this);
			var price_obj = $(this).siblings('.td_price');
			var total_obj = $(this).siblings('.td_subtotal');//.find('.sell_price');
			
			num_comma(qty_obj,price_obj,total_obj,mag);
		});
		
		big_total_input.val(big_total_mag);
		big_total_obj.text(set_comma(big_total_mag));
		
		//if(!confirm('해당 할인율로 반영하시겠습니까?')) return false;
		
		form_check('modify');
	});

	//할인취소
	$('.mag_cancel').on('click',function(){
		var big_total_input = $('#sod_bsk_tot').find('dd.sod_bsk_cnt').find('input');
		var big_total_obj = $('#sod_bsk_tot').find('dd.sod_bsk_cnt').find('strong');
		let total = 0;
		$('.td_qty').each(function(){
			var qty_obj = $(this);
			var price_s_obj = $(this).siblings('.td_s_price');
			var price_obj = $(this).siblings('.td_price');
			var total_obj = $(this).siblings('.td_subtotal');//.find('.sell_price');
			
			total += num_comma2(qty_obj,price_s_obj,price_obj,total_obj);
		});
		
		// return false;
		big_total_input.val(total);
		big_total_obj.text(set_comma(total));

		form_check('reset');
	});
	
    // 선택사항수정
    $(".mod_options").click(function(e) {
        var it_id = $(this).closest("tr").find("input[name^=it_id]").val();
        var $this = $(this);
		var com_idx = $(this).attr('com_idx');
        close_btn_idx = $(".mod_options").index($(this));
		// console.log(it_id);
		// return false;
        $.post(
            "./item_order_cartoption.php",
            { it_id: it_id, com_idx: com_idx },
            function(data) {
                $("#mod_option_frm").remove();
                $this.after("<div id=\"mod_option_frm\"></div>");
                $("#mod_option_frm").html(data);
                price_calculate();
            }
        );
    });

    // 모두선택
    $("input[name=ct_all]").click(function() {
        if($(this).is(":checked"))
            $("input[name^=ct_chk]").attr("checked", true);
        else
            $("input[name^=ct_chk]").attr("checked", false);
    });

    // 옵션수정 닫기
    $(document).on("click", "#mod_option_close", function() {
        $("#mod_option_frm").remove();
        $(".mod_options").eq(close_btn_idx).focus();
    });
    $("#win_mask").click(function () {
        $("#mod_option_frm").remove();
        $(".mod_options").eq(close_btn_idx).focus();
    });
	
	//모달관련
	$('.com_mdl').appendTo('body');
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
	
	var price_m = price_num - price_num/100 * mag
	price_input.val(price_m);
	price_txt.text(set_comma(price_m));
	
	var total_m = price_m * qty_num;
	total_input.val(total_m);
	total_txt.text(set_comma(total_m));
}

function num_comma2(qty,sprice,price,tprice){
	var qty_input = qty.find('input');
	var qty_num = Number(qty.find('input').val());
	var qty_txt = qty.find('.ct_qty');
	
	var price_s_num = Number(sprice.find('.ct_s_price').attr('it_price'));

	var price_input = price.find('input');
	var price_num = Number(price.find('input').val());
	var price_txt = price.find('.buy_price');
	
	var total_input = tprice.find('input');
	var total_num = Number(tprice.find('input').val());
	var total_txt = tprice.find('.sell_price');

	price_input.val(price_s_num);
	price_txt.text(set_comma(price_s_num));
	
	var total_m = price_s_num * qty_num;
	total_input.val(total_m);
	total_txt.text(set_comma(total_m));
	// console.log(total_m);
	return total_m;
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

    if (act == "order")
    {
        //if($("input[name^=ct_chk]:checked").size() < 1) {
        //    alert("견적내실 부품을 하나이상 선택해 주십시오.");
        //    return false;
        //}

        f.act.value = act;
        f.submit();
    }
	else if (act == 'modify'){
		if($("input[name^=ct_chk]:checked").size() < 1) {
            alert("수정하실 부품을 하나이상 선택해 주십시오.");
            return false;
        }
		
		f.act.value = act;
        f.submit();
	}
	else if (act == 'reset'){
		if($("input[name^=ct_chk]:checked").size() < 1) {
            alert("수정하실 부품을 하나이상 선택해 주십시오.");
            return false;
        }
		
		f.act.value = act;
        f.submit();
	}
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

    return true;
}
</script>
<!-- } 장바구니 끝 -->
<?php
include_once ('./_tail.php');
?>
