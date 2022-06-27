<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 8;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<!-- 게시판 목록 시작 { -->
<div id="bo_list" style="width:<?php echo $width; ?>">
    <div class="local_ov01 local_ov">
        <a href="<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=<?=$bo_table?>" class="ov_listall">전체목록</a>
        <span class="btn_ov01">
            <span class="ov_txt">총</span>
            <span class="ov_num"><?php echo number_format($total_count) ?></span>
            <span class="ov_txt" style="margin-left:3px;">페이지</span>
            <span class="ov_num"><?php echo $page ?></span>
        </span>
    </div>

    <fieldset id="bo_sch">
    <legend>게시물 검색</legend>
    <form name="fsearch" method="get" onsubmit="return fsearch_submit(this);">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sop" value="and">
        <label for="sfl" class="sound_only">검색대상</label>
        <div class="bo_sch_group">
            <select name="sfl" id="sfl">
                <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>안건제목</option>
                <option value="wr_1"<?php echo get_selected($sfl, 'wr_1'); ?>>부서명</option>
                <option value="wr_3"<?php echo get_selected($sfl, 'wr_3'); ?>>책임자명</option>
                <option value="wr_2"<?php echo get_selected($sfl, 'wr_2'); ?>>책임자ID</option>
                <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>안건내용</option>
                <option value="wr_5"<?php echo get_selected($sfl, 'wr_5'); ?>>계획내용</option>
                <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>안건제목+안건내용</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
            <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
        <div>
    </form>
    </fieldset>
    <!-- 게시판 카테고리 시작 { -->
    <?php if ($is_category) { ?>
    <nav id="bo_cate">
        <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?php echo $category_option ?>
        </ul>
    </nav>
    <?php } ?>
    <!-- } 게시판 카테고리 끝 -->
    
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_USER_ADMIN_URL; ?>/bbs_board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div id="bo_btn_top">
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>

        <ul class="btn_bo_user">
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
        	<?php if ($is_admin == 'super' || $is_auth) {  ?>
        	<li>
        		<button type="button" class="btn_more_opt is_list_btn btn_b01 btn" title="게시판 리스트 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
        		<?php if ($is_checkbox) { ?>	
		        <ul class="more_opt is_list_btn">  
		            <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
		        </ul>
		        <?php } ?>
        	</li>
        	<?php }  ?>
        </ul>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->
        	
    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption><?php echo $board['bo_subject'] ?> 목록</caption>
        <thead>
        <tr>
            <?php if ($is_checkbox) { ?>
            <th scope="col" class="all_chk chk_box">
            	<input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);" class="selec_chk">
                <label for="chkall">
                	<span></span>
                	<b class="sound_only">현재 페이지 게시물  전체선택</b>
				</label>
            </th>
            <?php } ?>
            <th scope="col">접수번호</th>
            <th scope="col">관련부서</th>
            <th scope="col"><?php echo subject_sort_link('wr_datetime', $qstr2, 1) ?>등록일  </a></th>
            <th scope="col">책임자</th>
            <th scope="col">안건제목</th>
            <th scope="col" style="background:#d8a23e;border:1px solid #f1b03a;">확인날짜</th>
            <th scope="col" style="background:#d8a23e;border:1px solid #f1b03a;">상태</th>
            <th scope="col" style="background:#d8a23e;border:1px solid #f1b03a;">계획내용</th>
        </tr>
        </thead>
        <tbody>
        <?php
        //wr_1=부서,wr_2=책임자ID,wr_3=책임자명,wr_4=안건상태,wr_5=계획내용,wr_6=접수일시
        for ($i=0; $i<count($list); $i++) {
        	if ($i%2==0) $lt_class = "even";
        	else $lt_class = "";
		?>
        <tr class="<?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> <?php echo $lt_class ?>">
            <?php if ($is_checkbox) { ?>
            <td class="td_chk chk_box">
				<input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
            	<label for="chk_wr_id_<?php echo $i ?>">
            		<span></span>
            		<b class="sound_only"><?php echo $list[$i]['subject'] ?></b>
            	</label>
            </td>
            <?php } ?>
            <td class="td_num2"><?php echo $list[$i]['num']; ?></td>
            <td class="td_part"><?php echo $g5['set_department_name_value'][$list[$i]['wr_1']]; ?></td><!--관련부서-->
            <td class="td_datetime"><?php echo $list[$i]['datetime'] ?></td><!--등록일-->
            <td class="td_pic"><?php echo $list[$i]['wr_3'] ?></td><!--책임자-->
            <td class="td_subject" style="padding-left:<?php echo $list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '0'; ?>px;width:300px;">
                <?php
                if ($is_category && $list[$i]['ca_name']) {
				?>
                <a href="<?php echo $list[$i]['ca_name_href'] ?>" class="bo_cate_link"><?php echo $list[$i]['ca_name'] ?></a>
                <?php } ?>
                <div class="bo_tit">
                    <a href="<?php echo $list[$i]['href'] ?>">
                        <?php echo $list[$i]['icon_reply'] ?>
                        <?php
                            if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']);
                         ?>
                        <?php echo $list[$i]['subject'] ?>
                    </a>
                    <?php
                    if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
                    // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }
                    //if (isset($list[$i]['icon_hot'])) echo rtrim($list[$i]['icon_hot']);
                    if (isset($list[$i]['fcnt'])) echo rtrim($list[$i]['icon_file']);
                    //if (isset($list[$i]['icon_link'])) echo rtrim($list[$i]['icon_link']);
                    ?>
                    <?php if ($list[$i]['comment_cnt']) { ?><span class="sound_only">댓글</span><span class="cnt_cmt"><?php echo $list[$i]['wr_comment']; ?></span><span class="sound_only">개</span><?php } ?>
                </div>
            </td><!--안건제목-->
            <td class="td_chkdt" style="background:#fff9ed"><?php echo ($list[$i]['wr_6'])?substr($list[$i]['wr_6'],0,10):'-'; ?></td><!--확인날짜-->
            <?php
            $status_class = '';
            if($list[$i]['wr_4'] == 'pending') $status_class = ' txt_redblink';
            else if($list[$i]['wr_4'] == 'check') $status_class = ' txt_blueblink';
            ?>
            <td class="td_status<?=$status_class?>" style="background:#fff9ed"><?php echo $g5['set_agenda_status_value'][$list[$i]['wr_4']]; ?></td><!--상태-->
            <td class="td_plan" style="background:#fff9ed;width:300px;"><a href="<?php echo $list[$i]['href'] ?>"><?php if($list[$i]['wr_5']){ ?><?php echo cut_str($list[$i]['wr_5'],50,'...'); ?></a><?php } ?></td><!--계획내용-->
        </tr>
        <?php } ?>
        <?php if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
        </tbody>
        </table>
    </div>
	<!-- 페이지 -->
	<?php echo $write_pages; ?>
	<!-- 페이지 -->
	
    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="btn_bo_user">
        	<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
        </ul>	
        <?php } ?>
    </div>
    <?php } ?>   
    </form>

</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

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
        f.action = "<?=G5_USER_ADMIN_URL?>"+"/bbs_board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
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
<!-- } 게시판 목록 끝 -->
