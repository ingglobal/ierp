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



$where = array();
$where[] = " od_status IN ('주문','전송') ";

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
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";
$sort3 = ", ct_id";

$sql_common = " FROM {$g5['g5_shop_cart_table']} ct
                    LEFT JOIN {$g5['g5_shop_item_table']} it ON ct.it_id = it.it_id
                    LEFT JOIN {$g5['g5_shop_order_table']} od ON ct.od_id = od.od_id
                    LEFT JOIN {$g5['companyreseller_table']} com ON com.com_idx = od.com_idx
                    LEFT JOIN {$g5['companyreseller_member_table']} cmm ON com.com_idx = cmm.com_idx
                    LEFT JOIN {$g5['member_table']} mb ON cmm.mb_id = mb.mb_id
                    $sql_search
";

$sql = " select count(od_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " SELECT ct_id
            , ct.it_id
            , ct.it_name
            , ct_status
            , ct_price
            , it_price
            , od.od_id
            , ct_qty
            , ct_time
            , od.mb_id
            , od_name
            , od_cart_count
            , od_cart_price
            , od_time
            , mb_id_saler
            , mb_name AS com_mb_name
            , mb.mb_id AS com_mb_id
            , mb_hp AS com_mb_hp
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
    // if(!is_array($od_arr[$row['od_id']])){
    //     $od_arr[$row['od_id']] = array();
    // }
    $smb = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_saler']}' ");
    $od_arr[$row['od_id']]['mb_id_saler'] = $row['mb_id_saler'];
    $od_arr[$row['od_id']]['com_idx'] = $row['com_idx'];
    $od_arr[$row['od_id']]['com_name'] = $row['com_name'];
    $od_arr[$row['od_id']]['mb_id_saler'] = $row['mb_id_saler'];
    $od_arr[$row['od_id']]['mb_name_saler'] = $smb['mb_name'];
    $od_arr[$row['od_id']]['com_level'] = $g5['set_com_level_value'][$row['com_level']].'('.$g5['set_com_dc_rate_value'][$row['com_level']].' %)';
    $od_arr[$row['od_id']]['com_mb_id'] = $row['com_mb_id'];
    $od_arr[$row['od_id']]['com_mb_name'] = $row['com_mb_name'];
    $od_arr[$row['od_id']]['com_mb_hp'] = $row['com_mb_hp'];
    $od_arr[$row['od_id']]['od_cart_count'] = $row['od_cart_count'];
    $od_arr[$row['od_id']]['od_cart_price'] = $row['od_cart_price'];
    $od_arr[$row['od_id']]['od_time'] = substr($row['od_time'],0,10);
    if(!is_array($od_arr[$row['od_id']]['ct_arr'])){
        $od_arr[$row['od_id']]['ct_arr'] = array();
    }
    $ct_arr = array(
        'ct_id' => $row['ct_id']
        , 'it_id' => $row['it_id']
        , 'it_name' => $row['it_name']
        , 'it_price' => $row['it_price']
        , 'ct_qty' => $row['ct_qty']
        , 'ct_price' => $row['ct_price']
        , 'ct_sell_price' => $row['ct_price'] * $row['ct_qty']
    );
    array_push($od_arr[$row['od_id']]['ct_arr'], $ct_arr);
    $od_arr[$row['od_id']]['rowspan'] = count($od_arr[$row['od_id']]['ct_arr']);
}

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
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
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
    <strong>접수일자</strong>
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

<div class="local_desc01 local_desc" style="display:none;">
    <p><span style="color:red;">상품변경은 불가</span>합니다. 상변인 경우 전체 신청을 취소하고 다시 신청해 주세요.</p>
    <p></p>
</div>


