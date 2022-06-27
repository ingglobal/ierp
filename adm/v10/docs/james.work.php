CREATE TABLE `g5_1_company_rate` (
  `cra_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '업체견적비율idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `cra_percent` bigint(20) NOT NULL DEFAULT '0' COMMENT '견적비율',
  `cra_start_date` date DEFAULT '0000-00-00' COMMENT '적용일자',
  `cra_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  PRIMARY KEY (`cra_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_company_item` (
  `cit_idx` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '입고가격idx',
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `it_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '부품아이디',
  `cit_price` bigint(20) NOT NULL DEFAULT '0' COMMENT '입고가격',
  `cit_start_date` date DEFAULT '0000-00-00' COMMENT '적용일자',
  `cit_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  PRIMARY KEY (`cit_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_project` (
  `prj_idx` bigint(20) NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(20) DEFAULT '',
  `mms_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT 'MMS번호',
  `prj_type` varchar(20) DEFAULT 'pending' COMMENT 'list,graph',
  `prj_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  PRIMARY KEY (`prj_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_project` (
  `prj_idx` bigint(20) NOT NULL AUTO_INCREMENT,
  `com_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '업체번호',
  `mb_id_company` varchar(20) DEFAULT '',
  `mb_id_saler` varchar(20) DEFAULT '',
  `mb_id_account` varchar(20) DEFAULT '',
  `prj_doc_no` varchar(30) DEFAULT '',
  `prj_name` varchar(255) DEFAULT '',
  `prj_content` text DEFAULT NULL,
  `prj_belongto` varchar(10) DEFAULT '',
  `prj_price` int(11) NOT NULL COMMENT '수주금액',
  `prj_receivable` int(11) NOT NULL COMMENT '미수금',
  `prj_percent` int(11) NOT NULL DEFAULT '0',
  `prj_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `prj_ask_date` date DEFAULT '0000-00-00' COMMENT '요청날짜',
  `prj_submit_date` date DEFAULT '0000-00-00' COMMENT '제출날짜',
  `prj_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `prj_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`prj_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_project_price` (
  `prp_idx` bigint(20) NOT NULL AUTO_INCREMENT,
  `prj_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '프로젝트번호',
  `prp_type` varchar(20) DEFAULT '',
  `prp_pay_no` int(11) NOT NULL COMMENT '결제차수',
  `prp_content` text DEFAULT NULL COMMENT '지시내용',
  `prp_content2` text DEFAULT NULL COMMENT '미수내용',
  `prp_doc_deal` varchar(20) DEFAULT '' COMMENT '거래명세표',
  `prp_plan_date` date DEFAULT '0000-00-00' COMMENT '발행예정일',
  `prp_issue_date` date DEFAULT '0000-00-00' COMMENT '계산서발행일',
  `prp_pay_date` date DEFAULT '0000-00-00' COMMENT '수금완료일',
  `prp_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `prp_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `prp_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`prp_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `g5_1_project_schedule` (
  `prs_idx` bigint(20) NOT NULL AUTO_INCREMENT,
  `prj_idx` bigint(20) NOT NULL DEFAULT '0' COMMENT '프로젝트번호',
  `mb_id_worker` varchar(20) DEFAULT '',
  `prs_type` varchar(20) DEFAULT '',
  `prs_task` varchar(255) DEFAULT '' COMMENT '주요업무',
  `prs_content` text DEFAULT NULL COMMENT '지시내용',
  `prs_graph_color` varchar(20) DEFAULT '',
  `prs_graph_type` varchar(20) DEFAULT '',
  `prs_graph_thickness` varchar(20) DEFAULT '',
  `prs_start_date` date DEFAULT '0000-00-00',
  `prs_end_date` date DEFAULT '0000-00-00',
  `prs_status` varchar(20) DEFAULT 'pending' COMMENT '상태',
  `prs_reg_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '등록일시',
  `prs_update_dt` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '수정일시',
  PRIMARY KEY (`prs_idx`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


// 1차 분류 카테고리 추출 쿼리
select MAX(SUBSTRING(ca_id,1,2)) as max_subid from g5_shop_category where SUBSTRING(ca_id,1,0) = ''


// 2차 카테고리 추출 쿼리
select MAX(SUBSTRING(ca_id,3,2)) as max_subid from g5_shop_category where SUBSTRING(ca_id,1,2) = '10'

// 3차 카테고리 추출 쿼리
select MAX(SUBSTRING(ca_id,5,2)) as max_subid from g5_shop_category where SUBSTRING(ca_id,1,4) = '1010'


Warning: trim() expects parameter 1 to be string, array given in /Library/WebServer/Documents/ingglobal/erp/adm/v10/itemlist_excel_upload2.php on line 12

TRUNCATE g5_shop_item;
TRUNCATE g5_shop_category;


update g5_shop_item SET it_update_time = '2020-09-20 20:58:59' , ca_id = '0202', it_name = TRIM('Q312B'), it_cust_price = TRIM('231952'), it_price = TRIM('231952'), it_use = '1', it_stock_qty = '9999999', it_order = '0' where it_id = '1599210000'
update g5_shop_item SET it_update_time = '2020-09-20 20:58:59' , ca_id = '0202', it_name = TRIM('Q312B'), it_cust_price = TRIM('231952'), it_price = TRIM('231952'), it_use = '1', it_stock_qty = '9999999', it_order = '0' where it_id = '1599210001'
update g5_shop_item SET it_update_time = '2020-09-20 20:58:59' , ca_id = '0202', it_name = TRIM('Q312B'), it_cust_price = TRIM('231952'), it_price = TRIM('231952'), it_use = '1', it_stock_qty = '9999999', it_order = '0' where it_id = '1599210002'


// 푸시키값을 받을 수 있도록 해 두었습니다
URL: http://erp.ingglobal.net/app/pushkey.php
변수2개: 아이디(id), 푸시키(pushkey)
샘플URL: http://erp.ingglobal.net/app/pushkey.php?id=test01&pushkey=abcd12345

확인하려면 수퍼관리자로 로그인해서 확인하면 됩니다
로그인: http://erp.ingglobal.net/
수퍼관리자계정 = super/super@ingglobal
페이지위치: 대시보드 > 사원관리
'푸시키'라는 항목이 보이도록 해 두었습니다.

// 통계관리
// 영업통계 메인에서는 업체별 관심통계, 상담기록 건수
// 영업내부에서는 개인별 영업기록 필요합니다. 그렇다면 누가 영업자인지를 알수 있는 필드값이 하나 필요하겠네요.

// 업체 상태별 통계
SELECT wr_5, COUNT(wr_id) AS count_total
FROM g5_write_sales
WHERE wr_is_comment = 0
GROUP BY wr_5


SELECT (CASE WHEN n='1' THEN wr_5 ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT 
        wr_5
        , SUM(count_total) AS count_total
    FROM
    (
        SELECT wr_5
            , COUNT(wr_id) AS count_total
        FROM g5_write_sales
        WHERE wr_is_comment = 0
        GROUP BY wr_5
    ) AS db_table
    GROUP BY wr_5
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name





// 날짜별 상담기록 건수 통계
SELECT SUBSTRING(wr_datetime,1,10) AS wr_date, COUNT(wr_id) AS cnt
FROM g5_write_sales
WHERE wr_is_comment = 1
  AND wr_datetime >= '2020-11-01 00:00:00'
  AND wr_datetime <= '2020-11-31 23:59:59'
GROUP BY wr_date

// 날짜별 상담 건수
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT 
        ymd_date
        , SUM(count_total) AS count_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS count_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '2020-11-01' AND '2020-11-09'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            SUBSTRING(wr_datetime,1,10) AS ymd_date
            , COUNT(wr_id) AS count_total
        FROM g5_write_sales
        WHERE wr_is_comment = 1
          AND wr_datetime >= '2020-11-01 00:00:00'
          AND wr_datetime <= '2020-11-31 23:59:59'
        GROUP BY ymd_date
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 견적 통계 상태 통계
SELECT (CASE WHEN n='1' THEN prj_status ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT 
        prj_status
        , SUM(count_total) AS count_total
    FROM
    (
        SELECT prj_status
            , COUNT(prj_idx) AS count_total
        FROM g5_1_project
        WHERE prj_status NOT IN ('trash','delete')
        GROUP BY prj_status
    ) AS db_table
    GROUP BY prj_status
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name



// 이달의 수주금액 통계
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_date
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS price_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '2020-11-01' AND '2020-11-31'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            SUBSTRING(prj_submit_date,1,10) AS ymd_date
            , SUM(prp_price) AS price_total
        FROM g5_1_project AS prj
            LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
        WHERE prj_status NOT IN ('trash','delete')
            AND prp_type IN ('submit')
            AND prj_submit_date != '0000-00-00'
            AND prj_submit_date >= '2020-11-01 00:00:00'
            AND prj_submit_date <= '2020-11-31 23:59:59'
        GROUP BY ymd_date            
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 제출금액만 
SELECT
    SUBSTRING(prj_submit_date,1,10) AS ymd_date
    , SUM(prp_price) AS price_total
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
WHERE prj_status NOT IN ('trash','delete')
    AND prp_type IN ('submit')
    AND prj_submit_date != '0000-00-00'
    AND prj_submit_date >= '2020-11-01 00:00:00'
    AND prj_submit_date <= '2020-11-31 23:59:59'
GROUP BY ymd_date

SELECT
    *
FROM g5_1_project AS prj
WHERE prj_status NOT IN ('trash','delete')
    AND prj_submit_date != '0000-00-00'
    AND prj_submit_date >= '2020-11-01 00:00:00'
    AND prj_submit_date <= '2020-11-31 23:59:59'

SELECT
    *
FROM g5_1_project_price AS prp
WHERE prp_status NOT IN ('trash','delete')
    AND prp_type IN ('submit')
    AND prj_idx IN (25, 29, 50)


// 분기별 A/S 접수 건수
SELECT (CASE WHEN n='1' THEN ymd_quarter ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT
        QUARTER(wr_datetime) AS ymd_quarter
        , COUNT(wr_id) AS count_total
    FROM g5_write_as
    WHERE wr_is_comment = 0
        AND wr_datetime >= '2020-01-01 00:00:00'
        AND wr_datetime <= '2020-12-31 23:59:59'
    GROUP BY ymd_quarter
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 분기를 어떻게 뽑을 것인가? 일자별로 하면 아래처럼
SELECT
    SUBSTRING(wr_datetime,1,10) AS ymd_date
    , COUNT(wr_id) AS count_total
FROM g5_write_as
WHERE wr_is_comment = 0
    AND wr_datetime >= '2020-01-01 00:00:00'
    AND wr_datetime <= '2020-12-31 23:59:59'
GROUP BY ymd_date

// 월별로 하려면
SELECT
    SUBSTRING(wr_datetime,1,7) AS ymd_ym
    , COUNT(wr_id) AS count_total
FROM g5_write_as
WHERE wr_is_comment = 0
    AND wr_datetime >= '2020-01-01 00:00:00'
    AND wr_datetime <= '2020-12-31 23:59:59'
GROUP BY ymd_ym

// 분기로 하려면 QUARTER 함수가 있구만.
SELECT
    QUARTER(wr_datetime) AS ymd_quarter
    , COUNT(wr_id) AS count_total
FROM g5_write_as
WHERE wr_is_comment = 0
    AND wr_datetime >= '2020-01-01 00:00:00'
    AND wr_datetime <= '2020-12-31 23:59:59'
GROUP BY ymd_quarter


===========================================================================================
// 일자별 A/S 비용 금액
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_date
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS price_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '2020-11-01' AND '2020-11-31'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            SUBSTRING(wr_8,1,10) AS ymd_date
            , SUM(wr_7) AS price_total
        FROM g5_write_as
        WHERE wr_is_comment = 0
            AND wr_8 >= '2020-11-01 00:00:00'
            AND wr_8 <= '2020-11-31 23:59:59'
        GROUP BY ymd_date
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


SELECT
    SUBSTRING(wr_8,1,10) AS ymd_date
    , SUM(wr_7) AS price_total
FROM g5_write_as
WHERE wr_is_comment = 0
    AND wr_8 >= '2020-11-01 00:00:00'
    AND wr_8 <= '2020-11-31 23:59:59'
GROUP BY ymd_date



// 수입지출 예정일자 비율 (올해 기준)
SELECT 
    'prp_plan_date' AS prp_name
    , COUNT(prp_idx) AS count_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_plan_date != '0000-00-00'
    AND prp_plan_date >= '2020-01-01'
    AND prp_plan_date <= '2020-12-31'

SELECT 
    'prp_issue_date' AS prp_name
    , COUNT(prp_idx) AS count_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_issue_date != '0000-00-00'
    AND prp_issue_date >= '2020-01-01'
    AND prp_issue_date <= '2020-12-31'

SELECT 
    'prp_planpay_date' AS prp_name
    , COUNT(prp_idx) AS count_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_planpay_date != '0000-00-00'
    AND prp_planpay_date >= '2020-01-01'
    AND prp_planpay_date <= '2020-12-31'

SELECT 
    'prp_pay_date' AS prp_name
    , COUNT(prp_idx) AS count_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_pay_date != '0000-00-00'
    AND prp_pay_date >= '2020-01-01'
    AND prp_pay_date <= '2020-12-31'


SELECT *
FROM (
        (
        SELECT 
            'prp_plan_date' AS prp_name
            , COUNT(prp_idx) AS count_total
        FROM g5_1_project_price
        WHERE prp_status NOT IN ('trash','delete')
            AND prp_plan_date != '0000-00-00'
            AND prp_plan_date >= '2020-01-01'
            AND prp_plan_date <= '2020-12-31'
        )
        UNION ALL
        (
        SELECT 
            'prp_issue_date' AS prp_name
            , COUNT(prp_idx) AS count_total
        FROM g5_1_project_price
        WHERE prp_status NOT IN ('trash','delete')
            AND prp_issue_date != '0000-00-00'
            AND prp_issue_date >= '2020-01-01'
            AND prp_issue_date <= '2020-12-31'
        )
        UNION ALL
        (
        SELECT 
            'prp_planpay_date' AS prp_name
            , COUNT(prp_idx) AS count_total
        FROM g5_1_project_price
        WHERE prp_status NOT IN ('trash','delete')
            AND prp_planpay_date != '0000-00-00'
            AND prp_planpay_date >= '2020-01-01'
            AND prp_planpay_date <= '2020-12-31'
        )
        UNION ALL
        (
        SELECT 
            'prp_pay_date' AS prp_name
            , COUNT(prp_idx) AS count_total
        FROM g5_1_project_price
        WHERE prp_status NOT IN ('trash','delete')
            AND prp_pay_date != '0000-00-00'
            AND prp_pay_date >= '2020-01-01'
            AND prp_pay_date <= '2020-12-31'
        )
    ) AS db_table

// 수입지출 날짜 명칭별 비율분포
SELECT (CASE WHEN n='1' THEN prp_name ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT *
    FROM (
            (
            SELECT 
                'prp_plan_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_plan_date != '0000-00-00'
                AND prp_plan_date >= '2020-01-01'
                AND prp_plan_date <= '2020-12-31'
            )
            UNION ALL
            (
            SELECT 
                'prp_issue_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_issue_date != '0000-00-00'
                AND prp_issue_date >= '2020-01-01'
                AND prp_issue_date <= '2020-12-31'
            )
            UNION ALL
            (
            SELECT 
                'prp_planpay_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_planpay_date != '0000-00-00'
                AND prp_planpay_date >= '2020-01-01'
                AND prp_planpay_date <= '2020-12-31'
            )
            UNION ALL
            (
            SELECT 
                'prp_pay_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_pay_date != '0000-00-00'
                AND prp_pay_date >= '2020-01-01'
                AND prp_pay_date <= '2020-12-31'
            )
    ) AS db_table
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 수입지출 분기별
SELECT
    QUARTER(prj_contract_date) AS ymd_quarter
    , SUM(prp_price) AS price_total
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
WHERE prj_status NOT IN ('trash','delete')
    AND prp_type IN ('order')
    AND prj_contract_date != '0000-00-00'
    AND prj_contract_date >= '2020-01-01'
    AND prj_contract_date <= '2020-12-31'
GROUP BY ymd_quarter



// 수입지출 분기별 최종
SELECT (CASE WHEN n='1' THEN ymd_quarter ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_quarter
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            n AS ymd_quarter
            , 0 AS price_total
        FROM g5_5_tally AS ymd
        WHERE n < 5
        ORDER BY ymd_quarter
        )
        UNION ALL
        (
        SELECT
            QUARTER(prj_contract_date) AS ymd_quarter
            , SUM(prp_price) AS price_total
        FROM g5_1_project AS prj
            LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
        WHERE prj_status NOT IN ('trash','delete')
            AND prp_type IN ('order')
            AND prj_contract_date != '0000-00-00'
            AND prj_contract_date >= '2020-01-01'
            AND prj_contract_date <= '2020-12-31'
        GROUP BY ymd_quarter
        )
    ) AS db_table
    GROUP BY ymd_quarter
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 수입지출 일자별
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_date
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS price_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '2020-11-01' AND '2020-11-31'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            prj_contract_date AS ymd_date
            , SUM(prp_price) AS price_total
        FROM g5_1_project AS prj
            LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
        WHERE prj_status NOT IN ('trash','delete')
            AND prp_type IN ('order')
            AND prj_contract_date != '0000-00-00'
            AND prj_contract_date >= '2020-11-01'
            AND prj_contract_date <= '2020-11-31'
        GROUP BY ymd_date
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 견적통계 상태별 분포
SELECT (CASE WHEN n='1' THEN prj_status ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT 
        prj_status
        , SUM(count_total) AS count_total
    FROM
    (
        SELECT prj_status
            , COUNT(prj_idx) AS count_total
        FROM g5_1_project
        WHERE prj_status IN ('inprocess','pending','ng','ok')
        GROUP BY prj_status
    ) AS db_table
    GROUP BY prj_status
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 견적통계 분기별
SELECT (CASE WHEN n='1' THEN ymd_quarter ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_quarter
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            n AS ymd_quarter
            , 0 AS price_total
        FROM g5_5_tally AS ymd
        WHERE n < 5
        ORDER BY ymd_quarter
        )
        UNION ALL
        (
        SELECT
            QUARTER(prj_contract_date) AS ymd_quarter
            , SUM(prp_price) AS price_total
        FROM g5_1_project AS prj
            LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
        WHERE prj_status IN ('inprocess','pending','ng','ok')
            AND prp_type IN ('order')
            AND prj_contract_date != '0000-00-00'
            AND prj_contract_date >= '2020-01-01'
            AND prj_contract_date <= '2020-12-31'
        GROUP BY ymd_quarter
        )
    ) AS db_table
    GROUP BY ymd_quarter
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 견적통계 일자별
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_date
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS price_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '2020-11-01' AND '2020-11-31'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            prj_contract_date AS ymd_date
            , SUM(prp_price) AS price_total
        FROM g5_1_project AS prj
            LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
        WHERE prj_status NOT IN ('trash','delete')
            AND prp_type IN ('order')
            AND prj_contract_date != '0000-00-00'
            AND prj_contract_date >= '2020-11-01'
            AND prj_contract_date <= '2020-11-31'
        GROUP BY ymd_date
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 가격 정렬 순서 조정 (매입이 제일 뒤로)
SELECT prj_idx, prp_type, prp_price FROM g5_1_project_price
WHERE prj_idx = '44'
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_status NOT IN ('trash','delete') ORDER BY prp_type, prp_reg_dt


SELECT prj_idx, prp_type, prp_price 
    , IF( prp_type IN ('manday','buy','etc'), 1, 0 ) AS prp_sort
FROM g5_1_project_price
WHERE prj_idx = '44'
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_status NOT IN ('trash','delete')
ORDER BY prp_sort, prp_type, prp_reg_dt

SELECT prj_idx, prp_type, prp_price 
    , IF( prp_type = 'manday', 1, 0 ) AS prp_sort
FROM g5_1_project_price
WHERE prj_idx = '44'
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_status NOT IN ('trash','delete') ORDER BY prp_type, prp_reg_dt


// 기준 날짜 이후에도 100%가 안 된 일정들
SELECT prs.prj_idx, prj_name, prj_percent, prs_end_date, prj_status, prs_status, mb_id, mb_name, mb_hp
FROM g5_1_project_schedule AS prs
    LEFT JOIN g5_1_project AS prj ON prj.prj_idx = prs.prj_idx
    LEFT JOIN g5_member AS mb ON mb.mb_id = prs.mb_id_worker
WHERE prs_status NOT IN ('cancel','trash')
    AND prj_status NOT IN ('trash','delete')
    AND prs_end_date < '2020-11-24'
    AND prj_percent < 100


// 영업상태별 분포
SELECT (CASE WHEN n='1' THEN wr_10 ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT 
        wr_10
        , SUM(count_total) AS count_total
    FROM
    (
        SELECT wr_10
            , COUNT(wr_id) AS count_total
        FROM g5_write_sales
        WHERE wr_is_comment = 0
            AND wr_10 != ''
        GROUP BY wr_10
    ) AS db_table
    GROUP BY wr_10
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 영업 관심등급별 분포

// 영업자별 분포

// 월별 수주금액 통계
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_date
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS price_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '2020-11-01' AND '2020-11-31'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            ctr_sales_date AS ymd_date
            , SUM(ctr_price) AS price_total
        FROM g5_1_contract
        WHERE ctr_status IN ('ok')
            AND ctr_sales_date >= '2020-11-01'
            AND ctr_sales_date <= '2020-11-31'
        GROUP BY ymd_date
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 수주금액 매출
SELECT
    ctr_sales_date AS ymd_date
    , SUM(ctr_price) AS price_total
FROM g5_1_contract
WHERE ctr_status IN ('ok')
    AND ctr_sales_date >= '2020-11-01'
    AND ctr_sales_date <= '2020-11-31'
GROUP BY ymd_date



// 매출매입 종류별(착수금, 중도금, 잔금, 인건비, 기타비용등..) 분포
SELECT (CASE WHEN n='1' THEN prp_name ELSE 'total' END) AS item_name
    , SUM(count_total) AS count_total
FROM
(
    SELECT *
    FROM (
            (
            SELECT 
                'prp_plan_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_plan_date != '0000-00-00'
                AND prp_plan_date >= '".$st_date."'
                AND prp_plan_date <= '".$en_date."'
            )
            UNION ALL
            (
            SELECT 
                'prp_issue_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_issue_date != '0000-00-00'
                AND prp_issue_date >= '".$st_date."'
                AND prp_issue_date <= '".$en_date."'
            )
            UNION ALL
            (
            SELECT 
                'prp_planpay_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_planpay_date != '0000-00-00'
                AND prp_planpay_date >= '".$st_date."'
                AND prp_planpay_date <= '".$en_date."'
            )
            UNION ALL
            (
            SELECT 
                'prp_pay_date' AS prp_name
                , COUNT(prp_idx) AS count_total
            FROM g5_1_project_price
            WHERE prp_status NOT IN ('trash','delete')
                AND prp_pay_date != '0000-00-00'
                AND prp_pay_date >= '".$st_date."'
                AND prp_pay_date <= '".$en_date."'
            )
    ) AS db_table
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


// 매출매입 종류별(착수금, 중도금, 잔금, 인건비, 기타비용등..) 분포
SELECT (CASE WHEN n='1' THEN prp_type ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
    , prp_sort
FROM
(
    SELECT 
        prp_type
        , SUM(price_total) AS price_total
        , prp_sort
    FROM
    (
        SELECT prp_type
            , SUM(prp_price) AS price_total
            , IF( prp_type IN ('manday','buy','etc'), 2, 1 ) AS prp_sort
        FROM g5_1_project_price
        WHERE prp_status NOT IN ('trash','delete')
            AND prp_type NOT IN ('submit','nego','order')
        GROUP BY prp_sort, prp_type
        ORDER BY prp_sort
    ) AS db_table
    GROUP BY prp_type
    ORDER BY prp_sort
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, prp_sort, item_name



// 분기별 매출, 매입
SELECT
    QUARTER(prj_contract_date) AS ymd_quarter
    , SUM(prp_price) AS price_total
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx 
WHERE prp_type NOT IN ('trash','delete')
    AND prp_type IN ('order')
    AND prj_contract_date != '0000-00-00'
    AND prj_contract_date >= '2020-01-01'
    AND prj_contract_date <= '2020-12-31'
GROUP BY ymd_quarter


SELECT
    QUARTER(prp_pay_date) AS ymd_quarter
    , SUM(prp_price) AS price_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_pay_date >= '2020-01-01'
    AND prp_pay_date <= '2020-12-31'
GROUP BY ymd_quarter

// 매입 반영해서 음수로 합계하면
SELECT
    QUARTER(prp_pay_date) AS ymd_quarter
    , SUM( IF( prp_type IN ('manday','buy','etc'), prp_price*-1, prp_price ) ) AS price_total
    , SUM( IF( prp_type IN ('manday','buy','etc'), prp_price, 0 ) ) AS price_purchase_total
    , SUM( IF( prp_type IN ('manday','buy','etc'), 0, prp_price ) ) AS price_sales_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_pay_date >= '2020-01-01'
    AND prp_pay_date <= '2020-12-31'
GROUP BY ymd_quarter



// 월간 매입, 매출
SELECT (CASE WHEN n='1' THEN ymd_date ELSE 'total' END) AS item_name
    , SUM(price_total) AS price_total
FROM
(
    SELECT 
        ymd_date
        , SUM(price_total) AS price_total
    FROM
    (
        (
        SELECT 
            CAST(ymd_date AS CHAR) AS ymd_date
            , 0 AS price_total
        FROM g5_5_ymd AS ymd
        WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
        ORDER BY ymd_date
        )
        UNION ALL
        (
        SELECT
            prp_pay_date AS ymd_date
            , SUM(prp_price) AS price_total
        FROM g5_1_project_price
        WHERE prp_status NOT IN ('trash','delete')
            AND prp_type NOT IN ('submit','nego','order')
            AND prp_pay_date >= '2020-11-01'
            AND prp_pay_date <= '2020-11-31'
        GROUP BY ymd_date
        )
    ) AS db_table
    GROUP BY ymd_date
) AS db2, g5_5_tally AS db_no
WHERE n <= 2
GROUP BY item_name
ORDER BY n DESC, item_name


SELECT
    prp_pay_date AS ymd_date
    , SUM(prp_price) AS price_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_pay_date >= '2020-11-01'
    AND prp_pay_date <= '2020-11-31'
GROUP BY ymd_date


SELECT
    prp_pay_date AS ymd_date
    , SUM( IF( prp_type IN ('manday','buy','etc'), prp_price*-1, prp_price ) ) AS price_total
    , SUM( IF( prp_type IN ('manday','buy','etc'), prp_price, 0 ) ) AS price_purchase_total
    , SUM( IF( prp_type IN ('manday','buy','etc'), 0, prp_price ) ) AS price_sales_total
FROM g5_1_project_price
WHERE prp_status NOT IN ('trash','delete')
    AND prp_type NOT IN ('submit','nego','order')
    AND prp_pay_date >= '2020-11-01'
    AND prp_pay_date <= '2020-11-31'
GROUP BY ymd_date


SELECT * FROM g5_1_project2 AS prj, g5_1_project_price2 AS prp

SELECT prj.prj_idx, prp.prp_idx, prp.prp_type FROM g5_1_project2 AS prj, g5_1_project_price2 AS prp
WHERE prp_type IN ('deposit','middle','remainder','all','manday','buy','etc','')

SELECT * FROM (
    SELECT * FROM g5_1_project
) AS db1

g5_1_project2 AS prj, g5_1_project_price2 AS prp



SELECT 
    prj_idx, prj_name, prj_order_price
    , GROUP_CONCAT(CONCAT(prp_type,prp_price,prp_pay_date)) AS pay_info
FROM
(
    (
    SELECT 
        prj_idx, prj_name, prj_order_price
        , '' AS prp_type, 0 AS prp_price , '' AS prp_pay_date
    FROM g5_1_project2
    )
    UNION ALL
    (
    SELECT prj.prj_idx, prj_name, prj_order_price
        , prp.prp_type, prp_price, prp_pay_date
    FROM g5_1_project_price2 AS prp
        LEFT JOIN g5_1_project2 AS prj ON prj.prj_idx = prp.prj_idx
    WHERE prp_type IN ('deposit','middle','remainder','all','manday','buy','etc','')
    )
) AS db_table
GROUP BY prj_idx



SELECT 
    prj_idx, prj_name, prj_order_price
    , GROUP_CONCAT(CONCAT(prp_type,prp_price,prp_pay_date)) AS pay_info
FROM
(
    (
    SELECT 
        prj_idx, prj_name, prj_order_price
        , '' AS prp_type, 0 AS prp_price , '' AS prp_pay_date
    FROM g5_1_project
    )
    UNION ALL
    (
    SELECT prj.prj_idx, prj_name, prj_order_price
        , prp.prp_type, prp_price, prp_pay_date
    FROM g5_1_project_price AS prp
        LEFT JOIN g5_1_project AS prj ON prj.prj_idx = prp.prj_idx
    WHERE prp_type IN ('deposit','middle','remainder','all','manday','buy','etc','')
    )
) AS db_table
GROUP BY prj_idx



SELECT prj.prj_idx, prj_order_price, prp_idx, prp_price, prp_type
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,164, 165)
    AND prp.prj_idx IN (137,164, 165)
    AND prp_type NOT IN ('order','submit','nego','')
.

// 전체 범위 추출
// 일단 범위 안에 있는 것들은 조건을 너무 많이 달지 말고 다 가지고 와야 한다.
SELECT prj.prj_idx, prj_name, prj_order_price, prp_idx, prp_price, prp_type
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,164, 165)
.

// 이제 조건에 맞는 것들 select 절에서 추출하자.
SELECT prj.prj_idx, prj_name, prj_order_price, prp_idx, prp_price, prp_type
    , GROUP_CONCAT(prp_idx) AS prps
    , GROUP_CONCAT(prp_type) AS prp_types
    , GROUP_CONCAT(prp_price) AS prp_prices
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,164, 165)
GROUP BY prj_idx
ORDER BY prj_idx
.


// 이제 조건에 맞는 것들 select 절에서 추출하자.
SELECT prj.prj_idx, prj_order_price, prp_idx, prp_price
    , GROUP_CONCAT(prp_type) AS prp_types
    , GROUP_CONCAT(prp_price) AS prp_prices
    , SUM(IF(prp_type NOT IN ('order','submit','nego',''), prp_price, 0)) AS prp_paid
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,164, 165)
GROUP BY prj_idx
ORDER BY prj_idx
.


SELECT prj.prj_idx, prj_order_price, prp_idx, prp_price
    , GROUP_CONCAT(prp_type) AS prp_types
    , GROUP_CONCAT(prp_price) AS prp_prices
    , SUM(IF(prp_type NOT IN ('order','submit','nego',''), prp_price, 0)) AS prp_paid
    , SUM(IF(prp_type NOT IN ('order','submit','nego','') AND prp_issue_date = '0000-00-00', prp_price, 0)) AS prp_paid
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,164, 165)
GROUP BY prj_idx
ORDER BY prj_idx
.


SELECT prj.prj_idx, prj_name, prj_order_price, prp_idx, prp_price, prp_type
, GROUP_CONCAT(prp_idx) AS prp_idxs
, GROUP_CONCAT(prp_type) AS prp_types
, GROUP_CONCAT(prp_price) AS prp_prices
, SUM(IF(prp_type NOT IN ('submit','nego','order',''),prp_price,0)) AS prp_prices2
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_planpay_date = '0000-00-00',prp_price,0)) AS misu
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,164, 165)
GROUP BY prj_idx


SELECT prj.prj_idx, prj_name, prj_order_price
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_planpay_date = '0000-00-00',prp_price,0)) AS misu1
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0)) AS misu2
,(prj_order_price - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0))) AS misu3
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,163, 165)
GROUP BY prj_idx


SELECT prj.prj_idx, prj_name, prj_order_price
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_planpay_date = '0000-00-00',prp_price,0)) AS misu1
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0)) AS misu2
,(prj_order_price - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0))) AS misu3
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,163, 165)
    AND prp_planpay_date != '0000-00-00'
    AND prp_planpay_date >= '2020-02-01'
    AND prp_planpay_date <= '2021-02-01'
