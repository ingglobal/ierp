<?php
$sub_menu = "960215";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form_update/","",$g5['file_name']); // _form_update를 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
}

// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt','mb_id_company','mb_id_saler'
				,'mb_id_account',$pre.'_doc_no',$pre.'_quot_yn',$pre.'_belongto',$pre.'_order_price'
				,$pre.'_receivable',$pre.'_keys',$pre.'_quot_file',$pre.'_order_file',$pre.'_contract_file'
				,$pre.'_ask_date',$pre.'_submit_date',$pre.'_contract_date');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';

$quot_yn = ($_POST['prj_status'] == 'request' || $_POST['prj_status'] == 'inprocess' || $_POST['prj_status'] == 'ok') ? 1 : 0;

if ($w == '') {
    
    $sql = " INSERT into {$g5_table_name} SET 
                {$sql_common} 
                , ".$pre."_reg_dt = '".G5_TIME_YMDHIS."'
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
				, ".$pre."_quot_yn = '".$quot_yn."'
	";
    sql_query($sql,1);
	${$pre."_idx"} = sql_insert_id();
    
}
else if ($w == 'u') {

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
 
    $sql = "	UPDATE {$g5_table_name} SET 
					{$sql_common}
					, ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
					, ".$pre."_quot_yn = '".$quot_yn."'
				WHERE ".$pre."_idx = '".${$pre."_idx"}."' 
	";
    //echo $sql.'<br>';
    sql_query($sql,1);
        
}
else if ($w == 'd') {

    $sql = "UPDATE {$g5_table_name} SET
                ".$pre."_status = 'trash'
            WHERE ".$pre."_idx = '".${$pre."_idx"}."'
            ";
    sql_query($sql,1);
    goto_url('./'.$fname.'_list.php?'.$qstr, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');



// 파일 처리2 (파일 타입이 여러개면 일련번호 붙여서 확장해 주세요.) ----------------
//파일 삭제처리
$merge_del = array();
$del_arr = array();
if(@count($ref_del)){
	foreach($ref_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}
if(count($merge_del)){
	foreach($merge_del as $k=>$v) {
		array_push($del_arr,$k);
	}
}
if(count($del_arr)) delete_idx_file($del_arr);\
//멀티파일처리
upload_multi_file($_FILES['prj_ref_files'],'project',$prj_idx,'ref');


//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$fields[] = "mms_zip";	// 건너뛸 변수명은 배열로 추가해 준다.
$fields[] = "mms_sido_cd";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$fields) && substr($key,0,3)==$pre) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>$table_name,"mta_db_id"=>${$pre."_idx"},"mta_key"=>$key,"mta_value"=>$value));
	}
}

//exit;
if($url){
	check_url_host($url,'',G5_URL,true);
	$link = urldecode($url);
	goto_url($link, false);
}else{
	//goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
	alert('데이터가 등록되었습니다.','./'.$fname.'_list.php?'.$qstr, false);
}
?>