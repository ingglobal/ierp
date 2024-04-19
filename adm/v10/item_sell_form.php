<?php
$sub_menu = '960226';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/iteminfo.lib.php');

auth_check($auth[$sub_menu], "w");

$html_title = "부품 ";

if ($w == "")
{
    $html_title .= "등록";

    // 옵션은 쿠키에 저장된 값을 보여줌. 다음 입력을 위한것임
    //$it[ca_id] = _COOKIE[ck_ca_id];
    $it['ca_id'] = get_cookie("ck_ca_id");
    $it['ca_id2'] = get_cookie("ck_ca_id2");
    $it['ca_id3'] = get_cookie("ck_ca_id3");
    if (!$it['ca_id'])
    {
        $sql = " select ca_id from {$g5['g5_shop_category_table']} WHERE ca_id LIKE '7m%' order by ca_order, ca_id limit 1 ";
        $row = sql_fetch($sql);
        if (!$row['ca_id'])
            alert("등록된 분류가 없습니다. 우선 분류를 등록하여 주십시오.", './categorylist.php');
        $it['ca_id'] = $row['ca_id'];
    }
    //$it[it_maker]  = stripslashes($_COOKIE[ck_maker]);
    //$it[it_origin] = stripslashes($_COOKIE[ck_origin]);
    $it['it_maker']  = stripslashes(get_cookie("ck_maker"));
    $it['it_origin'] = stripslashes(get_cookie("ck_origin"));
}
else if ($w == "u")
{
    $html_title .= "수정";

    if ($is_admin != 'super')
    {
        $sql = " select it_id from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['it_id'])
            alert("\'{$member['mb_id']}\' 님께서 수정 할 권한이 없는 부품입니다.");
    }

    $it = get_shop_item($it_id);

    if(!$it)
        alert('부품정보가 존재하지 않습니다.');

    if (!$ca_id)
        $ca_id = $it['ca_id'];

    $sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);
}
else
{
    alert();
}

$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;

$g5['title'] = $html_title;
include_once ('./_head.php');

// 분류리스트
$category_select = '';
$script = '';
$sql = " select * from {$g5['g5_shop_category_table']} ";
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
$sql .= ($is_admin != 'super') ? " ca_id LIKE '7m%' " : " where ca_id LIKE '7m%' ";
$sql .= " order by ca_order, ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;

    $nbsp = "";
    for ($i=0; $i<$len; $i++)
        $nbsp .= "&nbsp;&nbsp;&nbsp;";

    $category_select .= "<option value=\"{$row['ca_id']}\">$nbsp{$row['ca_name']}</option>\n";

    $script .= "ca_use['{$row['ca_id']}'] = {$row['ca_use']};\n";
    $script .= "ca_stock_qty['{$row['ca_id']}'] = {$row['ca_stock_qty']};\n";
    //$script .= "ca_explan_html['$row[ca_id]'] = $row[ca_explan_html];\n";
    $script .= "ca_sell_email['{$row['ca_id']}'] = '{$row['ca_sell_email']}';\n";
}

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>

<form name="fitemform" action="./item_sell_form_update.php" method="post" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="return fitemformcheck(this)">

