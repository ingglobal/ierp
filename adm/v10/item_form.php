<?php
$sub_menu = '960220';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],"d");


$it = get_table_meta('g5_shop_item','it_id',$it_id,'shop_item');
if(!$it)
    alert('상품정보가 존재하지 않습니다.');

if (!$ca_id)
    $ca_id = $it['ca_id'];
$ca = get_table_meta('g5_shop_category','ca_id',$ca_id,'shop_category');

$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;


$g5['title'] = '부품정보 수정';
include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

?>

<form name="form01" action="./item_form_update.php" method="post" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="return form01check(this)">

<input type="hidden" name="codedup" value="<?php echo $default['de_code_dup_use']; ?>">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod"  value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx"  value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<section id="anc_sitfrm_ini">
    <div class="local_desc02 local_desc">
        <p>상품 기본 설정은 [쇼핑몰관리 > 상품관리] 페이지에서 관리해 주세요.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <tbody>
        <tr>
            <th scope="row"><label for="it_name">상품명</label></th>
            <td colspan="3">
                <input type="text" name="it_name" value="<?php echo get_text(cut_str($it['it_name'], 250, "")); ?>" id="it_name" required class="frm_input required" size="95">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="it_basic">기본설명</label></th>
            <td colspan="3">
                <input type="text" name="it_basic" value="<?php echo get_text(html_purifier($it['it_basic'])); ?>" id="it_basic" class="frm_input" size="95">
            </td>
        </tr>
        <tr>
            <th scope="row">상품코드</th>
            <td>
                <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
                <span class="frm_ca_id"><?php echo $it['it_id']; ?></span>
            </td>
            <th scope="row">분류</th>
            <td>
                <span class="frm_ca_id"><?php echo $shop_category_name[$it['ca_id']]; ?></span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="it_tel_inq">견적형 상품</label></th>
            <td colspan="3">
                <input type="checkbox" name="it_tel_inq" value="1" id="it_tel_inq" <?php echo ($it['it_tel_inq']) ? "checked" : ""; ?>> 예
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="it_price">판매가격</label></th>
            <td>
                <input type="text" name="it_price" value="<?php echo $it['it_price']; ?>" id="it_price" class="frm_input" size="8"> 원
            </td>
            <th scope="row"><label for="it_order">출력순서(정렬번호)</label></th>
            <td>
                <input type="text" name="it_order" value="<?php echo $it['it_order']; ?>" id="it_order" class="frm_input" size="8">
            </td>
        </tr>
        <tr>
            <th scope="row">원가(수수료)</th>
            <td colspan="3">
                <?=help('확정금액이 있는 경우는 비율보다는 확정금액이 우선합니다. 비율인 경우 부가세를 뺀 금액에 대한 %입니다.');?>
                <input type="text" name="it_price_cost_rate" value="<?php echo $it['it_price_cost_rate']; ?>" id="it_price_cost_rate" class="frm_input" size="3"> %
                또는
                <input type="text" name="it_price_cost" value="<?php echo $it['it_price_cost']; ?>" id="it_price_cost" class="frm_input" size="8"> 원
            </td>
        </tr>
        <tr>
            <th scope="row">수당적용기준</th>
            <td colspan="3">
                <select name="it_share_type">
                    <option value="">공급가기준정산</option>
                    <option value="1">원가공제후정산</option>
				</select>
				<script>$("select[name=it_share_type] option[value='<?=$it['it_share_type']?>']").attr('selected','selected');</script>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="it_use">판매가능</label></th>
            <td>
                <input type="checkbox" name="it_use" value="1" id="it_use" <?php echo ($it['it_use']) ? "checked" : ""; ?>> 예
            </td>
            <th scope="row"><label for="it_soldout">상품품절</label></th>
            <td>
                <input type="checkbox" name="it_soldout" value="1" id="it_soldout" <?php echo ($it['it_soldout']) ? "checked" : ""; ?>> 예
            </td>
        </tr>
        <tr>
            <th scope="row">상품분리</th>
            <td>
                <input type="checkbox" name="it_cart_separate_yn" value="1" id="it_cart_separate_yn" <?php echo ($it['it_cart_separate_yn']) ? "checked" : ""; ?>> 예
            </td>
            <th scope="row">제작여부</th>
            <td>
                <input type="checkbox" name="it_make_yn" value="1" id="it_make_yn" <?php echo ($it['it_make_yn']) ? "checked" : ""; ?>> 예
            </td>
        </tr>
        <tr>
            <th scope="row">인센제외</th>
            <td colspan="3">
                <?=help('인센티브 계산 시 제외되는 상품인 경우 체크하세요.');?>
                <input type="checkbox" name="it_insen_no" value="1" id="it_insen_no" <?php echo ($it['it_insen_no']) ? "checked" : ""; ?>> 예
            </td>
        </tr>
        <tr>
            <th scope="row">매출0상품</th>
            <td colspan="3">
                <?=help('매출인정금액을 0으로 설정하는 상품 (충전 상품인 경우 충전 후 실제 소진 시에 개별 매출 반영됩니다.)');?>
                <input type="checkbox" name="it_sales_zero" value="1" id="it_sales_zero" <?php echo ($it['it_sales_zero']) ? "checked" : ""; ?>> 예
            </td>
        </tr>
        <tr>
            <th scope="row">가격0일때 주문가능</th>
            <td colspan="3">
                <?=help('판매가격이 0일 때도 신청이 가능하게 하려면 체크하세요. 연장 시 서비스 개념으로 제작되는 상품들이 주로 그 대상입니다.');?>
                <input type="checkbox" name="it_3" value="1" id="it_3" <?php echo ($it['it_3']) ? "checked" : ""; ?>> 예
            </td>
        </tr>
        <tr>
            <th scope="row">매출구분</th>
            <td>
                <select name="it_price_type">
					<option value="">매출구분선택</option>
					<?=$g5['set_sls_price_types_options']?>
				</select>
				<script>$('select[name=it_price_type] option[value=<?=$it['it_price_type']?>]').attr('selected','selected');</script>
            </td>
            <th scope="row">제작타입</th>
            <td>
                <select name="it_sit_type">
					<option value="">제작타입선택</option>
					<?=$g5['set_sit_types_options']?>
				</select>
				<script>$("select[name=it_sit_type] option[value='<?=$it['it_sit_type']?>']").attr('selected','selected');</script>
            </td>
        </tr>
        <tr>
            <th scope="row">상품유형1</th>
            <td colspan="3">
                <?php echo help("홈페이지, 검색광고, 블로그, 카페인, Y카트, sns상품, 원페이지, 우리펜션, 촬영, 기타광고, 카페인모바일제작, 차이나상품 등에 대한 구분입니다."); ?>
                <select name="it_sls_type1">
					<option value="">상품유형선택</option>
					<?=$g5['set_sls_type1_options']?>
				</select>
				<script>$("select[name=it_sls_type1] option[value='<?=$it['it_sls_type1']?>']").attr('selected','selected');</script>
            </td>
        </tr>
        <tr>
            <th scope="row">광고상품 구분</th>
            <td colspan="3">
                <?php echo help("광고상품인 경우 광고 상품의 종류를 선택하세요. 상품선택에 따라 광고대장접수 양식이 달라집니다."); ?>
                <select name="it_1">
					<option value="">광고상품구분선택</option>
					<?=$g5['set_ad_prod_types_options']?>
				</select>
				<script>$("select[name=it_1] option[value='<?=$it['it_1']?>']").attr('selected','selected');</script>
            </td>
        </tr>
        <tr>
            <th>상품노출설정</th>
            <td colspan="3">
                <a href="javascript:" id="btn_uncheck" class="btn_02 btn">체크해제</a>&nbsp;
                <a href="javascript:" id="btn_check" class="btn_02 btn">전체선택</a>
                <div>
                <?=$department_checkbox_options?>
                </div>
                <?//=$it['it_10']?>
                <script>
                    <?php
                    $it_1s = explode(",",$it['it_10']);
                    for($i=0;$i<sizeof($it_1s);$i++) {
                        //echo $it_1s[$i].'<br>';
                        echo "$('#set_department_idx_".preg_replace("/:/","",$it_1s[$i])."').prop('checked','checked');\n";
                    }
                    ?>
                    <?php if($w=='') { // 쓰기에서는 디폴트 전부다 체크 ?>
                    $('input[name^=set_department_idx]').prop('checked','checked');
                    <?php } ?>
                    
                    // 전체해제
                    $(document).on('click','#btn_uncheck',function(e){
                        e.preventDefault();
                        $('input[name^=set_department_idx]').prop('checked',false);
                    });
                
                    // 전체선택
                    $(document).on('click','#btn_check',function(e){
                        e.preventDefault();
                        $('input[name^=set_department_idx]').prop('checked','checked');
                        
                    });
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">계약내용</th>
            <td colspan="3">
                <?php echo help('치환 변수: {법인명} {업체명} {이름} / 내용이 존재하는 경우 계약서에 내용이 추가됩니다.'); ?>
                <?php echo editor_html("it_contract", get_text($it['it_contract'], 0)); ?>
            </td>
        </tr>
		</tbody>
        </table>
    </div>
</section>


<div class="btn_fixed_top">
    <a href="./item_list.php?<?php echo $qstr; ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>


<script>
function form01check(f)
{

    <?php echo get_editor_js("it_contract"); ?>
    <?php echo chk_editor_js("it_contract",0); ?>

    if (!f.ca_id.value) {
        alert("기본분류를 선택하십시오.");
        f.ca_id.focus();
        return false;
    }

    if(f.it_point_type.value == "1" || f.it_point_type.value == "2") {
        var point = parseInt(f.it_point.value);
        if(point > 99) {
            alert("포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.");
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
