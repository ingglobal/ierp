<?php
$sub_menu = "960220";
include_once('./_common.php');

auth_check($auth[$sub_menu],"d");

$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);


// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
$objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 읽기전용으로 설정
//$objReader->setReadDataOnly(true);

// 엑셀파일을 읽는다
$objExcel = $objReader->load($filename);

// 첫번째 시트를 선택
$objExcel->setActiveSheetIndex(0);

$objWorksheet = $objExcel->getActiveSheet();

$rowIterator = $objWorksheet->getRowIterator();
foreach ($rowIterator as $row) { // 모든 행에 대해서
	$cellIterator = $row->getCellIterator();
	$cellIterator->setIterateOnlyExistingCells(false); 
}
$maxRow = $objWorksheet->getHighestRow();
$maxColumn = $objWorksheet->getHighestDataColumn();
//echo $maxRow.'<br>';
//echo $maxColumn.'<br>';


$g5['title'] = '엑셀 업로드';
// include_once('./_top_menu_stat_data.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once ('./_tail.php');
?>

<?php
$eidx = 0;  // 엑셀 카운터

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();


for($i = 2 ; $i <= $maxRow ; $i++) {
    $cnt++;

    // from A to Z
    //'고유코드','ca_id','구분','품명','형식(부품명)','매입처(제조사)','매입가','견적가(판매가)','재고'
    for($j=65;$j<=90;$j++) {
        // echo chr($j);
        if($objWorksheet->getCell(chr($j).$i)->getValue()) {
            ${'data'.chr($j)}[$i] = $objWorksheet->getCell(chr($j).$i)->getValue();
            ${'data'.chr($j)}[$i] = trim(${'data'.chr($j)}[$i]);
            // echo '===================================== '.${'data'.chr($j)}[$i].'<br>';
        }
    }
    
    // 변수 생성
    // $dataA[$i] = ($dataA[$i]) ? $dataA[$i] : $item['매출기준일'][$i];

    if( is_numeric($dataA[$i]) ) {

        // remove all characters which is not number
        $dataG[$i] = preg_replace("/[^0-9]*/s", "", $dataG[$i]);    // 매입가
        $dataH[$i] = preg_replace("/[^0-9]*/s", "", $dataH[$i]);    // 견적가(판매가)
        $dataI[$i] = preg_replace("/[^0-9]*/s", "", $dataI[$i]);    // 재고
        
        $sql_common = " it_name	        = '".$dataE[$i]."',
                        it_buy_price	= '".$dataG[$i]."',
                        it_price	    = '".$dataH[$i]."',
                        it_stock_qty	= '".$dataI[$i]."'
        ";
        
        // create if not exists, update for existing
        $sql = "	SELECT it_id FROM {$g5['g5_shop_item_table']} 
                                WHERE it_id = '".$dataA[$i]."'
        ";
        // echo $sql.'<br>';
        $it = sql_fetch($sql,1);
        if(!$it['it_id']) {
            // $sql = "INSERT INTO {$g5['g5_shop_item_table']} SET
            //             cod_status = 'ok',
            //             cod_reg_dt = '".G5_TIME_YMDHIS."',
            //             cod_update_dt = '".G5_TIME_YMDHIS."',
            //             {$sql_common}
            // ";
            // if(!$demo) {sql_query($sql,1);}
        }
        else {
            $sql = "UPDATE {$g5['g5_shop_item_table']} SET
                        {$sql_common}
                    WHERE it_id = '".$it['it_id']."'
            ";
            if(!$demo) {sql_query($sql,1);}
        }
        if($demo) {echo $sql.'<br>';}
			
        $eidx++;

        // 메시지 보임
        echo "<script> document.all.cont.innerHTML += '".$cnt
                .". ".$dataE[$i].", 매입가:".$dataG[$i].", 견적가:".$dataH[$i].", 재고:".$dataI[$i]
                ." >> 처리완료<br>'; </script>\n";
        
        flush();
        ob_flush();
        ob_end_flush();
        usleep($sleepsec);
        
        // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        if ($cnt % $countgap == 0)
            echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
        
        // 화면 정리! 부하를 줄임 (화면 싹 지움)
        if ($cnt % $maxscreen == 0)
            echo "<script> document.all.cont.innerHTML = ''; </script>\n";
        
    }

}
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($eidx) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>