GROUP BY prj_idx





SELECT prj.prj_idx, prj_name, prj_order_price
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_planpay_date = '0000-00-00',prp_price,0)) AS misu1
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0)) AS misu2
,(prj_order_price - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0))) AS misu3
,SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_planpay_date != '0000-00-00'
    AND prp_planpay_date >= '2020-02-01'
    AND prp_planpay_date <= '2020-06-01' AND prp_status = 'pending',prp_price,0)) AS misu4
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
    AND prj.prj_idx IN (137,163, 165)
    AND prp_planpay_date != '0000-00-00'
    AND prp_planpay_date >= '2020-02-01'
    AND prp_planpay_date <= '2020-06-01' AND prp_status = 'pending'
GROUP BY prj_idx



SELECT 
    prj_idx, prj_name, prj_order_price
    , GROUP_CONCAT(CONCAT(prp_type,prp_price,prp_pay_date)) AS pay_info
    ,SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_planpay_date != '0000-00-00'
    AND prp_planpay_date >= '2020-02-01'
    AND prp_planpay_date <= '2020-06-01' AND prp_status = 'pending',prp_price,0)) AS misu4
FROM
(
    (
    SELECT 
        prj_idx, prj_name, prj_order_price
        , '' AS prp_type, 0 AS prp_price, '' AS prp_planpay_date , '' AS prp_pay_date, '' AS prp_status
    FROM g5_1_project
    WHERE prj_idx IN ('165','163','139','137')
    )
    UNION ALL
    (
    SELECT prj.prj_idx, prj.prj_name, prj_order_price
        , prp.prp_type, prp_price, prp_planpay_date, prp_pay_date
        , prp_status
    FROM g5_1_project_price AS prp
        LEFT JOIN g5_1_project AS prj ON prj.prj_idx = prp.prj_idx
    WHERE prp_type NOT IN ('submit','nego','order','')
    AND prj.prj_idx IN ('165','163','139','137')
    )
) AS db_table
GROUP BY prj_idx

