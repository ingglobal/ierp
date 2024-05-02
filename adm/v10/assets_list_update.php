<?php
$sub_menu = "960650";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

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
        $astsql = " UPDATE {$g5['assets_table']} SET
                    ast_status = '{$ast_status[$idx]}'
                    , ast_update_dt = '".G5_TIME_YMDHIS."'
                WHERE ast_idx = '{$ast_idx[$idx]}'
        ";
        sql_query($astsql,1);

        if($asm_idx[$idx]){
            $asmsql = " UPDATE {$g5['assets_manager_table']} SET
                            asm_return_date = '{$asm_return_date[$idx]}'
                            , asm_status = '{$asm_status[$idx]}'
                            , asm_update_dt = '".G5_TIME_YMDHIS."'
                        WHERE asm_idx = '{$asm_idx[$idx]}'
            ";
            sql_query($asmsql,1);
        }
    }
}
else if($act_button == "선택삭제"){
    foreach($chk as $idx){
        // $asmsql = " UPDATE {$g5['assets_manager_table']} SET
        //                 asm_status = 'trash'
        //                 , asm_update_dt = '".G5_TIME_YMDHIS."'
        //             WHERE ast_idx = '{$ast_idx[$idx]}'
        // ";
        $asmsql = " DELETE FROM {$g5['assets_manager_table']} WHERE ast_idx = '{$ast_idx[$idx]}' ";
        sql_query($asmsql,1);

        // $astsql = " UPDATE {$g5['assets_table']} SET
        //             ast_status = 'trash'
        //             , ast_update_dt = '".G5_TIME_YMDHIS."'
        //         WHERE ast_idx = '{$ast_idx[$idx]}'
        // ";
        $astsql = " DELETE FROM {$g5['assets_table']} WHERE ast_idx = '{$ast_idx[$idx]}' ";
        sql_query($astsql,1);
    }
}




goto_url('./assets_list.php?'.$qstr);