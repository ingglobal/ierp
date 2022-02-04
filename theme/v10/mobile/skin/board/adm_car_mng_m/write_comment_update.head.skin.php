<?php
$wr_3 = (int)preg_replace("/,/","",$_POST['wr_3']);
$wr_4 = (int)preg_replace("/,/","",$_POST['wr_4']);
if(!preg_match("/^[0-9]/i",$wr_3))
alert('주행거리가 숫자형식에 맞지 않습니다.');

if(!preg_match("/^[0-9]/i",$wr_4))
alert('정비비용이 숫자형식에 맞지 않습니다.');


$pa_sql = " UPDATE {$write_table} SET wr_1 = '{$parent_wr_1}' WHERE wr_id = '{$_POST['wr_id']}' ";
sql_query($pa_sql,1);
//print_r2($_POST);
//exit;