// 3month before , 2020-10-31 ~ 2020-02-01
SELECT 
    prj_idx, prj_order_price
    ,SUM(IF(prp_type NOT IN ('submit','nego','order','') 
    AND prp_planpay_date != '0000-00-00'
    AND prp_planpay_date >= '2020-02-01'
    AND prp_planpay_date <= '2020-10-31' AND prp_status = 'pending',prp_price,0)) AS misu4
FROM
(
    (
    SELECT 
        prj_idx, prj_name, prj_order_price
        , '' AS prp_type, 0 AS prp_price, '' AS prp_planpay_date , '' AS prp_pay_date, '' AS prp_status
    FROM g5_1_project AS prj
        LEFT JOIN g5_1_project_price AS prp
    WHERE prj_idx IN ('165','163','139','137')
        
    )
    UNION ALL
    (
    SELECT prj.prj_idx, prj.prj_name, prj_order_price
        , prp.prp_type, prp_price, prp_planpay_date, prp_pay_date
        , prp_status
    FROM g5_1_project_price AS prp
        LEFT JOIN g5_1_project AS prj ON prj.prj_idx = prp.prj_idx
    WHERE prp_type NOT IN ('submit','nego','order','')
        AND prj.prj_idx IN ('165','163','139','137')
        AND prp_planpay_date != '0000-00-00'
        AND prp_planpay_date >= '2020-02-01'
        AND prp_planpay_date <= '2020-10-31' 
        AND prp_status = 'pending'
    )
) AS db_table
GROUP BY prj_idx




