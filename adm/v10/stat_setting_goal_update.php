<?php
$sub_menu = "960500";
include_once('./_common.php');

check_demo();

$count = count($_POST['chk']);
if (!$count)
	alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");

// 수정
if($w == 'u') {
	
	for ($i=0; $i<$count; $i++) {
		// 실제 번호를 넘김
		$k = $chk[$i];
		
		// 천단위 제거
		$_POST['slg_sales_goal'][$k] = preg_replace("/,/","",$_POST['slg_sales_goal'][$k]);

		// 회원의 조직코드 추출
        $mb2 = get_member2($_POST['mb_id_saler'][$k]);

		// 값 존재 여부 체크
		$row1 = sql_fetch(" SELECT * FROM {$g5['sales_goal_table']}
							WHERE mb_id_saler='".$_POST['mb_id_saler'][$k]."' 
								AND slg_ym='".$_POST['ym']."-00' 
								AND slg_status = 'ok'
		");
		if($row1['slg_idx']) {
			$sql = " UPDATE {$g5['sales_goal_table']} SET 
							trm_idx_department='".$mb2['mb_2']."',
							slg_sales_goal='".$_POST['slg_sales_goal'][$k]."' 
						WHERE slg_idx='".$row1[slg_idx]."'
			";
			sql_query($sql,1);
			//echo $sql.'<br>';
		}
		else {
			$sql = " INSERT INTO {$g5['sales_goal_table']} SET 
							mb_id_saler='".$_POST['mb_id_saler'][$k]."',
							trm_idx_department='".$mb2['mb_2']."',
							slg_ym='".$_POST['ym']."-00', 
							slg_sales_goal='".$_POST['slg_sales_goal'][$k]."',
							slg_status='ok',
							slg_reg_dt='".G5_TIME_YMDHIS."'
			";
			sql_query($sql,1);
			//echo $sql.'<br>';
		}
	}
}

//exit;
goto_url('./stat_setting_goal.php?'.$qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ym='.$ym, false);
?>
