<?php
$sub_menu = "960210";
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
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}

// prj_quot_yn
$_POST['prj_quot_yn'] = ($_POST['prj_status'] == 'request' || $_POST['prj_status'] == 'inprocess' || $_POST['prj_status'] == 'ok') ? 1 : 0;

//$_POST['prj_type'] = 'normal';

// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
//echo $prj_price_submit;exit;

$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';

$sql_prc_common = "
	prp_content = '',
	prp_content2 = '',
	prp_doc_deal = '',
	prp_plan_date = '',
	prp_issue_date = '',
	prp_pay_date = '',
	prp_status = 'ok'
";

$prj_price_submit = str_replace(',','',trim($prj_price_submit));
$prj_price_nego= str_replace(',','',trim($prj_price_nego));
$prj_price_order = str_replace(',','',trim($prj_price_order));



if ($w == '') {
    
    $sql = " INSERT into {$g5_table_name} SET 
                {$sql_common} 
                , ".$pre."_reg_dt = '".G5_TIME_YMDHIS."'
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	${$pre."_idx"} = sql_insert_id();
    
	
	
	$sql_prc_submit = " INSERT into {$g5['project_price_table']} SET
		{$sql_prc_common}
		,prj_idx = '".${$pre."_idx"}."'
		,prp_price = '{$prj_price_submit}'
		,prp_type = 'submit'
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql_prc_submit,1);
	
	
	$sql_prc_nego = " INSERT into {$g5['project_price_table']} SET 
		{$sql_prc_common}
		,prj_idx = '".${$pre."_idx"}."'
		,prp_price = '{$prj_price_nego}'
		,prp_type = 'nego'
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql_prc_nego,1);
	
	$sql_prc_order = " INSERT into {$g5['project_price_table']} SET
		{$sql_prc_common}
		,prj_idx = '".${$pre."_idx"}."'
		,prp_price = '{$prj_price_order}'
		,prp_type = 'order' 
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql_prc_order,1);
	
}
else if ($w == 'u') {

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
 
    $sql = "	UPDATE {$g5_table_name} SET 
					{$sql_common}
					, ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
				WHERE ".$pre."_idx = '".${$pre."_idx"}."' 
	";
	//echo $sql.'<br>';
	
    sql_query($sql,1);
	
	$sql_prc_submit1 = " INSERT into {$g5['project_price_table']} SET
		{$sql_prc_common}
		,prj_idx = '".${$pre."_idx"}."'
		,prp_price = '{$prj_price_submit}'
		,prp_type = 'submit'
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
	";
    $sql_prc_submit2 = " UPDATE {$g5['project_price_table']} SET
		{$sql_prc_common}
		,prp_price = '{$prj_price_submit}'
		,prp_type = 'submit'
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
		WHERE prj_idx = '".${$pre."_idx"}."' AND prp_type = 'submit'
	";
	
	$submit_cnt = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['project_price_table']} WHERE prj_idx = '".${$pre."_idx"}."' AND prp_type = 'submit' ");
	$sql_prc_submit = (!$submit_cnt['cnt']) ? $sql_prc_submit1 : $sql_prc_submit2;
	//echo $sql_prc_submit;exit;
	sql_query($sql_prc_submit,1);
	
	//==============================================================
	
	$sql_prc_nego1 = " INSERT into {$g5['project_price_table']} SET 
		{$sql_prc_common}
		,prj_idx = '".${$pre."_idx"}."'
		,prp_price = '{$prj_price_nego}'
		,prp_type = 'nego'
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
	";
	$sql_prc_nego2 = " UPDATE {$g5['project_price_table']} SET 
		{$sql_prc_common}
		,prp_price = '{$prj_price_nego}'
		,prp_type = 'nego'
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
		WHERE prj_idx = '".${$pre."_idx"}."' AND prp_type = 'nego'
	";
	
	$nego_cnt = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['project_price_table']} WHERE prj_idx = '".${$pre."_idx"}."' AND prp_type = 'nego' ");
	$sql_prc_nego = (!$nego_cnt['cnt']) ? $sql_prc_nego1 : $sql_prc_nego2;
	
	sql_query($sql_prc_nego,1);
	
	//==============================================================
	
	$sql_prc_order1 = " INSERT into {$g5['project_price_table']} SET
		{$sql_prc_common}
		,prj_idx = '".${$pre."_idx"}."'
		,prp_price = '{$prj_price_order}'
		,prp_type = 'order' 
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
	";
	$sql_prc_order2 = " UPDATE {$g5['project_price_table']} SET
		{$sql_prc_common}
		,prp_price = '{$prj_price_order}'
		,prp_type = 'order' 
		,prp_reg_dt = '".G5_TIME_YMDHIS."'
		,prp_update_dt = '".G5_TIME_YMDHIS."'
		WHERE prj_idx = '".${$pre."_idx"}."' AND prp_type = 'order'
	";
	
	$order_cnt = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['project_price_table']} WHERE prj_idx = '".${$pre."_idx"}."' AND prp_type = 'order' ");
	$sql_prc_order = (!$order_cnt['cnt']) ? $sql_prc_order1 : $sql_prc_order2;
	
	sql_query($sql_prc_order,1); 
	
	//견적서를 삭제
	if ($del_quot_file){
		$qsql = sql_fetch(" SELECT prj_quot_file FROM {$g5['project_table']} WHERE prj_idx = '".${$pre."_idx"}."' ");
    	@unlink(G5_DATA_PATH.'/ierp/'.${$pre."_idx"}.'/'.$qsql['prj_quot_file']);
		$qfsql = " UPDATE {$g5['project_table']} SET prj_quot_file = '' WHERE prj_idx = '".${$pre."_idx"}."' ";
		sql_query($qfsql,1); 	
	}
	//발주서를 삭제
	if ($del_order_file){
		$osql = sql_fetch(" SELECT prj_order_file FROM {$g5['project_table']} WHERE prj_idx = '".${$pre."_idx"}."' ");
    	@unlink(G5_DATA_PATH.'/ierp/'.${$pre."_idx"}.'/'.$osql['prj_order_file']);
		$ofsql = " UPDATE {$g5['project_table']} SET prj_order_file = '' WHERE prj_idx = '".${$pre."_idx"}."' ";
		sql_query($ofsql,1);		
	}
	//계약서를 삭제
	if ($del_contract_file){
		$csql = sql_fetch(" SELECT prj_contract_file FROM {$g5['project_table']} WHERE prj_idx = '".${$pre."_idx"}."' ");
    	@unlink(G5_DATA_PATH.'/ierp/'.${$pre."_idx"}.'/'.$csql['prj_contract_file']);
		$cfsql = " UPDATE {$g5['project_table']} SET prj_contract_file = '' WHERE prj_idx = '".${$pre."_idx"}."' ";
		sql_query($cfsql,1);	
	}
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

if($w != 'd'){
	sql_query(" UPDATE {$g5['project_table']} SET prj_order_price = '{$prj_price_order}' WHERE prj_idx = '".${$pre."_idx"}."' ");
}


//파일 삭제처리
$merge_del = array();
$del_arr = array();
if(@count($quot_del)){
	foreach($quot_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}
if(@count($order_del)){
	foreach($order_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}
if(@count($contract_del)){
	foreach($contract_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}
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
if(count($del_arr)) delete_idx_file($del_arr);

//멀티파일처리
upload_multi_file($_FILES['prj_q_datas'],'quot',$prj_idx,'quot');
upload_multi_file($_FILES['prj_o_datas'],'quot',$prj_idx,'order');
upload_multi_file($_FILES['prj_c_datas'],'quot',$prj_idx,'contract');
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
//goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
alert('데이터가 등록되었습니다.','./'.$fname.'_list.php?'.$qstr, false);
?>