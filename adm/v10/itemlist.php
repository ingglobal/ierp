<?php
$sub_menu = '960220';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '부품관리';
include_once('./_top_menu_item.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// 분류
$ca_list  = '<option value="">선택</option>'.PHP_EOL;
$sql = " select * from {$g5['g5_shop_category_table']} ";
// if ($is_admin != 'super')
//     $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
$sql .= " order by ca_order, ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;
    $nbsp = '';
    
    $row['ca_p_name'] = '';
    if($len){
        $row['ca_p_id'] = substr($row['ca_id'],0,2);
        $ca_p_name = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '".$row['ca_p_id']."' ");
        $row['ca_p_name'] = $ca_p_name['ca_name'].'&nbsp;>';
    }
    
    for ($i=0; $i<$len; $i++) {
        $nbsp .= '&nbsp;';
    }
    $ca_list .= '<option value="'.$row['ca_id'].'">'.$row['ca_p_name'].$nbsp.$row['ca_name'].'</option>'.PHP_EOL;
}

$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

if ($sfl == "")  $sfl = "it_name";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where (a.ca_id = b.ca_id";
// if ($is_admin != 'super')
//     $sql_common .= " and b.ca_mb_id = '{$member['mb_id']}'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";


$sql  = " select *, ( select com_name from {$g5['company_table']} where com_idx = a.com_idx ) as com_name
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);
// echo $sql.'<br>';

//$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;save_stx='.$stx;

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
.td_mng {width:150px;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}

#flist{position:relative;}
#flist .btn_s_cart{height:30px;line-height:30px;border:1px solid #ddd;background:#efefef;position:absolute;top:0;right:0;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">등록된 부품</span><span class="ov_num"> <?php echo $total_count; ?>건</span></span>
</div>
<?php
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>
<form name="flist" id="flist" class="local_sch01 local_sch">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sca" class="sound_only">분류선택</label>
<select name="sca" id="sca">
    <option value="">전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} order by ca_order, ca_id ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=sql_fetch_array($result1); $i++) {
        $len = strlen($row1['ca_id']) / 2 - 1;
        $nbsp = '';
        for ($i=0; $i<$len; $i++) $nbsp .= '&nbsp;&nbsp;&nbsp;';
        echo '<option value="'.$row1['ca_id'].'" '.get_selected($sca, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
    }
    ?>
</select>

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>부품명</option>
    <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>부품코드</option>
</select>

<label for="stx" class="sound_only">검색어</label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input">
<input type="submit" value="검색" class="btn_submit">
<a href="./order_cart.php" class="btn btn_s_cart">견적목록보기</a>
</form>

<div class="local_desc01 local_desc">
    <p>[담기] 버튼을 클릭하면 부품이 장바구니에 담깁니다. <a href="./order_cart.php">[장바구니 바로가기]</a> 장바구니에 담긴 부품들을 가격 조정하거나 혹은 수량을 조절한 후 견적을 따로 진행할 수 있습니다.</p>
</div>

<form name="fitemlistupdate" method="post" action="./itemlistupdate.php" onsubmit="return fitemlist_submit(this);" autocomplete="off" id="fitemlistupdate">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">부품 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">분류</th>
        <th scope="col" id="th_pc_title"><?php echo subject_sort_link('it_name', 'sca='.$sca); ?>부품명</a></th>
        <th scope="col"><?php echo subject_sort_link('it_id', 'sca='.$sca); ?>부품코드</a></th>
        <th scope="col" id="th_amt"><?php echo subject_sort_link('it_price', 'sca='.$sca); ?>판매가격</a></th>
        <th scope="col">매입가</th>
        <th scope="col">매입처</th>
        <th scope="col">재고</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $href = shop_item_url($row['it_id']);
        $bg = 'bg'.($i%2);
        // print_r2($row);

        $it_point = $row['it_point'];
        if($row['it_point_type'])
            $it_point .= '%';
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['it_id'] ?>">
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
        </td>
        <td class="td_sort"><!-- 분류 -->
            <select name="ca_id[<?php echo $i; ?>]" id="ca_id_<?php echo $i; ?>" style="width:100%;">
                <?php echo conv_selected_option($ca_list, $row['ca_id']); ?>
            </select>
        </td>
        <td headers="th_pc_title" class="td_input">
            <label for="name_<?php echo $i; ?>" class="sound_only">부품명</label>
            <input type="text" name="it_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>" id="name_<?php echo $i; ?>" required class="tbl_input required" size="30">
        </td>
        <td class="td_num">
            <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            <?php echo $row['it_id']; ?>
        </td>
        <td headers="th_amt" class="td_numbig td_input"><!-- 판매가격 -->
            <input type="text" name="it_price[<?php echo $i; ?>]" value="<?php echo $row['it_price']; ?>" id="price_<?php echo $i; ?>" class="tbl_input sit_amt" size="7">
        </td>
        <td headers="th_camt" class="td_numbig td_input"><!-- 매입가 -->
            <input type="text" name="it_buy_price[<?php echo $i; ?>]" value="<?php echo $row['it_buy_price']; ?>" id="cust_price_<?php echo $i; ?>" class="tbl_input sit_camt" size="7">
        </td>
        <td class="td_com">
            <select name="com_id[<?php echo $i; ?>]" id="com_id_<?php echo $i; ?>" style="width:100px;">
                <?=$g5['set_buyer_value_options']?>
            </select>
            <script>$('select[name="com_id[<?php echo $i; ?>]"]').val('<?=$row['com_idx']?>');</script>
        </td>
        <td headers="th_stock" class="td_numbig td_input"><!-- 재고 -->
            <input type="text" name="it_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['it_stock_qty']; ?>" id="stock_qty_<?php echo $i; ?>" class="tbl_input sit_qty" size="7">
        </td>
        <td class="td_mng">
            <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn_03"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>수정</a>
            <a href="./itemcopy.php?it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>" class="itemcopy btn btn_02" target="_blank"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>복사</a>
            <a href="javascript:" it_id="<?php echo $row['it_id']; ?>" class="itemcart btn btn_01">담기</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
    <a href="./itemlist_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <a href="javascript:" id="btn_excel_upload2" class="btn btn_03" style="display:none;">엑셀등록(비표준)</a>
    <a href="javascript:" id="btn_excel_upload" class="btn btn_03" style="margin-right:20px;display:no ne;">엑셀등록</a>
    <?php } ?>
    <input type="submit" name="act_button" value="선택담기" onclick="document.pressed=this.value" class="btn btn_02" style="margin-right:20px;">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if ($is_admin == 'super') { ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./itemform.php" class="btn btn_01">부품등록</a>
    <?php } ?>
