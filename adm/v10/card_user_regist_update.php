<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
// print_r2($_POST);
if(!$_POST['mb_id']) alert('사용자를 선택해 주세요.');
if(!$_POST['crd_idx']) alert('카드를 선택해 주세요.');
if(!$_POST['csr_start_date']) alert('지급일을 선택해 주세요.');
// if(!$_POST['crd_status']) alert('상태값을 선택해 주세요.');

$csr_memo = strip_tags($_POST['csr_memo']);
$csr_memo = trim($csr_memo);
$csr_memo = str_replace('&nbsp;','',$csr_memo);

$chk_sql = " SELECT COUNT(*) AS cnt, mb_name FROM {$g5['card_user_table']} csr
                LEFT JOIN {$g5['member_table']} mb ON csr.mb_id = mb.mb_id
            WHERE crd_idx = '{$crd_idx}'
                AND csr_status IN ('ok','pending','expire','hide')
";
// echo $chk_sql;
$chk_res = sql_fetch($chk_sql);
if($chk_res['cnt'])
    alert($chk_res['mb_name'].'님께서 이미 사용하고 계시는 카드입니다.');

/*
$mb = get_table_meta('member','mb_id',$member['mb_id']);
if($mb['mb_oil_type'] != $pcu_oil_type || !$mb['mb_oil_type']){
    meta_update(array("mta_db_table"=>"member","mta_db_id"=>$member['mb_id'],"mta_key"=>'mb_oil_type',"mta_value"=>$pcu_oil_type));
}
*/
$sql = " INSERT INTO {$g5['card_user_table']} SET
          mb_id = '{$mb_id}'
          , crd_idx = '{$crd_idx}' 
          , csr_start_date = '{$csr_start_date}'  
          , csr_memo = '{$csr_memo}'
          , csr_status = 'ok'
          , csr_reg_dt = '".G5_TIME_YMDHIS."' 
          , csr_update_dt = '".G5_TIME_YMDHIS."' 
";

sql_query($sql,1);

$qstr .= $qstr.'&sch_crd_idx='.$sch_crd_idx;
goto_url('./card_user_list.php?'.$qstr, false);