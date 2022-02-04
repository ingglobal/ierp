<?php
$sub_menu = "950900";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

check_admin_token();

$mb_id = trim($_POST['mb_id']);

// 휴대폰번호 체크
$mb_hp = hyphen_hp_number($_POST['mb_hp']);
if($mb_hp && $member['mb_level']<9) {
    $result = exist_mb_hp($mb_hp, $mb_id);
    if ($result)
        alert($result);
}

// 인증정보처리
if($_POST['mb_certify_case'] && $_POST['mb_certify']) {
    $mb_certify = $_POST['mb_certify_case'];
    $mb_adult = $_POST['mb_adult'];
} else {
    $mb_certify = '';
    $mb_adult = 0;
}

if ($mb_password)
    $sql_password = " , mb_password = '".get_encrypt_string($mb_password)."' ";
else
    $sql_password = "";

if ($passive_certify)
    $sql_certify = " , mb_email_certify = '".G5_TIME_YMDHIS."' ";
else
    $sql_certify = "";

$mb_zip1 = substr($_POST['mb_zip'], 0, 3);
$mb_zip2 = substr($_POST['mb_zip'], 3);

$mb_email = isset($_POST['mb_email']) ? get_email_address(trim($_POST['mb_email'])) : '';
$mb_nick = isset($_POST['mb_nick']) ? trim(strip_tags($_POST['mb_nick'])) : '';

if ($msg = valid_mb_nick($mb_nick))     alert($msg, "", true, true);

$_POST['mb_7'] = preg_replace("/,/","",trim($_POST['mb_7']));

$leave_flag = false;
if($_POST['mb_leave_date'] || $_POST['mb_intercept_date']){
    $leave_flag = true;
    $_POST['mb_3'] = '';
    $_POST['mb_4'] = '';
    $_POST['mb_5'] = '';
    $_POST['mb_6'] = '';
    $_POST['mb_7'] = '';
}


$sql_common = "  mb_name = '{$_POST['mb_name']}',
                 mb_nick = '{$mb_nick}',
                 mb_email = '{$mb_email}',
                 mb_homepage = '{$_POST['mb_homepage']}',
                 mb_birth = '{$_POST['mb_birth']}',
                 mb_tel = '{$_POST['mb_tel']}',
                 mb_hp = '{$mb_hp}',
                 mb_zip1 = '$mb_zip1',
                 mb_zip2 = '$mb_zip2',
                 mb_addr1 = '{$_POST['mb_addr1']}',
                 mb_addr2 = '{$_POST['mb_addr2']}',
                 mb_addr3 = '{$_POST['mb_addr3']}',
                 mb_addr_jibeon = '{$_POST['mb_addr_jibeon']}',
                 mb_memo = '{$_POST['mb_memo']}',
                 mb_leave_date = '{$_POST['mb_leave_date']}',
                 mb_intercept_date='{$_POST['mb_intercept_date']}',
                 mb_datetime = '{$_POST['mb_datetime']} 00:00:00',
                 mb_3 = '{$_POST['mb_3']}',
                 mb_4 = '{$_POST['mb_4']}',
                 mb_5 = '{$_POST['mb_5']}',
                 mb_6 = '{$_POST['mb_6']}',
                 mb_7 = '{$_POST['mb_7']}'
";

if($leave_flag){
    $sql_common .= " ,mb_level = '2' ";
}


