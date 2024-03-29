<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 사용자 테이블 정의
define('PROJ_TABLE_PREFIX', G5_TABLE_PREFIX.'1_');

$g5['card_table']                       = PROJ_TABLE_PREFIX.'card';
$g5['card_user_table']                  = PROJ_TABLE_PREFIX.'card_user';
$g5['company_table']                    = PROJ_TABLE_PREFIX.'company';
$g5['companyetc_table']                 = PROJ_TABLE_PREFIX.'companyetc';
$g5['company_member_table']             = PROJ_TABLE_PREFIX.'company_member';
$g5['companyetc_member_table']          = PROJ_TABLE_PREFIX.'companyetc_member';
$g5['company_saler_table']              = PROJ_TABLE_PREFIX.'company_saler';
$g5['company_rate_table']               = PROJ_TABLE_PREFIX.'company_rate';
$g5['order_log_table']       	        = PROJ_TABLE_PREFIX.'order_log';
$g5['project_table']                    = PROJ_TABLE_PREFIX.'project';
$g5['project_price_table']              = PROJ_TABLE_PREFIX.'project_price';
$g5['etc_exprice_table']    	        = PROJ_TABLE_PREFIX.'etc_exprice';
$g5['project_exprice_table']            = PROJ_TABLE_PREFIX.'project_exprice';
$g5['project_inprice_table']            = PROJ_TABLE_PREFIX.'project_inprice';
$g5['project_schedule_table']           = PROJ_TABLE_PREFIX.'project_schedule';
$g5['project_overtime_table']           = PROJ_TABLE_PREFIX.'project_overtime';
$g5['project_purchase_table']           = PROJ_TABLE_PREFIX.'project_purchase';
$g5['project_purchase_divide_table']    = PROJ_TABLE_PREFIX.'project_purchase_divide';
$g5['project_purchase_tmp_table']       = PROJ_TABLE_PREFIX.'project_purchase_tmp';
$g5['contract_table']                   = PROJ_TABLE_PREFIX.'contract';
$g5['personal_caruse_table']            = PROJ_TABLE_PREFIX.'personal_caruse';
$g5['personal_expenses_table']          = PROJ_TABLE_PREFIX.'personal_expenses';

$g5['data_table']           	        = PROJ_TABLE_PREFIX.'data';


?>
