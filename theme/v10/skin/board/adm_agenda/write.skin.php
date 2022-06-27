<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<section id="bo_w">
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
    <input type="hidden" name="wr_agd_chkdt" value="<?php echo $write['wr_agd_chkdt'] ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <div class="top_tip">
        <p><span>확인일시: </span><strong><?php echo ($write['wr_agd_chkdt'])?$write['wr_agd_chkdt']:'0000-00-00 00:00:00'; ?></strong></p>
        <p>(확인일시는 책임자가 안건상태를 [완료]로 등록하는 일시를 의미합니다.)</p>
    </div>
    <?php
    $option = '';
    $option_hidden = '';
    if ($is_notice || $is_html || $is_secret || $is_mail) { 
        $option = '';
        if ($is_notice) {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="notice" name="notice"  class="selec_chk" value="1" '.$notice_checked.'>'.PHP_EOL.'<label for="notice"><span></span>공지</label></li>';
        }
        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            } else {
                $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="html" name="html" onclick="html_auto_br(this);" class="selec_chk" value="'.$html_value.'" '.$html_checked.'>'.PHP_EOL.'<label for="html"><span></span>html</label></li>';
            }
        }
        if ($is_secret) {
            if ($is_admin || $is_secret==1) {
                $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="secret" name="secret"  class="selec_chk" value="secret" '.$secret_checked.'>'.PHP_EOL.'<label for="secret"><span></span>비밀글</label></li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
        if ($is_mail) {
            $option .= PHP_EOL.'<li class="chk_box"><input type="checkbox" id="mail" name="mail"  class="selec_chk" value="mail" '.$recv_email_checked.'>'.PHP_EOL.'<label for="mail"><span></span>답변메일받기</label></li>';
        }
    }
    echo $option_hidden;
    ?>

    <?php if ($is_category) { ?>
    <div class="bo_w_select write_div">
        <label for="ca_name" class="sound_only">분류<strong>필수</strong></label>
        <select name="ca_name" id="ca_name" required>
            <option value="">분류를 선택하세요</option>
            <?php echo $category_option ?>
        </select>
    </div>
    <?php } ?>
	
    <?php if (false){ //($option) { ?>
    <div class="write_div">
        <span class="sound_only">옵션</span>
        <ul class="bo_v_option">
        <?php echo $option ?>
        </ul>
    </div>
    <?php } ?>
    
    <ul class="ul_con">
        <li class="li_first">
            <label for="wr_1">부서선택</label><br>
            <select name="wr_1" id="wr_1" title="부서선택" class="">
                <option value="">부서선택</option>
                <?php echo $g5['set_department_name_value_options']?>
            </select>
            <script>
            $('select[name=wr_1]').val("<?=$write['wr_1']?>").attr('selected','selected');
            </script>
        </li>
        <li>
            <label for="mb_id_worker">책임자선택</label><br>
            <input type="hidden" name="wr_2" id="mb_id_worker" value="<?=$write['wr_2']?>">
            <?php $mb_pic = get_table_meta('member','mb_id',$write['wr_2']); ?>
            <input type="text" name="wr_3" id="mb_name" value="<?=$mb_pic['mb_name']?>" readonly required class="frm_input readonly required" style="width:100px;">
            <a href="javascript:" link="./_win_worker_select.php" class="btn btn_02 pic_select">찾기</a>
            <script>
            $('.pic_select').on('click',function(){
                var href = $(this).attr('link');
                var win_wrk_select = window.open(href, "win_wrk_select", "left=10,top=10,width=500,height=800");
                win_wrk_select.focus();
                return false;
            });    
            </script>
        </li>
        <?php //if(false){ ?>
        <li class="li_last">
            <label for="wr_4">안건상태</label><br>
            <select name="wr_4" id="wr_4" title="안건상태" class="">
                <?php echo $g5['set_agenda_status_value_options']?>
            </select>
            <script>
            <?php if($w == ''){ ?>
            $('select[name=wr_4').val("pending").attr('selected','selected');
            <?php } else { ?>
            $('select[name=wr_4]').val("<?=$write['wr_4']?>").attr('selected','selected');
            <?php } ?>
            </script>
        </li>
        <?php //} ?>       
    </ul>

    <div class="bo_w_tit write_div">
        <label for="wr_subject">안건제목<strong class="sound_only">필수</strong></label>
        <?php
        $wrreadonly = ($w != '' && $member['mb_id'] != $write['mb_id']) ? ' readonly' : '';//($member['mb_level'] < 8 && $member['mb_id'] != $write['mb_id']) ? ' readonly' : '';
        ?>
        <div id="autosave_wrapper" class="write_div"  style="margin-top:0;">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required<?=$wrreadonly?> class="frm_input full_input required<?=$wrreadonly?>" size="50" maxlength="255" placeholder="제목">
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
        </div>
        
    </div>

    <div class="write_div">
        <label for="wr_content">안건내용<strong class="sound_only">필수</strong></label>
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
        <?php if($u != '' && $member['mb_id'] != $write['mb_id']){ //($member['mb_level'] < 8 && $member['mb_id'] != $write['mb_id']){ ?>
        <script>
        if($('textarea#wr_content').length > 0){
            $('textarea#wr_content').attr('readonly',true).addClass('readonly');
            $('.pic_select').hide();
        }
        </script>
        <?php } ?>
    </div>

    <div class="write_div">
        <label for="wr_5">계획내용</label>
        <div style="text-align:left !important;">
            <p>안건요청에 대해서 "답변/수정"페이지로 들어가 계획내용의 답변을 작성하고 안건상태를 "<span style="font-weight:bold;color:#000;">완료</span>"로 해 주세요.</p>
            <p>계획내용의 답변을 작성할 수 없는 상황이면 안건상태를 "<span style="font-weight:bold;color:blue;">확인중</span>"으로 해 주세요.</p>
        </div>
        <textarea name="wr_5" id="wr_5" style="width:100%;height:250px;" maxlength="65536"><?=$write['wr_5']?></textarea>
    </div>

    <div class="multifile">
        <?php echo help("참고 할 자료가 있으면 등록하고 관리해 주시면 됩니다."); ?>
        <input type="file" id="bo_ref_file" name="bo_ref_files[]" multiple class="">
        <?php
        if(@count($row['bo_f_ref'])){
            echo '<ul>'.PHP_EOL;
            for($i=0;$i<count($row['bo_f_ref']);$i++) {
                echo "<li>[".($i+1).']'.$row['bo_f_ref'][$i]['file']."</li>".PHP_EOL;
            }
            echo '</ul>'.PHP_EOL;
        }
        ?>
        <script>
        //기초자료 멀티파일
        $('#bo_ref_file').MultiFile();
        </script>
    </div>


    <?php if ($is_use_captcha) { //자동등록방지  ?>
    <div class="write_div">
        <?php echo $captcha_html ?>
    </div>
    <?php } ?>
    

    <div class="btn_confirm write_div">
        <a href="<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=<?=$bo_table?>&amp;<?=$qstr?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn">작성완료</button>
    </div>
    </form>

    <script>
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

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->