<?php
$sub_menu = '960226';
include_once('./_common.php');

check_demo();

// 보관기간이 지난 상품 삭제
cart_item_clean();

// cart id 설정 
set_cart_id($sw_direct);

if($sw_direct)
    $tmp_cart_id = get_session('ss_cart_direct');
else
    $tmp_cart_id = get_session('ss_cart_id');

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
if (!$tmp_cart_id)
    alert('더 이상 작업을 진행할 수 없습니다.\\n\\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\\n\\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\\n\\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.');


// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member['mb_level'] < $default['de_level_sell'])
{
    alert('상품을 구입할 수 있는 권한이 없습니다.');
}
/*
//echo $act;exit;
print_r2($_POST);
exit;
*/
//echo $sw_direct;exit;
if($act == 'order'){
    /*
    print_r2($ct_chk);
    $records = 2;
    $mb_id = 'super';
    $od_name = '전산실';
    */
    
    // 장바구니가 비어있는가?
    if (get_session("ss_direct"))
        $tmp_cart_id = get_session('ss_cart_direct');
    else
        $tmp_cart_id = get_session('ss_cart_id');
    if (get_cart_count($tmp_cart_id) == 0)// 장바구니에 담기
        alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SHOP_URL.'/cart.php');
    
    //echo $tmp_cart_id;
    $sql = " UPDATE {$g5['g5_shop_cart_table']} SET ct_status = '견적', ct_select = '1' WHERE od_id = '$tmp_cart_id' ";
    sql_query($sql,1);
    
    $error = "";
    // 장바구니 상품 재고 검사
    $sql = " select it_id,
                    ct_qty,
                    it_name,
                    io_id,
                    io_type,
                    ct_option
               from {$g5['g5_shop_cart_table']}
              where od_id = '$tmp_cart_id'
                and ct_select = '1' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++){
        // 상품에 대한 현재고수량
        if($row['io_id']) {
            $it_stock_qty = (int)get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
        } else {
            $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
        }
        // 장바구니 수량이 재고수량보다 많다면 오류
        if ($row['ct_qty'] > $it_stock_qty)
            $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
    }
    
    if($i == 0)
        alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SHOP_URL.'/cart.php');

    if ($error != ""){
        $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
        alert($error);
    }
    
    // 주문금액이 상이함
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_price,
                  COUNT(distinct it_id) as cart_count
                from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_select = '1' ";
    $row = sql_fetch($sql);
    $tot_ct_price = $row['od_price'];
    $cart_count = $row['cart_count'];
    $tot_od_price = $tot_ct_price;


    
    if ($is_member)
        $od_pwd = $member['mb_password'];
    else
        $od_pwd = get_encrypt_string($_POST['od_pwd']);
    
    
    // 주문번호를 얻는다.
    //$od_id = get_session('ss_order_id');
    $od_id = get_uniqid();
    //set_session('ss_order_id', $od_id);
    
    $od_email         = get_email_address($od_email);
    $od_name          = clean_xss_tags($od_name);
    $od_status        = '견적';
    
    // 주문서에 입력
    $sql = " insert {$g5['g5_shop_order_table']}
                set od_id             = '$od_id',
                    mb_id             = '{$member['mb_id']}',
                    od_pwd            = '$od_pwd',
                    od_name           = '$od_name',
                    od_email          = '',
                    od_tel            = '',
                    od_hp             = '',
                    od_zip1           = '',
                    od_zip2           = '',
                    od_addr1          = '',
                    od_addr2          = '',
                    od_addr3          = '',
                    od_addr_jibeon    = '',
                    od_b_name         = '',
                    od_b_tel          = '',
                    od_b_hp           = '',
                    od_b_zip1         = '',
                    od_b_zip2         = '',
                    od_b_addr1        = '',
                    od_b_addr2        = '',
                    od_b_addr3        = '',
                    od_b_addr_jibeon  = '',
                    od_deposit_name   = '',
                    od_memo           = '',
                    od_cart_count     = '$cart_count',
                    od_cart_price     = '$tot_ct_price',
                    od_cart_coupon    = '',
                    od_send_cost      = '',
                    od_send_coupon    = '',
                    od_send_cost2     = '',
                    od_coupon         = '',
                    od_receipt_price  = '',
                    od_receipt_point  = '',
                    od_bank_account   = '',
                    od_receipt_time   = '',
                    od_misu           = '',
                    od_pg             = '',
                    od_tno            = '',
                    od_app_no         = '',
                    od_escrow         = '',
                    od_tax_flag       = '',
                    od_tax_mny        = '',
                    od_vat_mny        = '',
                    od_free_mny       = '',
                    od_status         = '견적',
                    od_shop_memo      = '',
                    od_hope_date      = '',
                    od_time           = '".G5_TIME_YMDHIS."',
                    od_ip             = '$REMOTE_ADDR',
                    od_settle_case    = '',
                    od_test           = '',
                    mb_id_saler       = '{$member['mb_id']}',
                    com_idx           = ''
                    ";
    $result = sql_query($sql, false);
    
    $cart_status = $od_status;
    $sql = "update {$g5['g5_shop_cart_table']}
           set od_id = '$od_id',
               ct_status = '$cart_status'
         where od_id = '$tmp_cart_id'
           and ct_select = '1' ";
    $result = sql_query($sql, false);
    
    // orderview 에서 사용하기 위해 session에 넣고
    $uid = md5($od_id.G5_TIME_YMDHIS.$REMOTE_ADDR);
    set_session('ss_orderview_uid', $uid);
    
    // 주문번호제거
    set_session('ss_order_id', '');

    // 기존자료 세션에서 제거
    if (get_session('ss_direct'))
        set_session('ss_cart_direct', '');

    goto_url('./item_order_form.php?od_id='.$od_id.'&amp;uid='.$uid);
}
else if($act == "buy")
{
    if(!count($_POST['ct_chk']))
        alert("주문하실 상품을 하나이상 선택해 주십시오.");

    $fldcnt = count($_POST['it_id']);
    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = $_POST['ct_chk'][$i];
        if($ct_chk) {
            $it_id = $_POST['it_id'][$i];

            // 주문 상품의 재고체크
            $sql = " select ct_qty, it_name, ct_option, io_id, io_type
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '$tmp_cart_id'
                          and it_id = '$it_id' ";
            $result = sql_query($sql);

            for($k=0; $row=sql_fetch_array($result); $k++) {
                $sql = " select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']}
                          where od_id <> '$tmp_cart_id'
                            and it_id = '$it_id'
                            and io_id = '{$row['io_id']}'
                            and io_type = '{$row['io_type']}'
                            and ct_stock_use = 0
                            and ct_status = '쇼핑'
                            and ct_select = '1' ";
                $sum = sql_fetch($sql);
                $sum_qty = $sum['cnt'];

                // 재고 구함
                $ct_qty = $row['ct_qty'];
                if(!$row['io_id'])
                    $it_stock_qty = get_it_stock_qty($it_id);
                else
                    $it_stock_qty = get_option_stock_qty($it_id, $row['io_id'], $row['io_type']);

                if ($ct_qty + $sum_qty > $it_stock_qty)
                {
                    $item_option = $row['it_name'];
                    if($row['io_id'])
                        $item_option .= '('.$row['ct_option'].')';

                    alert($item_option." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty - $sum_qty) . " 개");
                }
            }

            $sql = " update {$g5['g5_shop_cart_table']}
                        set ct_select = '1',
                            ct_select_time = '".G5_TIME_YMDHIS."'
                        where od_id = '$tmp_cart_id'
                          and it_id = '$it_id' ";
            sql_query($sql);
        }
    }

    if ($is_member) // 회원인 경우
        goto_url('./item_order_cart_form.php');
    else
        goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_USER_ADMIN_URL.'/item_order_cart_form.php'));
}
else if ($act == "alldelete") // 모두 삭제이면
{
    $sql = " delete from {$g5['g5_shop_cart_table']}
              where od_id = '$tmp_cart_id' ";
    sql_query($sql);
}
else if ($act == "seldelete") // 선택삭제
{
    if(!count($_POST['ct_chk']))
        alert("삭제하실 상품을 하나이상 선택해 주십시오.");

    $fldcnt = count($_POST['it_id']);
    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = $_POST['ct_chk'][$i];
        if($ct_chk) {
            $it_id = $_POST['it_id'][$i];
            $sql = " delete from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$tmp_cart_id' ";
            sql_query($sql);
        }
    }
}
else if ($act == "modify"){
   //print_r2($ct_chk);
   //print_r2($_POST);
   foreach($ct_chk as $k => $v){
        $mdf_sql = " update {$g5['g5_shop_cart_table']}
                            set ct_price = '{$it_buy_price[$k]}'
                            where ct_id = '{$ct_id[$k]}' ";
        //echo $mdf_sql."<br>";
        sql_query($mdf_sql,1);
   }
}
else // 장바구니에 담기
{
    $count = count($_POST['it_id']);
    if ($count < 1)
        alert('장바구니에 담을 상품을 선택하여 주십시오.');
    print_r2($_POST);
    exit;
    $ct_count = 0;
    for($i=0; $i<$count; $i++) {
        // 보관함의 상품을 담을 때 체크되지 않은 상품 건너뜀
        if($act == 'multi' && !$_POST['chk_it_id'][$i])
            continue;

        $it_id = $_POST['it_id'][$i];
        // 견적형 상품인 경우는 배열이 아니므로 에러가 남
        $opt_count = is_array($_POST['io_id'][$it_id]) ? count($_POST['io_id'][$it_id]) : 0;

        if($opt_count && $_POST['io_type'][$it_id][0] != 0)
            alert('상품의 선택옵션을 선택해 주십시오.');

        for($k=0; $k<$opt_count; $k++) {
            if ($_POST['ct_qty'][$it_id][$k] < 1)
                alert('수량은 1 이상 입력해 주십시오.');
        }

        // 상품정보
        $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
        $it = sql_fetch($sql);
        if(!$it['it_id'])
            alert('상품정보가 존재하지 않습니다.');
		
		// 견적형인 경우 가격
        if($act == 'optionmod') {
			// $it['it_price'] = ($it['it_tel_inq'] == 1) ? trim(preg_replace("/,/","",$_POST['ct_price'][$it_id][$k])) : trim(preg_replace("/,/","",$it['it_price']));
			$it['it_price'] = trim(preg_replace("/,/","",$_POST['ct_price'][$it_id][$k]));
        }
		

        // 바로구매에 있던 장바구니 자료를 지운다.
        if($i == 0 && $sw_direct)
            sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 ", false);

        // 최소, 최대 수량 체크
        if($it['it_buy_min_qty'] || $it['it_buy_max_qty']) {
            $sum_qty = 0;
            for($k=0; $k<$opt_count; $k++) {
                if($_POST['io_type'][$it_id][$k] == 0)
                    $sum_qty += $_POST['ct_qty'][$it_id][$k];
            }

            if($it['it_buy_min_qty'] > 0 && $sum_qty < $it['it_buy_min_qty'])
                alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_min_qty']).'개 이상 주문해 주십시오.');

            if($it['it_buy_max_qty'] > 0 && $sum_qty > $it['it_buy_max_qty'])
                alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.');

            // 기존에 장바구니에 담긴 상품이 있는 경우에 최대 구매수량 체크
            if($it['it_buy_max_qty'] > 0) {
                $sql4 = " select sum(ct_qty) as ct_sum
                            from {$g5['g5_shop_cart_table']}
                            where od_id = '$tmp_cart_id'
                              and it_id = '$it_id'
                              and io_type = '0'
                              and ct_status = '쇼핑' ";
                $row4 = sql_fetch($sql4,1);

                if(($sum_qty + $row4['ct_sum']) > $it['it_buy_max_qty'])
                    alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.', './cart.php');
            }
        }

        // 옵션정보를 얻어서 배열에 저장
        $opt_list = array();
        $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' order by io_no asc ";
        $result = sql_query($sql);
        $lst_count = 0;
        for($k=0; $row=sql_fetch_array($result); $k++) {
            $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
            $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
            $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
            $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];

            // 선택옵션 개수
            if(!$row['io_type'])
                $lst_count++;
        }

        //--------------------------------------------------------
        //  재고 검사, 바로구매일 때만 체크
        //--------------------------------------------------------
        // 이미 주문폼에 있는 같은 상품의 수량합계를 구한다.
        if($sw_direct) {
            for($k=0; $k<$opt_count; $k++) {
                $io_id = $_POST['io_id'][$it_id][$k];
                $io_type = $_POST['io_type'][$it_id][$k];
                $io_value = $_POST['io_value'][$it_id][$k];

                $sql = " select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']}
                          where od_id <> '$tmp_cart_id'
                            and it_id = '$it_id'
                            and io_id = '$io_id'
                            and io_type = '$io_type'
                            and ct_stock_use = 0
                            and ct_status = '쇼핑'
                            and ct_select = '1' ";
                $row = sql_fetch($sql);
                $sum_qty = $row['cnt'];

                // 재고 구함
                $ct_qty = $_POST['ct_qty'][$it_id][$k];
                if(!$io_id)
                    $it_stock_qty = get_it_stock_qty($it_id);
                else
                    $it_stock_qty = get_option_stock_qty($it_id, $io_id, $io_type);

                if ($ct_qty + $sum_qty > $it_stock_qty)
                {
                    alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty - $sum_qty) . " 개");
                }
            }
        }
        //--------------------------------------------------------

        // 옵션수정일 때 기존 장바구니 자료를 먼저 삭제
        if($act == 'optionmod') {
			sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and it_id = '$it_id' ");
		}

		
        // 장바구니에 Insert
        // 바로구매일 경우 장바구니가 체크된것으로 강제 설정
        if($sw_direct) {
            $ct_select = 1;
            $ct_select_time = G5_TIME_YMDHIS;
        } else {
            $ct_select = 0;
            $ct_select_time = '0000-00-00 00:00:00';
        }

        // 장바구니에 Insert
        $comma = '';
		$sql = " INSERT INTO {$g5['g5_shop_cart_table']}
					( od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty
					, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty
					, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time )
				VALUES ";

        for($k=0; $k<$opt_count; $k++) {
            $io_id = $_POST['io_id'][$it_id][$k];
            $io_type = $_POST['io_type'][$it_id][$k];
            $io_value = $_POST['io_value'][$it_id][$k];
			if($act == 'optionmod') {
				// 견적형인 경우 가격 정보 수정
				// $it['it_price'] = ($it['it_tel_inq'] == 1) ? trim(preg_replace("/,/","",$_POST['ct_price'][$it_id][$k])) : trim(preg_replace("/,/","",$it['it_price']));
				$it['it_price'] = trim(preg_replace("/,/","",$_POST['ct_price'][$it_id][$k]));
			}

            // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
            if($lst_count && $io_id == '')
                continue;

            // 구매할 수 없는 옵션은 건너뜀
            if($io_id && !$opt_list[$io_type][$io_id]['use'])
                continue;

            $io_price = $opt_list[$io_type][$io_id]['price'];
            $ct_qty = $_POST['ct_qty'][$it_id][$k];

            // 구매가격이 음수인지 체크 (추가매출(it_id=1572333668)인 경우는 음수 매출도 인정함)
			if($it_id != 1572333668	) {
				if($io_type) {
					if((int)$io_price < 0)
						alert('구매금액이 음수인 상품은 구매할 수 없습니다. \n확인 후 다시 장바구니에 담아주세요.');
				} else {
					if((int)$it['it_price'] + (int)$io_price < 0)
						alert('구매금액이 음수인 상품은 구매할 수 없습니다. \n확인 후 다시 장바구니에 담아주세요.');
				}
			}

            // 동일옵션의 상품이 있으면 수량 더함
            $sql2 = " select ct_id, io_type, ct_qty
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '$tmp_cart_id'
                          and it_id = '$it_id'
                          and io_id = '$io_id'
                          and ct_status = '쇼핑' ";
            $row2 = sql_fetch($sql2,1);
            if($row2['ct_id']) {
                // 재고체크
                $tmp_ct_qty = $row2['ct_qty'];
                if(!$io_id)
                    $tmp_it_stock_qty = get_it_stock_qty($it_id);
                else
                    $tmp_it_stock_qty = get_option_stock_qty($it_id, $io_id, $row2['io_type']);

                if ($tmp_ct_qty + $ct_qty > $tmp_it_stock_qty)
                {
                    alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($tmp_it_stock_qty) . " 개");
                }

                $sql3 = " update {$g5['g5_shop_cart_table']}
                            set ct_qty = ct_qty + '$ct_qty'
                            where ct_id = '{$row2['ct_id']}' ";
                sql_query($sql3,1);
                continue;
            }

            // 포인트
            $point = 0;
            if($config['cf_use_point']) {
                if($io_type == 0) {
                    $point = get_item_point($it, $io_id);
                } else {
                    $point = $it['it_supply_point'];
                }

                if($point < 0)
                    $point = 0;
            }

            // 배송비결제
            if($it['it_sc_type'] == 1)
                $ct_send_cost = 2; // 무료
            else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
                $ct_send_cost = 1; // 착불

            $sql .= $comma."( '$tmp_cart_id'
				, '{$member['mb_id']}', '{$it['it_id']}', '".addslashes($it['it_name'])."', '{$it['it_sc_type']}'
				, '{$it['it_sc_method']}', '{$it['it_sc_price']}', '{$it['it_sc_minimum']}', '{$it['it_sc_qty']}'
				, '쇼핑', '{$it['it_price']}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price'
				, '".G5_TIME_YMDHIS."', '$REMOTE_ADDR', '$ct_send_cost', '$sw_direct', '$ct_select' , '$ct_select_time' )";
            $comma = ' , ';
            $ct_count++;
        }


        if($ct_count > 0)
            sql_query($sql);    // ct_count 가 있을 때만 정상 실행되므로 error flag를 true로 할 수 없다.
		
		// echo $sql.'<br>';
		// exit;
		
    }
}


// exit;
// 바로 구매일 경우
if ($sw_direct)
	goto_url("./item_order_cart_form.php?sw_direct=$sw_direct");
else {
	// 장바구니에서 옵션 수정인 경우는 바로 장바구니로 이동!
	if($act == 'optionmod' || $act == 'seldelete' | $act == 'alldelete') {
		goto_url("./item_order_cart.php");
	}
	else {
		$qstr .= "&amp;sca=".$sca;
	?>
	<script>
        location.href = "<?php echo "./item_order_cart.php"; ?>";
        
	</script>
	<?php
	}
}
    
?>
