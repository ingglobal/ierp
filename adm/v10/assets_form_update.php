<?php
$sub_menu = "960650";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

$ast_name = trim(strip_tags($_POST['ast_name']));
$ast_buycom = trim(strip_tags($_POST['ast_buycom']));
$ast_memo = trim(strip_tags($_POST['ast_memo']));

if($w == ''){
    $sql = " INSERT INTO {$g5['assets_table']} SET
                mb_id_buy = '{$mb_id_buy}'
                , ast_name = '{$ast_name}'
                , ast_no = '{$ast_no}'
                , ast_part = '{$ast_part}'
                , ast_memo = '{$ast_memo}'
                , ast_buycom = '{$ast_buycom}'
                , ast_date = '{$ast_date}'
                , ast_status = '{$ast_status}'
                , ast_reg_dt = '".G5_TIME_YMDHIS."'
                , ast_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql,1);
	$ast_idx = sql_insert_id();
}
else if($w != ''){
    $astsql = " UPDATE {$g5['assets_table']} SET
                    mb_id_buy = '{$mb_id_buy}'
                    , ast_name = '{$ast_name}'
                    , ast_no = '{$ast_no}'
                    , ast_part = '{$ast_part}'
                    , ast_memo = '{$ast_memo}'
                    , ast_buycom = '{$ast_buycom}'
                    , ast_date = '{$ast_date}'
                    , ast_status = '{$ast_status}'
                    , ast_update_dt = '".G5_TIME_YMDHIS."'
                WHERE ast_idx = '{$ast_idx}'
    ";
    sql_query($astsql,1);

    if(count($mb_id_mng)){
        foreach($mb_id_mng as $asm_idx => $mbmng){
            $sql = " UPDATE {$g5['assets_manager_table']} SET
                        mb_id = '{$member['mb_id']}'
                        , mb_id_mng = '{$mbmng}'
                        , asm_memo = '{$asm_memo[$asm_idx]}'
                        , asm_given_date = '{$asm_given_date[$asm_idx]}'
                        , asm_return_date = '{$asm_return_date[$asm_idx]}'
                        , mb_id_acceptor = '{$mb_id_acceptor[$asm_idx]}'
                        , asm_status = '{$asm_status[$asm_idx]}'
                        , asm_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE asm_idx = '{$asm_idx}'
            ";
            // print_r2($mbmng);
            sql_query($sql,1);
        }
    }
}
// exit;
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

$qstr .= '&w=u&ast_idx='.$ast_idx;
goto_url('./assets_form.php?'.$qstr, false);