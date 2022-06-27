<?php
include_once('./_common.php');

// 자료사용동의 요청 메일에 대한 인증 처리
// 요청페이지: /adm/v10/company_form.php 
// Ajax 처리 페이지: /adm/v10/ajax/data_agree_email.php 
// 응답페이지: /user/e1.php?8314 (->com_idx)
// ex: https://woogle.kr/user/e1.php?8314

// 끝에 일련번호(com_idx)만 추출 
$uri_array = explode("?",$_SERVER['REQUEST_URI']);
$com_idx = $uri_array[1];

if(!$com_idx)
	alert('자료사용동의 업체정보가 없습니다.',G5_URL);
else {
	// 업체 정보 수정
	$com = sql_fetch(" SELECT com_data_agree_dt FROM {$g5['company_table']} WHERE com_idx = '$com_idx' ");
	if ($com['com_data_agree_dt'] != '0000-00-00 00:00:00') {
		alert('이미 자료사용동의를 해 주셨습니다. \n(자료사용동의 일시: '.$com['com_data_agree_dt'].')\n\n감사합니다.',G5_URL);
	}
}

$g5['title'] = "업체자료 사용동의";
include_once('./_head.php');


// 스킨 파일
include_once($user_skin_path.'/data_agree_form.skin.php');

include_once('./_tail.php');
?>