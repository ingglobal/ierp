<?php
$sub_menu = "960640";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');
// print_r2($_POST);
/*
    [mb_id] => tomasjoa
    [pep_date] => 2022-05-18
    [pep_subject] => nocar
    [pep_content] => sfs
    [pep_status] => diesel
*/
if(!$_POST['pep_date']) alert('사용일을 입력해 주세요.');
if(!$_POST['pep_subject']) alert('목적지를 입력해 주세요.');
if(!$_POST['pep_content']) alert('사용내용을 입력해 주세요.');
if(!$_POST['pep_price']) alert('사용금액을 입력해 주세요.');

$pep_subject = strip_tags($_POST['pep_subject']);
$pep_subject = trim($pep_subject);
$pep_subject = str_replace('&nbsp;','',$pep_subject);
$pep_content = strip_tags($_POST['pep_content']);
$pep_content = trim($pep_content);
$pep_content = str_replace('&nbsp;','',$pep_content);
$pep_price = preg_replace("/,/","",$_POST['pep_price']);


$sql = " INSERT INTO {$g5['personal_expenses_table']} SET
          mb_id = '{$member['mb_id']}'
          , pep_date = '{$pep_date}' 
          , pep_subject = '{$pep_subject}'  
          , pep_content = '{$pep_content}'  
          , pep_price = '{$pep_price}'
          , pep_status = 'pending'
          , pep_reg_dt = '".G5_TIME_YMDHIS."' 
          , pep_update_dt = '".G5_TIME_YMDHIS."' 
";
sql_query($sql,1);

if($year_month) $qstr .= "&year_month=".$year_month;
if($mb_name2) $qstr .= "&mb_name2=".$mb_name2;

goto_url('./personal_expenses_list.php?'.$qstr, false);