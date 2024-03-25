정식발주테이블 생성
CREATE TABLE `g5_1_project_purchase` (
  `ppc_idx` bigint(20) AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '정식발주idx',
  `com_idx` bigint(20) NOT NULL COMMENT '공급업체',
  `prj_idx` bigint(20) NOT NULL COMMENT '프로젝트idx',
  `mb_id` varchar(20) NOT NULL COMMENT '발주자',
  `ppc_date` date DEFAULT '0000-00-00' COMMENT '발주일',
  `ppc_subject` varchar(255) DEFAULT NULL COMMENT '발주중요품목',
  `ppc_content` text DEFAULT NULL COMMENT '발주내용',
  `ppc_price` bigint(20) NOT NULL DEFAULT 0 COMMENT '발주금액',
  `ppc_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '상태',
  `ppc_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `ppc_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

임시발주테이블 생성
CREATE TABLE `g5_1_project_purchase_tmp` (
  `ppt_idx` bigint(20) AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '임시발주idx',
  `com_idx` bigint(20) NOT NULL COMMENT '공급업체',
  `prj_idx` bigint(20) NOT NULL COMMENT '프로젝트idx',
  `ppc_idx` bigint(20) NULL DEFAULT '0' COMMENT '정식발주idx',
  `mb_id` varchar(20) NOT NULL COMMENT '발주자',
  `ppt_date` date DEFAULT '0000-00-00' COMMENT '발주일',
  `ppt_subject` varchar(255) DEFAULT NULL COMMENT '발주중요품목',
  `ppt_content` text DEFAULT NULL COMMENT '발주내용',
  `ppt_price` bigint(20) NOT NULL DEFAULT 0 COMMENT '발주금액',
  `ppt_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '상태',
  `ppt_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `ppt_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

임시발주테이블 생성
CREATE TABLE `g5_1_project_purchase_divide` (
  `ppd_idx` bigint(20) AUTO_INCREMENT PRIMARY KEY NOT NULL COMMENT '지출분배idx',
  `ppc_idx` bigint(20) NULL DEFAULT '0' COMMENT '정식발주idx',
  `ppd_content` text DEFAULT NULL COMMENT '내용',
  `ppd_price` bigint(20) NOT NULL DEFAULT 0 COMMENT '지출금액',
  `ppd_plan_date` date DEFAULT '0000-00-00' COMMENT '지출예정일',
  `ppd_done_date` date DEFAULT '0000-00-00' COMMENT '지출확정일',
  `ppd_bank` varchar(20) NOT NULL DEFAULT 'bank' COMMENT '지급통장',
  `ppd_type` varchar(20) NOT NULL DEFAULT 'all' COMMENT '지급통장',
  `ppd_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '상태',
  `ppd_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `ppd_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;