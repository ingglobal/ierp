<?php
include_once('./_common.php');

// 자료사용동의 요청 메일에 대한 인증 처리
// 발송페이지: /adm/v10/order_contract_list_update.php 
// ex: https://woogle.kr/user/od1.php?201909161102

// 끝에 일련번호(od_id)만 추출 
$uri_array = explode("?",$_SERVER['REQUEST_URI']);
$od_id = $uri_array[1];

if(!$od_id)
	alert('계약 정보가 존재하지 않습니다.',G5_URL);
else {
	$od = get_table_meta('g5_shop_order','od_id',$od_id,'shop_order');
    if(!$od['od_id'])
        alert('계약 정보가 존재하지 않습니다.',G5_URL);
	$mb = get_table_meta('member','mb_id',$od['mb_id']);
	$com = get_table_meta('company','com_idx',$od['com_idx']);
	$mb1 = get_table_meta('member','mb_id',$od['mb_id_saler']);
}

$g5['title'] = "계약 확인 및 승인하기";
include_once('./_head.php');


// 치환 패턴 정의
$pattern1 = array('/{법인명}/', '/{업체명}/', '/{이름}/', '/{회원아이디}/', '/{HOME_URL}/');
$replace1 = array($g5['board']['setting2_name'][$od['od_company']], $com['com_name'], $od['od_name'], $od['mb_id'], '<a href="'.G5_URL.'">'.G5_URL.'</a>');


// 디폴트값
$od['od_contract_it_names'] = $od['od_contract_it_names'] ?: $g5['setting']['set_contract_it_names'];    //온라인 컨설팅 및 제휴상품
$od['od_contract_memo'] = $od['od_contract_memo'] ?: get_text($g5['setting']['set_contract_memo']);


// 주문총액 = 상품구입금액 + 배송비 + 추가배송비
$amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'];


// 상품목록, shop_admin/ajax.orderitem.php 참조 -----------------------------------------------------
// 옵션이 있는 경우 각각 레코드가 따로 등록되므로 장바구니 보여줄 때는 it_id 단위로 GROUP BY 해서 묶은 다음 
// 다시 분리해서 보여줘야 옵션까지 제대로 보여줄 수 있다.
$sql2 = "	SELECT * FROM {$g5['g5_shop_cart_table']} WHERE od_id = '".$od['od_id']."' AND ct_status NOT IN ('".implode("','",$g5['set_exclude_ct_status_array'])."') GROUP BY it_id ORDER BY ct_id ";
$rs2 = sql_query($sql2);
for($j=0; $row2=sql_fetch_array($rs2); $j++) {
    // 상품표현: 상품명 일단 표현후 옵션들은 추가, 추가..
    //$row['cart_list'] .= '<div class="item_main">'.$row2['it_name'].' <span style="color:#818181;font-weight:normal;">('.$row2['ct_status'].')</span></div>';	// 상품명

    $row['it'] = get_table_meta('g5_shop_item','it_id',$row2['it_id'],'shop_item');
    //$row['it'] = get_meta('shop_item',$row2['it_id']);
    //print_r2($row['it']);

    // 상품계약서
    if($row['it']['it_contract']) {
        $od['od_it_contract_title'][] .= $row['it']['it_name'];
        $od['od_it_contract'][] .= $row['it']['it_contract'];
    }

    // 상품의 옵션정보
    $sql3 = " SELECT ct_id, it_id, ct_price, ct_qty, ct_option, ct_status, ct_history, cp_price, ct_send_cost, io_type, io_price, ct_select_time
                FROM {$g5['g5_shop_cart_table']}
                WHERE od_id = '".$od['od_id']."'
                    AND it_id = '".$row2['it_id']."'
                    AND ct_status NOT IN ('".implode("','",$g5['set_exclude_ct_status_array'])."')
                ORDER BY io_type asc, ct_id asc
    ";
    $rs3 = sql_query($sql3);
    for($k=0; $opt=sql_fetch_array($rs3); $k++) {

        // 추가옵션상품(io_type=1), 선택(필수)옵션(io_type=0)
        $opt['item_option_class'] = ($opt['io_type']) ? 'item_option_add' : 'item_option';
        if($opt['io_type'])
            $opt_price = $opt['io_price'];	// 추가옵션 상품은 단가=io_price
        else
            $opt_price = $opt['ct_price'] + $opt['io_price'];	// 선택(필수)옵션 상품은 단가=상품가격+옵션가격

        // 소계
        $ct_price['stotal'] = $opt_price * $opt['ct_qty'];	// 상품 sub_total
        $ct_point['stotal'] = $opt['ct_point'] * $opt['ct_qty'];	// 포인트 합계

        // 상품표현: 옵션아이템들 추가
        $row['cart_list'] .= '<div class="'.$opt['item_option_class'].'">'.$opt['ct_option'].': <span class="item_option_price">'.number_format($opt_price).'×'.$opt['ct_qty'].'='.number_format($ct_price['stotal']).'</span> <span class="span_ct_id">('.$opt['ct_id'].')</span></div>';	// 옵션명: 20,000*2=40,000
        $row['cart_it_names'][] = $opt['ct_option'];

        // 히스토리 + 수당을 한 div 박스에 넣기
        $row['cart_list'] .= '<div class="div_history_sales">'.$opt['cart_list'].$opt['sales_list'].'</div>';
    }
}
//echo $row['cart_list'];

