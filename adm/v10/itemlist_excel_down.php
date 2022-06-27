<?php
$sub_menu = "960220";
include_once('./_common.php');

auth_check($auth[$sub_menu],"d");

function column_char($i) { return chr( 65 + $i ); }

$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

if ($sfl == "")  $sfl = "it_name";

$sql_common = " from {$g5['g5_shop_item_table']} a ,
                     {$g5['g5_shop_category_table']} b
               where a.ca_id = b.ca_id ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "a.ca_id";
    $sod = "asc";
}
$sql_order = "order by $sst $sod";

// $sql  = " select *
//            $sql_common
//            $sql_order
//            limit $from_record, $rows
// ";
$sql  = " select *
           $sql_common
           $sql_order
";
$result = sql_query($sql);
//echo $sql;
if (!$total_count)
    alert("출력할 내역이 없습니다.");


// 각 항목 설정
$headers = array('고유코드','ca_id','구분','품명','형식(부품명)','매입처(제조사)','매입가','견적가(판매가)','재고');
$widths  = array(15, 10, 20, 20, 20, 20, 10, 15, 15);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');


// 두번째 줄부터 실제 데이터 입력
$rows = array();
for($i=1; $row=sql_fetch_array($result); $i++) {
    // print_r2($row);
    $com = get_table_meta('company','com_idx',$row['com_idx']);    

    // 상태
    $row['evt_status_text'] = $g5['set_evt_status_value'][$row['evt_status']];
    
    $rows[] = array(' '.$row['it_id']
                  , ' '.$row['ca_id']
                  , $row['ca_name']
                  , $row['ca_name']
                  , $row['it_name']
                  , $com['com_name']
                  , $row['it_buy_price']
                  , $row['it_price']
                  , $row['it_stock_qty']
              );
}
// print_r2($rows);
// exit;


$data = array_merge(array($headers), $rows);

$excel = new PHPExcel();
$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
$excel->getActiveSheet()->fromArray($data,NULL,'A1');

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"item-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>