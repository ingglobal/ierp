<?php
// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 2;

if ($is_checkbox) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);


//차량종류( carname )
if($board['bo_2_subj'] && $board['bo_2'] && preg_match("/,/",$board['bo_2']) && preg_match("/=/",$board['bo_2'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_2']));
    $valname = $board['bo_2_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
    }
}

//시간1( time1 )
if($board['bo_3_subj'] && $board['bo_3'] && preg_match("/,/",$board['bo_3']) && preg_match("/=/",$board['bo_3'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_3']));
    $valname = $board['bo_3_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
    }
}
//시간1( time2 )
if($board['bo_4_subj'] && $board['bo_4'] && preg_match("/,/",$board['bo_4']) && preg_match("/=/",$board['bo_4'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_4']));
    $valname = $board['bo_4_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
    }
}
?>

<form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="spt" value="<?php echo $spt ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="sw" value="">

<?php if ($rss_href || $write_href) { ?>
<ul class="<?php echo isset($view) ? 'view_is_list btn_top' : 'btn_top top btn_bo_user';?>">
    <li><a href="<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=<?=$bo_table?>&calendar=1" id="" class="btn_02 btn"><i class="fa fa-calendar" aria-hidden="true"></i><span class="sound_only">달력</span></a></li>
	<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
    <?php if ($is_admin == 'super' || $is_auth) {  ?>
	<li>
		<button type="button" class="btn_more_opt btn_b03 btn is_list_btn" title="게시판 리스트 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
		<?php if ($is_checkbox) { ?>	
        <ul class="more_opt is_list_btn">
            <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
        </ul>
        <?php } ?>
	</li>
    <?php } ?>
	<?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="fix_btn write_btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
</ul>
<?php } ?>
<!-- 게시판 목록 시작 -->
<div id="bo_list">

    <?php if ($is_category) { ?>
    <nav id="bo_cate">
        <h2><?php echo ($board['bo_mobile_subject'] ? $board['bo_mobile_subject'] : $board['bo_subject']) ?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?php echo $category_option ?>
        </ul>
    </nav>
    <?php } ?>

    <div class="list_01">
        <?php if ($is_checkbox) { ?>
        <div class="all_chk chk_box">
            <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
            <label for="chkall">
            	<span></span>
            	<b class="sound_only">현재 페이지 게시물 </b> 전체선택
            </label>
        </div>
        <?php } ?>
        <ul>
            <?php for ($i=0; $i<count($list); $i++) { ?>
            <li class="<?php if ($list[$i]['is_notice']) echo "bo_notice"; ?>">
                <?php if ($is_checkbox) { ?>
                <div class="bo_chk chk_box">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                    <label for="chk_wr_id_<?php echo $i ?>">
                    	<span></span>
                    	<b class="sound_only"><?php echo $list[$i]['subject'] ?></b>
                    </label>   	
                </div>
                <?php } ?>

                <div class="bo_cnt">
                    <a href="<?=G5_USER_ADMIN_URL?>/bbs_write.php?bo_table=<?=$bo_table?>&w=u&wr_id=<?=$list[$i]['wr_id']?>&<?=$qstr?>" class="bo_subject">
                        <?php echo $bo_carname_value[$list[$i]['subject']].'-('.$list[$i]['wr_content'].')'; ?>
                        <?php
                        if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
                        ?>
                    </a>
                </div>
				<div class="bo_info">
                    <?php
                    $startdt = explode(' ',$list[$i]['wr_1']);
                    $list[$i]['wr_1_date'] = substr($startdt[0],2,8);
                    $list[$i]['wr_1_time'] = $startdt[1];

                    $enddt = explode(' ',$list[$i]['wr_2']);
                    $list[$i]['wr_2_date'] = substr($enddt[0],2,8);
                    $list[$i]['wr_2_time'] = $enddt[1];

                    echo $list[$i]['wr_1_date'].' ('.$bo_time2_value[$list[$i]['wr_1_time']].')';
                    echo ' ~ ';
                    echo $list[$i]['wr_2_date'].' ('.$bo_time2_value[$list[$i]['wr_2_time']].')';
                    ?>
                </div>    
            </li>
            <?php } ?>
            <?php if (count($list) == 0) { echo '<li class="empty_table">게시물이 없습니다.</li>'; } ?>
        </ul>
    </div>
</div>

</form>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<!-- 페이지 -->
<?php echo $write_pages; ?>

<div id="bo_list_total">
    <span>전체 <?php echo number_format($total_count) ?>건</span>
    <?php echo $page ?> 페이지
</div>

<fieldset id="bo_sch">
    <legend>게시물 검색</legend>
    <form name="fsearch" method="get">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sop" value="and">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <?php echo get_board_sfl_select_options($sfl); ?>
    </select>
    <input name="stx" value="<?php echo stripslashes($stx) ?>" placeholder="검색어를 입력하세요" required id="stx" class="sch_input" size="15" maxlength="20">
    <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i> <span class="sound_only">검색</span></button>
    </form>
</fieldset>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = g5_user_admin_url+"/bbs_board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == 'copy')
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = g5_user_admin_url+"/bbs_move.php";
    f.submit();
}

// 게시판 리스트 관리자 옵션
jQuery(function($){
    $(".btn_more_opt.is_list_btn").on("click", function(e) {
        e.stopPropagation();
        $(".more_opt.is_list_btn").toggle();
    });
    $(document).on("click", function (e) {
        if(!$(e.target).closest('.is_list_btn').length) {
            $(".more_opt.is_list_btn").hide();
        }
    });
});
</script>
<?php } ?>
<!-- 게시판 목록 끝 -->
