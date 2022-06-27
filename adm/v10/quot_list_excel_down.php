<?php
$sub_menu = "960210";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

function column_char($i) { return chr( 65 + $i ); }

$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mbc ON mbc.mb_id = prj.mb_id_company
                    LEFT JOIN {$g5['member_table']} AS mbs ON mbs.mb_id = prj.mb_id_saler
"; 
$where = array();
$where[] = " prj_status IN ('inprocess','pending','ng','ok') ";   // 디폴트 검색조건

// // 운영권한이 없으면 자기 업체만
// if (!$member['mb_manager_yn']) {
//     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
// }

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler') :
			$where[] = " (mbs.mb_name LIKE '%{$stx}%' ) ";
            break;
		case ($sfl == 'mb_id_company' ) :
			$where[] = " (mbc.mb_name LIKE '%{$stx}%' ) ";
            break;
		case ($sfl == 'prj_name' || $sfl == 'prj_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
		case ($sfl == 'prj_status') :
			$stx = $g5['set_prj_status_reverse'][$stx];
			$where[] = " ({$sfl} = '{$stx}') ";
			break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "prj_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'submit' AND prp_status = 'ok' ) AS prp_submit_price
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'nego' AND prp_status = 'ok' ) AS prp_nego_price
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price
        {$sql_common}
		{$sql_search}
        {$sql_order}		
";
//LIMIT {$from_record}, {$rows} 

//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$count)
    alert("출력할 내역이 없습니다.");



// 각 항목 설정
$headers = array('번호','업체명','최종고객','프로젝트명','업체견적담당','영업담당','요청날짜','제출날짜','발행번호','NEGO금액','제출금액','수주금액','등록일','상태');
$widths  = array(10, 16, 16, 40, 18, 15, 11, 11, 16, 15, 15, 15, 11, 10);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

// 두번째 줄부터 실제 데이터 입력
$rows = array();
for($i=1; $row=sql_fetch_array($result); $i++) {
    $row['com_mng_arr'] = get_member($row['mb_id_company'],'mb_name');
    $row['com_mng_name'] = $row['com_mng_arr']['mb_name'];
    $rsql = "SELECT cmm_title FROM {$g5['company_member_table']}
                WHERE mb_id = '".$row['mb_id_company']."'
                   AND com_idx = '".$row['com_idx']."'
             ORDER BY cmm_reg_dt DESC
            LIMIT 1
    ";
    $rmb = sql_fetch($rsql,1);
    $row['com_mng_rank'] = $g5['set_mb_ranks_value'][$rmb['cmm_title']];

    $row['com_mng'] = $row['com_mng_name'].' '.$row['com_mng_rank'];
    $row['saler_arr'] = get_member($row['mb_id_saler'],'mb_name');
    $row['saler_name'] =$row['saler_arr']['mb_name'];
    $row['prj_status'] = $g5['set_prj_status_value'][$row['prj_status']];

    $rows[] = array(
         ' '.$row['prj_idx']
        ,' '.$row['com_name']
        ,' '.$row['prj_end_company']
        ,' '.$row['prj_name']
        ,' '.$row['com_mng']
        ,' '.$row['saler_name']
        ,' '.$row['prj_ask_date']
        ,' '.$row['prj_submit_date']
        ,' '.$row['prj_doc_no']
        ,' '.number_format($row['prp_nego_price'])
        ,' '.number_format($row['prp_submit_price'])
        ,' '.number_format($row['prp_order_price'])
        ,' '.substr($row['prj_reg_dt'],0,10)
        ,' '.$row['prj_status']
    );
}

$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->getActiveSheet()->freezePane('A2');
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "J:L" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"quot-list-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
?>