<?php
$sub_menu = "960630";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
// print_r2($_POST);
/*
    [mb_id] => tomasjoa
    [pcu_date] => 2022-05-18
    [pcu_why] => nocar
    [pcu_reason] => sfs
    [pcu_start_km] => 234,234
    [pcu_arrival_km] => 23,423
    [pcu_oil_type] => diesel
*/
if(!$_POST['pcu_date']) alert('사용일을 입력해 주세요.');
if(!$_POST['pcu_reason']) alert('사용목적을 입력해 주세요.');
if(!$_POST['pcu_start_km']) alert('출발당시 km를 입력해 주세요.');
if(!$_POST['pcu_arrival_km']) alert('도착당시 km를 입력해 주세요.');
if(!$_POST['pcu_oil_type']) alert('유종을 선택해 주세요.');

$pcu_reason = strip_tags($_POST['pcu_reason']);
$pcu_reason = trim($pcu_reason);
$pcu_reason = str_replace('&nbsp;','',$pcu_reason);
$pcu_start_km = preg_replace("/,/","",$_POST['pcu_start_km']);
$pcu_arrival_km = preg_replace("/,/","",$_POST['pcu_arrival_km']);

$mb = get_table_meta('member','mb_id',$member['mb_id']);
if($mb['mb_oil_type'] != $pcu_oil_type || !$mb['mb_oil_type']){
    meta_update(array("mta_db_table"=>"member","mta_db_id"=>$member['mb_id'],"mta_key"=>'mb_oil_type',"mta_value"=>$pcu_oil_type));
}

$sql = " INSERT INTO {$g5['personal_caruse_table']} SET
          mb_id = '{$member['mb_id']}'
          , pcu_date = '{$pcu_date}' 
          , pcu_reason = '{$pcu_reason}'  
          , pcu_start_km = '{$pcu_start_km}'  
          , pcu_arrival_km = '{$pcu_arrival_km}'  
          , pcu_oil_type = '{$pcu_oil_type}'
          , pcu_status = 'pending'
          , pcu_reg_dt = '".G5_TIME_YMDHIS."' 
          , pcu_update_dt = '".G5_TIME_YMDHIS."' 
";
sql_query($sql,1);

if($year_month) $qstr .= "&year_month=".$year_month;
if($mb_name2) $qstr .= "&mb_name2=".$mb_name2;

goto_url('./personal_caruse_list.php?'.$qstr, false);