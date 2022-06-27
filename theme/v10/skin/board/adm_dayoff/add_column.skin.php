<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$ovt_column_arr = array(
    'wr_10'
    ,'wr_mb_part'
    ,'wr_mb_id_applicant'
    ,'wr_mb_id_approver'
    ,'wr_start_date'
    ,'wr_start_ampm'
    ,'wr_work_date'
    ,'wr_work_ampm'
    ,'wr_dayoff_cnt'
    ,'wr_apply_status'
);
$ovt_column_tpy = array(
    "varchar(20) DEFAULT '' COMMENT '신청자 부서'"
    ,"varchar(20) DEFAULT '' COMMENT '신청자id'"
    ,"varchar(20) DEFAULT '' COMMENT '승인자id'"
    ,"date DEFAULT '0000-00-00' COMMENT '휴가시작일'"
    ,"varchar(20) NOT NULL DEFAULT 'am' COMMENT 'am(오전부터)/pm(오후부터)'"
    ,"date DEFAULT '0000-00-00' COMMENT '출근일'"
    ,"varchar(20) NOT NULL DEFAULT 'am' COMMENT 'am(오전출근/pm(오후출근)'"
    ,"decimal(5,1) DEFAULT 0.0 COMMENT '연차개수'"
    ,"varchar(20) DEFAULT 'pending' COMMENT '신청상태'"
);
$ovt_column_sql = array(
    " SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_mb_part' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_mb_id_applicant' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_mb_id_approver' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_start_date' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_start_ampm' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_work_date' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_work_ampm' "
    ," SHOW COLUMNS FROM `{$write_table}` LIKE 'wr_dayoff_cnt' "
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