<?php
// 푸시값 입력
// id, pushkey
// http://erp.ingglobal.net/james/sesskey.php?uid=test01&unick=abcd12345
// http://localhost/ingglobal/erp/james/sesskey.php?uid=test01&unick=abcd12345
header("Content-Type: text/plain; charset=utf-8");
include_once('./_common.php');
if(isset($_SERVER['HTTP_ORIGIN'])){
	header("Access-Control-Allow-Origin:{$_SERVER['HTTP_ORIGIN']}");
	header("Access-Control-Allow-Credentials:true");
	header("Access-Control-Max-Age:86400"); //cache for 1 day
}

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	exit(0);
}

//-- 디폴트 상태 (실패) --//
$response = new stdClass();
$list = array();
$list['result']=false;

// print_r2($_REQUEST);

// 끝에 아이디(mb_id)만 추출 
// $uri_array = explode("?",$_SERVER['REQUEST_URI']);
// $mb_id = $uri_array[1];

// if(!$_REQUEST['uid'] || !$_REQUEST['unick']) {
//     $list['msg']='아이디 or 닉네임 값이 존재하지 않습니다.';
// }
// else {

	$secretKey = 'lR59kleOalRgOyB4';
	$userId = $_REQUEST['uid']; // 유니크아이디 (중복되면 안 되는 seq같은 값)
	$nick = $_REQUEST['unick']; 	// 닉네임
	$host = 'comeetstore.com';
	$serviceId = 'icecreative_dev';
	$timestamp = time();
	
	$plainparams = '{"userId":"'.$userId.'","nickname":"'.$nick.'","host":"'.$host.'","serviceId":"'.$serviceId.'","timestamp":'.$timestamp.'}';
	$iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
	$encrypted = base64_encode(openssl_encrypt($plainparams, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $iv));
	
	$list['sesskey']=$encrypted;
	$list['result']=true;
	$list['msg']='세션값 출력 성공!';

// }

echo json_encode( $list );
?>