</div>
<!-- <div class="btn_confirm01 btn_confirm">
    <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
</div> -->
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./itemlist_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 15px 10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>
<div id="modal02" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./itemlist_excel_upload2.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 15px 10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>

<script>
function fitemlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

// 엑셀등록 실행
function form02_submit(f) {
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}

$(function() {
    // 엑셀등록 버튼
    $( "#modal01" ).dialog({
        autoOpen: false
        , position: { my: "right-40 top-10", of: "#btn_excel_upload"}
    });
    $( "#modal02" ).dialog({
        autoOpen: false
        , position: { my: "right-40 top-10", of: "#btn_excel_upload2"}
    });
    $( "#btn_excel_upload" ).on( "click", function() {
        $( "#modal01" ).dialog( "open" );
    });
    $( "#btn_excel_upload2" ).on( "click", function() {
        $( "#modal02" ).dialog( "open" );
    });

    $(".itemcopy").click(function() {
        var href = $(this).attr("href");
        window.open(href, "copywin", "left=100, top=100, width=300, height=200, scrollbars=0");
        return false;
    });

    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#e6e6e6 ');
            
        },
        mouseleave: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });

    // 선택한 상품을 장바구니에 추가한다. ajax처리 (주문 정보도 함께 처리해야 함)
    $(".itemcart").click(function(e) {
        e.preventDefault();
        var it_id = $(this).attr('it_id');
        $.ajax({
        	url:g5_user_admin_url+'/ajax/cart.json.php',
        	type:'get', data:{"aj":"put","it_id":it_id},
        	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res) {
                //alert(res.sql);
                if(res.result == true) {
                    alert("견적목록에 담기 완료, 담긴 부품은 견적목록보기에서 확인하세요.");
                }
                else {
                    alert(res.msg);
                }				
            }, error:this_ajax_error	////-- 디버깅 Ajax --//
        });

    });

});

function excelform(url)
{
    var opt = "width=600,height=450,left=10,top=10";
    window.open(url, "win_excel", opt);
    return false;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
