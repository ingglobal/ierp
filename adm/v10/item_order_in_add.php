<?php
$isql = " SELECT it_id,it_name,it_price FROM {$g5['g5_shop_item_table']} WHERE it_use = '1' ";
$ires = sql_query($isql,1);
for($j=0;$irow=sql_fetch_array($ires);$j++){
    $item_opt .= '<option value="'.$irow['it_id'].'" it_price="'.$irow['it_price'].'">'.$irow['it_name'].'</option>'.PHP_EOL;
}
?>
<form name="form02" method="post" action="./item_order_add_update.php" onsubmit="return form02_submit(this);" autocomplete="off" id="form02">
<!-- 없는 변수들을 선언해 줘야 함 -->
<input type="hidden" name="od_status" value="<?php echo $od_status; ?>">
<input type="hidden" name="od_settle_case" value="<?php echo $od_settle_case; ?>">
<input type="hidden" name="od_misu" value="<?php echo $od_misu; ?>">
<input type="hidden" name="od_cancel_price" value="<?php echo $od_cancel_price; ?>">
<input type="hidden" name="od_refund_price" value="<?php echo $od_refund_price; ?>">
<input type="hidden" name="od_receipt_point" value="<?php echo $od_receipt_point; ?>">
<input type="hidden" name="od_coupon" value="<?php echo $od_coupon; ?>">
<input type="hidden" name="fr_date" value="<?php echo $fr_date; ?>">
<input type="hidden" name="to_date" value="<?php echo $to_date; ?>">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs; ?>">
<?=$form_input?>

<input type="text" id="a_od_id"  name="a_od_id" placeholder="목록에서 주문번호 '+'선택" value="" readonly class="frm_input readonly" style="background:#FDFFC2;">
<input type="hidden" id="a_it_ids" value="">
<input type="hidden" id="a_com_idx"  name="a_com_idx" value="">
<input type="text" id="a_com_name"  name="a_com_name" placeholder="판매처" value="" readonly class="frm_input readonly" style="background:#FFEAE3;">
<select name="a_it_id" id="a_it_id" class="frm_input" style="">
    <option value="" it_price="">::제품선택::</option>
    <?=$item_opt?>
</select>
<input type="text" id="a_it_price"  name="a_it_price" placeholder="기준단가" value="" readonly class="frm_input readonly" style="width:120px;text-align:right;background:#ededed;">
<input type="text" id="a_it_rate"  name="a_it_rate" placeholder="할인률" value="0" class="frm_input" style="padding-right:5px;width:40px;text-align:right;">%
<input type="text" id="a_ct_price"  name="a_ct_price" placeholder="할인단가" value="" readonly class="frm_input readonly" style="width:120px;text-align:right;background:#ededed;"> x
<input type="text" id="a_ct_cnt"  name="a_ct_cnt" placeholder="갯수" value="0" class="frm_input" style="padding-right:5px;width:60px;text-align:right;">개
<input type="text" id="a_od_price"  name="a_od_price" placeholder="판매가" value="" readonly class="frm_input readonly" style="width:140px;text-align:right;background:#ededed;">
<input type="submit" name="act_btn" value="추가" onclick="document.pressed=this.value" class="btn btn_02">
<a href="javascript:" class="btn btn_01 add_cancel">취소</a>
</form>
<script>
$(function(){
    //od_id="" com_idx="" com_name="" it_ids="" dc_rate=""
    $('.ct_add').on('click',function(){
        if($('#a_od_id').val() != $(this).attr('od_id')){
            all_clear();
            $('#a_od_id').val($(this).attr('od_id'));
            $('#a_it_ids').val($(this).attr('it_ids'));
            $('#a_com_idx').val($(this).attr('com_idx'));
            $('#a_com_name').val($(this).attr('com_name'));
            $('#a_it_rate').val($(this).attr('dc_rate'));
        }
    });
});

