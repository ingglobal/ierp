<?php
$sub_menu = "960900";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

if(!$config['cf_faq_skin']) $config['cf_faq_skin'] = "basic";
if(!$config['cf_mobile_faq_skin']) $config['cf_mobile_faq_skin'] = "basic";

$g5['title'] = '환경설정 관리';
// include_once('./_top_menu_setting.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_item">판매상품관리 설정</a></li>
</ul>';
?>
<div class="local_desc01 local_desc">
    <p style="color:darkorange;font-weight:bold;">이 페이지는 정말 중요한 페이지 입니다.</p>
    <p style="color:red;font-weight:bold;">기존에 설정된 내용을 함부로 변경하면 시스템에 큰 장애를 일으킬 수 있습니다</p>
    <p style="color:red;font-weight:bold;">기존값을 변경하거나 삭제하지 말고, 기존내용의 형태를 유지하며 값을 추가하는 방향으로 수정해 주세요</p>
    <p style="color:red;font-weight:bold;font-size:1.2em;">제발! 제발! 제발! 신중하게 수정작업을 진행해 주세요.</p>
</div>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">

<section id="anc_cf_item">
	<h2 class="h2_frm">판매상품관리 설정</h2>
	<?php echo $pg_anchor ?>

	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>기본설정</caption>
		<colgroup>
			<col class="grid_4" style="width:15%;">
			<col style="width:35%;">
			<col class="grid_4" style="width:15%;">
			<col style="width:35%;">
		</colgroup>
		<tbody>
		<tr>
            <th scope="row">판매업체등급</th>
            <td colspan="3">
                <?php echo help('1=최종고객,2=SI기업,3=SI우수,4=SI최우수,5=대리점기본,6=대리점우수,7=대리점최우수'); ?>
                <input type="text" name="set_com_level" value="<?php echo $g5['setting']['set_com_level']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">판매업체등급별할인률</th>
            <td colspan="3">
                <?php echo help('1=0,2=3,3=5,4=8,5=10,6=12,7=15'); ?>
                <input type="text" name="set_com_dc_rate" value="<?php echo $g5['setting']['set_com_dc_rate']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		</tbody>
		</table>
	</div>
</section>


<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){

});

function fconfigform_submit(f) {

    <?php echo get_editor_js("set_expire_email_content"); ?>
    <?php echo chk_editor_js("set_expire_email_content"); ?>
    <?php echo get_editor_js("set_maintain_plan_content"); ?>
    <?php echo chk_editor_js("set_maintain_plan_content"); ?>
    <?php echo get_editor_js("set_error_content"); ?>
    <?php echo chk_editor_js("set_error_content"); ?>

    f.action = "./mng_config_form_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
