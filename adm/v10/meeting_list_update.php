<?php
$sub_menu = '960265';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

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


if ($act_button == "선택수정"){

}
else if($act_button == "선택삭제"){

    if(!$super_ceo_admin){
        alert('선택삭제는 최고관리자만 할 수 있습니다');
    }

    foreach($chk as $idx){
        // 먼저 해당 ppt_idx와 관련된 모든파일을 삭제
        $dfres = sql_fetch("SELECT GROUP_CONCAT(DISTINCT fle_idx) AS fle_idxs FROM {$g5['file_table']}
            WHERE fle_db_table = 'mtg' AND fle_type = 'mtg' AND fle_db_id = '{$mtg_idx[$idx]}' ");
        $dfarr = ($dfres['fle_idxs']) ? explode(',',$dfres['fle_idxs']) : array();
        if(count($dfarr)){
            delete_idx_file($dfarr);
            // ppt_idx와 관련된 fle_idx 데이터를 전부 삭제
            $dfsql = " DELETE FROM {$g5['file_table']}
                WHERE fle_db_table = 'mtg' AND fle_type = 'mtg' AND fle_db_id = '{$mtg_idx[$idx]}'
            ";
            sql_query($dfsql,1);
        }

        // mtp_idx의 레코드 삭제
        $dsql = " DELETE FROM {$g5['meeting_participant_table']} WHERE mtg_idx = '{$mtg_idx[$idx]}' ";
        sql_query($dsql,1);

        // mtg_idx의 레코드를 삭제
        $dsql2 = " DELETE FROM {$g5['meeting_table']} WHERE mtg_idx = '{$mtg_idx[$idx]}' ";
        sql_query($dsql2,1);
    }
    // 만약 테이블에 아무 레코드도 없으면 AUTO_INCREMENT를 1로 초기화한다.
    $cntres = sql_fetch(" SELECT EXISTS( SELECT 1 FROM {$g5['meeting_table']} ) AS cnt ");
    $cntres2 = sql_fetch(" SELECT EXISTS( SELECT 1 FROM {$g5['meeting_participant_table']} ) AS cnt ");
    if(!$cntres['cnt']){
        $resetsql = " ALTER TABLE {$g5['meeting_table']} auto_increment=1 ";
        sql_query($resetsql);
    }
    if(!$cntres2['cnt']){
        $resetsql2 = " ALTER TABLE {$g5['meeting_participant_table']} auto_increment=1 ";
        sql_query($resetsql2);
    }
}

goto_url('./meeting_list.php?'.$qstr);