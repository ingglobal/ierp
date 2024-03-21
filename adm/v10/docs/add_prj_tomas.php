매입테이블 생성
CREATE TABLE `g5_1_project_purchase` (
  `ppc_idx` bigint(20) AUTO_INCCREMENT PRIMARY KEY NOT NULL COMMENT '발주idx',
  `com_idx` bigint(20) NOT NULL COMMENT '공급업체'
  `prj_idx` bigint(20) NOT NULL COMMENT '프로젝트idx'
  `mb_id` varchar(20) NOT NULL COMMENT '발주자',
  `ppc_date` date DEFAULT '0000-00-00' COMMENT '발주일',
  `ppc_subject` varchar(255) DEFAULT NULL COMMENT '발주중요품목',
  `ppc_content` text DEFAULT NULL COMMENT '발주내용',
  `ppc_price` bigint(20) NOT NULL DEFAULT 0 COMMENT '발주금액',
  `ppc_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '상태',
  `ppc_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `ppc_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;