$('#a_it_id').on('change', function(){
    if($('#a_it_ids').val()){
        let it_arr = $('#a_it_ids').val().split(',');
        let it_id = $(this).find('option:selected').val();
        let it_name = $(this).find('option:selected').text();
        if(it_arr.indexOf(it_id) !== -1){
            alert(it_name + '이 이미 등록된 상태입니다.');
            $(this).val('');
            item_clear();
            return false;
        }
        if(it_id){
            let dc_rate = ($('#a_it_rate').val()) ? Number($('#a_it_rate').val()) : 0;
            let it_price = ($(this).find('option:selected').attr('it_price')) ? Number($(this).find('option:selected').attr('it_price')) : 0;
            let cma_it_price = thousand_comma(it_price);
            $('#a_it_price').val(cma_it_price);
            rate_set(dc_rate, it_price);
            let ct_cnt = ($('#a_ct_cnt').val()) ? Number($('#a_ct_cnt').val()) : 0;
            calculate();
        }
        else{
            item_clear();
        }
    }
    else{
        alert('주문번호를 먼저 선택해 주세요.');
        $(this).val('');
        return false;
    }
});

$('#a_it_rate').on('input', function(){
    if(!$('#a_it_id').val()){
        alert('제품을 선택해 주세요.');
        $(this).val('0');
        return false;
    }
    var num = $(this).val().replace(/[^0-9]/g,"");
    if(num.charAt(0) == '0' && num.length > 1) num = num.substring(1);
    num = (num == '') ? '0' : num;
    if(Number(num) > 100) num = 100;
    $(this).val(num);

    let it_price = ($('#a_it_id').find('option:selected').attr('it_price')) ? Number($('#a_it_id').find('option:selected').attr('it_price')) : 0;
    rate_set(num, it_price);
    calculate();
});

$('#a_ct_cnt').on('input', function(){
    var num = $(this).val().replace(/[^0-9]/g,"");
    if(num.charAt(0) == '0' && num.length > 1) num = num.substring(1);
    num = (num == '') ? '0' : num;
    $(this).val(num);
    calculate();
});

$('.add_cancel').on('click', function(){
    all_clear();
});

function rate_set(dc_rate=0, it_price=0){
    let ct_price = it_price - ((it_price / 100) * dc_rate);
    let cma_ct_price = thousand_comma(ct_price);
    $('#a_ct_price').val(cma_ct_price);
}

function calculate(){
    let rate = Number($('#a_it_rate').val());
    let ct_price = ($('#a_ct_price').val()) ? Number($('#a_ct_price').val().replace(/,/g,'')) : 0;
    let ct_cnt = Number($('#a_ct_cnt').val());
    if(ct_price && ct_cnt){
        let od_price = ct_price * ct_cnt;
        let cma_od_price = thousand_comma(od_price);
        $('#a_od_price').val(cma_od_price);
    }
    else{
        $('#a_od_price').val(''); 
    }
}

function all_clear(){
    $('#a_od_id').val('');
    $('#a_it_ids').val('');
    $('#a_com_idx').val('');
    $('#a_com_name').val('');
    $('#a_it_id').val('');
    $('#a_it_price').val('');
    $('#a_it_rate').val('0');
    $('#a_ct_price').val('');
    $('#a_ct_cnt').val('0');
    $('#a_od_price').val('');
}

function item_clear(){
    $('#a_it_id').val('');
    $('#a_it_price').val('');
    $('#a_ct_price').val('');
    $('#a_ct_cnt').val('0');
    $('#a_od_price').val('');
}
function form02_submit(f){
    if(!f.a_ct_cnt.value || f.a_ct_cnt.value == '0'){
        alert('수량을 입력해 주세요.');
        f.a_ct_cnt.focus();
        return false;
    }

    if(!f.a_od_price.value){
        alert('판매가가 없으니 제품선택을 선택해 주세요.');
        f.a_it_id.focus();
        return false;
    }

    return true;
}
</script>