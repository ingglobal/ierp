<div style="margin:30px auto;width:660px;border:">
	<h2 style="font-size:20px;text-align:center;">견적서</h2>
	<p style="margin:0;font-size:12px;padding-bottom:5px;">
	No.<?=$orl_no?>
	</p>
	<div style="position:relative;border:2px solid #333;border-bottom:0;font-size:12px;letter-spacing:0.1em;">
		<table style="display:table;border-collapse:collapse;border-spacing:0;width:100%;">
			<tbody>
				<tr>
					<td style="position:relative;width:40%;border:1px solid #333;text-align:left;padding:5px;vertical-align:top;padding:20px 10px;">
						<h3 style="margin:0;"><?=$com_name?> 귀하</h3>
						<p style="margin:0;margin-top:10px;">견적일자 : <?=G5_TIME_YMD?></p>
						<p style="margin:0;margin-top:5px;">전화 : <?=$com_tel?></p>
						<p style="margin:0;margin-top:5px;">팩스 : <?=$com_fax?></p>
						<p style="margin:0;margin-top:5px;">제목 : <?=$orl_subject?></p>
						<p style="margin:0;position:absolute;left:10px;bottom:10px;font-weight:bold;font-size:0.7em;">견적요청에 감사드리며 아래와 같이 견적합니다.</p>
					</td>
					<td style="width:6%;border:1px solid #333;text-align:center;vertical-align:middle;padding:5px;background:#dfdfdf;">
						작<br>성<br>자
					</td>
					<td style="width:54%;border:1px solid #333;">
						<table style="display:table;border-collapse:collapse;border-spacing:0;width:100%;">
							<tbody>
								<tr style="border-bottom:1px solid #333;">
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">사업자<br>번호</td>
									<td colspan="2" style="padding:5px;"><?=$default['de_admin_company_saupja_no']?></td>
									<td style="position:relative;padding:5px;text-align:right;">
										<div style="display:inline-block;width:48px;height:48px;">
										<img src="<?=G5_USER_ADMIN_IMG_URL?>/stamp.png" style="">
										</div>
									</td>
								</tr>
								<tr style="border-bottom:1px solid #333;">
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">상호</td>
									<td style="border-right:1px solid #333;text-align:center;width:35%;padding:5px;"><?=$default['de_admin_business_name']?></td>
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">성명</td>
									<td style="width:35%;text-align:center;"><?=$default['de_admin_company_owner']?></td>
								</tr>
								<tr style="border-bottom:1px solid #333;">
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">주소</td>
									<td colspan="3" style="padding:5px;"><?=$default['de_admin_company_addr']?></td>
								</tr>
								<tr style="border-bottom:1px solid #333;">
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">업태</td>
									<td style="border-right:1px solid #333;width:35%;padding:5px;">제조업</td>
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">종목</td>
									<td>전장응용가공,<br>배전반전기자동제어</td>
								</tr>
								<tr>
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">전화<br>번호</td>
									<td style="border-right:1px solid #333;width:35%;padding:5px;"><?=$default['de_admin_company_tel']?></td>
									<td style="border-right:1px solid #333;text-align:center;width:15%;padding:5px;">팩스<br>번호</td>
									<td style="width:35%;padding:5px;"><?=$default['de_admin_company_fax']?></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr style="border-top:1px solid #333;border-bottom:1px solid #333;">
					<td colspan="2" style="border-left:1px solid #333;padding:5px;font-weight:bold;">
					합계금액 : <?=number_format($total_price)?>원 (부가세포함)
					</td>
					<td style="border-right:1px solid #333;padding:5px;">
					프로젝트/현장 : 
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div style="border:2px solid #333;font-size:12px;letter-spacing:0.1em;">
		<table style="display:table;border-collapse:collapse;border-spacing:0;border:1px solid #ddd;width:100%;">
			<thead>
				<tr>
					<th style="font-size:12px;border:1px solid #333;padding:5px;background:#dfdfdf;color:#333;text-align:center;">상품번호</th>
					<th style="font-size:12px;border:1px solid #333;padding:5px;background:#dfdfdf;color:#333;text-align:center;">분류</th>
					<th style="font-size:12px;border:1px solid #333;padding:5px;background:#dfdfdf;color:#333;text-align:center;">상품명</th>
					<th style="font-size:12px;border:1px solid #333;padding:5px;background:#dfdfdf;color:#333;text-align:center;">견적가</th>
					<th style="font-size:12px;border:1px solid #333;padding:5px;background:#dfdfdf;color:#333;text-align:center;">수량</th>
					<th style="font-size:12px;border:1px solid #333;padding:5px;background:#dfdfdf;color:#333;text-align:center;">소계</th>
				</tr>
			</thead>
			<tbody>
				<?php for($i=0;$i<count($it_id);$i++){ ?>
				<tr>
					<td style="font-size:12px;border:1px solid #333;padding:5px;"><?=$it_id[$i]?></td>
					<td style="font-size:12px;border:1px solid #333;padding:5px;"><?=$ca_str[$i]?></td>
					<td style="font-size:12px;border:1px solid #333;padding:5px;"><?=$it_name[$i]?></td>
					<td style="font-size:12px;border:1px solid #333;padding:5px;text-align:right;"><?=number_format($it_buy_price[$i])?>원</td>
					<td style="font-size:12px;border:1px solid #333;padding:5px;text-align:right;"><?=$it_qty[$i]?></td>
					<td style="font-size:12px;border:1px solid #333;padding:5px;text-align:right;"><?=number_format($it_tot_buy_price[$i])?>원</td>
				</tr>
				<?php } ?>
				<tr>
					<td style="border:1px solid #333;padding:5px;background:#f1f1f1;color:#333;">총금액</td>
					<td colspan="5" style="border:1px solid #333;padding:5px;text-align:right;font-weight:bold;"><?=number_format($total_price)?>원</td>
				</tr>
				<tr>
					<td style="border:1px solid #333;padding:5px;background:#f1f1f1;color:#333;height:50px;">비고</td>
					<td colspan="5" style="border:1px solid #333;padding:5px;text-align:left;vertical-align:top;"><?=$od_memo?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>