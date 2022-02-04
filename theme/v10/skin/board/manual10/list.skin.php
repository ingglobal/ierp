<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/list.php');

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 10;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

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
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>

        <?php if ($rss_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn"><i class="fa fa-rss" aria-hidden="true"></i> RSS</a></li><?php } ?>
            <?php if ($is_admin) { ?><li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><a href="<?php echo $board_skin_url ?>/config_form.php?bo_table=<?php echo $bo_table;?>" class="btn_admin btn"><i class="fa fa-gear" aria-hidden="true"></i> 환경설정</a></li><?php } ?>
            <?php if ($admin_href) { ?><li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><a href="<?php echo $admin_href ?>" class="btn_admin btn"><i class="fa fa-user-circle" aria-hidden="true"></i> 관리자</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn"><i class="fa fa-pencil" aria-hidden="true"></i> 글쓰기</a></li><?php } ?>
        </ul>
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
    <form name="fsearch" method="get">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sop" value="and">
        <label for="sfl" class="sound_only">검색대상</label>
        
        <div class="bo_sch_date">
            등록일:
            <input type="text" id="fr_date" name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" maxlength="10" placeholder="시작일">
            ~
            <input type="text" id="to_date" name="to_date" value="<?php echo $to_date; ?>" class="frm_input" maxlength="10" placeholder="종료일">
            &nbsp;&nbsp;
            다음일정일
            <input type="text" id="pl_date" name="pl_date" value="<?php echo $pl_date; ?>" class="frm_input" maxlength="10" placeholder="">
        </div>
        
        <div class="bo_sch_group">
            <select name="sch_wr_5" id="sch_wr_5">
                <option value="">관심등급전체 &nbsp;</option>
                <?php echo $g5['set_sales_grades_options'];?>
            </select>
            <script>$('#sch_wr_5').val('<?php echo $sch_wr_5;?>').attr('selected','selected');</script>

            <select name="sch_wr_10" id="sch_wr_10">
                <option value="">상태값전체 &nbsp;</option>
                <?php echo $g5['set_sales_status_options'];?>
            </select>
            <script>$('#sch_wr_10').val('<?php echo $sch_wr_10;?>').attr('selected','selected');</script>

            <select name="sfl" id="sfl">
                <option value="wr_1"<?php echo get_selected($sfl, 'wr_1', true); ?>>업체명</option>
                <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
                <option value="wr_name,1"<?php echo get_selected($sfl, 'wr_name,1'); ?>>작성자(영업자)</option>
                <option value="wr_name,0"<?php echo get_selected($sfl, 'wr_name,0'); ?>>작성자(코멘트)</option>
                <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>내용</option>
                <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+내용</option>
                <option value="mb_id,1"<?php echo get_selected($sfl, 'mb_id,1'); ?>>아이디</option>
                <option value="mb_id,0"<?php echo get_selected($sfl, 'mb_id,0'); ?>>아이디(코멘트)</option>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
            <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
        <div>
    </form>
    </fieldset>
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
            <?php if ($is_checkbox) { ?>
            <th scope="col">
                <label for="chkall" class="sound_only">현재 페이지 게시물 전체</label>
                <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
                <input type="hidden" name="chk[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_<?php echo $i; ?>">
            </th>
            <?php } ?>
            <th scope="col">번호</th>
            <th scope="col">업체/담당자</th>
            <th scope="col">제목</th>
            <th scope="col">관심등급</th>
            <th scope="col" style="width:80px;">작성자</th>
            <th scope="col" style="width:90px;">상태</th>
            <th scope="col" style="width:50px;display:<?php if(!$is_admin) echo 'none';?>;">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i=0; $i<count($list); $i++) {
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
            if ($list[$i]['is_notice']) // 공지사항
                echo '<strong class="notice_icon"><i class="fa fa-bullhorn" aria-hidden="true"></i><span class="sound_only">공지</span></strong>';
            else if ($wr_id == $list[$i]['wr_id'])
                echo "<span class=\"bo_current\">열람중</span>";
            else
                echo $list[$i]['num'];
             ?>
            </td>
            <td class="td_com_name"><!-- 업체/고객 -->
                <?php echo $list[$i]['com']['com_name'] ?>
                <br>
                <?php echo $list[$i]['cmm']['cmm_name_rank'] ?>
            </td>

            <!-- 제목 [ -->
            <td class="td_subject" style="padding-left:<?php echo $list[$i]['reply'] ? (strlen($list[$i]['wr_reply'])*10) : '5'; ?>px">
                <div class="bo_tit">
                    
                    <a href="<?php echo $list[$i]['href'] ?>">
                        <?php echo $list[$i]['icon_reply'] ?>
                        <?php
                            if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']);
                         ?>
                        <?php echo $list[$i]['subject'] ?>
                       
                    </a>
                    <?php
                    // if ($list[$i]['file']['count']) { echo '<'.$list[$i]['file']['count'].'>'; }
                    if (isset($list[$i]['icon_file'])) echo rtrim($list[$i]['icon_file']);
                    if (isset($list[$i]['icon_link'])) echo rtrim($list[$i]['icon_link']);
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
                // 등록일, 다음일정일
                echo '<span class="span_wr_datetime">등록일: '.$list[$i]['datetime2'].'</span>';
                echo '<span class="span_next_duedate">다음일정일: '.$list[$i]['wr_6'].'</span>';
                ?>
            </td>
            <!--] 제목 -->
            
            <td class="td_sales_grade"><?php echo $g5['set_sales_grades'][$list[$i]['wr_5']] ?></td><!-- 관심등급 -->
            <td class="td_wr_name sv_use"><?php echo $list[$i]['mb']['mb_name'] ?></td><!-- 작성자 -->
            <td class="td_status sv_use"><!-- 상태 -->
                <?php echo $g5['set_sales_status_value'][$list[$i]['wr_10']] ?>
            </td>
            <td class="td_modify sv_use" style="display:<?php if(!$is_admin) echo 'none';?>;"><!-- 수정 -->
                <a href="<?php echo G5_BBS_URL?>/write.php?w=u&bo_table=<?php echo $bo_table?>&wr_id=<?php echo $list[$i]['wr_id']?>&<?php echo $qstr?>">수정</a>
            </td>

        </tr>
        <?php } ?>
        <?php if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
        </tbody>
        </table>
    </div>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <?php if ($list_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($is_checkbox) { ?>
            <li style="display:none;"><button type="submit" name="btn_submit" value="선택수정" onclick="document.pressed=this.value" class="btn btn_admin"><i class="fa fa-edit" aria-hidden="true"></i> 선택수정</button></li>
            <li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_admin"><i class="fa fa-trash-o" aria-hidden="true"></i> 선택삭제</button></li>
            <li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="btn btn_admin"><i class="fa fa-files-o" aria-hidden="true"></i> 선택복사</button></li>
            <li style="display:<?php if(auth_check($auth[$sub_menu],"d",1)) echo 'none';?>"><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="btn btn_admin"><i class="fa fa-arrows" aria-hidden="true"></i> 선택이동</button></li>
            <?php } ?>
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_b01 btn"><i class="fa fa-list" aria-hidden="true"></i> 목록</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn"><i class="fa fa-pencil" aria-hidden="true"></i> 글쓰기</a></li><?php } ?>
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

$("#fr_date, #to_date, #pl_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
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
