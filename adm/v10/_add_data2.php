<?php
include_once("./_common.php");
/*
CREATE TABLE `g5_1_workreport` (
  `wrp_idx` int(20) NOT NULL COMMENT '업무보고idx',
  `prj_idx` bigint(20) DEFAULT 0 COMMENT '프로젝트idx',
  `wrp_type` varchar(255) NOT NULL COMMENT '보고타입',
  `wrp_code` varchar(255) NOT NULL COMMENT '문서코드',
  `wrp_part` tinyint(3) DEFAULT 0 COMMENT '작성자부서',
  `wrp_rank` tinyint(3) DEFAULT 0 COMMENT '작성자직급',
  `mb_id` varchar(20) NOT NULL COMMENT '작성자mb_id',
  `wrp_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '보고날짜',
  `wrp_week` tinyint(3) NOT NULL COMMENT '보고주차',
  `wrp_month` tinyint(3) NOT NULL COMMENT '보고월',
  `wrp_subject` varchar(255) NOT NULL COMMENT '제목',
  `wrp_content` text NOT NULL COMMENT '내용',
  `wrp_status` varchar(20) DEFAULT 'ok' COMMENT '상태',
  `wrp_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `wrp_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
*/
$add_mode = 0; // 코드실행모드=1, 비활성화모드=0

$wrp_cnt = 20;


if($add_mode){
    $wrp_truncate_sql = " TRUNCATE {$g5['workreport_table']} ";
    // sql_query($wrp_truncate_sql, 1);

    $prj_id_arr = array(771, 770, 769, 768, 767, 766, 765, 764, 763, 762, 761, 760, 759, 755, 754, 753, 752, 751, 750, 749);
    $wrp_cd_arr = array(
        'W2024-CTR-0061','W2024-CTR-0062','W2024-CTR-0063','W2024-CTR-0064','W2024-CTR-0065','W2024-CTR-0066','W2024-CTR-0067','W2024-CTR-0068'
        ,'W2024-CTR-0069','W2024-CTR-0070','W2024-CTR-0071','W2024-CTR-0072','W2024-CTR-0073','W2024-CTR-0074','W2024-CTR-0075','W2024-CTR-0076'
        ,'W2024-CTR-0077','W2024-CTR-0078','W2024-CTR-0079','W2024-CTR-0080'
    );
    $part_id_arr = array(2,3,4,8,9,10,11,12,13,14,2,3,4,8,9,10,11,12,13,14);
    // $rank_id_arr = array(10,12,15,17,18,25,35,50,60,70,80,100,110,115,120,123,126,130,135,140,145,150,155);//직급
    $rank_id_arr = array(10,12,15,17,18,25,35,50,60,70,80,100,110,115,120,123,126,130,135,140);//직급
    $mb_id_arr = array('syeong230','sulmh','tomasjoa','idaekyun','ingcjh','syeong230','sulmh','tomasjoa','idaekyun','ingcjh','syeong230','sulmh','tomasjoa','idaekyun','ingcjh','syeong230','sulmh','tomasjoa','idaekyun','ingcjh');
    $wrp_date_arr = array(
        '2024-01-02','2024-01-09','2024-01-14','2024-01-19','2024-01-26','2024-02-02','2024-02-10','2024-02-14','2024-02-19','2024-02-25'
        ,'2024-03-02','2024-03-08','2024-03-14','2024-03-19','2024-03-29','2024-04-02','2024-04-06','2024-04-19','2024-04-24','2024-04-28'
    );

    $subject_arr = array(
        '제목001','제목002','제목003','제목004','제목005','제목006','제목007','제목008','제목009','제목010'
        ,'제목011','제목012','제목013','제목014','제목015','제목016','제목017','제목018','제목019','제목020'
    );
    $content_arr = array(
        '내용입니다01','내용입니다02','내용입니다03','내용입니다04','내용입니다05','내용입니다06','내용입니다07','내용입니다08','내용입니다09','내용입니다10'
        ,'내용입니다11','내용입니다12','내용입니다13','내용입니다14','내용입니다15','내용입니다16','내용입니다17','내용입니다18','내용입니다19','내용입니다20'
    );
    $status_arr = array('ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok','ok');
    $time_arr = array(
        '09:10:20','09:20:30','09:30:40','09:40:50','10:10:20','10:20:30','10:30:40','10:40:50','11:10:20','11:20:30'
        ,'11:30:40','11:40:50','11:50:50','12:10:20','12:30:40','12:40:50','13:10:20','13:20:30','13:10:20','13:20:20'
    );
    // echo rand(0, count($prj_id_arr)-1);
    // echo rand(0, count($wrp_cd_arr)-1);
    // echo rand(0, count($part_id_arr)-1);
    // echo rand(0, count($rank_id_arr)-1);
    // echo rand(0, count($mb_id_arr)-1);
    // echo rand(0, count($subject_arr)-1);
    // echo rand(0, count($content_arr)-1);
    // echo rand(0, count($status_arr)-1);

    $wrp_sql = " INSERT INTO {$g5['workreport_table']} ( prj_idx, wrp_type, wrp_code, wrp_part, wrp_rank, mb_id, wrp_date, wrp_week, wrp_month, wrp_subject, wrp_content, wrp_status, wrp_reg_dt, wrp_update_dt ) VALUES ";
    for($i=0;$i<$wrp_cnt;$i++){
        $prj_idx = $prj_id_arr[$i];
        $wrp_code = $wrp_cd_arr[$i];
        $wrp_part = $part_id_arr[$i];
        $wrp_rank = $rank_id_arr[$i];
        $mb_id = $mb_id_arr[$i];
        $wrp_date = $wrp_date_arr[$i];
        $wrp_week = getWeekNumOfMonth($wrp_date);
        $wrp_month = substr($wrp_date,5,2);
        $wrp_subject = $subject_arr[$i];
        $wrp_content = $content_arr[$i];
        $wrp_status = $status_arr[$i];
        $wrp_reg_dt = $wrp_date.' '.$time_arr[$i];
        $wrp_update_dt = $wrp_date.' '.$time_arr[$i];

        
        $wrp_sql .= ($i == 0) ? '' : ',';
        $wrp_sql .= "('{$prj_idx}','month','{$wrp_code}','{$wrp_part}','{$wrp_rank}','{$mb_id}','{$wrp_date}','{$wrp_week}','{$wrp_month}','{$wrp_subject}','{$wrp_content}','{$wrp_status}','{$wrp_reg_dt}','{$wrp_update_dt}')";
    }
    // echo $wrp_sql."<br>";exit;
    sql_query($wrp_sql, 1);

    echo '데이터2등록완료';
}
else{
    echo '비활성화 상태';
}