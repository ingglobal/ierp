<?php
include_once('./_common.php');

$del_arr = ($dels) ? explode(',',$dels) : array();

//파일 삭제처리
if(@count($del_arr)) delete_idx_file($del_arr);

//멀티파일처리
if(@count($_FILES['prx_files'])){
	upload_multi_file($_FILES['prx_files'],'project_exprice',$prx_idx,$type);
}
echo 'ok';