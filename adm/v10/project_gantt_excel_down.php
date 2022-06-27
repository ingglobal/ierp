<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

function column_char($i) { return chr( 65 + $i ); }

$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
"; 

$where = array();
$where[] = " prs.prs_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

if ($st_date && $en_date) {
    $where[] = " prs.prs_start_date <= '$en_date' AND prs.prs_end_date >= '$st_date' ";
}
else if ($st_date) {
    $where[] = " prs.prs_start_date >= '$st_date' ";
}
else if ($en_date) {
    $where[] = " prs.prs_end_date <= '$en_date' ";
}

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_worker' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'prs_role' ) :
            $where[] = " ({$sfl} LIKE '".strtolower($stx)."%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

$sql_order = " ORDER BY prj_idx, prs_role, mb_id_worker, prs_start_date ";

$sql = " SELECT SQL_CALC_FOUND_ROWS prs.*
            , com.com_name AS com_name
            , prs.prj_idx AS prj_idx
            , prj.prj_name AS prj_name
            , mb.mb_name AS mb_name
            , GREATEST('".$st_date."', prs_start_date ) AS st_date
            , LEAST('".$en_date."', prs_end_date ) AS en_date
        {$sql_common}
		{$sql_search}
        {$sql_order}
";
// echo $sql.'<br>';
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );

// 각 항목 설정
$headers = array('번호','업체명','프로젝트명','역할','담당자','작업','작업시작일','작업종료일');
$widths  = array(10, 16, 40, 10, 10, 20, 15, 15);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

// 두번째 줄부터 실제 데이터 입력
$rows = array();
for($i=1; $row=sql_fetch_array($result); $i++) {
    $rows[] = array(
        ' '.$row['prs_idx']
       ,' '.$row['com_name']
       ,' '.$row['prj_name']
       ,' '.strtoupper($row['prs_role'])
       ,' '.$row['mb_name']
       ,' '.$row['prs_task']
       ,' '.$row['prs_start_date']
       ,' '.$row['prs_end_date']
   );
}

$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->getActiveSheet()->freezePane('A2');
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
//$excel->setActiveSheetIndex(0)->getStyle( "J:L" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"schedule-list-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');