if ($w == '')
{
//    $mb = get_member($mb_id);
//    if ($mb['mb_id'])
//        alert('이미 존재하는 회원아이디입니다.\\nＩＤ : '.$mb['mb_id'].'\\n이름 : '.$mb['mb_name'].'\\n닉네임 : '.$mb['mb_nick'].'\\n메일 : '.$mb['mb_email']);
//
//    // 닉네임중복체크
//    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_nick = '{$mb_nick}' ";
//    $row = sql_fetch($sql);
//    if ($row['mb_id'])
//        alert('이미 존재하는 닉네임입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);
//
//    // 이메일중복체크
//    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_email = '{$mb_email}' ";
//    $row = sql_fetch($sql);
//    if ($row['mb_id'])
//        alert('이미 존재하는 이메일입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

    //sql_query(" insert into {$g5['member_table']} set mb_id = '{$mb_id}', mb_password = '".get_encrypt_string($mb_password)."', mb_datetime = '".G5_TIME_YMDHIS."', mb_ip = '{$_SERVER['REMOTE_ADDR']}', mb_email_certify = '".G5_TIME_YMDHIS."', {$sql_common} ");
    $sql = " update {$g5['member_table']}
                set mb_level = '6'
                    , mb_open = '1'
                    , {$sql_common}
                    {$sql_password}
                    {$sql_certify}
                where mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    //echo $sql;
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

//    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'])
//        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    if ($is_admin !== 'super' && is_admin($mb['mb_id']) === 'super' ) {
        alert('최고관리자의 비밀번호를 수정할수 없습니다.');
    }

    if ($_POST['mb_id'] == $member['mb_id'] && $_POST['mb_level'] != $mb['mb_level'])
        alert($mb['mb_id'].' : 로그인 중인 관리자 레벨은 수정 할 수 없습니다.');

    // 닉네임중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_nick = '{$mb_nick}' and mb_id <> '$mb_id' ";
    $row = sql_fetch($sql);
    if ($row['mb_id'])
        alert('이미 존재하는 닉네임입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

    // 이메일중복체크
    $sql = " select mb_id, mb_name, mb_nick, mb_email from {$g5['member_table']} where mb_email = '{$mb_email}' and mb_id <> '$mb_id' ";
    $row = sql_fetch($sql);
    if ($row['mb_id'] && $member['mb_level']<9)
        alert('이미 존재하는 이메일입니다.\\nＩＤ : '.$row['mb_id'].'\\n이름 : '.$row['mb_name'].'\\n닉네임 : '.$row['mb_nick'].'\\n메일 : '.$row['mb_email']);

    $mb_dir = substr($mb_id,0,2);

    $sql = " update {$g5['member_table']}
                set {$sql_common}
                    {$sql_password}
                    {$sql_certify}
                where mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    //echo $sql;
    
    // 조직이 바뀐 경우 매출을 포함한 관련 정보들을 함께 다 바꿔줘야 합니다.
    // 같은 값이면 리턴 (다른 값이면 관련내용 수정)
    department_change($mb_id,$mb_2_old,$mb_2);
    
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

//기존 회원권한 삭제
$auth_del_sql = " DELETE FROM {$g5['auth_table']} where mb_id = '{$mb_id}' ";
sql_query($auth_del_sql,1);

//탈퇴 및 접근차단이 아닐때만 권한 재설정
if(!$leave_flag){
    //회원권한 재설정
    if($_POST['mb_6'] == 1) {
        $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
            ('{$mb_id}', '200100', 'r,w'),
            ('{$mb_id}', '960100', 'r,w'),
            ('{$mb_id}', '960120', 'r,w'),
            ('{$mb_id}', '960130', 'r,w'),
            ('{$mb_id}', '960140', 'r,w'),
            ('{$mb_id}', '960150', 'r,w'),
            ('{$mb_id}', '960200', 'r,w,d'),
            ('{$mb_id}', '960250', 'r,w'),
            ('{$mb_id}', '960210', 'r,w'),
            ('{$mb_id}', '960240', 'r,w'),
            ('{$mb_id}', '960215', 'r,w'),
            ('{$mb_id}', '960230', 'r,w'),
            ('{$mb_id}', '960260', 'r,w'),
            ('{$mb_id}', '960280', 'r,w'),
            ('{$mb_id}', '960220', 'r,w'),
            ('{$mb_id}', '960400', 'r,w,d'),
            ('{$mb_id}', '960500', 'r,w'),
            ('{$mb_id}', '960600', 'r,w'),
            ('{$mb_id}', '960610', 'r,w'),
            ('{$mb_id}', '960620', 'r,w'),
            ('{$mb_id}', '960700', 'r,w,d'),
            ('{$mb_id}', '960800', 'r,w,d') ";
        sql_query($auth_ins_sql,1);
    }
    else if($_POST['mb_6'] == 2) {
        $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
            ('{$mb_id}', '960100', 'r,w'),
            ('{$mb_id}', '960130', 'r,w'),
            ('{$mb_id}', '960140', 'r,w'),
            ('{$mb_id}', '960150', 'r,w'),
            ('{$mb_id}', '960200', 'r,w'),
            ('{$mb_id}', '960250', 'r,w'),
            ('{$mb_id}', '960210', 'r,w'),
            ('{$mb_id}', '960215', 'r,w'),
            ('{$mb_id}', '960230', 'r,w'),
            ('{$mb_id}', '960260', 'r,w'),
            ('{$mb_id}', '960280', 'r,w'),
            ('{$mb_id}', '960220', 'r,w'),
            ('{$mb_id}', '960400', 'r,w'),
            ('{$mb_id}', '960600', 'r,w'),
            ('{$mb_id}', '960610', 'r,w'),
            ('{$mb_id}', '960620', 'r,w'),
            ('{$mb_id}', '960700', 'r,w,d'),
            ('{$mb_id}', '960800', 'r,w,d') ";
        sql_query($auth_ins_sql,1);
    }
    else if($_POST['mb_6'] == 3) {
        $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
            ('{$mb_id}', '960100', 'r,w'),
            ('{$mb_id}', '960130', 'r,w'),
            ('{$mb_id}', '960140', 'r,w'),
            ('{$mb_id}', '960150', 'r'),
            ('{$mb_id}', '960215', 'r,w'),
            ('{$mb_id}', '960230', 'r,w'),
            ('{$mb_id}', '960260', 'r,w'),
            ('{$mb_id}', '960280', 'r,w'),
            ('{$mb_id}', '960220', 'r,w'),
            ('{$mb_id}', '960400', 'r,w'),
            ('{$mb_id}', '960600', 'r,w'),
            ('{$mb_id}', '960610', 'r,w'),
            ('{$mb_id}', '960620', 'r,w'),
            ('{$mb_id}', '960700', 'r'),
            ('{$mb_id}', '960800', 'r,w') ";
        sql_query($auth_ins_sql,1);
    }
    else if($_POST['mb_6'] == 4) {
        $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
            ('{$mb_id}', '960100', 'r,w'),
            ('{$mb_id}', '960130', 'r,w'),
            ('{$mb_id}', '960140', 'r,w'),
            ('{$mb_id}', '960150', 'r'),
            ('{$mb_id}', '960215', 'r,w'),
            ('{$mb_id}', '960230', 'r,w'),
            ('{$mb_id}', '960260', 'r,w'),
            ('{$mb_id}', '960280', 'r,w'),
            ('{$mb_id}', '960220', 'r,w'),
            ('{$mb_id}', '960400', 'r,w'),
            ('{$mb_id}', '960600', 'r,w'),
            ('{$mb_id}', '960610', 'r,w'),
            ('{$mb_id}', '960620', 'r,w'),
            ('{$mb_id}', '960700', 'r'),
            ('{$mb_id}', '960800', 'r,w') ";
        sql_query($auth_ins_sql,1);
    }
    else if($_POST['mb_6'] == 5) {
        $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
            ('{$mb_id}', '960100', 'r,w'),
            ('{$mb_id}', '960130', 'r,w'),
            ('{$mb_id}', '960140', 'r,w'),
            ('{$mb_id}', '960150', 'r'),
            ('{$mb_id}', '960215', 'r'),
            ('{$mb_id}', '960230', 'r,w'),
            ('{$mb_id}', '960260', 'r,w'),
            ('{$mb_id}', '960280', 'r,w'),
            ('{$mb_id}', '960220', 'r,w'),
            ('{$mb_id}', '960400', 'r,w'),
            ('{$mb_id}', '960600', 'r,w'),
            ('{$mb_id}', '960620', 'r,w'),
            ('{$mb_id}', '960700', 'r'),
            ('{$mb_id}', '960800', 'r,w') ";
        sql_query($auth_ins_sql,1);
    }
    else {
        if($mb_id == 'iljung'){
            $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
                ('{$mb_id}', '960100', 'r,w'),
                ('{$mb_id}', '960230', 'r,w'),
                ('{$mb_id}', '960400', 'r,w'),
                ('{$mb_id}', '960280', 'r,w'),
                ('{$mb_id}', '960600', 'r,w'),
                ('{$mb_id}', '960620', 'r,w') ";
            sql_query($auth_ins_sql,1);
        }
        else{
            $auth_ins_sql = " INSERT INTO {$g5['auth_table']} (`mb_id`, `au_menu`, `au_auth`) VALUES
                ('{$mb_id}', '960100', 'r,w'),
				('{$mb_id}', '960130', 'r,w'),
                ('{$mb_id}', '960140', 'r,w'),
                ('{$mb_id}', '960150', 'r'),
                ('{$mb_id}', '960215', 'r'),
                ('{$mb_id}', '960260', 'r,w'),
                ('{$mb_id}', '960280', 'r,w'),
                ('{$mb_id}', '960400', 'r,w'),
                ('{$mb_id}', '960600', 'r,w'),
                ('{$mb_id}', '960620', 'r,w'),
                ('{$mb_id}', '960700', 'r'),
                ('{$mb_id}', '960800', 'r,w') ";
            sql_query($auth_ins_sql,1);
        }
    }
}

// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs;

// 법인 다중선택 변수
$_REQUEST['mb_firm_idxs'] = @implode(",",$mb_firm_idx);

//-- 필드명 추출 mb_ 와 같은 앞자리 3자 추출 --//
$r = sql_query(" desc {$g5['member_table']} ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,3);

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "mb_2_old";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>"member","mta_db_id"=>$mb_id,"mta_key"=>$key,"mta_value"=>$value));
	}
}


//exit;
//goto_url('./employee_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$mb_id, false);
alert('데이터가 등록되었습니다.','./employee_list.php?'.$qstr, false);
?>