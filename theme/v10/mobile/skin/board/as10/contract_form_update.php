<?php
include_once('./_common.php');

if($member['mb_level']<6)
    alert('접근이 불가능한 메뉴입니다.');

$wr = get_table_meta($bo_table,'wr_id',$wr_id);
if(!$wr['wr_id'])
    alert('관련 영업정보가 존재하지 않습니다.');


if($w=='u') {
    $ctr = get_table_meta('contract','ctr_idx',$ctr_idx);
}

// 천단위 제거
$ctr_price = preg_replace("/,/","",$_POST['ctr_price']);

$sql_common = "mb_id_saler = '".$wr['mb_id']."'
				, com_idx = '".$wr['wr_2']."'
				, wr_id = '".$wr['wr_id']."'
				, ctr_item = '".$_POST['ctr_item']."'
				, ctr_percent = '".$_POST['ctr_percent']."'
				, ctr_price = '".$ctr_price."'
				, ctr_sales_date = '".$_POST['ctr_sales_date']."'
				, ctr_memo = '".$_POST['ctr_memo']."'
				, ctr_status = '".$_POST['ctr_status']."'
";

if ($w == 'd') {
	$sql = "UPDATE {$g5['contract_table']} SET
				ctr_memo = CONCAT(ctr_memo,'\n삭제 ".G5_TIME_YMDHIS.' by '.$member['mb_name']."')
				, ctr_status = 'trash'
			WHERE ctr_idx = '".$ctr_idx."'
	";
//    echo $sql.'<br>';
	sql_query($sql,1);
}
else if (!$ctr_idx) {
	$sql = " INSERT INTO {$g5['contract_table']} SET
				{$sql_common}
				, ctr_reg_dt = '".G5_TIME_YMDHIS."'
	";
//    echo $sql.'<br>';
	sql_query($sql,1);
}
else {
	$sql = " UPDATE {$g5['contract_table']} SET
					{$sql_common}
				WHERE ctr_idx = '".$ctr_idx."'
	";
    echo $sql.'<br>';
	sql_query($sql,1);
}

//exit;
echo "<script>opener.document.location.reload();</script>";
alert_close("수주 정보를 업데이트했습니다.");
?>