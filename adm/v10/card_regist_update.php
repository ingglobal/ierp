<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
// print_r2($_POST);
if(!$_POST['crd_code']) alert('카드사를 선택해 주세요.');
if(!$_POST['crd_no']) alert('카드번호 입력해 주세요.');
if(!$_POST['crd_expire_month']) alert('만기월을 선택해 주세요.');
if(!$_POST['crd_expire_year']) alert('만기년을 선택해 주세요.');
// if(!$_POST['crd_status']) alert('상태값을 선택해 주세요.');

$crd_no = strip_tags($_POST['crd_no']);
$crd_no = trim($crd_no);
$crd_no = str_replace('&nbsp;','',$crd_no);
$crd_memo = strip_tags($_POST['crd_memo']);
$crd_memo = trim($crd_memo);
$crd_memo = str_replace('&nbsp;','',$crd_memo);
// $pcu_start_km = preg_replace("/,/","",$_POST['pcu_start_km']);
/*
$mb = get_table_meta('member','mb_id',$member['mb_id']);
if($mb['mb_oil_type'] != $pcu_oil_type || !$mb['mb_oil_type']){
    meta_update(array("mta_db_table"=>"member","mta_db_id"=>$member['mb_id'],"mta_key"=>'mb_oil_type',"mta_value"=>$pcu_oil_type));
}
*/
$sql = " INSERT INTO {$g5['card_table']} SET
          crd_code = '{$crd_code}'
          , crd_no = '{$crd_no}' 
          , crd_expire_month = '{$crd_expire_month}'  
          , crd_expire_year = '{$crd_expire_year}'  
          , crd_memo = '{$crd_memo}'
          , crd_status = 'ok'
          , crd_reg_dt = '".G5_TIME_YMDHIS."' 
          , crd_update_dt = '".G5_TIME_YMDHIS."' 
";
sql_query($sql,1);

goto_url('./card_list.php?'.$qstr, false);