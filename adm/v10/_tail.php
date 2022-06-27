<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

?>
<?php if(G5_IS_MOBILE){ ?>
<button type="button" class="goto_back" onclick="window.history.back();"><img src="https://icongr.am/clarity/undo.svg?size=20&color=333333"><div>BACK</div></button>
<?php if($board['gr_id']=='intra'){ ?>
<script>
if($('#bo_cate').length > 0){
/*
var swiper = new Swiper('#bo_cate', {
    slidesPerView: 'auto',
    centeredSlides: true,
    spaceBetween: 0,
});
*/


}
</script>
<?php } ?>
<?php } ?>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>