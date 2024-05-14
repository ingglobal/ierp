<?php
$seller_opt = '';
$ssql = " SELECT com.com_idx
                , com_name
                , com_level
                , ( SELECT GROUP_CONCAT(mb_id) FROM {$g5['companyreseller_member_table']} WHERE com_idx = com.com_idx AND cmm_status = 'ok' ORDER BY mb_id ) AS mb_idxs
        FROM {$g5['companyreseller_table']} com
        WHERE com_status = 'ok' 
        ORDER BY com_name, com_idx ";
$sres = sql_query($ssql,1);
for($j=0;$srow=sql_fetch_array($sres);$j++){
    $seller_opt .= '<option value="'.$srow['com_idx'].'" dc_rate="'.$g5['set_com_dc_rate_value'][$srow['com_level']].'">'.$srow['com_name'].'('.$g5['set_com_dc_rate_value'][$srow['com_level']].'%)</option>'.PHP_EOL;
}

$isql = " SELECT it_id,it_name,it_price FROM {$g5['g5_shop_item_table']} WHERE it_use = '1' ";
$ires = sql_query($isql,1);
for($j=0;$irow=sql_fetch_array($ires);$j++){
    $item_opt .= '<option value="'.$irow['it_id'].'" it_price="'.$irow['it_price'].'">'.$irow['it_name'].'</option>'.PHP_EOL;
}
?>
<form name="form02" method="post" action="./item_order_reg_update.php" onsubmit="return form02_submit(this);" autocomplete="off" id="form02">
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
<input type="hidden" name="a_od_id" value="<?php echo get_uniqid(); ?>">
<select name="a_com_idx" id="a_com_idx">
    <option value="" dc_rate="">::판매처선택::</option>
    <?=$seller_opt?>
</select>
<select name="a_it_id" id="a_it_id" class="frm_input" style="">
    <option value="" it_price="">::제품선택::</option>
    <?=$item_opt?>
</select>
<input type="text" id="a_it_price"  name="a_it_price" placeholder="기준단가" value="" readonly class="frm_input readonly" style="width:120px;text-align:right;background:#ededed;">
<input type="text" id="a_it_rate"  name="a_it_rate" placeholder="할인률" value="0" class="frm_input" style="padding-right:5px;width:40px;text-align:right;">%
<input type="text" id="a_ct_price"  name="a_ct_price" placeholder="할인단가" value="" readonly class="frm_input readonly" style="width:120px;text-align:right;background:#ededed;"> x
<input type="text" id="a_ct_cnt"  name="a_ct_cnt" placeholder="갯수" value="1" readonly class="frm_input readonly" style="padding-right:5px;width:60px;text-align:right;background:#ededed;">개
<input type="text" id="a_od_price"  name="a_od_price" placeholder="판매가" value="" readonly class="frm_input readonly" style="width:140px;text-align:right;background:#ededed;">
<input type="submit" name="act_btn" value="추가" onclick="document.pressed=this.value" class="btn btn_02">
<a href="javascript:" class="btn btn_01 add_cancel">취소</a>
</form>
<script>
$(function(){
    $('#a_com_idx').on('change', function(){
        all_clear('com');
        if($(this).val()){
            let dc_rate = $(this).find('option:selected').attr('dc_rate');
            $('#a_it_rate').val(dc_rate);
        }
    });
    
    $('#a_it_id').on('change', function(){
        if($('#a_com_idx').val()){
            let it_id = $(this).find('option:selected').val();
            let it_name = $(this).find('option:selected').text();
            
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
            alert('판매업체를 먼저 선택해 주세요.');
            $(this).val('');
            return false;
        }
    });
    $('#a_it_rate').on('change',function(){
        console.log('changed');
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
    
    // $('#a_ct_cnt').on('input', function(){
    //     var num = $(this).val().replace(/[^0-9]/g,"");
    //     if(num.charAt(0) == '0' && num.length > 1) num = num.substring(1);
    //     num = (num == '') ? '1' : num;
    //     $(this).val(num);
    //     calculate();
    // });
    
    $('.add_cancel').on('click', function(){
        all_clear();
    });
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

function all_clear(typ=''){
    if(typ=='com') {
        $('#a_it_id').val('');
        $('#a_it_price').val('');
        $('#a_it_rate').val('0');
        $('#a_ct_price').val('');
        $('#a_ct_cnt').val('1');
        $('#a_od_price').val('');
    }
    else {
        $('#a_com_idx').val('');
        $('#a_it_id').val('');
        $('#a_it_price').val('');
        $('#a_it_rate').val('0');
        $('#a_ct_price').val('');
        $('#a_ct_cnt').val('1');
        $('#a_od_price').val('');
    }
}

function item_clear(){
    $('#a_it_id').val('');
    $('#a_it_price').val('');
    $('#a_ct_price').val('');
    $('#a_ct_cnt').val('0');
    $('#a_od_price').val('');
}
function form02_submit(f){
    if(!f.a_com_idx.value){
        alert('판매처를 선택해 주세요.');
        f.a_com_idx.focus();
        return false;
    }
    if(!f.a_it_id.value){
        alert('상품을 선택해 주세요.');
        f.a_it_id.focus();
        return false;
    }
    if(!f.a_it_price.value){
        alert('상품관리에서 해당제품의 판매단가를 등록되어 있어야 합니다.');
        f.a_it_price.focus();
        return false;
    }
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