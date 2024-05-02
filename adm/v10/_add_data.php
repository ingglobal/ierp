<?php
include_once("./_common.php");

$add_mode = 0; // 코드실행모드=1, 비활성화모드=0

$ast_cnt = 20;
$asm_cnt = 40;


if($add_mode){
    $ast_truncate_sql = " TRUNCATE {$g5['assets_table']} ";
    $asm_truncate_sql = " TRUNCATE {$g5['assets_manager_table']} ";
    sql_query($ast_truncate_sql, 1);
    sql_query($asm_truncate_sql, 1);
    /*
    $part_arr = array(
        '1' => 'ING', '2' => '총무/회계', '3' => '영업부', '4' => '시스템사업부', '5' => '지역사무소'
        , '6' => '대리점', '7' => '울산TP', '8' => 'R&D', '9' => '총괄', '10' => '과제영업'
        , '11' => '해외영업', '12' => '기계설계', '13' => '솔루션개발', '14' => '공장'
    );
    $mb_arr = array(
        'lbk1130' => '이병구', 'sulmh' => '이민희', 'tomasjoa' => '임채완', 'kimct' => '김청탁', 'idaekyun' => '임대균'
    );
    */
    $mb_id_arr = array('lbk1130','sulmh','tomasjoa','kimct','idaekyun');
    $ast_name_arr = array('맥북001','맥북002','맥북003','삼성노트북001','삼성노트북002','삼성노트북003','LG노트북001','LG노트북002','LG노트북003','HP노트북001');
    $memo_arr = array('메모001','메모002','메모003','메모004','메모005','메모006','메모007','메모008','메모009','메모0010');
    $buycom_arr = array('애플','이마트','탑마트','홈플러스','하이마트','파나소닉','소니','미쯔비시','LG전자','삼성전자','HP','삼보전자','대우전자','일본전자','X전자','히다치','하야시전기');
    $part_id_arr = array(2,3,4,8,9,10,11,12,13,14);
    // echo rand(0, count($mb_id_arr)-1);
    // echo rand(0, count($ast_name_arr)-1);
    // echo rand(0, count($memo_arr)-1);
    // echo rand(0, count($buycom_arr)-1);
    // echo rand(0, count($part_id_arr)-1);

    $ast_sql = " INSERT INTO {$g5['assets_table']} (mb_id_buy,ast_name,ast_no,ast_part,ast_memo,ast_buycom,ast_date,ast_status,ast_reg_dt,ast_update_dt) VALUES ";
    for($i=0;$i<$ast_cnt;$i++){
        $mb_id_buy = $mb_id_arr[rand(0, count($mb_id_arr)-1)];
        $ast_name = $ast_name_arr[rand(0, count($ast_name_arr)-1)];
        $ast_memo = $memo_arr[rand(0, count($memo_arr)-1)];
        $ast_part = $part_id_arr[rand(0, count($part_id_arr)-1)];
        $ast_buycom = $buycom_arr[rand(0, count($buycom_arr)-1)];

        $ast_no = get_uniqid();
        $time = rand(strtotime('2023-10-01 09:42:16'),strtotime('2023-12-30 18:52:30'));
        $time_ymdhis = date('Y-m-d H:i:s', $time);
        $time_ymd = substr($time_ymdhis,0,10);
        // $time_his = substr($time_ymdhis,11,8);
        // echo $i." - ".$time_ymdhis."<br>";
        $ast_sql .= ($i == 0) ? '' : ',';
        $ast_sql .= "('{$mb_id_buy}','{$ast_name}','{$ast_no}','{$ast_part}','{$ast_memo}','{$ast_buycom}','{$time_ymd}','ok','{$time_ymdhis}','{$time_ymdhis}')";
    }
    // echo $ast_sql;
    sql_query($ast_sql, 1);

    // echo '<br><br><br>';
    $asm_sql = " INSERT INTO {$g5['assets_manager_table']} (ast_idx,mb_id,mb_id_mng,asm_memo,asm_given_date,asm_return_date,mb_id_acceptor,asm_status,asm_reg_dt,asm_update_dt) VALUES ";
    for($j=0;$j<$asm_cnt;$j++){
        $ast_idx = rand(1,$ast_cnt);
        $mb_id = $mb_id_arr[rand(0, count($mb_id_arr)-1)];
        $mb_id_mng = $mb_id_arr[rand(0, count($mb_id_arr)-1)];
        $asm_memo = $memo_arr[rand(0, count($memo_arr)-1)];
        $mb_id_acceptor = $mb_id_arr[rand(0, count($mb_id_arr)-1)];

        $time = rand(strtotime('2024-01-01 09:42:16'),strtotime('2024-02-28 18:52:30'));
        $time_ymdhis = date('Y-m-d H:i:s', $time);
        $time_ymd = substr($time_ymdhis,0,10);

        $time2 = rand(strtotime('2024-03-01 09:42:16'),strtotime('2024-05-01 18:52:30'));
        $time_ymdhis2 = date('Y-m-d H:i:s', $time2);
        $time_ymd2 = substr($time_ymdhis2,0,10);

        $asm_sql .= ($j == 0) ? '' : ',';
        $asm_sql .= "('{$ast_idx}','{$mb_id}','{$mb_id_mng}','{$asm_memo}','{$time_ymd}','{$time_ymd2}','{$mb_id_acceptor}','ok','{$time_ymdhis}','{$time_ymdhis2}')";
    }
    // echo $asm_sql;
    sql_query($asm_sql, 1);
}
else{
    echo '비활성화 상태';
}