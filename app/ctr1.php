<?php
include_once('./_common.php');

// 계약 요청 메일에 대한 인증 처리
// 발송페이지: /adm/v10/intra2_contract_list.php
// ex: http://c.ncts.kr/user/ctr1.php?1336

// 끝에 일련번호(ctr_idx)만 추출
$uri_array = explode("?",$_SERVER['REQUEST_URI']);
$ctr_idx = $uri_array[1];

if(!$ctr_idx)
	alert('계약 정보가 존재하지 않습니다.',G5_URL);
else {
	$ctr = get_table_meta('intra2_contract','ctr_idx',$ctr_idx);
    if(!$ctr['ctr_idx'])
        alert('계약 정보가 존재하지 않습니다.',G5_URL);
}

$g5['title'] = "계약 확인 및 승인하기";
include_once('./_head.php');


$ctr['ctr_company'] = 3;

// 치환 패턴 정의
$pattern1 = array('/{법인명}/', '/{업체명}/', '/{이름}/', '/{회원아이디}/', '/{HOME_URL}/');
$replace1 = array($g5['board']['setting2_name'][$ctr['ctr_company']], $ctr['ctr_com_name'], $ctr['ctr_mb_name'], $ctr['ctr_mb_id'], '<a href="'.$g5['board']['setting2_com_homepage'][$ctr['ctr_company']].'">'.$g5['board']['setting2_com_homepage'][$ctr['ctr_company']].'</a>');


// 디폴트값
$ctr['ctr_prd_name'] = $ctr['ctr_prd_name'] ?: $g5['setting']['set_contract_it_names'];    //온라인 컨설팅 및 제휴상품
$ctr['ctr_remark'] = $ctr['ctr_remark'] ?: get_text($g5['setting']['set_contract_memo']);
//echo $ctr['ctr_remark'];


// 상품목록
$sql2 = "   SELECT *
            FROM {$g5['intra2_ctr_work_table']} AS ctw
                LEFT JOIN {$g5['intra2_work_table']} AS wrk ON wrk.no = ctw.work_no
            WHERE ctr_idx = '".$ctr['ctr_idx']."'
                AND work_status NOT IN (10)
            ORDER BY work_update_date DESC
";
//echo $sql2.'<br>';
$rs2 = sql_query($sql2);
for($j=0; $row2=sql_fetch_array($rs2); $j++) {
    $row2['prd'] = get_table_meta('intra2_product','prd_idx',$row2['prd_idx']);
//    print_r2($row2['prd']);

    // 상품계약서 배열 (폼에서 사용)
    if($row2['prd']['prd_contract']) {
        $ctr['ctr_prd_contract_title'][] .= $row2['prd']['prd_name'];
        $ctr['ctr_prd_contract'][] .= $row2['prd']['prd_contract'];
    }

    // 상품이름 배열
    $prd_names[] = $row2['prd']['prd_name'];

}
// 상품내용은 상품명 연결
$ctr['ctr_prd_content'] = $ctr['ctr_prd_content'] ?: implode(", ",$prd_names);


// 결제목록
$sql3 = "   SELECT *
            FROM {$g5['intra2_ctr_payment_table']} AS ctp
                LEFT JOIN {$g5['intra2_payment_detail_table']} AS pay ON pay.no = ctp.pay_no
            WHERE ctr_idx = '".$ctr['ctr_idx']."'
                AND payment_date > '0000-00-00'
            ORDER BY payment_date DESC
