<?php
$sub_menu = '960220';
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

/*
$ct_id = array(
	[0] => 112
	[1] => 113
	[2] => 114
);
$ca_str = array(
	[0] => 콘센트  >  안전플러그
	[1] => 온도  >  PT센서
	[2] => 컨넥터  >  MS 컨넥터
);
$it_id = array(
	[0] => 1599220565
	[1] => 1599220555
	[2] => 1599220568
);
$it_name = array(
	[0] => SPT-11(안전)  [콘센트  >  안전플러그]
	[1] => 100옴 150L(HD)  [온도  >  PT센서]
	[2] => 3102A-24  [컨넥터  >  MS 컨넥터]
);
$it_qty = array(
	[0] => 1
	[1] => 1
	[2] => 2
);
$it_buy_price = array(
	[0] => 19800
	[1] => 16500
	[2] => 30800
);
$it_tot_buy_price = array(
	[0] => 19800
	[1] => 16500
	[2] => 61600
);
$total_price => 97900
$records => 3
$od_id => 
$mb_id => super
$od_name => 전산실
$od_email => websiteman@naver.com

$com_idx = 3
$mng_id = 1599388073
$com_name = 세원물산
$mng_name = 김세원
$mng_email = tomasjoa@nate.com
*/
//print_r2($_POST);
//exit;
if($act == 'exel'){
	function column_char($i) { return chr( 65 + $i ); }
	
	// 각 항목 설정
	//$headers = array('상품코드','분류','품명','견적가(판매가)','수량','소계','총계');
	$headers = array('상품코드','분류','품명','견적가(판매가)','수량','소계');
	//$widths  = array(15, 30, 30, 15, 6, 15);
	$widths  = array(15, 30, 30, 15, 6, 15, 15);
	$header_bgcolor = 'FFABCDEF';
	$last_char = column_char(count($headers) - 1);
	//print_r2($_POST);
	
	// 엑셀 데이타 출력
	include_once(G5_LIB_PATH.'/PHPExcel.php');
	
	// 두번째 줄부터 실제 데이터 입력
	$rows = array();
	//for($i=1; $row=sql_fetch_array($result); $i++) {
	for($i=0; $i<count($it_id); $i++) {
		$rows[] = array(
			' '.$it_id[$i]
			,' '.$ca_str[$i]
			,$it_name[$i]
			,$it_buy_price[$i]
			,$it_qty[$i]
			,$it_tot_buy_price[$i]
		);
	}
	
	//$rows[0][6] = $total_price;
	
	//print_r2($rows);exit;
	
	$data = array_merge(array($headers), $rows);
	
	$excel = new PHPExcel();
	$excel->setActiveSheetIndex(0)->getStyle( "A1:${last_char}1" )->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB($header_bgcolor);
	$excel->setActiveSheetIndex(0)->getStyle( "A:$last_char" )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	foreach($widths as $i => $w) $excel->setActiveSheetIndex(0)->getColumnDimension( column_char($i) )->setWidth($w);
	$excel->getActiveSheet()->fromArray($data,NULL,'A1');

	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"items-quot-".date("ymdHi", time()).".xls\"");
	header("Cache-Control: max-age=0");
	
	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
	$writer->save('php://output');
}
else if($act == 'email'){
	//$doc_form = 'deal';
	$doc_arr = array('quot'=>'견적서','order'=>'발주서','deal'=>'거래명세서');
	$orl_no = $od_id.'-'.$doc_form.'-'.G5_SERVER_TIME;
	/*
Array
(
    [com_idx] => 1
    [mng_id] => GeunTaePark
    [doc_form] => quot
    [orl_subject] => 기아자동차 설비
    [com_name] => 아진산업
    [mng_name] => 박근태
    [mng_email] => tomasjoa@nate.com
    [com_biz_no] => 000-00-00000
    [com_tel] => 053-856-9100
    [com_fax] => 053-856-9111
    [com_addr] => 경북 경산시 진량읍 공단8로26길 40   (신제리)
    [od_memo] => 인건비 추가비용이 발생할 수 있습니다.

    [total_price] => 10538500
    [url] => ./orderform.php
    [records] => 4
    [od_id] => 2020102917092497
    [mb_id] => super
    [od_name] => 전산실
    [od_email] => websiteman@naver.com
    [act] => email
    [ct_id] => Array
        (
            [0] => 174
            [1] => 175
            [2] => 176
            [3] => 178
        )

    [it_id] => Array
        (
            [0] => 1603240745
            [1] => 1599220567
            [2] => 1599220562
            [3] => 1603813707
        )

    [ca_str] => Array
        (
            [0] => PLC  >  증설 BASE
            [1] => LS  >  LIMIT S/W
            [2] => PH  >  포토센서
            [3] => ETC  >  LABOR COST
        )

    [it_name] => Array
        (
            [0] => 서보 500DS
            [1] => SZL-WLC-BL3 90도
            [2] => BM3M-TDT 1.2
            [3] => 인건비
        )

    [it_qty] => Array
        (
            [0] => 1
            [1] => 1
            [2] => 1
            [3] => 1
        )

    [it_buy_price] => Array
        (
            [0] => 5500000
            [1] => 15300
            [2] => 23200
            [3] => 5000000
        )

    [it_tot_buy_price] => Array
        (
            [0] => 5500000
            [1] => 15300
            [2] => 23200
            [3] => 5000000
        )
)
	*/
	//echo $od_memo;
	//exit;
	if ($od_email){
		include_once(G5_LIB_PATH.'/mailer.lib.php');
		
		$subject = '['.$mng_name.' 님] 요청하신 '.$doc_arr[$doc_form].'를 전송했습니다..';
		/*
		include_once ('./order_form_update_mail.php');
		exit;
		*/
		ob_start();
		include_once ('./order_form_update_mail.php');
		$content = ob_get_contents();
		ob_end_clean();
		/*
		echo $content;
		exit;
		*/		
		mailer($od_name, $od_email, $mng_email, $subject, $content, 1);	

		//$g5['order_log_table']
		
		$sql = " INSERT {$g5['order_log_table']} 
					SET od_id = '{$od_id}'
						,orl_no = '{$orl_no}'
						,orl_com_name = '{$com_name}'
						,orl_com_biz_no = '{$com_biz_no}'
						,orl_com_manager = '{$mng_name}'
						,orl_com_tel = '{$com_tel}'
						,orl_com_fax = '{$com_fax}'
						,orl_com_email = '{$com_email}'
						,orl_com_addr = '{$com_addr}'
						,orl_sender_id = '{$mb_id}'
						,orl_receiver_id = '{$mng_id}'
						,orl_type = '{$doc_form}'
						,orl_subject = '{$orl_subject}'
						,orl_memo = '{$od_memo}'
						,orl_reg_dt = NOW()
		";
		sql_query($sql,1);
	}else{
		alert('발신자 email정보가 없습니다.');
	}
	
	alert($com_name.'의 '.$mng_name.'님께 email을 전송하였습니다.','./order_form.php?od_id='.$od_id);
}
?>