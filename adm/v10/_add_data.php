<?php
include_once("./_common.php");
/*
CREATE TABLE `g5_1_meeting` (
  `mtg_idx` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '회의idx',
  `prj_idx` bigint(20) DEFAULT '0' COMMENT '프로젝트idx',
  `mtg_type` varchar(10) NOT NULL DEFAULT 'in' COMMENT '회의타입',
  `mtg_part` tinyint(3) DEFAULT '0' COMMENT '작성자부서',
  `mtg_rank` tinyint(3) DEFAULT '0' COMMENT '작성자직급',
  `mb_id_writer` varchar(20) NOT NULL COMMENT '작성자mb_id',
  `mtg_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '회의날짜',
  `mtg_start_time` TIME NOT NULL DEFAULT '00:00:00' COMMENT '회의시작시간',
  `mtg_end_time` TIME NOT NULL DEFAULT '00:00:00' COMMENT '회의종료시간',
  `mtg_subject` varchar(255) NOT NULL COMMENT '주요안건',
  `mtg_content` text NOT NULL COMMENT '회의내용',
  `mtg_result` text NULL COMMENT '회의결과',
  `mtg_status` varchar(20) DEFAULT 'ok' COMMENT '상태',
  `mtg_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일',
  `mtg_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `g5_1_meeting_participant` (
  `mtp_idx` int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '참석자idx',
  `mtg_idx` int(20) NOT NULL COMMENT '회의idx',
  `mtp_belong` varchar(255) NULL COMMENT '참석자소속',
  `mtp_name` varchar(255) NOT NULL COMMENT '참석자명',
  `mtp_rank` varchar(255) NULL COMMENT '참석자직급',
  `mtp_phone` varchar(255) NULL COMMENT '참석자연락처',
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
*/
$add_mode = 0; // 코드실행모드=1, 비활성화모드=0

$mtg_cnt = 20;
$mtp_cnt = 60;


