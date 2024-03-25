<?php
$sub_menu = "960266";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");
include_once(G5_EDITOR_LIB);

$fname = preg_replace("/_form/","",$g5['file_name']); // _list을 제외한 파일명

$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '발주'.$html_title;
//include_once('./_top_menu_company.php');
include_once('./_head.php');

?>
<style>

</style>
<div class="local_desc01 local_desc" style="display:none;">
    <p>발주상세관리 페이지입니다.</p>
</div>

<?php
include_once ('./_tail.php');