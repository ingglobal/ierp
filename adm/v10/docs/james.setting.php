// 설정 페이지 관련
// 네이티브 코어 업데이트 관련
// 정보를 저장하고 관리합니다.

# 네이티브 코어 버전 동기화 파일들입니다.
1. /adm/ajax.token.php
    사용자단에서도 관리자단의 게시판을 쓰기 위해서 불가피하게 수정 필요함
2. 에러 주석 처리 (이건 이제 해결된 듯 하다.)
    Warning: mysqli_connect(): Headers and client library minor version mismatch. Headers:50560 Library:100144 in /home/ingiot/intra/lib/common.lib.php on line 1518
3. /bbs/board_head.php
    관리자단에서 모바일 게시판을 사용하려면 어쩔 수 없는 수정이 필요합니다.
4. /bbs/board_tail.php
    관리자단에서 모바일 게시판을 사용하려면 어쩔 수 없는 수정이 필요합니다.

