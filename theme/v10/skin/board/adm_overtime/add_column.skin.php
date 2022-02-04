<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$ovt_column_arr = array(
    'wr_10'
    ,'wr_prj_idx'
    ,'wr_mb_part'
    ,'wr_mb_id_worker'
    ,'wr_mb_id_approver'
    ,'wr_work_dt'
    ,'wr_hour_count'
    ,'wr_hour_price'
    ,'wr_total_price'
    ,'wr_work_type'
    ,'wr_apply_status'
);
$ovt_column_tpy = array(
    "bigint(20) NULL DEFAULT 0 COMMENT '프로젝트idx'"
    ,"varchar(20) DEFAULT '' COMMENT '신청자 부서'"
    ,"varchar(20) DEFAULT '' COMMENT '신청자id'"
    ,"varchar(20) DEFAULT '' COMMENT '승인자id'"
    ,"datetime DEFAULT '0000-00-00 00:00:00' COMMENT '업무날짜'"
    ,"int(8) NOT NULL DEFAULT 0 COMMENT '몇시간?'"
    ,"int(8) NOT NULL DEFAULT 0 COMMENT '시급(수당포함)'"
    ,"int(10) NOT NULL DEFAULT 0 COMMENT '총지급액(수당포함)'"
    ,"varchar(20) DEFAULT 'overtime' COMMENT '업무유형'"
    ,"varchar(20) DEFAULT 'pending' COMMENT '신청상태'"
);
$ovt_column_sql = array(
    " SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_prj_idx' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_mb_part' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_mb_id_worker' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_mb_id_approver' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_work_dt' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_hour_count' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_hour_price' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_total_price' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_work_type' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_apply_status' "
);

//print_r2($ovt_column_tpy);
for($i=0;$i<count($ovt_column_sql);$i++){
    $n = $i+1;
    $col_res = sql_fetch($ovt_column_sql[$i]);
    if(!$col_res){
        $ovtsql = " ALTER TABLE `{$write_table}`
                        ADD `{$ovt_column_arr[$n]}` {$ovt_column_tpy[$i]} AFTER `{$ovt_column_arr[$i]}` ";
        sql_query($ovtsql, true);
        //echo $ovtsql."<br>";
    }
}