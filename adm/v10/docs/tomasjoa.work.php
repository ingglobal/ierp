기타수입
1) 프로젝트/제목/수입일/금액 : 기계,전기,기타 구분필요없음

지출관리
1) 지출예정일(필수항목), 지출완료일(필수아님) 필요
2) 지출예정일/지출완료일은 보안등급 1등급만 입력가능
3) 현재 프로젝트별 목록이 있는 상태에서
추가적으로 각지출업체별 목록페이지가 있어서
프로젝트귀속지출
비프로젝트지출
2가지 분류로 등록/수정 할 수 있도록한다.
4) 목록에서 지출예정일5일전 빨간색 표시


비프로젝트 지출은 반드시 따로 관리해야 하지 않을까?

기타수입 수입예정일/수입완료일




SELECT
		SQL_CALC_FOUND_ROWS * ,
		com.com_idx AS com_idx ,
		(SELECT prp_pay_date
				FROM g5_1_project_price
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date ,
		(SELECT prp_price
				FROM g5_1_project_price
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price ,
		(SELECT mb_hp
				FROM g5_member
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp ,
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name
	FROM g5_1_project AS prj
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx
	WHERE prj_status = 'ok' AND prj_idx IN (171,167) ORDER BY prj_idx DESC LIMIT 0, 25
##############################################################################################################
SELECT
		prj.prj_idx,
		prj.prj_name,
		(SELECT prp_pay_date
				FROM g5_1_project_price
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date ,
		(SELECT prp_price
				FROM g5_1_project_price
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price ,
		(SELECT mb_hp
				FROM g5_member
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp ,
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name
	FROM g5_1_project AS prj
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx
	WHERE prj_status = 'ok'
		AND prj_idx IN (171,167)
		AND
			(SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
				  - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0)))
				  AS misu FROM g5_1_project_price AS prp WHERE prp.prj_idx = prj.prj_idx) > 0

	ORDER BY prj_idx DESC LIMIT 0, 25
##############################################################################################################
SELECT
		prj.prj_idx,
		prj.prj_name,
		(SELECT prp_pay_date
				FROM g5_1_project_price
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date ,
		(SELECT prp_price
				FROM g5_1_project_price
			WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price ,
		(SELECT mb_hp
				FROM g5_member
			WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp ,
		(SELECT mb_name FROM g5_member WHERE mb_id = prj.mb_id_account ) AS prj_mb_name
	FROM g5_1_project AS prj
		LEFT JOIN g5_1_company AS com ON com.com_idx = prj.com_idx
	WHERE prj_status = 'ok'
		AND prj_idx IN (194,193)
		AND
			((SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
				  - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0)))
				  FROM g5_1_project_price AS prp WHERE prp.prj_idx = prj.prj_idx) > 0)

	ORDER BY prj_idx DESC LIMIT 0, 25
