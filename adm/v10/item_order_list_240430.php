<?php
$sub_menu = '960226';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '판매내역관리';
include_once('./_top_menu_reseller.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// 주문내역 추출 초기화 설정(ct_history 첫줄에 줄바꿈 제거해서 \n분리를 통한 리스트 추출에 중복을 제거한 후 리스팅)
order_init();


// 아래 foreach블록은 XXX_form.php파일에 제일 상단에도 서술하자
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}


$where = array();
$where[] = " od_status IN ('주문') ";

// 관리자가 아닌 경우
if(!$member['mb_manager_yn']) {
    // 팀원이면 무조건 자기것만 보임
    if($member['mb_1']<=4) {
        // 매출조회 아이디가 있는 경우
        if($member['mb_sales_ids_array'][0]) {
            $where[] = " mb_id_saler IN ('".$member['mb_id']."','".implode("','",$member['mb_sales_ids_array'])."') ";
        }
        else {
            $where[] = " mb_id_saler = '".$member['mb_id']."' ";
        }
    }
    //print_r3(get_dept_idxs());
    else if(get_dept_idxs()) {
        $where[] = " trm_idx_department IN (".get_dept_idxs().") ";
    }
}
// 관리자인 경우 팀 검색이 있으면
else if($ser_trm_idxs) {
    $where[] = " trm_idx_department IN (".$ser_trm_idxs.") ";
}


$doc = strip_tags($doc);
$sort1 = in_array($sort1, array('od_id', 'od_cart_price', 'od_receipt_price', 'od_cancel_price', 'od_misu', 'od_cash')) ? $sort1 : '';
$sort2 = in_array($sort2, array('desc', 'asc')) ? $sort2 : 'desc';
$sel_field = get_search_string($sel_field);
$od_status = get_search_string($od_status);
$search = get_search_string($search);
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$od_misu = preg_replace('/[^0-9a-z]/i', '', $od_misu);
$od_cancel_price = preg_replace('/[^0-9a-z]/i', '', $od_cancel_price);
$od_refund_price = preg_replace('/[^0-9a-z]/i', '', $od_refund_price);
$od_receipt_point = preg_replace('/[^0-9a-z]/i', '', $od_receipt_point);
$od_coupon = preg_replace('/[^0-9a-z]/i', '', $od_coupon); 

$sql_search = "";
if ($search != "") {
    if ($sel_field == "com_name") {
        $where[] = " (com.com_name LIKE '%".trim($search)."%' or com.com_names LIKE '%".trim($search)."%') ";
    }
    else if ($sel_field == "mb_name_saler") {
        $where[] = " od_keys REGEXP '".$sel_field."=[가-힝]*(".trim($search).")+[가-힝]*:' ";
    }
    else if ($sel_field != "") {
        $where[] = " $sel_field like '%$search%' ";
    }
    //if ($save_search != $search) {
    //    $page = 1;
    //}
}

if ($od_status) {
    switch($od_status) {
        default:
            $where[] = " od_status = '$od_status' ";
            break;
    }

    switch ($od_status) {
        case '주문' :
            $sort1 = "od_id";
            $sort2 = "desc";
            break;
        case '전송' :   // 결제완료
            $sort1 = "od_receipt_time";
            $sort2 = "desc";
            break;
    }
}

