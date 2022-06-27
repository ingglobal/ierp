<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/list.php');

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = ($member['mb_6'] == 1 || $member['mb_level'] >= 8) ? 10 : 9;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;
if ($is_admin) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>
<style>
input[name$="_date"] {width:90px !important;}
.td_com_name {white-space: nowrap;}
.td_sales_grade {max-width:80px;}
.span_wr_datetime {margin-left:15px;font-size:0.8em;}
.span_next_duedate {margin-left:15px;font-size:0.8em;}
</style>

<!-- 게시판 목록 시작 { -->
<div id="bo_list" style="width:<?php echo $width; ?>">


    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div id="bo_btn_top">
        <div class="local_ov01 local_ov">
            <a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>" class="ov_listall">전체목록</a>
            <span class="btn_ov01">
                <span class="ov_txt">총</span>
                <span class="ov_num"><?php echo number_format($total_count) ?></span>
                <span class="ov_txt" style="margin-left:3px;">페이지</span>
                <span class="ov_num"><?php echo $page ?></span>
            </span>
        </div>

        <?php if ($rss_href || $write_href) { ?>
        <div class="btn_fixed_top">
            <?php if ($rss_href) { ?><a href="<?php echo $rss_href ?>" class="btn_b01 btn">RSS</a><?php } ?>
            <?php if ($is_admin) { ?><a href="<?php echo $board_skin_url ?>/config_form.php?bo_table=<?php echo $bo_table;?>" class="btn_admin btn" style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>">환경설정</a><?php } ?>
            <?php if ($admin_href) { ?><a href="<?php echo $admin_href ?>" class="btn_admin btn" style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>">관리자</a><?php } ?>
            <?php if ($write_href) { ?><a href="<?php echo $write_href ?>" class="btn_b02 btn">글쓰기</a><?php } ?>
        </div>
        <?php } ?>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

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

    <!-- 게시판 검색 시작 { -->
    <fieldset id="bo_sch">
    <legend>게시물 검색</legend>
    <form name="fsearch" method="get" onsubmit="return fsearch_submit(this);">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sop" value="and">
        <label for="sfl" class="sound_only">검색대상</label>
        
        <div class="bo_sch_date">
            접수일:
            <input type="text" id="fr_date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" maxlength="10" placeholder="부터">
            ~
            <input type="text" id="to_date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" maxlength="10" placeholder="까지">
            &nbsp;&nbsp;
            방문예정일:
            <input type="text" id="fr_date2" name="fr_date2" value="<?php echo $pl_date2; ?>" class="frm_input" maxlength="10" placeholder="부터">
            ~
            <input type="text" id="to_date2" name="to_date2" value="<?php echo $to_date2; ?>" class="frm_input" maxlength="10" placeholder="까지">
        </div>
        
        <div class="bo_sch_group">
            <select name="sch_wr_9" id="sch_wr_9">
                <option value="">접수경로 선택</option>
                <?php echo $g5['set_as_receiptpath_options'];?>
            </select>
            <script>$('#sch_wr_9').val('<?php echo $sch_wr_9;?>').attr('selected','selected');</script>

            <select name="sch_wr_10" id="sch_wr_10">
                <option value="">상태 선택</option>
                <?php echo $g5['set_as_status_options'];?>
            </select>
            <script>$('#sch_wr_10').val('<?php echo $sch_wr_10;?>').attr('selected','selected');</script>

            <select name="sfl" id="sfl">
                <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
                <option value="wr_1"<?php echo get_selected($sfl, 'wr_1', true); ?>>업체명</option>
                <option value="wr_4,1"<?php echo get_selected($sfl, 'wr_4,1'); ?>>업체담당자</option>
                <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>접수내용</option>
                <option value="wr_link1"<?php echo get_selected($sfl, 'wr_link1'); ?>>조치내용</option>
                <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+접수내용</option>
                <option value="wr_5,1"<?php echo get_selected($sfl, 'wr_5,1'); ?>>AS담당자명</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
            <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
        <div>
    </form>
    </fieldset>
<script>
function fsearch_submit(f)
{
    if (f.to_date.value < f.fr_date.value) {
        alert('접수일자 종료일은 시작일보다 커야 합니다.');
        return false;
    }
    if (f.to_date2.value < f.fr_date2.value) {
        alert('방문예정일자 종료일은 시작일보다 커야 합니다.');
        return false;
    }
    return true;
}
</script>

    <!-- } 게시판 검색 끝 -->

    <form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption><?php echo $board['bo_subject'] ?> 목록</caption>
        <thead>
        <tr>
            <?php if ($is_admin) { ?>
            <th scope="col">
                <label for="chkall" class="sound_only">현재 페이지 게시물 전체</label>
                <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
                <input type="hidden" name="chk[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_<?php echo $i; ?>">
            </th>
            <?php } ?>
            <th scope="col">번호</th>
            <th scope="col">접수일</th>
            <th scope="col">업체/담당자</th>
            <th scope="col" style="width:270px;">A/S접수제목</th>
            <th scope="col">A/S담당자</th>
            <!--th scope="col">접수경로</th-->
            <th scope="col">비용</th>
            <th scope="col">방문예정</th>
            <th scope="col" style="width:200px;">조치내용</th>
            <!--th scope="col">비용</th-->
            <th scope="col" style="width:60px;">상태</th>
            <?php if($member['mb_6'] == 1 || $member['mb_level'] >= 8){ ?>
            <th scope="col">계산서발행</th>
            <?php } ?>
            <th scope="col" style="width:50px;display:<?php if(!$is_admin) echo 'none';?>;">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        
        for ($i=0; $i<count($list); $i++) {
            // print_r2($list[$i]);
            // wr_9 serialized 추출
            $list[$i]['sried'] = get_serialized($list[$i]['wr_9']);
//            print_r3($list[$i]['sried']);
            
            // 회원실명
            $list[$i]['mb'] = get_member($list[$i]['mb_id'],'mb_name');
//            print_r3($list[$i]['mb']);
            
            // 업체정보
            $list[$i]['com'] = get_table_meta('company','com_idx',$list[$i]['wr_2']);
//            print_r3($list[$i]['com']);
            
            // 고객정보
            $list[$i]['mb1'] = get_saler($list[$i]['wr_3']);
            // 고객직급
            $list[$i]['cmm'] = get_company_member($list[$i]['wr_3'],$list[$i]['wr_2']);
//            print_r2($list[$i]['cmm']);
            $list[$i]['href'] = G5_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$list[$i]['wr_id'].'&amp;'.$qstr;
        ?>
        <tr class="<?php if ($list[$i]['is_notice']) echo "bo_notice"; ?> status_<?php echo $list[$i]['wr_10'];?>">
            <?php if ($is_checkbox) { ?>
            <td class="td_chk">
                <label for="chk_wr_id_<?php echo $i ?>" class="sound_only"><?php echo $list[$i]['subject'] ?></label>
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
            </td>
            <?php } ?>
            <td class="td_num2"><!-- 번호 -->
            <?php
                echo $list[$i]['num'];
             ?>
            </td>
            <td class="td_reg font_size_8"><?php echo $list[$i]['wr_homepage']; ?></td><!-- 접수일 -->
            <td class="td_com_name td_left"><!-- 업체/담당자 -->
                <!--
                <?php //echo $list[$i]['com']['com_name'] ?>
                <br>
                <?php //echo $list[$i]['cmm']['cmm_name_rank'] ?>
                -->
                <?php echo $list[$i]['wr_1'] ?>
                <br>
                <?php echo $list[$i]['wr_4'] ?>
            </td>

            <!-- 제목 [ -->
            <td class="td_subject" style="padding-left:<?php echo $list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '5'; ?>px">
                <div class="bo_tit">
                    
                    <a href="<?php echo $list[$i]['href'] ?>">
                        <?php echo $list[$i]['wr_subject']; ?>
                    </a>
                    <?php
                    // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }
                    if (isset($list[$i]['icon_file'])) echo rtrim($list[$i]['icon_file']);
                    //if (isset($list[$i]['icon_link'])) echo rtrim($list[$i]['icon_link']);
                    if (isset($list[$i]['icon_new'])) echo rtrim($list[$i]['icon_new']);
                    if (isset($list[$i]['icon_hot'])) echo rtrim($list[$i]['icon_hot']);
                    ?>
                    <?php if ($list[$i]['comment_cnt']) { ?><span class="sound_only">댓글</span><span class="cnt_cmt">+ <?php echo $list[$i]['wr_comment']; ?></span><span class="sound_only">개</span><?php } ?>
                </div>
                <?php
                // 말머리(카테고리가)가 있는 경우
                if ($is_category && $list[$i]['ca_name']) {
                    echo '<a href="'.$list[$i]['ca_name_href'].'" class="bo_cate_link">'.$list[$i]['ca_name'].'</a>';
                }
                ?>
            </td>
            <!--] 제목 -->
            
            <td class="td_as"><?php echo $list[$i]['wr_5'] ?></td><!-- 작성자 -->
            <!--td class="td_as_receiptpath"><?php //echo $g5['set_as_receiptpath_value'][$list[$i]['wr_9']] ?></td--><!-- 접수경로 -->
            <td class="td_cost td_right"><?php echo number_format($list[$i]['wr_7']); ?></td><!-- 비용 -->
            <td class="td_visit_date font_size_8"><?php echo $list[$i]['wr_facebook_user'] ?></td><!-- 방문예정일 -->
            <td class="td_take td_left"><?php echo cut_str(trim(strip_tags($list[$i]['wr_link1'])),35,'...'); ?></td><!-- 조치내용 -->
            <td class="td_status sv_use"><!-- 상태 -->
                <span class="<?=(($list[$i]['wr_10'] == 'receipt' || $list[$i]['wr_10'] == 'pending') ? 'txt_redblink' : '')?>"><?php echo $g5['set_as_status_value'][$list[$i]['wr_10']] ?></span>
            </td>
            <?php if($member['mb_6'] == 1 || $member['mb_level'] >= 8){ ?>
            <td class="td_bill font_size_8"><?php echo $list[$i]['wr_8']; ?></td><!-- 계산서발행일 -->
            <?php } ?>
            <td class="td_modify sv_use" style="display:<?php if(!$is_admin) echo 'none';?>;"><!-- 수정 -->
                <a href="<?php echo G5_BBS_URL?>/write.php?w=u&bo_table=<?php echo $bo_table?>&wr_id=<?php echo $list[$i]['wr_id']?>&<?php echo $qstr?>">수정</a>
            </td>

        </tr>
        <?php } ?>
        <?php if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
        </tbody>
        </table>
    </div>

    <?php if ($list_href || $is_checkbox || $write_href || $is_admin) { ?>
    <div class="bo_fx">
        <?php if ($list_href || $write_href || $is_admin) { ?>
        <ul class="btn_bo_user">
            <?php if ($is_checkbox || $is_admin) { ?>
            <li style="display:none;"><button type="submit" name="btn_submit" value="선택수정" onclick="document.pressed=this.value" class="btn btn_admin">선택수정</button></li>
            <li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'no ne';?>"><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_admin">선택삭제</button></li>
            <li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="btn btn_admin">선택복사</button></li>
            <li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="btn btn_admin">선택이동</button></li>
            <?php } ?>
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_b01 btn">목록</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn">글쓰기</a></li><?php } ?>
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

<!-- 페이지 -->
<?php echo $write_pages;  ?>


<script>
// 상단 제목 수정
$('#container_title').text('<?php echo $board['bo_subject']?>');

$("#fr_date, #to_date, #fr_date2, #to_date2").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
</script>


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
        f.action = "./board_list_update.php";
    }
    if(document.pressed == "선택수정") {
        if (!confirm("선택한 게시물을 수정하시겠습니까?"))
            return false;

        f.removeAttribute("target");
        f.action = "<?php echo $board_skin_url?>/list_update.php";
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
    f.action = "./move.php";
    f.submit();
}
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
