<?php
include_once('./_common.php');

/*
$mtp_idx
*/
// 회의참석자정보 삭제
$sqld = " DELETE FROM {$g5['meeting_participant_table']} WHERE
    mtp_idx = '{$mtp_idx}'
";
sql_query($sqld,1);


echo 'ok';