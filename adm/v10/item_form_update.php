<?php
$sub_menu = '960220';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");
check_admin_token();

$it_name = strip_tags(trim($_POST['it_name']));
if ($it_name == "")
    alert("상품명을 입력해 주십시오.");

$it_10 = ':'.implode(":,:",$_REQUEST['set_department_idx']).':';

$sql_common = " it_name         = '$it_name',
				it_basic        = '$it_basic',
                it_order        = '$it_order',
                it_tel_inq      = '$it_tel_inq',
				it_price        = '$it_price',
				it_use          = '$it_use',
				it_soldout      = '$it_soldout',
				it_1            = '$it_1',
				it_3            = '$it_3',
				it_10            = '$it_10'
";

// 상품 수정만 가능합니다.
if ($w == "u") {

    $it = get_table_meta('g5_shop_item','it_id',$it_id,'shop_item');

    $sql = "UPDATE {$g5['g5_shop_item_table']} SET
                $sql_common
            WHERE it_id = '$it_id' ";
    sql_query($sql,1);
    //echo $sql.'<br>';

    // 상품분리, 제작여부, 원가
    $it_more = serialized_update('it_cart_separate_yn',$_POST['it_cart_separate_yn'],$it['it_more']);
    $it_more = serialized_update('it_make_yn',$_POST['it_make_yn'],$it_more);
    $it_more = serialized_update('it_price_cost_rate',$_POST['it_price_cost_rate'],$it_more);
    $it_more = serialized_update('it_price_cost',$_POST['it_price_cost'],$it_more);
    $it_more = serialized_update('it_sales_zero',$_POST['it_sales_zero'],$it_more);
    $it_more = serialized_update('it_price_type',$_POST['it_price_type'],$it_more);
    $it_more = serialized_update('it_sls_type1',$_POST['it_sls_type1'],$it_more);
    $it_more = serialized_update('it_share_type',$_POST['it_share_type'],$it_more);
    $it_more = serialized_update('it_sit_type',$_POST['it_sit_type'],$it_more);
    $it_more = serialized_update('it_insen_no',$_POST['it_insen_no'],$it_more);

    $ar['mta_db_table'] = 'shop_item';
    $ar['mta_db_id'] = $it_id;
    $ar['mta_key'] = 'it_more';
    $ar['mta_value'] = $it_more;
    meta_update($ar);
    unset($ar);


    // 메타 정보 업데이트
    $ar['mta_db_table'] = 'shop_item';
    $ar['mta_db_id'] = $it_id;
    $ar['mta_key'] = 'it_contract';
    $ar['mta_value'] = $it_contract;
    meta_update($ar);
    //print_r2($ar);
    unset($ar);
    
}



$qstr = "$qstr&amp;sca=$sca&amp;page=$page";

//exit;
goto_url("./item_form.php?w=u&amp;it_id=$it_id&amp;$qstr");
?>