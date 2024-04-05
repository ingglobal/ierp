<?php
if($member['mb_id'] == 'super'){
$menu["menu960"] = array (
	array('960000', 'i-ERP', ''.G5_USER_ADMIN_URL.'/index.php', 'index'),
	array('960100', '대시보드', ''.G5_USER_ADMIN_URL.'/index.php', 'index'),
	array('960120', '사원관리', ''.G5_USER_ADMIN_URL.'/employee_list.php', 'employee_list'),
	array('960150', '일정관리', ''.G5_THEME_URL.'/skin/board/schedule11/list.calendar.php?bo_table=schedule', 'schedule'),
	array('960200', '업체관리', ''.G5_USER_ADMIN_URL.'/company_list.php', 'company_list'),
	array('960250', '영업관리', ''.G5_BBS_URL.'/board.php?bo_table=sales', 'sales_list'),
	array('960280', '프로젝트안건', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=agenda', 'bbs_board'),
	array('960210', '프로젝트견적', ''.G5_USER_ADMIN_URL.'/quot_list.php', 'quot_list'),
	array('960240', '수입관리', ''.G5_USER_ADMIN_URL.'/project_group_price_list.php', 'project_group_price_list'),
	array('960244', '매입관리', ''.G5_USER_ADMIN_URL.'/prj_order_list.php', 'prj_order_list'),
	// array('960248', '기타수입관리(과제별)', ''.G5_USER_ADMIN_URL.'/project_income_list.php', 'project_income_list'),
	array('960245', '실행가관리', ''.G5_USER_ADMIN_URL.'/project_expense_list.php', 'project_expense_list'),
	// array('960255', '기타지출관리', ''.G5_USER_ADMIN_URL.'/etc_expense_list.php', 'etc_expense_list'),
	array('960266', '그룹발주관리', ''.G5_USER_ADMIN_URL.'/prj_purchase_list.php', 'prj_purchase_list'),
	array('960268', '개별발주관리', ''.G5_USER_ADMIN_URL.'/prj_purchasetmp_list.php', 'prj_purchasetmp_list'),
	array('960257', '카드관리', ''.G5_USER_ADMIN_URL.'/card_user_list.php', 'card_user_list'),
	array('960215', '프로젝트관리', ''.G5_USER_ADMIN_URL.'/project_list.php', 'project_list'),
	array('960230', '프로젝트일정', ''.G5_USER_ADMIN_URL.'/project_gantt.php', 'project_gantt'),
	array('960260', '회의록', ''.G5_BBS_URL.'/board.php?bo_table=meeting', 'meeting_list'),
	array('960220', '부품/재고관리', ''.G5_USER_ADMIN_URL.'/itemlist.php', 'itemlist'),
	array('960400', 'A/S관리', ''.G5_BBS_URL.'/board.php?bo_table=as', 'as_list'),
	array('960500', '통계', ''.G5_USER_ADMIN_URL.'/stat_project.php', 'stat_project'),
	array('960600', '내정보', ''.G5_USER_ADMIN_URL.'/my_info.php', 'my_info'),
	array('960130', '특근신청', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=overtime', 'overtime'),
	array('960140', '연차및휴가신청', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=dayoff', 'dayoff'),
	array('960610', '차량예약', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=car_rsv&calendar=1', 'car_rsv'),
	array('960620', '차량관리', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=car_mng', 'car_mng'),
	array('960630', '개인차량사용내역', ''.G5_USER_ADMIN_URL.'/personal_caruse_list.php', 'personal_caruse_list'),
	array('960640', '개인지출내역', ''.G5_USER_ADMIN_URL.'/personal_expenses_list.php', 'personal_expenses_list'),
	//array('960740', '매뉴얼', G5_BBS_URL.'/board.php?bo_table=manual', 'manual_list'),
	array('960700', '공지사항', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=notice1', 'notice1'),
	array('960800', '자료실', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=erpdata', 'bbs_board')
);
}
else{
$menu["menu960"] = array (
	array('960000', 'i-ERP', ''.G5_USER_ADMIN_URL.'/index.php', 'index'),
	array('960100', '대시보드', ''.G5_USER_ADMIN_URL.'/index.php', 'index'),
	array('960120', '사원관리', ''.G5_USER_ADMIN_URL.'/employee_list.php', 'employee_list'),
	array('960150', '일정관리', ''.G5_THEME_URL.'/skin/board/schedule11/list.calendar.php?bo_table=schedule', 'schedule'),
	array('960200', '업체관리', ''.G5_USER_ADMIN_URL.'/company_list.php', 'company_list'),
	array('960250', '영업관리', ''.G5_BBS_URL.'/board.php?bo_table=sales', 'sales_list'),
	array('960280', '프로젝트안건', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=agenda', 'bbs_board'),
	array('960210', '프로젝트견적', ''.G5_USER_ADMIN_URL.'/quot_list.php', 'quot_list'),
	array('960240', '수입관리', ''.G5_USER_ADMIN_URL.'/project_group_price_list.php', 'project_group_price_list'),
	// array('960248', '기타수입관리', ''.G5_USER_ADMIN_URL.'/project_income_list.php', 'project_income_list'),
	// array('960245', '실행가관리', ''.G5_USER_ADMIN_URL.'/project_expense_list.php', 'project_expense_list'),
	// array('960255', '기타지출관리', ''.G5_USER_ADMIN_URL.'/etc_expense_list.php', 'etc_expense_list'),
	array('960266', '그룹발주관리', ''.G5_USER_ADMIN_URL.'/prj_purchase_list.php', 'prj_purchase_list'),
	array('960268', '개별발주관리', ''.G5_USER_ADMIN_URL.'/prj_purchasetmp_list.php', 'prj_purchasetmp_list'),
	array('960257', '카드관리', ''.G5_USER_ADMIN_URL.'/card_user_list.php', 'card_user_list'),
	array('960215', '프로젝트관리', ''.G5_USER_ADMIN_URL.'/project_list.php', 'project_list'),
	array('960230', '프로젝트일정', ''.G5_USER_ADMIN_URL.'/project_gantt.php', 'project_gantt'),
	array('960260', '회의록', ''.G5_BBS_URL.'/board.php?bo_table=meeting', 'meeting_list'),
	array('960220', '부품/재고관리', ''.G5_USER_ADMIN_URL.'/itemlist.php', 'itemlist'),
	array('960400', 'A/S관리', ''.G5_BBS_URL.'/board.php?bo_table=as', 'as_list'),
	array('960500', '통계', ''.G5_USER_ADMIN_URL.'/stat_project.php', 'stat_project'),
	array('960600', '내정보', ''.G5_USER_ADMIN_URL.'/my_info.php', 'my_info'),
	array('960130', '특근신청', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=overtime', 'overtime'),
	array('960140', '연차신청', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=dayoff', 'dayoff'),
	array('960610', '차량예약', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=car_rsv&calendar=1', 'car_rsv'),
	array('960620', '차량관리', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=car_mng', 'car_mng'),
	array('960630', '개인차량사용내역', ''.G5_USER_ADMIN_URL.'/personal_caruse_list.php', 'personal_caruse_list'),
	array('960640', '개인지출내역', ''.G5_USER_ADMIN_URL.'/personal_expenses_list.php', 'personal_expenses_list'),
	//array('960740', '매뉴얼', G5_BBS_URL.'/board.php?bo_table=manual', 'manual_list'),
	array('960700', '공지사항', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=notice1', 'notice1'),
	array('960800', '자료실', ''.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table=erpdata', 'bbs_board')
);
}
?>
