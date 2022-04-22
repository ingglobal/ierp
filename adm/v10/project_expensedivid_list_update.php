<?php
$sub_menu = "960245";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_exprice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _update을 제외한 파일명
// $qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

// 삭제할 때
if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        ${$pre} = get_table_meta($table_name, $pre.'_idx', $_POST[$pre.'_idx'][$k]);
        if (!${$pre}[$pre.'_idx'])
            $msg .= ${$pre}[$pre.'_idx'].': 자료가 존재하지 않습니다.\\n';
        else {
            // 상태값 변경
            /*
            $sql = "	UPDATE {$g5_table_name} SET 
                            ".$pre."_status = 'trash'
                        WHERE ".$pre."_idx = '".$_POST[$pre.'_idx'][$k]."' 
            ";
			sql_query($sql,1);
            */
            //우선해당 prx_idx으로 등록된 fle_idx를 추출한다.
            $fle_idxs = sql_fetch(" SELECT GROUP_CONCAT(fle_idx) AS fidxs FROM {$g5['file_table']} WHERE
                            fle_db_table = 'project_exprice'
                            AND fle_db_id = '{$_POST[$pre.'_idx'][$k]}'
                            AND fle_type = '{$_POST[$pre.'_type'][$k]}'
            ");
            $del_arr = ($fle_idxs['fidxs']) ? explode(',',$fle_idxs['fidxs']) : array();
            //파일 삭제처리
            if(@count($del_arr)) delete_idx_file($del_arr);

            $sql = " DELETE FROM {$g5['project_exprice_table']} WHERE prx_idx = '{$_POST[$pre.'_idx'][$k]}' ";
            sql_query($sql,1);
        }
    }
}

if ($msg)
    alert($msg);
    //echo '<script> alert("'.$msg.'"); </script>';
	
goto_url('./'.$fname.'.php?'.$qstr, false);