// 상품내용은 상품명 연결
$od['od_contract_it_content'] = $od['od_contract_it_content'] ?: implode(", ",$row['cart_it_names']);


// 결제 정보
$sql = " SELECT *
			FROM {$g5['order_payment_table']}
			WHERE opa_status IN ('ok') 
				AND od_id = '".$od['od_id']."'
			ORDER BY opa_update_dt DESC
";
$result = sql_query($sql,1);
//echo $sql.'<br>';
for ($i=0; $row=sql_fetch_array($result); $i++) {

    // 메타 추출
    $row['mta'] = get_meta('order_payment',$row['opa_idx']);
    if(is_array($row['mta'])) {
        $row = array_merge($row, $row['mta']);
    }
    //print_r2($row);

	// 결제정보 (serialize)
	$pay_infos = unserialize($row['opa_pay_info']);
	//print_r2($pay_infos);
	if(is_array($pay_infos)) {
		foreach($pay_infos as $key => $value) {
			//echo $key.$value.'<br>';
			if($key == 'opa_card_no' || $key == 'opa_card_owner' || $key == 'opa_card_valid')
				$value = trim(decryption($value));
			//echo $key.$value.'<br>';
			$row[$key] = $value;
		}
	}

	// 결제정보
	if( $row['opa_type'] == 'card' ) {
		$row['opa_item2_name'] = '카드명';
		$row['opa_bank_company'] = $row['opa_card_company'];
		$row['opa_item4_name'] = '할부개월';
		$row['opa_item4_value'] = ($row['opa_card_installment']=='0') ? '일시불' : $row['opa_card_installment'].'개월';	// 할부기간
		$row['opa_item5_name'] = '승인일';
	}
	else if( $row['opa_type'] == 'bank' ) {
		$row['opa_item2_name'] = '은행명';
		$row['opa_bank_company'] = $row['opa_bank_name'];
		$row['opa_item4_name'] = '입금자';
		$row['opa_item4_value'] = $row['opa_deposit_name'];
		$row['opa_item5_name'] = '입금일';
	}
	else if( $row['opa_type'] == 'cms' ) {
		$row['opa_item2_name'] = '출금일';
		$row['opa_bank_company'] = $row['opa_cms_date'];
	}
	else if( $row['opa_type'] == 'etc' ) {
		$row['opa_item2_name'] = $g5['set_doc_types_value'][$row['opa_doc_type']];
		$row['opa_bank_company'] = $row['opa_doc_dt'];
	}
	else {
		$row['opa_item5_name'] = '승인일';
	}
	
	// 승인일시 (승인번호)
	$row['opa_pay_dt_text'] = ($row['opa_pay_dt']=='0000-00-00 00:00:00') ? '-' : substr($row['opa_pay_dt'],0,10) ;

    // 출력 변수 설정
	$od['od_payment_info'] .= '
        <table>
            <tr>
                <td class="row1">결제방법</td>
                <td>'.$g5['set_opa_types_value'][$row['opa_type']].'</td>
                <td class="row1">'.$row['opa_item2_name'].'</td>
                <td>'.$row['opa_bank_company'].'</td>
                <td class="row1">금액</td>
                <td>'.number_format($row['opa_price']).'원</td>
                <td class="row1">'.$row['opa_item4_name'].'</td>
                <td>'.$row['opa_item4_value'].'</td>
                <td class="row1">'.$row['opa_item5_name'].'</td>
                <td>'.$row['opa_pay_dt_text'].'</td>
            </tr>
        </table>
    ';
    
    // 출력 변수 설정 (모바일용)
    $row['opa_item2_display'] = ($row['opa_item2_name']) ?: 'display:none;';
    $row['opa_item4_display'] = ($row['opa_item4_name']) ?: 'display:none;';
    $row['opa_item5_display'] = ($row['opa_item5_name']) ?: 'display:none;';
    $row['opa_customer_type_display'] = ($row['opa_customer_type']) ? ' <span style="color:gray;">('.$g5['set_settle_customer_type_value'][$row['opa_customer_type']].')</span>' : '';
	$od['od_payment_info_mobile'] .= '
        <table>
            <tr>
                <td class="row1">결제방법</td>
                <td>'.$g5['set_opa_types_value'][$row['opa_type']].$row['opa_customer_type_display'].'</td>
            </tr>
            <tr style="'.$row['opa_item2_display'].'">
                <td class="row1">'.$row['opa_item2_name'].'</td>
                <td>'.$row['opa_bank_company'].'</td>
            </tr>
            <tr>
                <td class="row1">금액</td>
                <td>'.number_format($row['opa_price']).'원</td>
            </tr>
            <tr style="'.$row['opa_item4_display'].'">
                <td class="row1">'.$row['opa_item4_name'].'</td>
                <td>'.$row['opa_item4_value'].'</td>
            </tr>
            <tr style="'.$row['opa_item5_display'].'">
                <td class="row1">'.$row['opa_item5_name'].'</td>
                <td>'.$row['opa_pay_dt_text'].'</td>
            </tr>
        </table>
    ';
    
    
}
// 결제내역 없음
if($i==0) {
	$od['od_payment_info'] = '
        <table>
            <tr>
                <td>결제 정보 없음</td>
            </tr>
        </table>
    ';
}


