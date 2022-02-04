<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/add_column.skin.php');
// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 12;
$is_infoauth = ($member['mb_6'] < 2 || $member['mb_level'] >= 8) ? 1 : 0;
//if($is_infoauth) $colspan++;
if ($is_checkbox) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

//일차( dayoff_cnt )
if($board['bo_2_subj'] && $board['bo_2'] && preg_match("/,/",$board['bo_2']) && preg_match("/=/",$board['bo_2'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_2']));
    $valname = $board['bo_2_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
		${'bo_'.$valname.'_value'}[$key] = $value;
    }
}
//연차시작일의 오전/오후(start_ampm)
if($board['bo_3_subj'] && $board['bo_3'] && preg_match("/,/",$board['bo_3']) && preg_match("/=/",$board['bo_3'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_3']));
    $valname = $board['bo_3_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        //${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		//${'bo_'.$valname.'_reverse'}[$value] = $key;
		//${'bo_'.$valname.'_arr'}[] = $key;
    }
}
//출근일의 오전/오후(work_ampm)
if($board['bo_4_subj'] && $board['bo_4'] && preg_match("/,/",$board['bo_4']) && preg_match("/=/",$board['bo_4'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_4']));
    $valname = $board['bo_4_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        //${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		//${'bo_'.$valname.'_reverse'}[$value] = $key;
		//${'bo_'.$valname.'_arr'}[] = $key;
    }
}
//연차휴가 유형(dayoff_type)
if($board['bo_5_subj'] && $board['bo_5'] && preg_match("/,/",$board['bo_5']) && preg_match("/=/",$board['bo_5'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_5']));
    $valname = $board['bo_5_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        //${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		//${'bo_'.$valname.'_reverse'}[$value] = $key;
		//${'bo_'.$valname.'_arr'}[] = $key;
		${'bo_'.$valname.'_options'} .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    }
}
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
            <input type="text" name="fr_date" id="fr_date" placeholder="검색시작일" value="<?=$fr_date?>" readonly class="frm_input readonly" style="width:100px;"> ~
            <input type="text" name="to_date" id="to_date" placeholder="검색종료일" value="<?=$to_date?>" readonly class="frm_input readonly" style="width:100px;">
            <select name="stat" id="stat">
                <option value="">신청상태</option>
                <?php echo $g5['set_approve_status_value_options']; ?>
            </select>
			<select name="type" id="type">
				<option value="">유형선택</option>
                <?php echo $bo_dayoff_type_options; ?>
            </select>
            <select name="sfl" id="sfl">
                <option value="wr_worker_name"<?php echo get_selected($sfl, 'wr_worker_name'); ?>>신청자명</option>
                <option value="wr_approver_name"<?php echo get_selected($sfl, 'wr_approver_name'); ?>>승인자명</option>
                <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>연차사유</option>
                <option value="wr_mb_part"<?php echo get_selected($sfl, 'wr_mb_part'); ?>>부서명</option>
                <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>비고내용</option>
                <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>사유내용+비고내용</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요" style="width:150px;">
            <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
        <div>
    </form>
    <script>
    $("input[name=fr_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=to_date]").datepicker('option','minDate',selectedDate);} });

    $("input[name=to_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){$("input[name=fr_date]").datepicker('option','maxDate',selectedDate); }});

    $('select[name="stat"]').val("<?=$stat?>");
    </script>
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
    
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    
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
        	<?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
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
            <th scope="col">번호</th>
            <th scope="col">연차사유</th>
            <th scope="col">신청자</th>
            <th scope="col">부서</th>
            <th scope="col">승인자</th>
            <th scope="col">시작일시</th>
            <th scope="col">연차시간</th>
            <th scope="col">일차수</th>
            <th scope="col">출근일</th>
            <th scope="col">출근시간</th>
            <th scope="col">유형</th>
            <th scope="col">상태</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $days_acc = 0;
        for ($i=0; $i<count($list); $i++) {
        	if ($i%2==0) $lt_class = "even";
        	else $lt_class = "";
            $days_acc += $list[$i]['wr_dayoff_cnt'];
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
            <td class="td_num2"><?php echo $list[$i]['num']; ?> </td>
            <td class="td_subject" style="padding-left:<?php echo $list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '0'; ?>px">
                <div class="bo_tit">
                    <a href="<?php echo G5_USER_ADMIN_URL.'/bbs_write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$list[$i]['wr_id'].'&amp;'.$qstr ?>">
                        <?php echo $list[$i]['subject'] ?>
                    </a>
                    <?php
                    if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N<span class=\"sound_only\">새글</span></span>";
                    ?>
                </div>
            </td>
            <td class="td_name sv_use">
                <?php
                $wmb = get_member($list[$i]['wr_mb_id_applicant'],'mb_name');
                echo $wmb['mb_name'];
                ?>
            </td>
            <td class="td_part"><?php echo $g5['set_department_name_value'][$list[$i]['wr_mb_part']]; ?></td>
            <td class="td_name">
                <?php
                $amb = get_member($list[$i]['wr_mb_id_approver'],'mb_name');
                echo $amb['mb_name'];
                ?>
            </td>
            <td class="td_datetime"><?php echo $list[$i]['wr_start_date']; ?></td>
            <td class="td_ampm"><?=$bo_start_ampm_value[$list[$i]['wr_start_ampm']]?></td>
            <td class="td_cnt"><?=$list[$i]['wr_dayoff_cnt']?>일</td>
            <td class="td_datetime"><?php echo $list[$i]['wr_work_date']; ?></td>
            <td class="td_ampm"><?=$bo_work_ampm_value[$list[$i]['wr_work_ampm']]?></td>
			<td class="td_ampm"><?=$bo_dayoff_type_value[$list[$i]['wr_dayoff_type']]?></td>
            <?php
                $blink_class = '';
                if($list[$i]['wr_apply_status'] == 'pending'){
                    $blink_class = ' txt_blueblink';
                }else if($list[$i]['wr_apply_status'] == 'reject'){
                    $blink_class = ' bo_txt_color_red';
                }else if($list[$i]['wr_apply_status'] == 'cancel'){
                    $blink_class = ' bo_txt_color_gray';
                }else if($list[$i]['wr_apply_status'] == 'ok'){
                    $blink_class = ' bo_txt_color_ok';
                }
            ?>
            <td class="td_status">
                <span class="<?=$blink_class?>"><?=$g5['set_approve_status_value'][$list[$i]['wr_apply_status']]?></span>
            </td>
        </tr>
        <?php if($i == (count($list) - 1)){ //if($i == (count($list) - 1) && ($member['mb_level'] >= 8 || $member['mb_6'] < 2)){ ?>
        <tr>
        <td colspan="<?=($colspan-4)?>" class="td_tot">합계</td>
        <td class="td_tot td_h"><?=$days_acc?>일</td>
        <td colspan="3" class="td_tot"></td>
        </tr>
        <?php } //if($i == (count($list) - 1)) ?>
        <?php } //for($i=0; $i<count($list); $i++) ?>
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
        f.action = g5_user_admin_url+"/bbs_board_list_update.php";
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
