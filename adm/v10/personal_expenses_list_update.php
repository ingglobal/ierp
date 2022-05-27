<?php
$sub_menu = "960640";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'personal_expenses';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명

// print_r2($_POST);exit;

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}


if ($_POST['act_button'] == "선택수정") {

    foreach($_POST['chk'] as $pep_idx_v){
        $_POST['pep_price'][$pep_idx_v] = preg_replace("/,/","",$_POST['pep_price'][$pep_idx_v]);

        $sql = " UPDATE {$g5_table_name} SET
                    pep_date = '".$_POST['pep_date'][$pep_idx_v]."'
                    ,pep_subject = '".trim(strip_tags($_POST['pep_subject'][$pep_idx_v]))."'
                    ,pep_content = '".trim(strip_tags($_POST['pep_content'][$pep_idx_v]))."'
                    ,pep_price = '".$_POST['pep_price'][$pep_idx_v]."'
                    ,pep_status = '".$_POST['pep_status'][$pep_idx_v]."'
                    ,pep_update_dt = '".G5_TIME_YMDHIS."'
                WHERE pep_idx = '".$pep_idx_v."'
        ";
        sql_query($sql,1);
    }

} else if ($_POST['act_button'] == "선택삭제") {

    foreach($_POST['chk'] as $pep_idx_v){
        delete_jt_file(array("fle_db_table"=>"personal_expenses"
            ,"fle_db_id"=>$pep_idx_v
            ,"fle_type"=>'pep_img'
            ,"fle_sort"=>0
            ,"fle_delete"=>1
        ));

        $sql = " UPDATE {$g5_table_name} SET
                    pep_status = 'trash'
                WHERE pep_idx = '".$pep_idx_v."'
        ";
        // echo $sql."<br>";
        sql_query($sql,1);
    }
}


if($year_month) $qstr .= "&year_month=".$year_month;
if($mb_name2) $qstr .= "&mb_name2=".$mb_name2;
if($sst2) $qstr .= "&sst2=".$sst2;
if($sod2) $qstr .= "&sod2=".$sod2;

goto_url('./'.$fname.'.php?'.$qstr);