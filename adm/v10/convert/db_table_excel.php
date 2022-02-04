<?php
include_once('./_common.php');

function column_char($i) { return chr( 65 + $i ); }

// 테이블 한글명
$tables = array('g5_1_company'=>'업체'
                ,'g5_1_company_item'=>'업체별입고부품'
                ,'g5_1_company_member'=>'업체별담당자'
                ,'g5_1_company_saler'=>'업체별영업자'
                ,'g5_1_project'=>'프로젝트'
                ,'g5_1_contract'=>'영업계약'
                ,'g5_1_project_price'=>'수금(결제)'
                ,'g5_1_project_schedule'=>'프로젝트진행일정'
                ,'g5_5_meta'=>'메타테이블'
                ,'g5_5_file'=>'첨부파일'
                ,'g5_5_setting'=>'환경설정'
                ,'g5_5_term'=>'코드(용어)'
                ,'g5_5_term_relation'=>'용어관계설정'
                ,'g5_auth'=>'메뉴권한설정'
                ,'g5_board'=>'게시판'
                ,'g5_board_file'=>'게시판첨부파일'
                ,'g5_config'=>'환경설정'
                ,'g5_group'=>'그룹설정'
                ,'g5_member'=>'회원'
                ,'g5_new_win'=>'팝업창'
                ,'g5_point'=>'포인트'
                ,'g5_shop_banner'=>'배너관리'
                ,'g5_shop_cart'=>'장바구니'
                ,'g5_shop_category'=>'부품분류'
                ,'g5_shop_default'=>'상품환경설정'
                ,'g5_shop_item'=>'부품'
                ,'g5_shop_order'=>'부품견적'
                ,'g5_write_sales'=>'영업관리'
                ,'g5_write_tech1'=>'기술정보'
                ,'g5_write_as'=>'A/S관리'
                ,'g5_write_notice1'=>'공지사항'
            );
// print_r2($tables);

// 각 항목 설정
$headers = array('테이블명','테이블ID','컬럼명','컬럼ID','Datatype','PK','FK','NULL허용','비고');
$widths  = array(18, 30, 15, 30, 20, 20, 20, 20, 20);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

$sql = " show tables ";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    $row['db_table'] = $row['Tables_in_ingglobal_erp']; ////////////////////// ingglobal_erp

    // $sql2 = " desc `".$row['db_table']."` ";
    $sql2 = " show full columns from `".$row['db_table']."` ";
    $result = sql_query($sql2);
    while($field = mysqli_fetch_array($result)) {
    //    print_r2($field);
        for($j=0;$j<sizeof($field);$j++) {
            $row['db_field'][$j] = $field[$j];
//            echo $j.':'.$field[$j].', ';
        }
    //    print_r2($row['db_field']);
        // 테이블명
        $row['db_table_name'] = $tables[$row['db_table']] ? $tables[$row['db_table']] : $row['db_table'];

        // // 컬럼명
        $row['db_field_name'] = $row['db_field'][8] ? $row['db_field'][8] : $row['db_field'][0];

        // PK
        $row['db_field'][4] = preg_match("/PRI/",$row['db_field'][4]) ? 'Y':'';
        
        // Null
        $row['db_field'][3] = preg_match("/NO/",$row['db_field'][3]) ? 'not null':'null';
        
        // 비고. 비밀번호인 경우
        $row['db_field'][20] = preg_match("/pass/",$row['db_field'][0]) ? 'sha256':'';

 
        
        $rows[] = array($row['db_table_name']
                      , $row['db_table']
                      , $row['db_field_name']
                      , $row['db_field'][0]
                      , $row['db_field'][1]
                      , $row['db_field'][4]
                      , ' '
                      , $row['db_field'][3]
                      , $row['db_field'][20]
                  );
    }
    
}
// print_r2($rows);


$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"db_tables-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>