if($add_mode){
    $mtg_truncate_sql = " TRUNCATE {$g5['meeting_table']} ";
    $mtp_truncate_sql = " TRUNCATE {$g5['meeting_participant_table']} ";
    sql_query($mtg_truncate_sql, 1);
    sql_query($mtp_truncate_sql, 1);
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
    $prj_id_arr = array(764, 765, 766, 767, 768, 769, 770, 771, 772, 773);
    $type_arr = array('in','out');
    $g5['set_mb_ranks_value']; //직급직책 (mb_3 : 직급데이터)
    $part_id_arr = array(2,3,4,8,9,10,11,12,13,14);
    $rank_id_arr = array(10,12,15,17,18,25,35,50,60,70,80,100,110,115,120,123,126,130,135,140,145,150,155);//직급
    $mb_id_arr = array('lbk1130','sulmh','tomasjoa','kimct','idaekyun');
    $subject_arr = array('안건001','안건002','안건003','안건004','안건005','안건006','안건007','안건008','안건009','안건010');
    $content_arr = array('이런저런 내용을 작성합니다01','이런저런 내용을 작성합니다02','이런저런 내용을 작성합니다03','이런저런 내용을 작성합니다04','이런저런 내용을 작성합니다05','이런저런 내용을 작성합니다06','이런저런 내용을 작성합니다07','이런저런 내용을 작성합니다08','이런저런 내용을 작성합니다09','이런저런 내용을 작성합니다10','이런저런 내용을 작성합니다11','이런저런 내용을 작성합니다12','이런저런 내용을 작성합니다13','이런저런 내용을 작성합니다14','이런저런 내용을 작성합니다15','이런저런 내용을 작성합니다16','이런저런 내용을 작성합니다17');
    $result_arr = array('결과내용입니다01','결과내용입니다02','결과내용입니다03','결과내용입니다04','결과내용입니다05','결과내용입니다06','결과내용입니다07','결과내용입니다08','결과내용입니다09','결과내용입니다10','결과내용입니다11','결과내용입니다12','결과내용입니다13','결과내용입니다14','결과내용입니다15','결과내용입니다16','결과내용입니다17');
    
    $time_arr = array('09:00:00','09:30:00','10:00:00','10:30:00','11:00:00','11:30:00','12:00:00','12:30:00','13:00:00','13:30:00','14:00:00','14:30:00','15:00:00','15:30:00','16:00:00','16:30:00');
    // echo rand(0, count($prj_id_arr)-1);
    // echo rand(0, count($type_arr)-1);
    // echo rand(0, count($part_id_arr)-1);
    // echo rand(0, count($rank_id_arr)-1);
    // echo rand(0, count($mb_id_arr)-1);
    // echo rand(0, count($subject_arr)-1);
    // echo rand(0, count($content_arr)-1);
    // echo rand(0, count($result_arr)-1);
    // echo rand(0, count($time_arr)-1);

    $mtg_sql = " INSERT INTO {$g5['meeting_table']} ( prj_idx, mtg_type, mtg_part, mtg_rank, mb_id_writer, mtg_date, mtg_start_time, mtg_end_time, mtg_subject, mtg_content, mtg_result, mtg_status, mtg_reg_dt, mtg_update_dt ) VALUES ";
    for($i=0;$i<$mtg_cnt;$i++){
        $prj_idx = $prj_id_arr[rand(0, count($prj_id_arr)-1)];
        $mtg_type = $type_arr[rand(0, count($type_arr)-1)];
        $mtg_part = $part_id_arr[rand(0, count($part_id_arr)-1)];
        $mtg_rank = $rank_id_arr[rand(0, count($rank_id_arr)-1)];
        $mb_id_writer = $mb_id_arr[rand(0, count($mb_id_arr)-1)];
        $mtg_subject = $subject_arr[rand(0, count($subject_arr)-1)];
        $mtg_content = $content_arr[rand(0, count($content_arr)-1)];
        $mtg_result = $result_arr[rand(0, count($result_arr)-1)];

        // $_no = get_uniqid();
        $time = rand(strtotime('2023-10-01 09:42:16'),strtotime('2023-05-03 18:52:30'));
        $time_ymdhis = date('Y-m-d H:i:s', $time);
        $time_ymd = substr($time_ymdhis,0,10);
        // 시작 시간과 종료 시간을 정의합니다.
        
        $time_from_his = $time_arr[rand(0, count($time_arr)-1)];
        $timeToHis = strtotime($time_from_his) + 2 * 60 * 60;
        $time_to_his = date('H:i:s', $timeToHis);
        $time_dt = $time_ymd.' '.$time_to_his;
        // $time_his = substr($time_ymdhis,11,8);
        // echo $i." - ".$time_ymdhis."<br>";
        $mtg_sql .= ($i == 0) ? '' : ',';
        $mtg_sql .= "('{$prj_idx}','{$mtg_type}','{$mtg_part}','{$mtg_rank}','{$mb_id_writer}','{$time_ymd}','{$time_from_his}','{$time_to_his}','{$mtg_subject}','{$mtg_content}','{$mtg_result}','ok','{$time_dt}','{$time_dt}')";
    }
    // echo $mtg_sql;exit;
    sql_query($mtg_sql, 1);


    $belong_arr = array('울산연구소','UNIST','ING','솔루션개발','관측소','현대자동차','기아자동차','현대자동차','토요타','닛산','미츠비시','스바루','벤츠','케딜락','아우디','폭스바겐','삼성');
    $name_arr = array('홍길동','이순신','강감찬','강호동','홍기훈','최진실','하희라','강동궁','조재호','박찬호','풍자','전현무','곽준빈','이세빈','정유라','이미래','김민아');
    $rank_arr = array('사원','기사','주임','계장','연구원','대리','매니저','팀장','과장','차장','부장','본부장','실장','연구소장','이사','상무','전무','부사장','사장','대표','부회장','회장','공장장');
    $phone_arr = array('010-1111-1111','010-1111-1112','010-1111-1113','010-1111-1114','010-1111-1115','010-1111-1116','010-1111-1117','010-1111-1118','010-1111-1119','010-1111-1121','010-1111-1122','010-1111-1123','010-1111-1124','010-1111-1125','010-1111-1126','010-1111-1127','010-1111-1128','010-1111-1129','010-1111-1130','010-1111-1131','010-1111-1132','010-1111-1133','010-1111-1134');
    // echo rand(0, count($belong_arr)-1);
    // echo rand(0, count($name_arr)-1);

    $mtp_sql = " INSERT INTO {$g5['meeting_participant_table']} (mtg_idx,mtp_belong,mtp_name,mtp_rank,mtp_phone) VALUES ";
    for($j=0;$j<$mtp_cnt;$j++){
        $mtg_idx = rand(1,$mtg_cnt);
        $mtp_belong = $belong_arr[rand(0, count($belong_arr)-1)];
        $mtp_name = $name_arr[rand(0, count($name_arr)-1)];
        $mtp_rank = $rank_arr[rand(0, count($rank_arr)-1)];
        $mtp_phone= $phone_arr[rand(0, count($phone_arr)-1)];

        $mtp_sql .= ($j == 0) ? '' : ',';
        $mtp_sql .= "('{$mtg_idx}','{$mtp_belong}','{$mtp_name}','{$mtp_rank}','{$mtp_phone}')";
    }
    // echo $asm_sql;
    sql_query($mtp_sql, 1);

    echo '데이터등록완료';
}
else{
    echo '비활성화 상태';
}