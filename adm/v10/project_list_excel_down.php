<?php
$sub_menu = "960210";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

function column_char($i) { return chr( 65 + $i ); }

$sql_common = " FROM {$g5['project_table']} AS prj
									LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
";

$where = array();
//request=견적요청,inprocess=견적중, pending=보류, ng=수주취소, ok=수주완료,etc=기타, trash=삭제
$where[] = " prj.prj_status NOT IN ('trash','delete','inprocess','pending','ng') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " (prj.{$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (prj.mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'prj_nick' ) :
            $where[] = " (prj.{$sfl} LIKE '{$stx}%') ";
            break;
		case ($sfl == 'com_name' ) :
            $where[] = " (com.{$sfl} LIKE '%{$stx}%') ";
            break;
        default :
            $where[] = " (prj.{$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    //$sst = "prs.prj_idx";
    $sst = "prj.prj_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " SELECT SQL_CALC_FOUND_ROWS * 
        {$sql_common}
		{$sql_search}
        {$sql_order}
";
//LIMIT {$from_record}, {$rows}
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
//$total_count = $count['total'];
//$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

if (!$count)
    alert("출력할 내역이 없습니다.");

// 각 항목 설정
$headers = array('번호','프로젝트명','견적형','타입','업체명','최종고객','프로젝트지시사항','수입지시사항','진행율','상태');
$widths  = array(10, 40, 10, 10, 16, 16, 60, 60, 10, 10);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

// 두번째 줄부터 실제 데이터 입력
$rows = array();
for($i=1; $row=sql_fetch_array($result); $i++) {
    $row['prj_quot_yn'] = ($row['prj_quot_yn']) ? '견적형' : '-';
    $row['prj_type'] = ($row['prj_type']) ? $g5['set_prj_type_value'][$row['prj_type']] : '-';
    $row['prj_content'] = cut_str($row['prj_content'],45);
    $row['prj_content2'] = cut_str($row['prj_content2'],45);
    $row['prj_percent'] = $row['prj_percent'].'%';
    $row['prj_status'] = ($row['prj_status']) ? $g5['set_prj_status_value'][$row['prj_status']] : '-';
    $rows[] = array(
        ' '.$row['prj_idx']
        ,' '.$row['prj_name']
        ,' '.$row['prj_quot_yn']
        ,' '.$row['prj_type']
        ,' '.$row['com_name']
        ,' '.$row['prj_end_company']
        ,' '.$row['prj_content']
        ,' '.$row['prj_content2']
        ,' '.$row['prj_percent']
        ,' '.$row['prj_status']
    );
}
$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->getActiveSheet()->freezePane('A2');
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//$excel->setActiveSheetIndex(0)->getStyle( "J:L" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"project-list-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');