SELECT prj.prj_idx, prj_name, prj_order_price
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0)) AS sugum
,(prj_order_price - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0))) AS suju_misu
,SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0)) AS gesan_misu
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
GROUP BY prj_idx
ORDER BY prj_idx DESC


//미수금이 남은 수주금액 합계
SELECT prj.prj_idx, prj_name
, SUM(IF(prp_type = 'order' AND prp_status = 'ok',prp_price,0)) AS prj_order_price
, SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0)) AS sugum
,(SUM(IF(prp_type = 'order' AND prp_status = 'ok',prp_price,0)) - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0))) AS suju_misu
,SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0)) AS gesan_misu
,(SUM((SUM(IF(prp_type = 'order' AND prp_status = 'ok',prp_price,0)) - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0)))) OVER(ORDER BY prj_idx)) AS nu_misu
,(SUM(SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0))) OVER(ORDER BY prj_idx)) AS nu_gesan_misu
FROM g5_1_project AS prj
    LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
WHERE prj_status IN ('ok')
GROUP BY prj_idx
ORDER BY prj_idx DESC LIMIT 1



SELECT 
    prj_idx, prj_name, prj_order_price
    , GROUP_CONCAT(CONCAT(prp_type,prp_price,prp_pay_date)) AS pay_info
FROM
(
    (
    SELECT 
        prj.prj_idx, prj_name, prj_order_price
        , '' AS prp_type, 0 AS prp_price , '' AS prp_pay_date
    FROM g5_1_project AS prj
        LEFT JOIN g5_1_project_price AS prp ON prp.prj_idx = prj.prj_idx
    WHERE prj_status IN ('ok')
        AND prj.prj_idx IN ('165','163','139','137')
    )
    UNION ALL
    (
    SELECT prj.prj_idx, prj.prj_name, prj_order_price
        , prp.prp_type, prp_price, prp_pay_date
    FROM g5_1_project_price AS prp
        LEFT JOIN g5_1_project AS prj ON prj.prj_idx = prp.prj_idx
    WHERE prp_type NOT IN ('submit','nego','order','')
        AND prj.prj_idx IN ('165','163','139','137')
    )
) AS db_table
GROUP BY prj_idx


