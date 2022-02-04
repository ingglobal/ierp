<?php
//$pa_sql = " UPDATE {$write_table} SET wr_1 = '{$parent_wr_1}' WHERE wr_id = '{$_POST['wr_id']}' ";
$cmchk_rst = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$write_table} WHERE wr_parent = '{$write['wr_parent']}' AND wr_is_comment = '1' ");
if(!$cmchk_rst['cnt']){
    $upt_sql = " UPDATE {$write_table} SET wr_1 = '' WHERE wr_id = '{$write['wr_parent']}' ";
    sql_query($upt_sql,1);
}