<input type="hidden" name="codedup" value="<?php echo $default['de_code_dup_use']; ?>">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod"  value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx"  value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<section id="anc_sitfrm_ini">
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption>기본정보 입력</caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_3">
            </colgroup>
            <tbody>
            <tr>
                <th scope="row"><label for="ca_id">분류</label></th>
                <td colspan="2">
                    <select name="ca_id" id="ca_id" onchange="categorychange(this.form)">
                        <option value="">선택하세요</option>
                        <?php echo conv_selected_option($category_select, $it['ca_id']); ?>
                    </select>
                    <script>
                        var ca_use = new Array();
                        var ca_stock_qty = new Array();
                        //var ca_explan_html = new Array();
                        var ca_sell_email = new Array();
                        var ca_opt1_subject = new Array();
                        var ca_opt2_subject = new Array();
                        var ca_opt3_subject = new Array();
                        var ca_opt4_subject = new Array();
                        var ca_opt5_subject = new Array();
                        var ca_opt6_subject = new Array();
                        <?php echo "\n$script"; ?>
                    </script>
                </td>
            </tr>
            <tr>
                <th scope="row">부품코드</th>
                <td colspan="2">
                    <?php if ($w == '') { // 추가 ?>
                        <!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
                        <!-- <input type=text class=required name=it_id value="<?php echo 10000000000-time()?>" size=12 maxlength=10 required> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
                        <?php echo help("부품의 코드는 10자리 숫자로 자동생성합니다."); ?>
                        <input type="text" name="it_id" value="<?php echo time(); ?>" id="it_id" required class="frm_input required" size="20" maxlength="20">
                        <!-- <?php if ($default['de_code_dup_use']) { ?><button type="button" class="btn_frmline" onclick="codedupcheck(document.all.it_id.value)">중복검사</a><?php } ?> -->
                    <?php } else { ?>
                        <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
                        <span class="frm_ca_id"><?php echo $it['it_id']; ?></span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_maker">매입처</label></th>
                <td colspan="2">
                    <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
                    <select name="com_idx" id="com_idx">
                        <?=$g5['set_buyer_value_options']?>
                    </select>
                    <script>$('select[name="com_idx"]').val('<?=$it['com_idx']?>');</script>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_name">부품명</label></th>
                <td colspan="2">
                    <input type="text" name="it_name" value="<?php echo get_text(cut_str($it['it_name'], 250, "")); ?>" id="it_name" required class="frm_input required" size="30" maxlength="40">
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_basic">간단설명</label></th>
                <td>
                    <input type="text" name="it_basic" value="<?php echo get_text(html_purifier($it['it_basic'])); ?>" id="it_basic" class="frm_input" size="50" maxlength="60">
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_basic" value="1" id="chk_ca_it_basic">
                    <label for="chk_ca_it_basic">분류적용</label>
                    <input type="checkbox" name="chk_all_it_basic" value="1" id="chk_all_it_basic">
                    <label for="chk_all_it_basic">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_price">판매가격</label></th>
                <td>
                    <input type="text" name="it_price" value="<?php echo $it['it_price']; ?>" id="it_price" class="frm_input" size="8"> 원
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_price" value="1" id="chk_ca_it_price">
                    <label for="chk_ca_it_price">분류적용</label>
                    <input type="checkbox" name="chk_all_it_price" value="1" id="chk_all_it_price">
                    <label for="chk_all_it_price">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_buy_price">매입가</label></th>
                <td>
                    <input type="text" name="it_buy_price" value="<?php echo $it['it_buy_price']; ?>" id="it_buy_price" class="frm_input" size="8"> 원
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_buy_price" value="1" id="chk_ca_it_buy_price">
                    <label for="chk_ca_it_buy_price">분류적용</label>
                    <input type="checkbox" name="chk_all_it_buy_price" value="1" id="chk_all_it_buy_price">
                    <label for="chk_all_it_buy_price">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row">입고일</th>
                <td>
                    <input type="text" name="it_buy_date" value="<?php echo $it['it_buy_date']; ?>" id="it_buy_date" class="frm_input" style="width:80px;">
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_buy_date" value="1" id="chk_ca_it_buy_date">
                    <label for="chk_ca_it_buy_date">분류적용</label>
                    <input type="checkbox" name="chk_all_it_buy_date" value="1" id="chk_all_it_buy_date">
                    <label for="chk_all_it_buy_date">전체적용</label>
                </td>
            </tr>
            <tr style="display:no ne;">
                <th scope="row"><label for="it_stock_qty">재고수량</label></th>
                <td>
                    <?php echo help("<b>주문관리에서 부품별 상태 변경에 따라 자동으로 재고를 가감합니다.</b> 재고는 규격/색상별이 아닌, 부품별로만 관리됩니다.<br>재고수량을 0으로 설정하시면 품절부품으로 표시됩니다."); ?>
                    <input type="text" name="it_stock_qty" value="<?=$it['it_stock_qty']?>" id="it_stock_qty" class="frm_input" size="8"> 개
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_stock_qty" value="1" id="chk_ca_it_stock_qty">
                    <label for="chk_ca_it_stock_qty">분류적용</label>
                    <input type="checkbox" name="chk_all_it_stock_qty" value="1" id="chk_all_it_stock_qty">
                    <label for="chk_all_it_stock_qty">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_notax">부품과세 유형</label></th>
                <td>
                    <?php echo help("부품의 과세유형(과세, 비과세)을 설정합니다."); ?>
                    <select name="it_notax" id="it_notax">
                        <option value="0"<?php echo get_selected('0', $it['it_notax']); ?>>과세</option>
                        <option value="1"<?php echo get_selected('1', $it['it_notax']); ?>>비과세</option>
                    </select>
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_notax" value="1" id="chk_ca_it_notax">
                    <label for="chk_ca_it_notax">분류적용</label>
                    <input type="checkbox" name="chk_all_it_notax" value="1" id="chk_all_it_notax">
                    <label for="chk_all_it_notax">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_use">견적가능</label></th>
                <td>
                    <?php echo help("잠시 중단하거나 재고가 없을 경우에 체크를 해제해 놓으면 출력되지 않으며, 견적할 수 없는 상품이 됩니다."); ?>
                    <input type="checkbox" name="it_use" value="1" id="it_use" <?php echo ($it['it_use']) ? "checked" : ""; ?>> 예
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_use" value="1" id="chk_ca_it_use">
                    <label for="chk_ca_it_use">분류적용</label>
                    <input type="checkbox" name="chk_all_it_use" value="1" id="chk_all_it_use">
                    <label for="chk_all_it_use">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_order">출력순서</label></th>
                <td>
                    <?php echo help("숫자가 작을 수록 상위에 출력됩니다."); ?>
                    <input type="text" name="it_order" value="<?php echo $it['it_order']; ?>" id="it_order" class="frm_input" size="12">
                </td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_order" value="1" id="chk_ca_it_order">
                    <label for="chk_ca_it_order">분류적용</label>
                    <input type="checkbox" name="chk_all_it_order" value="1" id="chk_all_it_order">
                    <label for="chk_all_it_order">전체적용</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="it_shop_memo">메모</label></th>
                <td><textarea name="it_shop_memo" id="it_shop_memo"><?php echo html_purifier($it['it_shop_memo']); ?></textarea></td>
                <td class="td_grpset">
                    <input type="checkbox" name="chk_ca_it_shop_memo" value="1" id="chk_ca_it_shop_memo">
                    <label for="chk_ca_it_shop_memo">분류적용</label>
                    <input type="checkbox" name="chk_all_it_shop_memo" value="1" id="chk_all_it_shop_memo">
                    <label for="chk_all_it_shop_memo">전체적용</label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</section>


<div class="btn_fixed_top">
    <a href="./item_sell_list.php?<?php echo $qstr; ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>


<script>
var f = document.fitemform;

<?php if ($w == 'u') { ?>
$(".banner_or_img").addClass("sit_wimg");
$(function() {

	$("#it_buy_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    $(".sit_wimg_view").bind("click", function() {
        var sit_wimg_id = $(this).attr("id").split("_");
        var $img_display = $("#"+sit_wimg_id[1]);

        $img_display.toggle();

        if($img_display.is(":visible")) {
            $(this).text($(this).text().replace("확인", "닫기"));
        } else {
            $(this).text($(this).text().replace("닫기", "확인"));
        }

        var $img = $("#"+sit_wimg_id[1]).children("img");
        var width = $img.width();
        var height = $img.height();
        if(width > 700) {
            var img_width = 700;
            var img_height = Math.round((img_width * height) / width);

            $img.width(img_width).height(img_height);
        }
    });
    $(".sit_wimg_close").bind("click", function() {
        var $img_display = $(this).parents(".banner_or_img");
        var id = $img_display.attr("id");
        $img_display.toggle();
        var $button = $("#it_"+id+"_view");
        $button.text($button.text().replace("닫기", "확인"));
    });
});
<?php } ?>

