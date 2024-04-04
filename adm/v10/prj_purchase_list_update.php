<?php
$sub_menu = "960266";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명

$table_name2 = 'project_purchase_tmp';
$g5_table_name2 = $g5[$table_name2.'_table'];
/*
echo $act_button."<br>";
print_r2($chk);
print_r2($ppc_idx);
print_r2($ppc_subject);
print_r2($ppc_price);
print_r2($ppc_date);
print_r2($ppc_status);
exit;
*/
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

if (!count($chk)) {
    alert($act_button." 하실 항목을 하나 이상 체크하세요.");
}

if ($act_button == "선택수정"){
    foreach($chk as $idx){
        $ppc_subject[$idx] = trim($ppc_subject[$idx]);
        $ppc_price[$idx] = preg_replace("/,/","",$ppc_price[$idx]);
        $sql = " UPDATE {$g5_table_name} SET
                    ppc_subject = '{$ppc_subject[$idx]}'
                    , ppc_price = '{$ppc_price[$idx]}'
                    , ppc_date = '{$ppc_date[$idx]}'
                    , ppc_status = '{$ppc_status[$idx]}'
                WHERE ppc_idx = '{$ppc_idx[$idx]}'
        ";
        // echo $sql."<br>";
        sql_query($sql,1);
    }
}
else if($act_button == "선택삭제"){
    foreach($chk as $idx){
        // 먼저 해당 ppt_idx와 관련된 모든파일을 삭제
        $dfres = sql_fetch("SELECT GROUP_CONCAT(DISTINCT fle_idx) AS fle_idxs FROM {$g5['file_table']}
            WHERE fle_db_table = 'ppc' AND fle_type = 'ppc' AND fle_db_id = '{$ppc_idx[$idx]}' ");
        $dfarr = ($dfres['fle_idxs']) ? explode(',',$dfres['fle_idxs']) : array();
        if(count($dfarr)){
            delete_idx_file($dfarr);
            // ppt_idx와 관련된 fle_idx 데이터를 전부 삭제
            $dfsql = " DELETE FROM {$g5['file_table']}
                WHERE fle_db_table = 'ppc' AND fle_type = 'ppc' AND fle_db_id = '{$ppc_idx[$idx]}'
            ";
            sql_query($dfsql,1);
        }
        // ppt테이블에서 ppc_idx를 가지고 있는것의 연결고리를 끊는다.
        $csql = " UPDATE {$g5_table_name2} SET ppc_idx = '0' WHERE ppc_idx = '$ppc_idx[$idx]' ";
        sql_query($csql,1);
        // ppc_idx의 레코드를 삭제
        $dsql = " DELETE FROM {$g5_table_name} WHERE ppc_idx = '{$ppc_idx[$idx]}' ";
        sql_query($dsql,1);
    }
    // 만약 테이블에 아무 레코드도 없으면 AUTO_INCREMENT를 1로 초기화한다.
    $cntres = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5_table_name} ");
    if(!$cntres['cnt']){
        $resetsql = " ALTER TABLE {$g5_table_name} auto_increment=1 ";
        sql_query($resetsql);
    }
}
// exit;

goto_url('./'.$fname.'.php?'.$qstr);