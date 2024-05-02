<?php
include_once('./_common.php');

/*
'ast_idx': ast_idx,
'mb_id_mng': mb_id_mng, 
'asm_memo': asm_memo, 
'asm_given_date': asm_given_date, 
'asm_return_date': asm_return_date, 
'mb_id_acceptor': mb_id_aceptor, 
'asm_status': asm_status
*/
// 천단위 제거
// if(preg_match("/_price$/",$asm_price))
//     $asm_price = preg_replace("/,/","",$asm_price);

// $asm_status = ($asm_return_date && $asm_return_date != '0000-00-00') ? 'pending' : 'ok';

// 지출분배 테이블에 등록
$sqlc = " INSERT into {$g5['assets_manager_table']} SET
    ast_idx = '{$ast_idx}'
    , mb_id = '{$member['mb_id']}'
    , mb_id_mng = '{$mb_id_mng}'
    , asm_memo = '{$asm_memo}'
    , asm_given_date = '{$asm_given_date}'
    , asm_return_date = '{$asm_return_date}'
    , mb_id_acceptor = '{$mb_id_acceptor}'
    , asm_status = '{$asm_status}'
    , asm_reg_dt = '".G5_TIME_YMDHIS."'
    , asm_update_dt = '".G5_TIME_YMDHIS."'
";
sql_query($sqlc,1);


echo 'ok';