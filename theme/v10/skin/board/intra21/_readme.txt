
디비 추가 필드

wr_dept_writer
> 작성자 조직코드 (영업자조직코드아님(작업자조직코드도 아님), 수정게시판 같은 경우는 작성자 조직코드가 있고 영업자조직코드가 따로 있어야 함)
> skin/list.php 맨 하단에 정의되어 있어요.

wr_1 업체명

wr_5 작업등급(작업난이도)
wr_6 완료예정일
wr_7 영업자조직코드

wr_8 => 검색키로 사용(:ct_id=장바구니아이디:,:mb_id_saler=영업자아이디:,:mb_name_saler=홍길동:,:mb_id_worker=작업자아이디:,:mb_name_worker=작업자명:,...)
wr_9 => more 자료들 serialized 값으로 들어감
> 검색키들 + 추가필드들(ct_id, com_idx, mb_id_saler, mb_name_saler, mb_id_worker, mb_name_worker, trm_idx_department_worker)

wr_10 상태값



// 게시판 설정
bo_1 sub_menu 코드
bo_2 레벨보기 아이디들
bo_3 운영관리 조직 코드들
bo_7 serialized 설정값들
bo_8 작업등급 설정
bo_9 상태값 설정


