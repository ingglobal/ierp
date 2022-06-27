<?php
$sub_menu = "960640";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

// print_r2($_POST);
// print_r2($_FILES);

$fle_type = 'pep_img';
// 삭제인 경우
if (${$fle_type.'_del'} == 1) {
    // fle_db_id를 던져서 바로 삭제할 수도 있고 $fle_db_table, $fle_db_id, $fle_token 를 던져서 삭제할 수도 있음
    delete_jt_file(array("fle_db_table"=>"personal_expenses"
        ,"fle_db_id"=>$pep_idx
        ,"fle_type"=>$fle_type
        ,"fle_sort"=>0
        ,"fle_delete"=>1
    ));
}
// 파일 등록
// print_r2($_FILES[$fle_type]['name']);exit;
if ($_FILES[$fle_type]['name']) {
    // 새로운 파일정보가 있으면 기존 등록된 파일을 먼저 삭제한다.
    delete_jt_file(array("fle_db_table"=>"personal_expenses"
        ,"fle_db_id"=>$pep_idx
        ,"fle_type"=>$fle_type
        ,"fle_sort"=>0
        ,"fle_delete"=>1
    ));

    $upfile_info = upload_jt_file(array("fle_idx"=>$fle_idx
                        ,"mb_id"=>$member['mb_id']
                        ,"fle_src_file"=>$_FILES[$fle_type]['tmp_name']
                        ,"fle_orig_file"=>$_FILES[$fle_type]['name']
                        ,"fle_mime_type"=>$_FILES[$fle_type]['type']
                        ,"fle_content"=>$fle_content
                        ,"fle_path"=>'/data/'.$fle_type		//<---- 저장 디렉토리
                        ,"fle_db_table"=>"personal_expenses"
                        ,"fle_db_id"=>$pep_idx
                        ,"fle_type"=>$fle_type
                        ,"fle_sort"=>0
    ));
    //print_r2($upfile_info);
}

if($year_month) $qstr .= "&year_month=".$year_month;
if($mb_name2) $qstr .= "&mb_name2=".$mb_name2;
if($sst2) $qstr .= "&sst2=".$sst2;
if($sod2) $qstr .= "&sod2=".$sod2;

goto_url('./personal_expenses_list.php?'.$qstr);