if ($fr_date && $to_date) {
    $where[] = " od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}

if ($where) {
    $sql_search = ' where '.implode(' and ', $where);
}

if ($sel_field == "")  $sel_field = "com_name";
if ($sort1 == "") $sort1 = "od_time";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " FROM {$g5['g5_shop_order_table']} od
                    LEFT JOIN {$g5['companyreseller_table']} com ON com.com_idx = od.com_idx
                    LEFT JOIN {$g5['companyreseller_member_table']} cmm ON com.com_idx = cmm.com_idx
                    LEFT JOIN {$g5['member_table']} mb ON cmm.mb_id = mb.mb_id
                    LEFT JOIN {$g5['member_table']} mb2 ON od.mb_id_saler = mb.mb_id
                    $sql_search
";

$sql = " select count(od.od_id) as cnt " . $sql_common;
// echo $sql;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 10;$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " SELECT od_id
            , od.mb_id
            , od.mb_id_saler
            , mb2.mb_name AS od_name
            , od_cart_count
            , od_cart_price
            , od_time
            , mb.mb_name AS com_mb_name
            , cmm.mb_id AS com_mb_id
            , mb.mb_hp AS com_mb_hp
            , com.com_idx
            , com_level
            , com.com_name AS com_name
            , com_president
           $sql_common
           order by $sort1 $sort2 $sort3
           limit $from_record, $rows
";
// print_r2($sql);exit;
$result = sql_query($sql,1);

$od_arr = array();

for($j=0;$row=sql_fetch_array($result);$j++){
    $od_arr[$row['od_id']]['com_idx'] = $row['com_idx'];
    $od_arr[$row['od_id']]['com_name'] = $row['com_name'];
    $od_arr[$row['od_id']]['mb_id_saler'] = $row['mb_id_saler'];
    $od_arr[$row['od_id']]['mb_name_saler'] = $row['mb_name'];
    $od_arr[$row['od_id']]['com_rate'] = $g5['set_com_dc_rate_value'][$row['com_level']];
    $od_arr[$row['od_id']]['com_level'] = $g5['set_com_level_value'][$row['com_level']].'('.$g5['set_com_dc_rate_value'][$row['com_level']].' %)';
    $od_arr[$row['od_id']]['com_mb_id'] = $row['com_mb_id'];
    $od_arr[$row['od_id']]['com_mb_name'] = $row['com_mb_name'];
    $od_arr[$row['od_id']]['com_mb_hp'] = $row['com_mb_hp'];
    $od_arr[$row['od_id']]['od_cart_count'] = $row['od_cart_count'];
    $od_arr[$row['od_id']]['od_cart_price'] = $row['od_cart_price'];
    $od_arr[$row['od_id']]['od_time'] = $row['od_time'];
    
    $od_arr[$row['od_id']]['it_arr'] = array();
    $od_arr[$row['od_id']]['ct_arr'] = array();
    $csql = " SELECT ct_id
                    , ct.it_id
                    , ct.it_name
                    , ct_status
                    , ct_price
                    , ct_qty
                    , ct_time
                    , it_price
                FROM {$g5['g5_shop_cart_table']} ct
                LEFT JOIN {$g5['g5_shop_item_table']} it ON ct.it_id = it.it_id
                WHERE od_id = '{$row['od_id']}' AND ct_status = '주문' ORDER BY it_name ";
    $cres = sql_query($csql,1);
    $od_arr[$row['od_id']]['rowspan'] = $cres->num_rows;
    for($l=0;$crow=sql_fetch_array($cres);$l++){
        array_push($od_arr[$row['od_id']]['it_arr'],$crow['it_id']);
        $ct_arr = array(
            'ct_id' => $crow['ct_id']
            , 'it_id' => $crow['it_id']
            , 'it_name' => $crow['it_name']
            , 'it_price' => $crow['it_price']
            , 'ct_qty' => $crow['ct_qty']
            , 'ct_price' => $crow['ct_price']
            , 'ct_sell_price' => $crow['ct_price'] * $crow['ct_qty']
        );
        array_push($od_arr[$row['od_id']]['ct_arr'], $ct_arr);
    }
}

// print_r2($od_arr);

$qstr1 = "od_status=".urlencode($od_status)."&amp;od_settle_case=".urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_cancel_price=$od_cancel_price&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;ser_trm_idxs=$ser_trm_idxs";

if($default['de_escrow_use'])
    $qstr1 .= "&amp;od_escrow=$od_escrow";

$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>
<style>
.tbl_head01 tbody tr.bg1{background:#FFFBDA !important;}
.item_main {font-weight:bold;}
.item_option {padding-left:10px;}
.item_option_add {padding-left:10px;color:#cacaca;}
.item_option_price {font-size:0.7rem;}
.div_history_sales {}
.div_history_sales:after {display:block;visibility:hidden;clear:both;content:'';}
.ct_history {padding-left:30px;color:#909090;}
.div_sales {padding-left:30px;color:#171dff;}
.span_ct_id {color:#a9a9a9;}
.span_sales_date {font-size:0.6rem;}
.btn_del_md_id_saler {display:none;}

#form02{margin-bottom:10px;}

.td_od_id{position:relative;}
.td_od_id i{position:absolute;cursor:pointer;font-size:1.2em;top:2px;right:2px;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 판매내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
</div>

<form name="frmorderlist" class="local_sch01 local_sch">
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

<label for="sel_field" class="sound_only">검색대상</label>

<select name="sel_field" id="sel_field">
    <option value="com_name" <?php echo get_selected($sel_field, 'com_name'); ?>>업체명</option>
    <option value="mb_name_saler" <?php echo get_selected($sel_field, 'mb_name_saler'); ?>>영업자명</option>
    <option value="mb_id_saler" <?php echo get_selected($sel_field, 'mb_id_saler'); ?>>영업자 ID</option>
    <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
    <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>접수번호</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off">
<input type="submit" value="검색" class="btn_submit">

</form>

<form class="local_sch03 local_sch">
<!-- 없는 변수들을 선언해 줘야 함 -->
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<!--i class="fa fa-chevron-down form_chevron fa2"></i>
<i class="fa fa-chevron-up form_chevron fa2"></i-->
<div class="form_search_more">
    <strong>견적내역상태</strong>
    <input type="radio" name="od_status" value="" id="od_status_all" <?php echo get_checked($od_status, '');     ?>>
    <label for="od_status_all">전체</label>
    <input type="radio" name="od_status" value="견적" id="od_status_quot" <?php echo get_checked($od_status, '주문'); ?>>
    <label for="od_status_dvr">견적</label>
    <input type="radio" name="od_status" value="전송" id="od_status_send" <?php echo get_checked($od_status, '전송'); ?>>
    <label for="od_status_done">전송</label>
</div>

<div class="sch_last">
    <strong>판매일자</strong>
    <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
    <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
    <button type="button" onclick="javascript:set_date('어제');">어제</button>
    <button type="button" onclick="javascript:set_date('이번주');">이번주</button>
    <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
    <button type="button" onclick="javascript:set_date('지난주');">지난주</button>
    <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
    <button type="button" onclick="javascript:set_date('전체');">전체</button>
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>
<?php
$isql = " SELECT it_id,it_name,it_price FROM {$g5['g5_shop_item_table']} WHERE ca_id LIKE '7m%' AND it_use = '1' ";
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
<div class="local_desc01 local_desc" style="display:no ne;">
    <p><span style="color:red;">상품변경은 불가</span>합니다. 상품변경인 경우 해당정보를 삭제하고 다시 주문바구니에서 부터 등록해 주세요.</p>
    <p>제품정보를 삭제하려면 해당 제품정보의 "삭제"버튼으로 삭제해 주세요. </p>
    <p>주문정보를 삭제하려면 해당 주문의 체크박스에 체크한 후 "선택삭제"를 해 주세요. </p>
    <p>"일괄수정은" 첵크박스와 상관없이 표시된 목록 전체가 일괄 수정됩니다. </p>
</div>


<form name="form01" method="post" action="./item_order_list_update.php" onsubmit="return form01_submit(this);" autocomplete="off" id="form01">
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
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5['file_name']; ?>">

<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>주문 내역 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">주문 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" id="th_od_id"><a href="<?php echo title_sort("od_id", 1)."&amp;$qstr1"; ?>">주문번호</a></th>
        <th scope="col" id="th_saler">영업자</th>
        <th scope="col" id="th_com">판매처</th>
        <th scope="col" id="th_com_level">판매처등급</th>
        <th scope="col" id="th_com_mb_name">판매처담당자</th>
        <th scope="col" id="th_com_mb_hp">담당자연락처</th>
        <th scope="col" id="th_odrcnt">판매품목건수</th>
        <th scope="col" id="th_sum_price">주문합계</th>
        <th scope="col" id="th_od_time">판매일자</th>
        <th scope="col">
            <label for="chkall2" class="sound_only">제품 전체</label>
            <input type="checkbox" name="chkall2" value="1" id="chkall2" onclick="check_all3(this.form)">
        </th>
        <th scope="col" id="th_item_name">제품명</th>
        <th scope="col" id="th_item_price">기준단가</th>
        <th scope="col" id="th_rate">할인률</th>
        <th scope="col" id="th_sell_price">판매단가</th>
        <th scope="col" id="th_qty">수량</th>
        <th scope="col" id="th_ct_price">판매가</th>
        <th scope="col" id="th_mng">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    $j = 0;
    foreach ($od_arr as $od_id=>$row) {
        // print_r2($row);
        $rowspan = ($row['rowspan']) ? ' rowspan="'.$row['rowspan'].'"' : '';
        $bg = 'bg'.($i%2);
    ?>
    <tr class="orderlist<?php echo ' '.$bg; ?>">
        <td class="td_chk"<?=$rowspan?>>
            <input type="hidden" name="od_id[<?=$i?>]" value="<?=$od_id?>" id="od_id_<?=$i?>">
            <label for="chk_<?=$i?>" class="sound_only">접수번호 <?=$od_id?></label>
            <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
        </td>
        <td headers="th_od_id" class="td_od_id"<?=$rowspan?>><!-- 주문번호 -->
            <span><?=$od_id?></span>
            <i class="fa fa-plus-circle ct_add" aria-hidden="true" od_id="<?=$od_id?>" com_idx="<?=$row['com_idx']?>" com_name="<?=$row['com_name']?>" it_ids="<?=implode(',',$row['it_arr'])?>" dc_rate="<?=$row['com_rate']?>"></i>
        </td>
        <td class="td_mb_id_saler"<?=$rowspan?>><!-- 영업자 -->
            <?=$row['mb_name_saler']?>
        </td>
        <td headers="th_com"<?=$rowspan?>>
            <div class="ordercomname"><?=$row['com_name']?></div>
        </td><!-- 판매처 -->
        <td headers="th_com_level"<?=$rowspan?>>
            <?=$row['com_level']?>
        </td><!--판매처등급-->
        <td headers="th_com_mb_name"<?=$rowspan?>><?=$row['com_mb_name']?></td>
        <td headers="th_com_mb_hp"<?=$rowspan?>><?=$row['com_mb_hp']?></td>
        <td headers="th_odrcnt"<?=$rowspan?>><?=$row['od_cart_count']?>건</td><!-- 주문상품수 -->
        <td class="td_num td_numsum"<?=$rowspan?>>
            <?php $od_cart_price = number_format($row['od_cart_price']); ?>
            <input type="text" name="od_cart_price[<?=$i?>]" ono="<?=$i?>" value="<?=$od_cart_price?>" readonly class="frm_input od_cart_price_<?=$i?> od_cart_price_<?=$od_id?>" style="width:110px;text-align:right;padding-right:5px;background:#ededed;">
        </td><!-- 주문합계 -->
        <td class="td_od_time"<?=$rowspan?> style="width:90px;" od_time="<?=$row['od_time']?>">
            <?php 
                $od_date = substr($row['od_time'],0,10);
                $od_time = substr($row['od_time'],11,8);
            ?>
            <input type="hidden" name="od_times[<?=$i?>]" value="<?=$od_time?>">
            <input type="text" name="od_date[<?=$i?>]" readonly value="<?=$od_date?>" class="frm_input readonly od_date_<?=$i?>" style="text-align:center;">
            <script>
            $(".od_date_<?=$i?>").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
            </script>
        </td>
        <?php
        for($k=0;$k<count($row['ct_arr']);$k++){
            $row2 = $row['ct_arr'][$k];
            if($k >= 1) echo '<tr class="'.$bg.'">'.PHP_EOL;
        ?>
        <td class="td_chk2">
            <input type="hidden" name="ct_id[<?=$k?>]" value="<?=$row2['ct_id']?>" id="ct_id_<?=$k?>">
            <input type="hidden" name="ct_od_id[<?=$k?>]" value="<?=$od_id?>" id="ct_od_id_<?=$k?>">
            <label for="chk2_<?=$k?>" class="sound_only">주문제품번호 <?=$row2['ct_id']?></label>
            <input type="checkbox" name="chk2[]" value="<?=$k?>" id="chk2_<?=$k?>">
        </td>
        <td headers="th_item_name"><?=$row2['it_name']?></td><!-- 제품명 -->
        <td headers="th_item_price" style="text-align:right;"><?=number_format($row2['it_price'])?></td><!--기준단가 -->
        <td headers="th_rate">
            <?php
            $rt = (($row2['it_price'] - $row2['ct_price']) / $row2['it_price']) * 100;
            $rate = number_format($rt,0,'','');
            ?> 
            <input type="text" class="frm_input inp_rate inp_rate_<?=$j?>" cno="<?=$j?>" od_id="<?=$od_id?>" it_price="<?=$row2['it_price']?>" value="<?=$rate?>" style="text-align:right;width:40px;padding-right:5px;"> %
        </td><!-- 할인률 -->
        <td headers="th_sell_price">
            <?php $ct_price = number_format($row2['ct_price']); ?>
            <input type="text" name="ct_price[<?=$j?>]" value="<?=$ct_price?>" readonly class="frm_input ct_price_<?=$j?>" style="text-align:right;width:90px;padding-right:5px;background:#ededed;">
        </td><!--판매단가 -->
        <td headers="th_qty">
            <input type="text" name="ct_qty[<?=$j?>]" cno="<?=$j?>" od_id="<?=$od_id?>" value="<?=$row2['ct_qty']?>" class="frm_input ct_qty ct_qty_<?=$j?>" style="width:50px;text-align:right;padding-right:5px;">개
        </td><!-- 수량 -->
        <td headers="th_price" style="text-align:right;">
            <?php $ct_sell_price = number_format($row2['ct_sell_price']); ?>
            <input type="text" name="ct_sell_price[<?=$j?>]" value="<?=$ct_sell_price?>" cno="<?=$j?>" od_id="<?=$od_id?>" readonly class="frm_input ct_sell_price_<?=$j?> ct_sell_price_<?=$od_id?>" style="text-align:right;width:100px;padding-right:5px;background:#ededed;">
        </td><!-- 판매가 -->
        <td class="td_mng td_mng_s"><!-- 보기 -->
            <a href="javascript:" ct_id="<?=$row2['ct_id']?>" od_id="<?=$od_id?>" class="btn btn_01 ct_del">삭제</a>
        </td>
    </tr>
    <?php
            $j++;
        }
        $i++;
    }
    //sql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="18" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="일괄수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    $('.ct_del').on('click', function(){
        if(!confirm('정말로 개별주문정보를 삭제 하시겠습니까?')){
            return false;
        }
        let ct_id = $(this).attr('ct_id');
        let od_id = $(this).attr('od_id');
        let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/cart_del.php';
        $.ajax({
            type: 'POST',
            dataType: 'text',
            url: ajxurl,
            data: {'ct_id': ct_id, 'od_id': od_id},
            success: function(res){
                if(res == 'ok'){
                    location.reload();
                }
                else{
                    alert(res);
                }
            },
            error: function(xmlReq){
                alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
            }
        });
    });

    $('.inp_rate').on('input', function(){
        var num = $(this).val().replace(/[^0-9]/g,"");
        if(num.charAt(0) == '0' && num.length > 1) num = num.substring(1);
        num = (num == '') ? '0' : num;
        if(Number(num) > 100) num = 100;
        $(this).val(num);

        let cno = $(this).attr('cno');
        let od_id = $(this).attr('od_id');
        let it_price = Number($(this).attr('it_price'));
        let dc_rate = Number($(this).val());
        let ct_price_obj = $('.ct_price_'+cno);
        let ct_qty = Number($('.ct_qty_'+cno).val());
        let sell_price_obj = $('.ct_sell_price_'+cno);
        let sell_price_objs = $('.ct_sell_pricee_'+od_id);
        let od_cart_price_obj = $('.od_cart_price_'+od_id);

        set_value('rate',cno,od_id,it_price,dc_rate,ct_price_obj,ct_qty,sell_price_obj,od_cart_price_obj);
    });

    $('.ct_qty').on('input', function(){
        var num = $(this).val().replace(/[^0-9]/g,"");
        if(num.charAt(0) == '0' && num.length > 1) num = num.substring(1);
        num = (num == '' || num == '0') ? '1' : num;
        $(this).val(num);

        let cno = $(this).attr('cno');
        let od_id = $(this).attr('od_id');
        let it_price = Number($('.inp_rate_'+cno).attr('it_price'));
        let dc_rate = Number($('.inp_rate_'+cno).val());
        let ct_price_obj = $('.ct_price_'+cno);
        let ct_qty = Number($(this).val());
        let sell_price_obj = $('.ct_sell_price_'+cno);
        let sell_price_objs = $('.ct_sell_pricee_'+od_id);
        let od_cart_price_obj = $('.od_cart_price_'+od_id);

        set_value('qty',cno,od_id,it_price,dc_rate,ct_price_obj,ct_qty,sell_price_obj,od_cart_price_obj);
    });
});

function set_value(_tpy,_cno,_od_id,_it_price,_dc_rate,_ct_price_obj,_ct_qty,_sell_price_obj,_od_cart_price_obj){
    let tpy = _tpy;
    let cno = _cno;
    let od_id = _od_id;
    let it_price = _it_price;
    let dc_rate = _dc_rate;
    let ct_price_obj = _ct_price_obj;
    let ct_qty = _ct_qty;
    let sell_price_obj = _sell_price_obj;
    let sell_price_objs = $('.ct_sell_price_'+_od_id);
    let od_cart_price_obj = _od_cart_price_obj;

    if(tpy == 'rate'){
        let ct_price = it_price - ((it_price / 100) * dc_rate);
        let cma_ct_price = thousand_comma(ct_price);
        ct_price_obj.val(cma_ct_price);
        let sell_price = ct_price * ct_qty;
        let cma_sell_price = thousand_comma(sell_price);
        sell_price_obj.val(cma_sell_price);
        let total_price = 0;
        sell_price_objs.each(function(){
            let sel_price = Number($(this).val().replace(/,/g,''));
            console.log(sel_price);
            total_price += sel_price;
        });
        let cma_total_price = thousand_comma(total_price);
        od_cart_price_obj.val(cma_total_price);
    }
    else if(tpy == 'qty'){
        let ct_price = it_price - ((it_price / 100) * dc_rate);
        let sell_price = ct_price * ct_qty;
        let cma_sell_price = thousand_comma(sell_price);
        sell_price_obj.val(cma_sell_price);
        let total_price = 0;
        sell_price_objs.each(function(){
            let sel_price = Number($(this).val().replace(/,/g,''));
            // console.log(sel_price);
            total_price += sel_price;
        });
        let cma_total_price = thousand_comma(total_price);
        od_cart_price_obj.val(cma_total_price);
    }
}

function set_date(today)
{
    <?php
    $date_term = date('w', G5_SERVER_TIME);
    $week_term = $date_term + 7;
    $last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
    ?>
    if (today == "오늘") {
        document.getElementById("fr_date").value = "<?php echo G5_TIME_YMD; ?>";
        document.getElementById("to_date").value = "<?php echo G5_TIME_YMD; ?>";
    } else if (today == "어제") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
    } else if (today == "이번주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "이번달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "지난주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
    } else if (today == "지난달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
    } else if (today == "전체") {
        document.getElementById("fr_date").value = "";
        document.getElementById("to_date").value = "";
    }
}

function form01_submit(f)
{
    if (!is_checked("chk[]") && !is_checked("chk2[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            f.action = "./order_list_delete.php";
            return true;
        }
        return false;
    }

    f.action = "./order_list_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>