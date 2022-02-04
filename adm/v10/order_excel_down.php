<?php
$sub_menu = "960220";
include_once('./_common.php');

auth_check($auth[$sub_menu],"d");

function column_char($i) { return chr( 65 + $i ); }

// $s_cart_id 로 현재 장바구니 자료 쿼리
$sql = " SELECT a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty, a.ct_status, a.ct_send_cost, a.it_sc_type,
				b.ca_id, b.ca_id2, b.ca_id3, b.it_tel_inq, c.ca_name, d.com_name
				,( SELECT ca_name FROM {$g5['g5_shop_category_table']} WHERE ca_id = SUBSTRING(c.ca_id,1,2) ) as ca_p_name
		FROM {$g5['g5_shop_cart_table']} a 
			LEFT JOIN {$g5['g5_shop_item_table']} b ON ( a.it_id = b.it_id )
			LEFT JOIN {$g5['company_table']} d ON ( d.com_idx = b.com_idx )
			LEFT JOIN {$g5['g5_shop_category_table']} c ON ( c.ca_id = b.ca_id )
		WHERE a.od_id = '$od_id' ";
$sql .= " GROUP BY a.it_id ";
$sql .= " ORDER BY a.ct_id ";
//echo $sql.'<br>';
$result = sql_query($sql);


// 각 항목 설정
$headers = array('부품명','분류','수량','판매가','소계');
$widths  = array(20, 20, 20, 10, 10, 10);
$header_bgcolor = 'FFABCDEF';
$last_char = column_char(count($headers) - 1);

// 엑셀 데이타 출력
include_once(G5_LIB_PATH.'/PHPExcel.php');


// 두번째 줄부터 실제 데이터 입력
$rows = array();
for($i=1; $row=sql_fetch_array($result); $i++) {
    // print_r2($row);

    // 합계금액
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(ct_point * ct_qty) as point,
                    SUM(ct_qty) as qty
                from {$g5['g5_shop_cart_table']}
                where it_id = '{$row['it_id']}'
                and od_id = '$od_id' ";
    $sum = sql_fetch($sql);

    $ca_str = ($row['ca_p_name'] == $row['ca_name']) ? '' : $row['ca_p_name'].' > '.$row['ca_name'];
    
    $rows[] = array($row['it_name']
                  , $ca_str
                  ,$sum['qty']
                  , $row['ct_price']
                  , $sum['price']
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
header("Content-Disposition: attachment; filename=\"order-".date("ymdHi", time()).".xls\"");
header("Cache-Control: max-age=0");

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');

?>