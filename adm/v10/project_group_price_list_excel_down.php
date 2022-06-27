<?php
$sub_menu = "960240";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

function column_char($i) { return chr( 65 + $i ); }

$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
";

$where = array();
$where[] = " prj_status = 'ok' ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
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


$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
            , (SELECT prp_pay_date FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price
            , (SELECT mb_hp FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp
            , (SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_account ) AS prj_mb_name
        {$sql_common}
		{$sql_search}
        {$sql_order}
";
//LIMIT {$from_record}, {$rows}
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );

if (!$count)
    alert("출력할 내역이 없습니다.");

/*
$headers = array('번호','프로젝트명','견적형','타입','업체명','최종고객','프로젝트지시사항','수입지시사항','진행율','상태');
$widths  = array(  10,       40,        10,     10,     16,       16,            60,                60,        10,    10);
*/
// 각 항목 설정
$headers = array('번호','의뢰기업','공사프로젝트','수주금액','수금상태','미수금','미수금(계)','회계담당자','개별수금률','수금예정','VAT미납여부','금액타입','발행예정일','계산서발행일','수금예정일','수금완료일');
$widths  = array(10, 16, 40, 15, 10, 15, 15, 17, 10, 15, 10, 10, 11, 11, 11, 11);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');

// 두번째 줄부터 실제 데이터 입력
$rows = array();
for($i=1; $row=sql_fetch_array($result); $i++) {

    $psql = "   SELECT *
                    , IF( prp_type IN ('manday','buy','etc'), prp_price*-1, prp_price ) AS prp_price2
                    , IF( prp_type IN ('manday','buy','etc'), 2, 1 ) AS prp_sort
                FROM {$g5['project_price_table']}
                WHERE prj_idx = '".$row['prj_idx']."'
                    AND prp_type NOT IN ('submit','nego','order')
                    AND prp_status NOT IN ('trash','delete')
                ORDER BY prp_sort, prp_type, prp_reg_dt
    ";
    // echo $psql.'<br>';
    $p_result = sql_query($psql);
    $p_cnt = $p_result->num_rows;

    //수금완료 합계를 구한다
    $ssql = " SELECT SUM(prp_price) AS sum_price
        FROM {$g5['project_price_table']}
        WHERE prj_idx = '".$row['prj_idx']."'
            AND prp_type NOT IN ('submit','nego','order','')
            AND prp_pay_date != '0000-00-00'
            AND prp_status = 'ok'
    ";
    //echo $ssql;
    $sugeum = sql_fetch($ssql);
    $row['prj_collect_price'] = $sugeum['sum_price'];
    $row['prj_collect_percent'] = ($row['prp_order_price'] > 0) ? round($row['prj_collect_price'] / $row['prp_order_price'] * 100) : 0;
    $row['prj_mi_price'] = $row['prp_order_price'] - $sugeum['sum_price'];
    $row['prj_misu_price'] = number_format($row['prp_order_price'] - $sugeum['sum_price']);
    $misu1_price += $row['prp_order_price'] - $sugeum['sum_price'];
    //계산서발행 미수금(계산서발행일은 있으나 수금완료일이 없는 항목)의 합계를 구한다
    $gsql = " SELECT SUM(prp_price) AS sum_price
        FROM {$g5['project_price_table']}
        WHERE prj_idx = '".$row['prj_idx']."'
            AND prp_type NOT IN ('submit','nego','order')
            AND prp_issue_date != '0000-00-00'
            AND prp_pay_date = '0000-00-00'
            AND prp_status = 'pending'
    ";
    //echo $gsql;
    $gmisu = sql_fetch($gsql);
    $row['prj_gemisu_price'] = $gmisu['sum_price'];
    $row['prj_gemusu_format'] = number_format($row['prj_gemisu_price']);
    $misu2_price += $row['prj_gemisu_price'];

    if($p_cnt){ //--가격레코드가 존재할 경우
        for($j=0;$prow=sql_fetch_array($p_result);$j++) {
            $prow['prc_percent'] = ($row['prp_order_price']) ? @floor(($prow['prp_price'] / $row['prp_order_price']) * 100).'%' : '0%';
            $prow['prp_vat_yn'] = ($prow['prp_vat_yn'])?'미납':'-';
            $prow['prc_type'] = $g5['set_price_type_value'][$prow['prp_type']].(($prow['prp_type'] == 'middle') ? '('.$prow['prp_pay_no'].')' : '');
            $prow['prp_plan_date'] = (strpos($prow['prp_plan_date'],'0000-00-00') === false) ? $prow['prp_plan_date'] : '';
            $prow['prp_issue_date'] = (strpos($prow['prp_issue_date'],'0000-00-00') === false) ? $prow['prp_issue_date'] : '';
            $prow['prp_planpay_date'] = (strpos($prow['prp_planpay_date'],'0000-00-00') === false) ? $prow['prp_planpay_date'] : '';
            $prow['prp_pay_date'] = ($prow['prp_pay_date'] != '0000-00-00') ? $prow['prp_pay_date'] : '-';

            if($j == 0){ //첫번째 라인
                $rows[] = array(
                    ' '.$row['prj_idx']
                    ,' '.$row['com_name']
                    ,' '.$row['prj_name']
                    ,' '.number_format($row['prp_order_price'])
                    ,' '.$row['prj_collect_percent'].'%'
                    ,' '.$row['prj_misu_price']
                    ,' '.$row['prj_gemusu_format']
                    ,' '.$row['prj_mb_hp']
                    ,' '.$prow['prc_percent']
                    ,' '.number_format($prow['prp_price2'])
                    ,' '.$prow['prp_vat_yn']
                    ,' '.$prow['prc_type']
                    ,' '.$prow['prp_plan_date']
                    ,' '.$prow['prp_issue_date']
                    ,' '.$prow['prp_planpay_date']
                    ,' '.$prow['prp_pay_date']
                );
            }else{ //두번째 이상의 라인
                $rows[] = array(
                    ' '
                    ,' '
                    ,' '
                    ,' '
                    ,' '
                    ,' '
                    ,' '
                    ,' '
                    ,' '.$prow['prc_percent']
                    ,' '.number_format($prow['prp_price2'])
                    ,' '.$prow['prp_vat_yn']
                    ,' '.$prow['prc_type']
                    ,' '.$prow['prp_plan_date']
                    ,' '.$prow['prp_issue_date']
                    ,' '.$prow['prp_planpay_date']
                    ,' '.$prow['prp_pay_date']
                );
            }
        }
    }
    else { //-------가격레코드가 없을 경우
        $rows[] = array(
            ' '.$row['prj_idx']
            ,' '.$row['com_name']
            ,' '.$row['prj_name']
            ,' '.number_format($row['prp_order_price'])
            ,' '.$row['prj_collect_percent'].'%'
            ,' '.$row['prj_misu_price']
            ,' '.$row['prj_gemusu_format']
            ,' '.$row['prj_mb_hp']
            ,' '
            ,' '
            ,' '
            ,' '
            ,' '
            ,' '
            ,' '
            ,' '
        );
    }
}

$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->getActiveSheet()->freezePane('A2');
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$excel->setActiveSheetIndex(0)->getStyle( "D:J" )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"income-list-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');