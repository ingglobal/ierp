<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/index.php');
    return;
}

define("_INDEX_", TRUE);

include_once(G5_THEME_SHOP_PATH.'/shop.head.php');
?>
<style>
    .main_wrapper {width:100%;padding:80px;}
    .main_wrapper:after {display:block;visibility:hidden;clear:both;content:'';}
    .main_left {width:43%;float:left;}
    .main_right {width:53%;float:right;border:solid 1px #ddd;height:503px;}
    .main01 {text-align:center;font-size:3em;font-weight:700;margin-top:50px;line-height:1.5em;}
    .main01 span{font-size:1.3em;}
    .main01 span b{color:#439e93;}
    .main02 {text-align:center;font-size:1.5em;margin-top:30px;line-height:1.6em;}
    .main02 b{color:#439e93;}
    .main03 {text-align:center;font-size:1.5em;margin-top:30px;padding-bottom:50px;line-height:1.6em;}
</style>

<!-å- 메인이미지 시작 { -->
<?//php echo display_banner('메인', 'mainbanner.10.skin.php'); ?>
<!-- } 메인이미지 끝 -->

<div class="main_wrapper">
    <div class="main_left">
        <!-- 왼편배너 (스킨을 테마에서 불러와야 해서 display_banner2 함수 재정의함) -->
        <?php echo display_banner2('왼쪽', 'boxbanner.skin.php'); ?>
    </div>
    <div class="main_right">
        <div class="main01">
            <span><b>i-ERP</b></span>
            <br>
            스마트한 ERP 시스템
        </div>

        <div class="main02">
            <b>INGGlobal ENTERPRISE RESOURCE PLANNING SYSTEM</b>
            <br>
            영업, 회계, 시스템 및 재고 등 모든 경영활동 통합 관리시스템!
        </div>

        <div class="main03">
            사용자 관점의 편리성, 현장 중심의 실제적인 데이터!
            <br>
            우리는 고객의 본질적 가치에 언제나 집중합니다.
        </div>
    </div>
</div>


<?php
include_once(G5_THEME_SHOP_PATH.'/shop.tail.php');
?>