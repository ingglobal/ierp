<?php
$sub_menu = "960230";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_schedule';
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
    
    //(')따옴표등이 붙으면 없애고 다시 대입
    /*
    if(preg_match("/_task/",$fields[$i]) || preg_match("/_content/",$fields[$i])){
        $_POST[$fields[$i]] = preg_replace("/'/","",$_POST[$fields[$i]]);
        $_POST[$fields[$i]] = preg_replace("/\"/","",$_POST[$fields[$i]]);
    }
    */
    if(preg_match("/_task/",$fields[$i])){
        $_POST[$fields[$i]] = preg_replace("/'/","",$_POST[$fields[$i]]);
        $_POST[$fields[$i]] = preg_replace("/\"/","",$_POST[$fields[$i]]);
    } 
}

// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';
//print_r2($_POST);exit;
//echo $sql_common;exit;

//2021.06.25 이후 수정 버전
if($_POST['mb_id_worker'] == 'iljung'){
    if($_POST['prs_department']){
        alert('[일정관리(iljung)]는 부서를 선택할 필요없습니다.');
    }
}
else {
    if(!$_POST['prs_department']){
        alert('부서를 선택하셔야 합니다.');
    }
} 


/*
2021.06.25 이전 버전
if($_POST['prs_role'] == 'pm'){
	if($_POST['mb_id_worker'] != 'iljung'){
		alert('PM역할은 [일정관리(iljung)]라는 담당자만 맡을 수 있습니다.');
	}
	
    if($_POST['prs_department']){
        alert('PM역할은 부서를 선택하지 마세요.');
    }
}
else{
	if($_POST['mb_id_worker'] == 'iljung'){
		if($_POST['prs_department']){
			alert('[일정관리(iljung)]는 부서를 선택할 필요없습니다.');
		}
	}
	else {
		if(!$_POST['prs_department']){
			alert('부서를 선택하셔야 합니다.');
		}
	} 
}
*/
// print_r2($prj_content);exit;
$prj_content = trim($prj_content);
$prj_content = conv_unescape_nl(stripslashes($prj_content));
if ($w == '') {
    
    $sql = " INSERT into {$g5_table_name} SET 
                {$sql_common} 
                , ".$pre."_reg_dt = '".G5_TIME_YMDHIS."'
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	${$pre."_idx"} = sql_insert_id();
    //prj_content 수정
    if($super_ceo_admin){
        $sql0 = " UPDATE {$g5['project_table']} SET
                    prj_content = '{$prj_content}'
                    , prj_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE prj_idx = '{$prj_idx}'
        ";
        sql_query($sql0,1);
    }
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
    //echo $sql.'<br>';exit;
    sql_query($sql,1);
    
    //prj_content 수정
    if($super_ceo_admin){
        $sql0 = " UPDATE {$g5['project_table']} SET
                    prj_content = '{$prj_content}'
                    , prj_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE prj_idx = '{$prj_idx}'
        ";
        sql_query($sql0,1);
    }
        
}
else if ($w == 'd') {

    $sql = "UPDATE {$g5_table_name} SET
                ".$pre."_status = 'trash'
            WHERE ".$pre."_idx = '".${$pre."_idx"}."'
            ";
    sql_query($sql,1);
    if($url && !$gant){
        check_url_host($url,'',G5_URL,true);
        $link = urldecode($url);
        goto_url($link, false);
    }else if($gant){
        goto_url('./project_gantt.php?'.$qstr, false);
    }else{
        //goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
        alert('데이터가 삭제되었습니다.','./'.$fname.'_list.php?'.$qstr, false);
    }
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


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
if($url && !$gant){
	check_url_host($url,'',G5_URL,true);
	$link = urldecode($url);
	goto_url($link, false);
}else if($gant){
	goto_url('./project_gantt.php?'.$qstr, false);
}else{
	//goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
	alert('데이터가 등록되었습니다.','./'.$fname.'_list.php?'.$qstr, false);
}
?>