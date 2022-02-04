<?php
$sub_menu = "960220";
include_once('./_common.php');

auth_check($auth[$sub_menu],"d");

function create_cat($ca_name,$pa_name='') {
    global $g5;

    // 나랑 일치하는 코드 추출
    $sql = " select ca_id AS ca_id_parent from {$g5['g5_shop_category_table']}
              where ca_name = TRIM('".$pa_name."') ";
    $row = sql_fetch($sql);
    $ca_id = $row['ca_id_parent'];

    $len = strlen($ca_id);
    $len2 = $len + 1;

    // 동 레벨 분류 max 추출
    $sql = " select MAX(SUBSTRING(ca_id,$len2,2)) as max_subid from {$g5['g5_shop_category_table']}
              where SUBSTRING(ca_id,1,$len) = '$ca_id' ";
    $row = sql_fetch($sql);
    $subid = base_convert($row['max_subid'], 36, 10);   // 36진수를 10진수로
    $subid += 2;   // +36
    $subid = base_convert($subid, 10, 36);  // 10진수를 다시 36진수로
    $subid = substr("00" . $subid, -2); // 뒤에서 두자리 추출
    $subid = $ca_id . $subid;

    // 같은 이름의 카테고리가 존재하면 디비 키값 리턴, 없으면 생성하고 생성된 코드값을 리턴
    $sql = " select ca_id from {$g5['g5_shop_category_table']}
              where ca_name = TRIM('".$ca_name."') ";
    $row = sql_fetch($sql);
    if(!$row['ca_id']) {
        $sql = " insert {$g5['g5_shop_category_table']} SET
                    ca_id   = '$subid',
                    ca_name = '$ca_name',
                    ca_skin         = 'list.10.skin.php',
                    ca_mobile_skin          = 'list.10.skin.php',
                    ca_img_width            = '230',
                    ca_img_height           = '230',
                    ca_list_mod             = '3',
                    ca_list_row             = '5',
                    ca_mobile_img_width     = '230',
                    ca_mobile_img_height    = '230',
                    ca_mobile_list_mod      = '3',
                    ca_mobile_list_row      = '5',
                    ca_use                  = '1',
                    ca_stock_qty            = '99999',
                    ca_explan_html          = '1'
        ";
        sql_query($sql);
    }
    else {
        $subid = $row['ca_id'];
    }

    return $subid;
}


$demo = 1;  // 데모모드 = 1
$com_idxs = array(
    "신아시스템"=>8
    , "아라에프에이"=>9
    , "두성시스템"=>10
    , "정우단가"=>11
    , "대인단가"=>12
);

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);


// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
$objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 읽기전용으로 설정
// $objReader->setReadDataOnly(true);

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

