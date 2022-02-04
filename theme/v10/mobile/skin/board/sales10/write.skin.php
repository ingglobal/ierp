<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/write.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
//print_r2($g5);
if(G5_IS_ADMIN){
    $list_href = get_pretty_url($bo_table,'',$qstr);    
}
?>
<style>
    .customer_info {font-size:1.4em;border:solid 1px #ddd;margin-top:10px;background: #f5f5f5;padding:10px;}
    .div_com_president {font-size:0.7em;}
    #wr_content {padding:5px;line-height:1.5em;}
    #wr_subject {padding-left:5px;}
</style>
<?php if(G5_IS_MOBILE){ ?>
<style>

</style>
<?php } ?>
<section id="bo_w" style="padding-top:10px;">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

    <!-- 게시물 작성/수정 시작 { -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="fr_date" value="<?php echo $fr_date ?>">
    <input type="hidden" name="to_date" value="<?php echo $to_date ?>">
    <input type="hidden" name="pl_date" value="<?php echo $pl_date ?>">
    <input type="hidden" name="sch_wr_10" value="<?php echo $sch_wr_10 ?>">
    <input type="hidden" name="sch_mb_asign_worker" value="<?php echo $sch_mb_asign_worker ?>">
    <input type="hidden" name="sch_wr_5" value="<?php echo $sch_wr_5 ?>">
    <?php if(G5_IS_ADMIN){ ?>
    <input type="hidden" name="url" value="<?php echo $urlencode ?>">
    <?php } ?>

    <div class="bo_user write_div" style="display:<?=(!$member['board_manager_yn'])?'none':''?>;">
        <label for="mb_name_worker" class="sound_only">작업자</label>
        <input type="hidden" name="mb_id_worker" id="mb_id_worker" value="<?=$write['mb_id_worker']?>"><!-- 작업자아이디 -->
        <input type="hidden" name="trm_idx_department_worker" id="trm_idx_department_worker" value="<?=$write['trm_idx_department_worker']?>"><!-- 작업자조직코드 -->
        <input type="text" name="mb_name_worker" id="mb_name_worker" href="<?php echo G5_USER_ADMIN_URL?>/employee_select.php?frm=fwrite&tar1=mb_id_worker&tar2=mb_name_worker&tar3=trm_idx_department_worker" value="<?php echo $write['mb_name_worker'] ?>" id="mb_name_worker" class="frm_input" placeholder="작업자" readonly>

        <label for="wr_5" class="sound_only">작업등급</label>
        <select name="wr_5" id="wr_5" class="frm_input">
            <option value="">작업등급을 선택하세요</option>
            <?php echo $g5['set_maintain_grades_options_value'] ?>
        </select>
        <script>$('select[name=wr_5]').val('<?php echo $write['wr_5'] ?>').attr('selected','selected');</script>

        <label for="wr_6" class="sound_only">완료예정일</label>
        <input type="text" name="wr_6" value="<?php echo $write['wr_6'] ?>" id="wr_6" class="frm_input" placeholder="완료예정일">

        <label for="wr_10" class="sound_only">상태</label>
        <select name="wr_10" id="wr_10" class="frm_input">
            <option value="">상태를 선택하세요</option>
            <?php echo $g5['set_maintain_status_options'] ?>
        </select>
        <script>
            $('select[name=wr_10]').val('<?php echo $write['wr_10'] ?>').attr('selected','selected');
            $('select[name=wr_10]').css('margin-right','0');
        </script>

    </div>

    <!-- 담당자찾기 -->
    <div class="write_div">
        <label for="wr_cart" class="sound_only">상품</label>
        <input type="hidden" name="cmm_idx" value="<?=$write['cmm_idx']?>"><!-- 업체-고객 번호 -->
        <input type="hidden" name="com_idx" value="<?=$write['wr_2']?>"><!-- 업체번호 -->
        <input type="hidden" name="mb_id_customer" value="<?=$write['wr_3']?>"><!-- 고객아이디 -->
        <div style="display:<?php if($w=='u'&&!$member['mb_manager_yn']) echo 'none';?>;padding-bottom:5px;">
            <button type="button" class="btn btn_b01" id="btn_customer">담당자<?=($w=='u')?'변경':'찾기'?></button>
            <p class="sound_only">담당자가 존재하지 않는 경우 업체등록 및 담당자등록을 먼저 하신 후 영업등록을 할 수 있습니다. <a href="<?=G5_USER_ADMIN_URL?>/member_list.php">[담당자등록바로가기]</a></p>
        </div>
        <!-- 고객(업체)정보 -->
        <div class="customer_info" style="display:<?=(!$write['customer_info'])?'none':''?>;margin-bottom:5px;">
            <?=$write['customer_info']?>
        </div>
        <input type="<?=((!$write['customer_info']) ? 'hidden':'hidden')?>" placeholder="담당업체" readonly class="frm_input readonly" name="com_name" value="<?=$write['wr_1']?>" style="width:49%;"><!-- 업체명 -->
        <input type="<?=((!$write['customer_info']) ? 'hidden':'hidden')?>" placeholder="담당자명" readonly class="frm_input readonly" name="mb_name_customer" value="<?=$write['wr_4']?>" style="width:49%;"><!-- 고객명 -->
    </div>

    <?php
    $option = '';
    $option_hidden = '';
    if ($is_notice || $is_html || $is_secret || $is_mail) {
        $option = '';
        if ($is_notice) {
            $option .= "\n".'<input type="checkbox" id="notice" name="notice" value="1" '.$notice_checked.'>'."\n".'<label for="notice">공지</label>';
        }

        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= "\n".'<input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label for="html">HTML</label>';
            }
        }

        if ($is_secret) {
            if ($is_admin || $is_secret==1) {
                $option .= "\n".'<input type="checkbox" id="secret" name="secret" value="secret" '.$secret_checked.'>'."\n".'<label for="secret">비밀글</label>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }

        if ($is_mail) {
            $option .= "\n".'<input type="checkbox" id="mail" name="mail" value="mail" '.$recv_email_checked.'>'."\n".'<label for="mail">답변메일받기</label>';
        }
    }

    echo $option_hidden;
    ?>

    <?php if ($is_category) { ?>
    <div class="bo_w_select write_div">
        <label for="ca_name"  class="sound_only">분류<strong>필수</strong></label>
        <select name="ca_name" id="ca_name" required>
            <option value="">분류를 선택하세요</option>
            <?php echo $category_option ?>
        </select>
    </div>
    <?php } ?>

    <div class="bo_w_tit write_div" style="margin-top:5px;">
        <label for="wr_subject" class="sound_only">제목<strong>필수</strong></label>
        <div id="autosave_wrapper write_div">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" size="50" maxlength="255" placeholder="제목"<?=((G5_IS_MOBILE) ? ' style="width:100%;padding-right:0;"' : '')?>>
            <?php if (!G5_IS_MOBILE) { // 임시 저장된 글 기능 ?>
            <?php if ($is_member) { // 임시 저장된 글 기능 ?>
            <script src="<?php echo G5_JS_URL; ?>/autosave.js"></script>
            <?php if($editor_content_js) echo $editor_content_js; ?>
            <button type="button" id="btn_autosave" class="btn_frmline">임시 저장된 글 (<span id="autosave_count"><?php echo $autosave_count; ?></span>)</button>
            <div id="autosave_pop">
                <strong>임시 저장된 글 목록</strong>
                <ul></ul>
                <div><button type="button" class="autosave_close">닫기</button></div>
            </div>
            <?php } ?>
            <?php } ?>
        </div>
        
    </div>

    <div class="bo_w_info write_div" style="margin-top:5px;">
    <?php if ($is_name) { ?>
        <label for="wr_name" class="sound_only">이름<strong>필수</strong></label>
        <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input required" placeholder="이름">
    <?php } ?>

    <?php if ($is_password) { ?>
        <label for="wr_password" class="sound_only">비밀번호<strong>필수</strong></label>
        <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input <?php echo $password_required ?>" placeholder="비밀번호">
    <?php } ?>

    <?php if ($is_email) { ?>
            <label for="wr_email" class="sound_only">이메일</label>
            <input type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input email " placeholder="이메일">
    <?php } ?>
    </div>

    <?php if ($is_homepage) { ?>
    <div class="write_div" style="display:none;">
        <label for="wr_homepage" class="sound_only">홈페이지</label>
        <input type="text" name="wr_homepage" value="<?php echo $homepage ?>" id="wr_homepage" class="frm_input full_input" size="50" placeholder="홈페이지">
    </div>
    <?php } ?>

    <?php if ($option) { ?>
    <div class="write_div" style="display:<?=(auth_check($auth[$sub_menu],'d',1))?'none':''?>;margin-top:5px;">
        <span class="sound_only">옵션</span>
        <?php echo $option ?>
    </div>
    <?php } ?>

    <div class="write_div" style="margin-top:5px;">
        <label for="wr_content" class="sound_only">내용<strong>필수</strong></label>
        <div class="wr_content <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
            <?php if($write_min || $write_max) { ?>
            <!-- 최소/최대 글자 수 사용 시 -->
            <p id="char_count_desc">이 게시판은 최소 <strong><?php echo $write_min; ?></strong>글자 이상, 최대 <strong><?php echo $write_max; ?></strong>글자 이하까지 글을 쓰실 수 있습니다.</p>
            <?php } ?>
            <?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
            <?php if($write_min || $write_max) { ?>
            <!-- 최소/최대 글자 수 사용 시 -->
            <div id="char_count_wrap"><span id="char_count"></span>글자</div>
            <?php } ?>
        </div>
        
    </div>
    <div class="div_icons">
        <i class="fa fa-link"></i>
        <i class="fa fa-file-text-o"></i>
    </div>

    <?php for ($i=1; $is_link && $i<=G5_LINK_COUNT; $i++) { ?>
    <div class="bo_w_link write_div" style="display:<?=$link_display?>">
        <label for="wr_link<?php echo $i ?>"><i class="fa fa-link" aria-hidden="true"></i><span class="sound_only"> 링크  #<?php echo $i ?></span></label>
        <input type="text" name="wr_link<?php echo $i ?>" value="<?php if($w=="u"){echo$write['wr_link'.$i];} ?>" id="wr_link<?php echo $i ?>" class="frm_input full_input" size="50">
    </div>
    <?php } ?>

    <?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
    <div class="bo_w_flie write_div" style="display:<?=$file_display?>">
        <div class="file_wr write_div">
            <label for="bf_file_<?php echo $i+1 ?>" class="lb_icon"><i class="fa fa-download" aria-hidden="true"></i><span class="sound_only"> 파일 #<?php echo $i+1 ?></span></label>
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file ">
        </div>
        <?php if ($is_file_content) { ?>
        <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="full_input frm_input" size="50" placeholder="파일 설명을 입력해주세요.">
        <?php } ?>

        <?php if($w == 'u' && $file[$i]['file']) { ?>
        <span class="file_del">
            <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1"> <label for="bf_file_del<?php echo $i ?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?> 파일 삭제</label>
        </span>
        <?php } ?>
        
    </div>
    <?php } ?>


    <?php if ($is_use_captcha) { //자동등록방지  ?>
    <div class="write_div">
        <?php echo $captcha_html ?>
    </div>
    <?php } ?>

    <div class="btn_fixed_top">
            <a href="javascript:history.back();" class="btn_cancel btn" style="line-height:27px;background-color:#ddd;"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">취소</span></a>
            <?php if(G5_IS_ADMIN){ ?>
            <a href="<?php echo $list_href ?>" class="btn_list btn" style="border:1px solid #ccc;margin:0;line-height:27px;"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a>
            <?php } ?>
            <input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit btn">
    </div>
    </form>

</section>
<!-- } 게시물 작성/수정 끝 -->


