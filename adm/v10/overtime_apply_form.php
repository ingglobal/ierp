<?php
$sub_menu = "960130";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '잔업신청목록';
//include_once('./_top_menu_project.php');
include_once('./_head.php');
/*
잔업상태(set_pro_status) : pending=대기,cancel=취소,reject=반려,ok=승인
잔업유형(set_pro_type) : etw=연장근무,ntw=야간근무,anw=철야근무,hdw=휴일근무,hew=휴일연장근무,hnw=휴일야간근무,haw=휴일철야근무
잔업수당(set_extrapay_rate) : 1.5=etw,2=ntw,2=anw,1.5=hdw,2=hew,2.5=hnw,2.5=haw
$g5[se__value][]
$g5[se__reverse][]
$g5[se__value_option]
$g5['project_overtime_table']
pro_idx
prj_idx
mb_id_worker
mb_id_approver
pro_work_date
pro_work_hours
pro_content
pro_type
pro_status
pro_reg_dt
*/
?>





<?php
include_once ('./_tail.php');
?>