$row = array();
$c1 = -1; // category index, 1st, 2nd
for($i = 2 ; $i <= $maxRow ; $i++) {
    $cnt++;

    // if($i>3)
    //     exit;

    $dataA[$i] = trim( $objWorksheet->getCell('A'.$i)->getValue() ); // 엑셀번호
    $dataB[$i] = trim( $objWorksheet->getCell('B'.$i)->getValue() ); // 구분
    $dataC[$i] = trim( $objWorksheet->getCell('C'.$i)->getValue() ); // 품명
    $dataD[$i] = trim( $objWorksheet->getCell('D'.$i)->getValue() ); // 형식
    $dataE[$i] = trim( $objWorksheet->getCell('E'.$i)->getValue() ); // 단위
    $dataF[$i] = trim( $objWorksheet->getCell('F'.$i)->getCalculatedValue() ); // 견적가 (cell calculated value)
    $dataG[$i] = trim( $objWorksheet->getCell('G'.$i)->getValue() ); // 신아시스템
    $dataH[$i] = trim( $objWorksheet->getCell('H'.$i)->getValue() ); // 아아에프에이
    $dataI[$i] = trim( $objWorksheet->getCell('I'.$i)->getValue() ); // 두성시스템

    // echo '=========================================================== '.$i.' line starts. <br>';
    // A ~ Z 각 항목 for loop.
    for($j = 65 ; $j <= 90 ; $j++) {
        // $item['A'] = '번호';... // 모든 항목이름을 변수로!
        // 번호, 구분, 품명, 형식, 단위, 견적가, 신아시스템, 아라에프에이, 두성시스템...
        if($i==2) {
            $item[chr($j)] = ${"data".chr($j)}[$i];
            continue;
        }
        else {
            // $row[$i]['번호'], $row[$i]['구분'], $row[$i]['품명'].... 
            $row[$i][$item[chr($j)]] = trim( ${"data".chr($j)}[$i] );
        }
        // if(${"data".chr($j)}[$i]) {
        //     echo '============================================ '.$item[chr($j)].': '.${"data".chr($j)}[$i].'<br>';
        // }
    }
    
    // if( $row[$i]['형식'] && is_numeric( $row[$i]['견적가'] ) ) {
    if( $row[$i]['형식'] ) {
        // 구분, 품명은 없으면 이전값으로 대체
        $row[$i]['구분'] = $row[$i]['구분'] ?: $old['구분'];
        $row[$i]['품명'] = $row[$i]['품명'] ?: $old['품명'];

        // 1차 카테고리
        if($row[$i]['구분'] != $old['구분']) {
            $c1++;
            $c2=-1;
        }
        $cat[$c1] = $row[$i]['구분'];

        // 2차 카테고리
        if($row[$i]['품명'] != $old['품명']) {
            $c2++;
        }
        $cat[$row[$i]['구분']][$c2] = $row[$i]['품명'];

        // print_r2($row[$i]);

        $ca_id_parent = create_cat($row[$i]['구분']);    //1차 카테고리 업데이트
        $ca_id = create_cat($row[$i]['품명'],$row[$i]['구분']);    //2차 카테고리 업데이트
        // echo "<script> document.all.cont.innerHTML += '".$ca_id.". [".$row[$i]['구분'].", ".$row[$i]['품명']."] >> 처리완료<br>'; </script>\n";

        $sql_common = " ca_id   = '$ca_id',
            it_name             = TRIM('".$row[$i]['형식']."'),
            it_cust_price       = TRIM('".$row[$i]['견적가']."'),
            it_price            = TRIM('".$row[$i]['견적가']."'),
            it_use              = '1',
            it_stock_qty        = '9999999',
            it_order            = '0'
        ";

        // 업체 이름만큼 배열 loop
        foreach($com_idxs AS $k1 => $v1) {
            if($row[$i][$k1]) {

                // $it_id = 1599210000 + $eidx;
                $it_id = 1599220000 + $eidx;

                // 같은 이름의 부품이 있는지
                $sql = "SELECT it_id FROM {$g5['g5_shop_item_table']}
                        WHERE it_name = TRIM('".$row[$i]['형식']."')
                            AND com_idx = '".$v1."'
                ";
                $one = sql_fetch($sql);
                if(!$one['it_id']) {

                    $sql = " insert {$g5['g5_shop_item_table']} SET
                                    it_id   = '$it_id'
                                    , it_maker = '$k1'
                                    , com_idx = '$v1'
                                    , it_buy_price = '".$row[$i][$k1]."'
                                    , it_time = '".G5_TIME_YMDHIS."'
                                    , it_update_time = '".G5_TIME_YMDHIS."'
                                    , $sql_common	";
                    sql_query($sql,1);

                }
                else {
                    $sql_common .= "  ";
                    $sql = " update {$g5['g5_shop_item_table']} SET
                                it_update_time = '".G5_TIME_YMDHIS."'
                                , it_buy_price = '".$row[$i][$k1]."'
                                , $sql_common
                            where it_id = '".$one['it_id']."' ";
                    sql_query($sql,1);
                }
                // echo $sql.'<br>';


                $eidx++;

                // 메시지 보임
                echo "<script> document.all.cont.innerHTML += '".$cnt.". [".$row[$i]['구분'].", ".$row[$i]['품명']."] ".$row[$i]['형식']." >> 처리완료<br>'; </script>\n";
                
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

    }

    // 구분, 품명 이전값 저장, 값이 없으면 이전 값과 같음
    $old['구분'] = $row[$i]['구분'] ?: $old['구분'];
    $old['품명'] = $row[$i]['품명'] ?: $old['품명'];


}
// 카테고리 먼저 만들자.
// print_r2($cat);
// for($i = 0 ; $i < sizeof($cat) ; $i++) {
//     if($cat[$i]) {
//         echo '============================================'.$i.': '.$cat[$i].'<br>';
//         echo '============================================'.$i.': '.create_cat($cat[$i]).'<br>';
//         for($j = 0 ; $j < sizeof($cat[$cat[$i]]) ; $j++) {
//             echo '==============================================='.$j.': '.$cat[$cat[$i]][$j].'<br>';
//             echo '==============================================='.$j.': '.create_cat($cat[$cat[$i]][$j],$cat[$i]).'<br>';
//         }

//     }
// }
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($eidx) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>