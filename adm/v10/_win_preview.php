<?php
include_once('./_common.php');
if(!$orl_subject) alert('제목을 입력해 주세요.');
if(!$com_name) alert('업체명을 입력해 주세요.');
if(!$mng_name) alert('담당자명을 입력해 주세요.');
if(!$mng_email) alert('담당자Email을 입력해 주세요.');
$doc_arr = array('quot'=>'견적서','order'=>'발주서','deal'=>'거래명세서');
$orl_no = $od_id.'-'.$doc_form.'-'.G5_SERVER_TIME;
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?=$doc_arr[$doc_form]?> 양식 미리보기</title>
</head>

<body>
<?php include_once(G5_USER_ADMIN_PATH.'/order_form_z'.$doc_form.'.php'); ?>
</body>
</html>