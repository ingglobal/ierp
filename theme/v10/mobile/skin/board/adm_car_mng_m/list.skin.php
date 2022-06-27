<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$cat_wr_id_arr = array();
$wr_id_cat_arr = array();
foreach($notice_arr as $nv){
    $csql = " SELECT ca_name FROM {$write_table} WHERE wr_id = '{$nv}' ";
    $carr = sql_fetch($csql);
    $cat_wr_id_arr[$carr['ca_name']] = $nv;
    $wr_id_cat_arr[$nv] = $carr['ca_name'];
}

// 분류 사용 여부
$is_category = false;
$category_option = '';
if ($board['bo_use_category']) {
    $is_category = true;
    $category_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table;
    if($member['mb_level'] >= 10){
        $category_option .= '<li><a href="'.$category_href.'"';
        if ($sca=='')
            $category_option .= ' id="bo_cate_on"';
        $category_option .= '>전체</a></li>';
    }
    $categories = explode('|', $board['bo_category_list']); // 구분자가 , 로 되어 있음
    for ($i=0; $i<count($categories); $i++) {
        $category = trim($categories[$i]);
        if ($category=='') continue;
        $category_option .= '<li><a href="'.G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;sca='.urlencode($category).'"';
        $category_msg = '';
        if ($category==$sca) { // 현재 선택된 카테고리라면
            $category_option .= ' id="bo_cate_on"';
            $category_msg = '<span class="sound_only">열린 분류 </span>';
        }
        $category_option .= '>'.$category_msg.$category.'</a></li>';
    }
}

if($member['mb_level'] < 10 && count($category_arr) > 0 && !$sca){
    //goto_url($category_link.urlencode($category_arr[0]));
    goto_url(G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&wr_id='.$cat_wr_id_arr[$category_arr[0]]);
}
else if($member['mb_level'] < 10 && count($category_arr) > 0 && $sca){
    goto_url(G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&wr_id='.$cat_wr_id_arr[$sca]);
}
//print_r3($cat_wr_id_arr);
// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 2;

if ($is_checkbox) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
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
	<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
    <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b03 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
    <?php if ($is_admin == 'super' || $is_auth) {  ?>
	<li>
		<button type="button" class="btn_more_opt btn_b03 btn is_list_btn" title="게시판 리스트 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
		<?php if ($is_checkbox) { ?>	
        <ul class="more_opt is_list_btn">
            <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
            <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
            <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
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
                	<?php if ($list[$i]['is_notice'] || ($is_category && $list[$i]['ca_name'])) { ?>
                	<div class="bo_cate_ico">
                		<?php if ($list[$i]['is_notice']) { ?><strong class="notice_icon">운영</strong><?php } ?>
	                    <?php if ($is_category && $list[$i]['ca_name']) { ?>       
	                    <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link"><?php echo $list[$i]['ca_name']; ?></a>
	                    <?php } ?>
                    </div>
                    <?php } ?> 
                    
                    <a href="<?php echo $list[$i]['href'] ?>" class="bo_subject">
                        <?php echo $list[$i]['icon_reply']; ?>
                        <?php if (isset($list[$i]['icon_secret'])) echo $list[$i]['icon_secret']; ?>
                        <?php echo $list[$i]['subject'] ?>
                        <?php
                        // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }
                        if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
                        //if (isset($list[$i]['icon_hot'])) echo $list[$i]['icon_hot'];
                        if (isset($list[$i]['fcnt'])) echo rtrim($list[$i]['icon_file']);
                        //if (isset($list[$i]['icon_link'])) echo $list[$i]['icon_link'];
                        ?>
                        
                        <?php if ($list[$i]['comment_cnt']) { ?>
                        <span class="bo_cmt">
							<span class="sound_only">댓글</span>
							<?php echo $list[$i]['comment_cnt']; ?>
							<span class="sound_only">개</span>
                        </span>
                        <?php } ?>
                    </a>
                </div>
				<div class="bo_info">
                    <span class="sound_only">작성자</span><?php echo $list[$i]['name'] ?>
                    <span class="bo_date"><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo $list[$i]['datetime2'] ?></span>
                	<span class="bo_view"><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($list[$i]['wr_hit']) ?><span class="sound_only">회</span></span>
                	<?php if ($is_good) { ?><span class="sound_only">추천</span><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> <?php echo $list[$i]['wr_good'] ?><?php } ?>
                    <?php if ($is_nogood) { ?><span class="sound_only">비추천</span><i class="fa fa-thumbs-o-down" aria-hidden="true"></i> <?php echo $list[$i]['wr_nogood'] ?><?php } ?>
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
        f.action = g5_bbs_url+"/board_list_update.php";
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
    f.action = g5_bbs_url+"/move.php";
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