<script>
$("#wr_6").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", minDate: "+0d" });


var g5_user_admin_url = "<?php echo G5_USER_ADMIN_URL;?>";
$(function(){
	// 고객찾기 버튼 클릭
	$("#btn_customer").click(function(e) {
		e.preventDefault();
		var url = g5_user_admin_url+"/customer_select.popup.php?frm=fwrite&file_name=<?php echo $g5['file_name']?>";
		winCustomerSelect = window.open(url, "winCustomerSelect", "left=300,top=150,width=720,height=600,scrollbars=1");
        winCustomerSelect.focus();
	});
    
    // 작업자 검색
    $("#mb_name_worker").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        memberwin.focus();
        return false;
    });
    
    // wr_content 높이 조정
    $('#wr_content').css('height','150px');

    // 링크 및 파일은 화면을 간략하게 하기 위해서 숨김
    $(document).on('click','.div_icons .fa-link',function(e){
        if( $('.bo_w_link').is(':hidden') ) {
            $('.bo_w_link').show();
        }
        else {
            $('.bo_w_link').hide();
        }
    });
    $(document).on('click','.div_icons i:last-child',function(e){
        if( $('.bo_w_flie').is(':hidden') ) {
            $('.bo_w_flie').show();
        }
        else {
            $('.bo_w_flie').hide();
        }
    });

});