// 인감도장 파일
$bo_table = 'setting2';
$od['board']['wr_id'] = $od['od_company'];
$board['bo_mobile_gallery_width'] = $board['bo_mobile_gallery_height'] = 60;
$od['board']['files'] = get_file($bo_table, $od['board']['wr_id']);
//print_r2($od['board']);
if( $od['board']['files'][0]['file'] ) {
    //echo $od['board']['files'][0]['file'].'<br>';
    $od['board']['thumbnail'] = thumbnail($od['board']['files'][0]['file'], 
                G5_DATA_PATH.'/file/'.$bo_table, G5_DATA_PATH.'/file/'.$bo_table,
                $board['bo_mobile_gallery_width'], $board['bo_mobile_gallery_height'],
                false, true, 'center', true, '85/3.4/15'
    );
    $thumb['src'] = G5_DATA_URL.'/file/'.$bo_table.'/'.$od['board']['thumbnail'];
    //echo $thumb['src'].'<br>';
    $ingam_image = '<img class="ingam_" src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" width="'.$board['bo_mobile_gallery_width'].'" height="'.$board['bo_mobile_gallery_height'].'">';
}


// 스킨 파일
if(check_dt($od['od_mb_auth_dt']))
    include_once($user_skin_path.'/contract_agree_done.skin.php');
else
    include_once($user_skin_path.'/contract_agree_form.skin.php');

include_once('./_tail.php');
?>