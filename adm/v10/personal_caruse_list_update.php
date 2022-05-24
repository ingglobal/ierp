<?php
$sub_menu = "960630";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'personal_caruse';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명

// print_r2($_POST);exit;

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}


if ($_POST['act_button'] == "선택수정") {

    foreach($_POST['chk'] as $pcu_idx_v){
        $_POST['pcu_start_km'][$pcu_idx_v] = preg_replace("/,/","",$_POST['pcu_start_km'][$pcu_idx_v]);
        $_POST['pcu_arrival_km'][$pcu_idx_v] = preg_replace("/,/","",$_POST['pcu_arrival_km'][$pcu_idx_v]);
        $adm_where = '';
        if($super_admin){
            if($_POST['pcu_status'][$pcu_idx_v] != 'ok'){
                $_POST['pcu_per_price'][$pcu_idx_v] = 0;
                $_POST['pcu_per_km'][$pcu_idx_v] = 0;
                $_POST['pcu_price'][$pcu_idx_v] = 0;
            }
            $_POST['pcu_per_price'][$pcu_idx_v] = preg_replace("/,/","",$_POST['pcu_per_price'][$pcu_idx_v]);
            $_POST['pcu_per_km'][$pcu_idx_v] = preg_replace("/,/","",$_POST['pcu_per_km'][$pcu_idx_v]);
            $_POST['pcu_price'][$pcu_idx_v] = preg_replace("/,/","",$_POST['pcu_price'][$pcu_idx_v]);
            $adm_where = "
                ,pcu_per_price = '".$_POST['pcu_per_price'][$pcu_idx_v]."'
                ,pcu_per_km = '".$_POST['pcu_per_km'][$pcu_idx_v]."'
                ,pcu_price = '".$_POST['pcu_price'][$pcu_idx_v]."'
            ";
        }


        $sql = " UPDATE {$g5_table_name} SET
                    pcu_date = '".$_POST['pcu_date'][$pcu_idx_v]."'
                    ,pcu_why = '".$_POST['pcu_why'][$pcu_idx_v]."'
                    ,pcu_reason = '".$_POST['pcu_reason'][$pcu_idx_v]."'
                    ,pcu_start_km = '".$_POST['pcu_start_km'][$pcu_idx_v]."'
                    ,pcu_arrival_km = '".$_POST['pcu_arrival_km'][$pcu_idx_v]."'
                    ,pcu_oil_type = '".$_POST['pcu_oil_type'][$pcu_idx_v]."'
                    {$adm_where}
                    ,pcu_status = '".$_POST['pcu_status'][$pcu_idx_v]."'
                    ,pcu_update_dt = '".G5_TIME_YMDHIS."'
                WHERE pcu_idx = '".$pcu_idx_v."'
        ";
        sql_query($sql,1);
    }

} else if ($_POST['act_button'] == "선택삭제") {

    foreach($_POST['chk'] as $pcu_idx_v){
        $sql = " UPDATE {$g5_table_name} SET
                    pcu_status = 'trash'
                WHERE pcu_idx = '".$pcu_idx_v."'
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