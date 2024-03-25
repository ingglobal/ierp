<?php
$sub_menu = "960268";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명

$g5['title'] = '개별발주관리';
//include_once('./_top_menu_company.php');
include_once('./_head.php');


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>

</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    $skips = array('prj_set_output','prj_image','trm_idx_category','prj_idx2','prp_submit_price','prp_nego_price','prp_submit_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item','imp_order_file');
    if(is_array($sch_items)) {
        foreach($sch_items as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
	<option value="prj.com_idx"<?php echo get_selected($_GET['sfl'], "prj.com_idx"); ?>>업체번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>발주관리 페이지입니다.</p>
</div>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>

    </thead>
    <tbody>

    </tbody>
    </table>
</div><!--//.tbl_head01.tbl_wrap-->
<div class="btn_fixed_top">
    <?php if(false){ ?>
        <a href="./pri_purchase_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <?php } ?>
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">발주추가</a>
    <?php } ?>
</div>
</form><!--//#form01-->
<?php
include_once ('./_tail.php');