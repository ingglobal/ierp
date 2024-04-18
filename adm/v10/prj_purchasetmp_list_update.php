<?php
$sub_menu = "960268";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase_tmp';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명
/*
echo $act_button."<br>";
print_r2($chk);
print_r2($ppc_idx);
print_r2($ppt_idx);
print_r2($ppt_subject);
print_r2($ppt_price);
print_r2($ppt_date);
print_r2($ppt_status);
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
        $ppt_subject[$idx] = trim($ppt_subject[$idx]);
        $ppt_price[$idx] = preg_replace("/,/","",$ppt_price[$idx]);

        if($ppc_idx[$idx]){
            $old = sql_fetch(" SELECT ppt_price FROM {$g5_table_name} WHERE ppt_idx = '{$ppt_idx[$idx]}' ");
            $dif_price = $ppt_price[$idx] - $old['ppt_price'];
            // echo $dif_price;exit;
            // 기존보다 금액이 늘었다면 ppc_price에서 dif_price를 더한다.
            if($dif_price > 0){
                sql_query(" UPDATE {$g5['project_purchase_table']} SET ppc_price = (ppc_price + {$dif_price}) WHERE ppc_idx = '{$ppc_idx[$idx]}' ");
            }
            // 기존보다 금액이 줄었다면 ppc_price에서 dif_price를 뺀다.
            else if($dif_price < 0){
                $abs_price = abs($dif_price);
                sql_query(" UPDATE {$g5['project_purchase_table']} SET ppc_price = (ppc_price - {$abs_price}) WHERE ppc_idx = '{$ppc_idx[$idx]}' ");
            }
        }

        $sql = " UPDATE {$g5_table_name} SET
                    ppt_subject = '{$ppt_subject[$idx]}'
                    , ppt_price = '{$ppt_price[$idx]}'
                    , ppt_date = '{$ppt_date[$idx]}'
                    , ppt_status = '{$ppt_status[$idx]}'
                WHERE ppt_idx = '{$ppt_idx[$idx]}'
        ";
        // echo $sql."<br>";
        sql_query($sql,1);
    }
}
else if($act_button == "선택삭제"){
    foreach($chk as $idx){
        // 먼저 해당 ppt_idx와 관련된 모든파일을 삭제
        $dfres = sql_fetch("SELECT GROUP_CONCAT(DISTINCT fle_idx) AS fle_idxs FROM {$g5['file_table']}
            WHERE fle_db_table = 'ppt' AND fle_type = 'ppt' AND fle_db_id = '{$ppt_idx[$idx]}' ");
        $dfarr = ($dfres['fle_idxs']) ? explode(',',$dfres['fle_idxs']) : array();
        if(count($dfarr)){
            delete_idx_file($dfarr);
            // ppt_idx와 관련된 fle_idx 데이터를 전부 삭제
            $dfsql = " DELETE FROM {$g5['file_table']}
                WHERE fle_db_table = 'ppt' AND fle_type = 'ppt' AND fle_db_id = '{$ppt_idx[$idx]}'
            ";
            sql_query($dfsql,1);
        }

        // ppt_idx의 삭제되는 금액만큼 ppc_price에서도 차감해준다.
        $csql = " UPDATE {$g5['project_purchase_table']} SET ppc_price = (ppc_price - {$ppt_price[$idx]}) WHERE ppc_idx = '{$ppc_idx[$idx]}' ";
        sql_query($csql,1);

        // ppt_idx의 레코드를 삭제
        $dsql = " DELETE FROM {$g5_table_name} WHERE ppt_idx = '{$ppt_idx[$idx]}' ";
        sql_query($dsql,1);

    }
    // 만약 테이블에 아무 레코드도 없으면 AUTO_INCREMENT를 1로 초기화한다.
    $cntres = sql_fetch(" SELECT EXISTS( SELECT 1 FROM {$g5_table_name} ) AS cnt ");
    if(!$cntres['cnt']){
        $resetsql = " ALTER TABLE {$g5_table_name} auto_increment=1 ";
        sql_query($resetsql);
    }
}
else if($act_button == "선택그룹발주해제"){
    foreach($chk as $idx){
        // ppt_idx의 삭제되는 금액만큼 ppc_price에서도 차감해준다.
        $csql = " UPDATE {$g5['project_purchase_table']} SET ppc_price = (ppc_price - {$ppt_price[$idx]}) WHERE ppc_idx = '{$ppc_idx[$idx]}' ";
        sql_query($csql,1);

        $sql = " UPDATE {$g5_table_name} SET
                    ppc_idx = '0'
                WHERE ppt_idx = '{$ppt_idx[$idx]}'
        ";
        // echo $sql."<br>";
        sql_query($sql,1);
    }
}
// exit;

goto_url('./'.$fname.'.php?'.$qstr);