<?php
// 견적서 메일 (회원님께 발송)
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
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

$doc_form => quot / order / deal

$total_price => 97900
$records => 3
$od_id => 
$mb_id => super
$od_name => 전산실
$od_email => websiteman@naver.com

$od_subject => 제목
$od_memo => 추가멧세지

$com_idx = 3
$mng_id = 1599388073
$com_name = 세원물산
$mng_name = 김세원
$mng_email = tomasjoa@nate.com
*/
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>견적서 메일</title>
</head>

<body>
<?php include_once(G5_USER_ADMIN_PATH.'/order_form_z'.$doc_form.'.php'); ?>
</body>
</html>