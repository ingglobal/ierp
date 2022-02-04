<?php






//파일 삭제처리
$merge_del = array();
$del_arr = array();

if(@count($prexp_con_del)){
	foreach($prexp_con_del as $k=>$v) {
		$merge_del[$k] = $v;
	}
}

if(@count($prexp_ord_del)){
	foreach($prexp_ord_del as $k=>$v) {
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
upload_multi_file($_FILES['prj_prexp_con_datas'],'expense',$prj_idx,'prexp_con');
upload_multi_file($_FILES['prj_prexp_ord_datas'],'expense',$prj_idx,'prexp_ord');