<form name="form01" method="post" action="./order_list_update.php" onsubmit="return form01_submit(this);" autocomplete="off" id="form01">
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
<input type="hidden" name="sort3" value="<?php echo $sort3; ?>">
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
        <th scope="col" id="th_ordnum"><a href="<?php echo title_sort("od_id", 1)."&amp;$qstr1"; ?>">주문번호</a></th>
        <th scope="col" id="th_saler">영업자</th>
        <th scope="col" id="th_com">판매처</th>
        <th scope="col" id="th_com_level">판매처등급</th>
        <th scope="col" id="th_com_mb_name">판매처담당자</th>
        <th scope="col" id="th_com_mb_hp">담당자연락처</th>
        <th scope="col" id="th_odrcnt">판매품목건수</th>
        <th scope="col" id="th_sum_price">주문합계</th>
        <th scope="col" id="th_od_time">접수일자</th>
        <th scope="col">
            <label for="chkall2" class="sound_only">제품 전체</label>
            <input type="checkbox" name="chkall2" value="1" id="chkall2" onclick="check_all(this.form)">
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
        $rowspan = ($row['rowspan']) ? ' rowspan="'.$row['rowspan'].'"' : '';
        $bg = 'bg'.($i%2);
    ?>
    <tr class="orderlist<?php echo ' '.$bg; ?>" tr_id="<?=$od_id?>">
        <td class="td_chk"<?=$rowspan?>>
            <input type="hidden" name="od_id[<?=$i?>]" value="<?=$od_id?>" id="od_id_<?=$i?>">
            <label for="chk_<?=$i?>" class="sound_only">접수번호 <?=$od_id?></label>
            <input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
        </td>
        <td headers="th_ordnum" class="td_odrnum2"<?=$rowspan?>><!-- 주문번호 -->
            <?php echo $od_id; ?>
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
        <td class="td_num td_numsum"<?=$rowspan?>><?php echo number_format($row['od_cart_price']); ?></td><!-- 주문합계 -->
        <td class="td_od_time"<?=$rowspan?>><?php echo substr($row['od_time'],0,10); ?></td>
        <?php
        for($k=0;$k<count($row['ct_arr']);$k++){
            $row2 = $row['ct_arr'][$k];
            if($k >= 1) echo '<tr class="'.$bg.'">'.PHP_EOL;
        ?>
        <td class="td_chk2">
            <input type="hidden" name="ct_id[<?=$j?>]" value="<?=$row2['ct_id']?>" id="ct_id_<?=$j?>">
            <label for="chk2_<?=$j?>" class="sound_only">카트번호 <?=$row2['ct_id']?></label>
            <input type="checkbox" name="chk2[]" value="<?=$j?>" id="chk2_<?=$i?>">
        </td>
        <td headers="th_item_name"><?=$row2['it_name']?></td><!-- 제품명 -->
        <td headers="th_item_price" style="text-align:right;"><?=number_format($row2['it_price'])?></td><!--기준단가 -->
        <td headers="th_rate">
            <?php
            $rate = (($row2['it_price'] - $row2['ct_price']) / $row2['it_price']) * 100;
            echo number_format($rate,0,'','');
            ?> %
        </td><!-- 할인률 -->
        <td headers="th_sell_price" style="text-align:right;"><?=number_format($row2['ct_price'])?></td><!--기준단가 -->
        <td headers="th_qty"><?=$row2['ct_qty']?>개</td><!-- 수량 -->
        <td headers="th_price" style="text-align:right;">
            <?php echo number_format($row2['ct_sell_price']); ?>
        </td><!-- 판매가 -->
        <td class="td_mng td_mng_s"><!-- 보기 -->
            <a href="javascript:" ct_id="<?=$row['ct_id']?>" class="btn btn_02">삭제</a>
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
    <!-- <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02"> -->
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // ct_history 정보 수정 (날짜 변경)
    $(document).on('click','.ct_edit_btn',function(){
        var this_ct_id = $(this).attr('ct_id');
        var href = './order_form_cart_edit.popup.php?ct_id='+this_ct_id+'&w=1';
        cartmemowin = window.open(href, "cartmemowin", "left=100, top=100, width=600, height=650, scrollbars=yes");
        cartmemowin.focus();
        return false;
    });
    
    // 마우스 hover 설정
    // $(".tbl_head01 tbody tr").on({
    //     mouseenter: function () {
    //         //stuff to do on mouse enter
    //         //console.log($(this).attr('od_id')+' mouseenter');
    //         //$(this).find('td').css('background','red');
    //         $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#e6e6e6 ');
            
    //     },
    //     mouseleave: function () {
    //         //stuff to do on mouse leave
    //         //console.log($(this).attr('od_id')+' mouseleave');
    //         //$(this).find('td').css('background','unset');
    //         $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    //     }    
    // });
    
    
    // 값이 바뀌면 체크해 준다.
//    $('input[name^="mb_id_saler["]').on('change', function() {
    $(document).on('change', 'input[id^=mb_id_saler_]', function() {
//    $("input[id^=mb_id_saler_]").change(function() {
        console.log( $(this).closest('tr').html() );
        $(this).closest('tr').prev().find('input[id^=chk_]').attr('checked','checked');
        return false;
    });
    
    
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    // 주문상품보기
    $(".orderitem").on("click", function() {
        var $this = $(this);
        var od_id = $this.text().replace(/[^0-9]/g, "");

        if($this.next("#orderitemlist").size())
            return false;

        $("#orderitemlist").remove();

        $.post(
            "ajax/ajax.orderitem.php",
            { od_id: od_id },
            function(data) {
                $this.after("<div id=\"orderitemlist\"><div class=\"itemlist\"></div></div>");
                $("#orderitemlist .itemlist")
                    .html(data)
                    .append("<div id=\"orderitemlist_close\"><button type=\"button\" id=\"orderitemlist-x\" class=\"btn_frmline\">닫기</button></div>");
            }
        );

        return false;
    });

    // 상품리스트 닫기
    $(".orderitemlist-x").on("click", function() {
        $("#orderitemlist").remove();
    });

    $("body").on("click", function() {
        $("#orderitemlist").remove();
    });

	// 정산 버튼 클릭
	$(".btn_setting").click(function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		win_order_share = window.open(url, "win_order_share", "left=500,top=50,width=520,height=700,scrollbars=1");
        win_order_share.focus();
	});
});

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
</script>

<script>
function form01_submit(f)
{
    if (!is_checked("chk[]")) {
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