function codedupcheck(id)
{
    if (!id) {
        alert('부품코드를 입력하십시오.');
        f.it_id.focus();
        return;
    }

    var it_id = id.replace(/[A-Za-z0-9\-_]/g, "");
    if(it_id.length > 0) {
        alert("부품코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.");
        return false;
    }

    $.post(
        "./codedupcheck.php",
        { it_id: id },
        function(data) {
            if(data.name) {
                alert("코드 '"+data.code+"' 는 '".data.name+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
                return false;
            } else {
                alert("'"+data.code+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
                document.fitemform.codedup.value = '';
            }
        }, "json"
    );
}

function fitemformcheck(f)
{
    if (!f.ca_id.value) {
        alert("기본분류를 선택하십시오.");
        f.ca_id.focus();
        return false;
    }

    if (f.w.value == "") {
        var error = "";
        $.ajax({
            url: "./ajax.it_id.php",
            type: "POST",
            data: {
                "it_id": f.it_id.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                error = data.error;
            }
        });

        if (error) {
            alert(error);
            return false;
        }
    }

    if(f.it_point_type.value == "1" || f.it_point_type.value == "2") {
        var point = parseInt(f.it_point.value);
        if(point > 99) {
            alert("포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.");
            return false;
        }
    }

    if(parseInt(f.it_sc_type.value) > 1) {
        if(!f.it_sc_price.value || f.it_sc_price.value == "0") {
            alert("기본배송비를 입력해 주십시오.");
            return false;
        }

        if(f.it_sc_type.value == "2" && (!f.it_sc_minimum.value || f.it_sc_minimum.value == "0")) {
            alert("배송비 상세조건의 주문금액을 입력해 주십시오.");
            return false;
        }

        if(f.it_sc_type.value == "4" && (!f.it_sc_qty.value || f.it_sc_qty.value == "0")) {
            alert("배송비 상세조건의 주문수량을 입력해 주십시오.");
            return false;
        }
    }

    // 관련부품처리
    var item = new Array();
    var re_item = it_id = "";

    $("#reg_relation input[name='re_it_id[]']").each(function() {
        it_id = $(this).val();
        if(it_id == "")
            return true;

        item.push(it_id);
    });

    if(item.length > 0)
        re_item = item.join();

    $("input[name=it_list]").val(re_item);

    // 이벤트처리
    var evnt = new Array();
    var ev = ev_id = "";

    $("#reg_event_list input[name='ev_id[]']").each(function() {
        ev_id = $(this).val();
        if(ev_id == "")
            return true;

        evnt.push(ev_id);
    });

    if(evnt.length > 0)
        ev = evnt.join();

    $("input[name=ev_list]").val(ev);

    <?php echo get_editor_js('it_head_html'); ?>
    <?php echo get_editor_js('it_tail_html'); ?>
    <?php echo get_editor_js('it_mobile_head_html'); ?>
    <?php echo get_editor_js('it_mobile_tail_html'); ?>

    return true;
}

function categorychange(f)
{
    var idx = f.ca_id.value;

    if (f.w.value == "" && idx)
    {
        f.it_use.checked = ca_use[idx] ? true : false;
        f.it_stock_qty.value = ca_stock_qty[idx];
        f.it_sell_email.value = ca_sell_email[idx];
    }
}

categorychange(document.fitemform);
</script>

<?php
include_once ('./_tail.php');
?>