";
//echo $sql3.'<br>';
$rs3 = sql_query($sql3);
for($j=0; $row3=sql_fetch_array($rs3); $j++) {

	// 결제정보 (serialize)
	$pay_infos = unserialize($row3['payment_content']);
//	print_r2($pay_infos);
	if(is_array($pay_infos)) {
		foreach($pay_infos as $key => $value) {
			//echo $key.$value.'<br>';
			$row3[$key] = $value;
		}
	}
//    print_r2($row3);

    // 결제정보 0=카드,1=무통장,2=기타
	if( $row3['payment_type'] == '0' ) {
		$row3['pay_item2_name'] = '카드명';
		$row3['pay_bank_company'] = $g5['set_intra2_payment_sub_type_card_value'][$row3['payment_sub_type']];
		$row3['pay_item4_name'] = '할부개월';
		$row3['pay_item4_value'] = ($row3['divisionCnt']=='0') ? '일시불' : $row3['divisionCnt'].'개월';	// 할부기간
		$row3['pay_item5_name'] = '승인일';
	}
	else if( $row3['payment_type'] == '1' ) {
		$row3['pay_item2_name'] = '은행명';
		$row3['pay_bank_company'] = $g5['set_intra2_payment_sub_type_bank_value'][$row3['payment_sub_type']];
		$row3['pay_item4_name'] = '입금자';
		$row3['pay_item4_value'] = $row3['deposit_name'];
		$row3['pay_item5_name'] = '입금일';
	}
	else {
		$row3['pay_item5_name'] = '승인일';
	}
	
	// 승인일시
	$row3['payment_date_text'] = ($row3['payment_date']=='0000-00-00 00:00:00') ? '-' : substr($row3['payment_date'],0,10) ;

    // 출력 변수 설정
	$ctr['ctr_payment_info'] .= '
        <table>
            <tr>
                <td class="row1">결제방법</td>
                <td>'.$g5['set_intra2_payment_type_value'][$row3['payment_type']].'</td>
                <td class="row1">'.$row3['pay_item2_name'].'</td>
                <td>'.$row3['pay_bank_company'].'</td>
                <td class="row1">금액</td>
                <td>'.number_format($row3['price']).'원</td>
                <td class="row1">'.$row3['pay_item4_name'].'</td>
                <td>'.$row3['pay_item4_value'].'</td>
                <td class="row1">'.$row3['pay_item5_name'].'</td>
                <td>'.$row3['payment_date_text'].'</td>
            </tr>
        </table>
    ';
    
    // 출력 변수 설정 (모바일용)
    $row3['pay_item2_display'] = ($row3['pay_item2_name']) ?: 'display:none;';
    $row3['pay_item4_display'] = ($row3['pay_item4_name']) ?: 'display:none;';
    $row3['pay_item5_display'] = ($row3['pay_item5_name']) ?: 'display:none;';
	$ctr['ctr_payment_info_mobile'] .= '
        <table>
            <tr>
                <td class="row1">결제방법</td>
                <td>'.$g5['set_intra2_payment_type_value'][$row3['payment_type']].'</td>
            </tr>
            <tr style="'.$row3['pay_item2_display'].'">
                <td class="row1">'.$row3['pay_item2_name'].'</td>
                <td>'.$row3['pay_bank_company'].'</td>
            </tr>
            <tr>
                <td class="row1">금액</td>
                <td>'.number_format($row3['price']).'원</td>
            </tr>
            <tr style="'.$row3['pay_item4_display'].'">
                <td class="row1">'.$row3['pay_item4_name'].'</td>
                <td>'.$row3['pay_item4_value'].'</td>
            </tr>
            <tr style="'.$row3['pay_item5_display'].'">
                <td class="row1">'.$row3['pay_item5_name'].'</td>
                <td>'.$row3['payment_date_text'].'</td>
            </tr>
        </table>
    ';
}
// 결제내역 없음
if($j==0) {
	$ctr['ctr_payment_info'] = $ctr['ctr_payment_info_mobile'] = '
        <table>
            <tr>
                <td>결제 정보 없음</td>
            </tr>
        </table>
    ';
}


// 인감도장 파일
$bo_table = 'setting2';
$ctr['board']['wr_id'] = $ctr['ctr_company'];
$board['bo_mobile_gallery_width'] = $board['bo_mobile_gallery_height'] = 60;
$ctr['board']['files'] = get_file($bo_table, $ctr['board']['wr_id']);
//print_r2($ctr['board']);
if( $ctr['board']['files'][0]['file'] ) {
    //echo $ctr['board']['files'][0]['file'].'<br>';
    $ctr['board']['thumbnail'] = thumbnail($ctr['board']['files'][0]['file'], 
                G5_DATA_PATH.'/file/'.$bo_table, G5_DATA_PATH.'/file/'.$bo_table,
                $board['bo_mobile_gallery_width'], $board['bo_mobile_gallery_height'],
                false, true, 'center', true, '85/3.4/15'
    );
    $thumb['src'] = G5_DATA_URL.'/file/'.$bo_table.'/'.$ctr['board']['thumbnail'];
    //echo $thumb['src'].'<br>';
    $ingam_image = '<img class="ingam_" src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" width="'.$board['bo_mobile_gallery_width'].'" height="'.$board['bo_mobile_gallery_height'].'">';
}


// 스킨 파일
if(check_dt($ctr['ctr_mb_auth_dt']))
    include_once($user_skin_path.'/intra2_contract_agree_done.skin.php');
else
    include_once($user_skin_path.'/intra2_contract_agree_form.skin.php');

include_once('./_tail.php');
?>