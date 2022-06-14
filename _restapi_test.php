<?php
include_once('./_common.php');
$url = 'https://log.smart-factory.kr/apisvc/sendLogData.json';
//$5$API$3Ue8EDeh9uh5SBT98qSOmiuz636aFqZyV5aMgFmL4rD
$darr = array(
    'crtfcKey' => $g5['kosmo_erp_crtfckey'],
    'logDt' => G5_TIME_YMDHIS.'.000',
    'useSe' => '등록',
    'sysUser' => 'test2',
    'conectIp' => ':::2',
    'dataUsgqty' => ''
);
// print_r2($darr);
// exit;
$opt = array(
    'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($darr)
    )
);
$context = stream_context_create($opt); //데이터 가공
$result = file_get_contents($url, false, $context); //전송 ~ 결과값 반환
$data = json_decode($result, true);
// print_r2($data);
/*
$url = 'https://log.smart-factory.kr/apisvc/sendLogData.json';
$crtcKey = $g5['kosmo_erp_crtfckey'];
$logDt = G5_TIME_YMDHIS;
$useSe = '등록';
$sysUser = $member['mb_id'];
$conectIp = $member['mb_login_ip'];
$dataUsgqty = '';
$send_tag = <<<HEREDOC
<script src="https://code.jquery.com/jquery-3.6.0.min.js""></script>
<script>
var _url = "$url";
var djson = {'crtfcKey':"{$crtcKey}",'logDt':"{$logDt}",'useSe':"{$useSe}",'sysUser':"{$sysUser}",'conectIp':"{$conectIp}",'dataUsgqty':"{$dataUsgqty}"};
$.ajax({
    type:"POST",
    url:_url,
    dataType:"json",
    data:djson,
    success:function(res){
        console.log(res);
    },
    error:function(req){
        console.log(req);
    }
});
</script>
HEREDOC;
echo $send_tag;
*/