<?php if($write_min || $write_max) { ?>
// 글자수 제한
var char_min = parseInt(<?php echo $write_min; ?>); // 최소
var char_max = parseInt(<?php echo $write_max; ?>); // 최대
check_byte("wr_content", "char_count");

$(function() {
    $("#wr_content").on("keyup", function() {
        check_byte("wr_content", "char_count");
    });
});

<?php } ?>
function html_auto_br(obj)
{
    if (obj.checked) {
        result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
        if (result)
            obj.value = "html2";
        else
            obj.value = "html1";
    }
    else
        obj.value = "";
}

function fwrite_submit(f)
{
    <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

    var subject = "";
    var content = "";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: {
            "subject": f.wr_subject.value,
            "content": f.wr_content.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data, textStatus) {
            subject = data.subject;
            content = data.content;
        }
    });

    if (subject) {
        alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
        f.wr_subject.focus();
        return false;
    }

    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        if (typeof(ed_wr_content) != "undefined")
            ed_wr_content.returnFalse();
        else
            f.wr_content.focus();
        return false;
    }

    if (document.getElementById("char_count")) {
        if (char_min > 0 || char_max > 0) {
            var cnt = parseInt(check_byte("wr_content", "char_count"));
            if (char_min > 0 && char_min > cnt) {
                alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
                return false;
            }
            else if (char_max > 0 && char_max < cnt) {
                alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                return false;
            }
        }
    }
    
    if(f.com_name.value=='') {
        alert("담당자찾기를 통해 담당자를 선택해 주세요.");
        return false;
    }
    

    <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}
</script>
