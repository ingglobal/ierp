<div style="margin:30px auto;width:700px;border:">
	<h2 style="font-size:20px;text-align:center;color:#00f;">거래명세서</h2>
	<p style="margin:0;font-size:12px;padding-bottom:5px;">
	No.<?=$orl_no?>
	</p>
	<div style="position:relative;font-size:12px;letter-spacing:0.1em;">
		<table style="display:table;border-collapse:collapse;border-spacing:0;width:100%;">
			<tbody>
				<tr>
					<td style="position:relative;width:46%;text-align:left;padding:5px;vertical-align:top;padding:10px;border-left:1px solid #00f;border-top:1px solid #00f;">
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">거래처 : </span><?=$com_name?></p>
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">사업자번호 : </span><?=$com_biz_no?></p>
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">일자 : </span><?=G5_TIME_YMD?></p>
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">전화 : </span><?=$com_tel?></p>
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">팩스 : </span><?=$com_fax?></p>
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">주소 : </span><?=$com_addr?></p>
						<p style="margin:0;margin-top:3px;"><span style="color:#00f;">제목 : </span><?=$orl_subject?></p>
					</td>
					<td rowspan="2" style="width:54%;padding:0;">
						<table style="display:table;border-collapse:collapse;border-spacing:0;width:100%;border:3px solid #00f;border-bottom:0;">
							<tbody>
								<tr style="border-bottom:1px solid #00f;">
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">등록<br>번호</td>
									<td colspan="2" style="padding:10px 5px;"><?=$default['de_admin_company_saupja_no']?></td>
									<td style="position:relative;padding:5px;text-align:right;">
										<div style="display:inline-block;width:48px;height:48px;">
										<img src="<?=G5_USER_ADMIN_IMG_URL?>/stamp.png" style="">
										</div>
									</td>
								</tr>
								<tr style="border-bottom:1px solid #00f;">
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">상호</td>
									<td style="border-right:1px solid #00f;text-align:center;width:35%;padding:10px 5px;font-size:0.9em;"><?=$default['de_admin_business_name']?></td>
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">성명</td>
									<td style="width:35%;text-align:center;padding:10px 5px;"><?=$default['de_admin_company_owner']?></td>
								</tr>
								<tr style="border-bottom:1px solid #00f;">
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">주소</td>
									<td colspan="3" style="padding:10px 5px;"><?=$default['de_admin_company_addr']?></td>
								</tr>
								<tr style="border-bottom:1px solid #00f;">
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">업태</td>
									<td style="border-right:1px solid #00f;width:35%;padding:10px 5px;"><?=$default['de_admin_business_condition']?></td>
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">종목</td>
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;"><?=$default['de_admin_business_category']?></td>
								</tr>
								<tr>
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">전화<br>번호</td>
									<td style="border-right:1px solid #00f;width:35%;padding:10px 5px;"><?=$default['de_admin_company_tel']?></td>
									<td style="border-right:1px solid #00f;text-align:center;width:15%;padding:10px 5px;color:#00f;">팩스<br>번호</td>
									<td style="width:35%;padding:10px 5px;"><?=$default['de_admin_company_fax']?></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr style="border-left:1px solid #00f;">
					<td style="position:relative;text-align:left;vertical-align:bottom;">
						<table style="display:table;border-collapse:collapse;border-spacing:0;width:100%;margin-left:-1px;margin-bottom:-1px;">
						<tbody>
						<tr>
							<td style="padding:10px;font-weight:bold;border:3px solid #00f;border-bottom:0;border-left:2px solid #00f;">합계금액</td>
							<td style="padding:10px;font-weight:bold;border:3px solid #00f;border-bottom:0;border-right:0;text-align:right;font-size:1.4em;"><?=number_format($total_price)?>원</td>
						</tr>
						</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div style="border:2px solid #00f;font-size:12px;letter-spacing:0.1em;">
		<table style="display:table;border-collapse:collapse;border-spacing:0;border:1px solid #ddd;width:100%;">
			<thead>
				<tr>
					<th style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;width:40px;">No</th>
					<th colspan="2" style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">상품명</th>
					<th colspan="2" style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">가격</th>
					<th style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;width:30px;">수량</th>
					<th colspan="2" style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">공급가</th>
					<th style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">세액</th>
					<th style="font-size:12px;border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">소계</th>
				</tr>
			</thead>
			<tbody>
				<?php for($i=0;$i<count($it_id);$i++){ ?>
				<tr>
					<td it_id="<?=$it_id[$i]?>" style="font-size:12px;border:1px solid #00f;padding:8px 5px;text-align:center;"><?=($i+1)?></td>
					<td colspan="2" it_id="" style="font-size:12px;border:1px solid #00f;padding:8px 5px;"><?=$it_name[$i]?></td>
					<td colspan="2" it_id="" style="font-size:12px;border:1px solid #00f;padding:8px 5px;text-align:right;"><?=number_format($it_buy_price[$i])?>원</td>
					<td it_id="" style="font-size:12px;border:1px solid #00f;padding:8px 5px;text-align:right;"><?=$it_qty[$i]?></td>
					<td colspan="2" ca_str="<?=$ca_str[$i]?>" style="font-size:12px;border:1px solid #00f;padding:8px 5px;text-align:right;"><?php echo number_format(get_supply_price($it_tot_buy_price[$i]));?>원</td>
					<td style="font-size:12px;border:1px solid #00f;padding:8px 5px;text-align:right;"><?php echo number_format(get_tariff_price($it_tot_buy_price[$i]));?>원</td>
					<td it_id="" style="font-size:12px;border:1px solid #00f;padding:8px 5px;text-align:right;"><?=number_format($it_tot_buy_price[$i])?>원</td>
				</tr>
				<?php } ?>
				<tr>
					<td style="border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">공급가총액</td>
					<td style="border:1px solid #00f;padding:8px 5px;text-align:right;"><?=number_format(get_supply_price($total_price))?>원</td>
					<td style="border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">세액총액</td>
					<td colspan="3" style="border:1px solid #00f;padding:8px 5px;text-align:right;"><?=number_format(get_tariff_price($total_price))?>원</td>
					<td style="border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;text-align:center;">총금액</td>
					<td colspan="3" style="border:1px solid #00f;padding:8px 5px;text-align:right;font-weight:bold;"><?=number_format($total_price)?>원</td>
				</tr>
				<tr>
					<td style="border:1px solid #00f;padding:8px 5px;background:#B7DBFF;color:#00f;height:50px;text-align:center;">비고</td>
					<td colspan="10" style="border:1px solid #00f;padding:8px 5px;text-align:left;vertical-align:top;"><?=$od_memo?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>