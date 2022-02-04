<?php
include_once('./_common.php');

define("_INDEX_", TRUE);

include_once(G5_THEME_MSHOP_PATH.'/shop.head.php');

add_stylesheet('<link type="text/css" href="'.G5_JS_URL.'/swiper/swiper.min.css" rel="stylesheet" />', 0);
?>
<style>
</style>
<script src="<?php echo G5_JS_URL; ?>/swipe.js"></script>
<script src="<?php echo G5_JS_URL; ?>/swiper/swiper.min.js"></script>

<?php
// 로그인전(비회원)인 경우
if(!$member['mb_id']) {
?>
<div>
    <?php echo display_banner2('왼쪽', 'boxbanner.skin.php'); ?>
</div>
<?php
}
// 로그인후 (회원인 경우 마이페이지처럼 보여준다.)
else {
?>

<div>
    <?php echo display_banner2('왼쪽', 'boxbanner.skin.php'); ?>
</div>
<div class="div_main02">

</div>


<script>
$(window).load(function(){
	document.location.href="ierp://update-push-key?id=<?=$member['mb_id']?>";
});
</script>
<?php
} // end of member
?>

<script>
    $("#container").addClass("idx-container");
</script>

<?php
include_once(G5_THEME_MSHOP_PATH.'/shop.tail.php');
?>