<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/write.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
//print_r2($g5);
?>
<style>
.customer_info {font-size:1.4em;border:solid 1px #666;margin-top:10px;background: #dedede;padding:10px;}
.div_com_president {font-size:0.7em;}
#wr_content {padding:5px;line-height:1.5em;}
#wr_subject {padding-left:5px;}
.btn_cke_sc{display:none;}

.tbl_info{}
.tbl_info table{display:table;border-collapse:collapse;border-spacing:0;}
.tbl_info table th{background:#f1f1f1;}
.tbl_info table th,.tbl_info table td{border-top:1px solid #ddd;border-bottom:1px solid #ddd;}
.tbl_info table td{}
.tbl_info table td input[type="text"]{padding:0 10px;}

.date_box{position:relative;display:inline-block;}
.date_box i{position:absolute;cursor:pointer;top:-5px;right:-10px;font-size:1.2em;}
</style>

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
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="fr_date" value="<?php echo $fr_date ?>">
    <input type="hidden" name="to_date" value="<?php echo $to_date ?>">
    <input type="hidden" name="pl_date" value="<?php echo $pl_date ?>">
    <input type="hidden" name="sch_wr_10" value="<?php echo $sch_wr_10 ?>">
    <input type="hidden" name="sch_mb_asign_worker" value="<?php echo $sch_mb_asign_worker ?>">
    <input type="hidden" name="sch_wr_5" value="<?php echo $sch_wr_5 ?>">


    <!-- 담당자찾기 -->
    <!--div class="write_div">
        <label for="wr_cart" class="sound_only">상품</label>
        <input type="hidden" name="cmm_idx" value="<?=$write['cmm_idx']?>" msg="업체-고객 번호">
        <input type="hidden" name="com_idx" value="<?=$write['wr_2']?>" msg="업체번호">
        <input type="hidden" name="com_name" value="<?=$write['wr_1']?>" msg="업체명">
        <input type="hidden" name="mb_id_customer" value="<?=$write['wr_3']?>" msg="고객아이디">
        <input type="hidden" name="mb_name_customer" value="<?=$write['wr_4']?>" msg="고객명">
        <div style="display:<?php if($w=='u'&&!$member['mb_manager_yn']) echo 'none';?>;">
            <button type="button" class="btn btn_b01" id="btn_customer">담당자<?=($w=='u')?'변경':'찾기'?></button>
            담당자가 존재하지 않는 경우 업체등록 및 담당자등록을 먼저 하신 후 AS등록을 할 수 있습니다. <a href="<?=G5_USER_ADMIN_URL?>/member_list.php">[담당자등록바로가기]</a>
        </div>
        
        <div class="customer_info" style="display:<?=(!$write['customer_info'])?'none':''?>;" msg="고객(업체)정보">
            <?=$write['customer_info']?>
        </div>
    </div-->

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


    $g5_time_ymdhis = str_replace('-','',G5_TIME_YMDHIS);
    $g5_time_ymdhis = str_replace(' ','-',$g5_time_ymdhis);
    $g5_time_ymdhis = str_replace(':','',$g5_time_ymdhis);
    $reg_num = ($w == '') ? $g5_time_ymdhis : $write['wr_twitter_user'];
    ?>
    <div class="tbl_info">
        <table>
            <tbody>
            <tr>
                <th>업체선택</th>
                <td>
                    <input type="hidden" name="com_idx" id="com_idx" value="<?=$write['wr_2']?>" required class="frm_input required" style="width:60px;">
                    <input type="text" id="com_name" name="com_name" value="<?=$write['wr_1']?>" readonly required class="frm_input readonly required" style="width:120px;">
                    <a href="javascript:" link="<?=G5_USER_ADMIN_URL?>/_win_company_select.php" class="btn btn_02 com_select">업체선택</a>
                    <script>
                    $('.com_select').on('click',function(){
                        var href = $(this).attr('link');
                        var win_com_name = window.open(href,"win_com_select","width=400,height=640");
                        win_com_select.focus();
                        return false;
                    });
                    </script>
                </td>
                <th>업체담당자</th>
                <td><input type="text" name="wr_4" value="<?php echo $write['wr_4'] ?>" id="wr_4" class="frm_input"></td>
            </tr>
            <tr>
                <th>접수일</th>
                <td>
                    <div class="date_box">
                        <input type="text" name="wr_homepage2" value="<?php echo $write['wr_homepage'] ?>" id="wr_homepage" readonly required class="frm_input readonly required">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                    </div>
                </td>
                <th>접수번호</th>
                <td><input type="text" name="wr_twitter_user2" value="<?php echo $reg_num ?>" id="wr_twitter_user" readonly required class="frm_input required"></td>
            </tr>
            <tr>
                <th>방문예정일</th>
                <td>
                    <div class="date_box">
                        <input type="text" name="wr_facebook_user2" value="<?php echo $write['wr_facebook_user'] ?>" id="wr_facebook_user" readonly class="frm_input readonly">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                    </div>
                </td>
                <th>AS담당자</th>
                <td>
                    <label for="wr_5" class="sound_only">작업자</label>
                    <input type="text" name="wr_5" href="<?php echo G5_USER_ADMIN_URL?>/employee_select.php?frm=fwrite&tar1=wr_6&tar2=wr_5" value="<?php echo $write['wr_5'] ?>" id="wr_5" readonly class="frm_input" placeholder="AS담당자">
                    <input type="hidden" name="wr_6" id="wr_6" value="<?=$write['wr_6']?>"><!-- 작업자아이디 -->
                    <!--input type="text" name="mb_name_worker" id="mb_name_worker" href="<?php //echo G5_USER_ADMIN_URL?>/employee_select.php?frm=fwrite&tar1=wr_6&tar2=wr_5&tar3=trm_idx_department_worker" value="<?php //echo $write['mb_name_worker'] ?>" id="mb_name_worker" class="frm_input" placeholder="작업자" readonly-->
                </td>
            </tr>
            <tr style="<?=(($member['mb_6'] == 1 || $member['mb_level'] >= 8) ? '' : 'display:none;')?>">
                <th>계산서발행일</th>
                <td>
                    <div class="date_box">
                        <input type="text" name="wr_8" value="<?php echo $write['wr_8'] ?>" id="wr_8" readonly class="frm_input readonly">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                    </div>
                </td>
                <th>비용</th>
                <td><input type="text" name="wr_7" value="<?php echo number_format($write['wr_7']) ?>" id="wr_7" class="frm_input" style="text-align:right;"></td>
            </tr>
            <tr>
                <th>접수경로</th>
                <td>
                    <label for="wr_9" class="sound_only">작업경로</label>
                    <select name="wr_9" id="wr_9" class="frm_input" style="width:170px;">
                        <option value="">접수경로를 선택하세요</option>
                        <?php echo $g5['set_as_receiptpath_value_options'] ?>
                    </select>
                    <script>$('select[name=wr_9]').val('<?php echo $write['wr_9'] ?>').attr('selected','selected');</script>
                </td>
                <th>처리상태</th>
                <td>
                    <label for="wr_10" class="sound_only">처리상태</label>
                    <select name="wr_10" id="wr_10" class="frm_input" style="width:170px;">
                        <option value="">처리상태를 선택하세요</option>
                        <?php echo $g5['set_as_status_value_options'] ?>
                    </select>
                    <script>$('select[name=wr_10]').val('<?php echo $write['wr_10'] ?>').attr('selected','selected');</script>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php if ($is_category) { ?>
    <div class="bo_w_select write_div">
        <label for="ca_name"  class="sound_only">분류<strong>필수</strong></label>
        <select name="ca_name" id="ca_name" required>
            <option value="">분류를 선택하세요</option>
            <?php echo $category_option ?>
        </select>
    </div>
    <?php } ?>

    <div class="bo_w_tit write_div">
        <label for="wr_subject">AS접수제목<strong class="sound_only">필수</strong></label>
        
        <div id="autosave_wrapper write_div">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" size="50" maxlength="255" placeholder="AS접수제목">
        </div>
        
    </div>

    <div class="bo_w_info write_div">
    <?php if (false){//($is_name) { ?>
        <label for="wr_name" class="sound_only">이름<strong>필수</strong></label>
        <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input required" placeholder="이름">
    <?php } ?>

    <?php if (false){//($is_password) { ?>
        <label for="wr_password" class="sound_only">비밀번호<strong>필수</strong></label>
        <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input <?php echo $password_required ?>" placeholder="비밀번호">
    <?php } ?>

    <?php if (false){//($is_email) { ?>
            <label for="wr_email" class="sound_only">이메일</label>
            <input type="text" name="wr_email" value="<?php echo $email ?>" id="wr_email" class="frm_input email " placeholder="이메일">
    <?php } ?>
    </div>

    <?php if(false){// ($is_homepage) { ?>
    <div class="write_div" style="display:none;">
        <label for="wr_homepage" class="sound_only">홈페이지</label>
        <input type="text" name="wr_homepage" value="<?php echo $homepage ?>" id="wr_homepage" class="frm_input full_input" size="50" placeholder="홈페이지">
    </div>
    <?php } ?>

    <?php if (false){//($option) { ?>
    <div class="write_div" style="display:<?=(auth_check($auth[$sub_menu],'d',1))?'none':''?>">
        <span class="sound_only">옵션</span>
        <?php echo $option ?>
    </div>
    <?php } ?>

    <div class="write_div">
        <label for="wr_content">AS접수내용<strong class="sound_only">필수</strong></label>
        <textarea name="wr_content" id="wr_content"><?=$write['wr_content']?></textarea>

        <label for="wr_link1">AS조치내용</label>
        <textarea name="wr_link1" id="wr_link1"><?=$write['wr_link1']?></textarea>
    </div>
    

    <?php for ($i=0; $is_file && $i<$file_count; $i++) { ?>
    <div class="bo_w_flie write_div">
        <div class="file_wr write_div">
            <label for="bf_file_<?php echo $i+1 ?>" class="lb_icon"><i class="fa fa-download" aria-hidden="true"></i><span class="sound_only"> 파일 #<?php echo $i+1 ?></span></label>
            <input type="file" name="bf_file[]" id="bf_file_<?php echo $i+1 ?>" title="파일첨부 <?php echo $i+1 ?> : 용량 <?php echo $upload_max_filesize ?> 이하만 업로드 가능" class="frm_file ">
        </div>
        <?php if ($is_file_content) { ?>
        <input type="text" name="bf_content[]" value="<?php echo ($w == 'u') ? $file[$i]['bf_content'] : ''; ?>" title="파일 설명을 입력해주세요." class="full_input frm_input" size="50" placeholder="파일 설명을 입력해주세요.">
        <?php } ?>

        <?php if($w == 'u' && $file[$i]['file']) { 
        //   print_r2($file[$i]);  
        ?>
        <span class="file_del">
            <input type="checkbox" id="bf_file_del<?php echo $i ?>" name="bf_file_del[<?php echo $i;  ?>]" value="1"> <label for="bf_file_del<?php echo $i ?>"> 파일 삭제</label>
            <a href="<?=$file[$i]['href']?>"><?php echo $file[$i]['source'].'('.$file[$i]['size'].')';  ?></a>
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
        <a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>&<?=$qstr?>" class="btn_cancel btn" style="background:#9eacc6;color:#fff;line-height:26px;">취소</a>
        <input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit btn">
    </div>
    </form>

</section>
<!-- } 게시물 작성/수정 끝 -->


<script>
$("#wr_homepage").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
$("#wr_facebook_user").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
$("#wr_8").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name=wr_7]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});


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
    //$("#mb_name_worker").click(function() {
    $("#wr_5").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        memberwin.focus();
        return false;
    });
    
    // wr_content 높이 조정
    $('#wr_content').css('height','150px');

   
    $('.date_box i').on('click',function(){
        $(this).siblings('input').val('');
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
    <?php echo get_editor_js('wr_link1'); ?>
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
    
    if(f.wr_homepage.value=='') {
        alert("접수일을 입력하세요.");
        f.wr_homepage.focus();
        return false;
    }
    
    //if(f.wr_facebook_user.value=='') {
    //    alert("방문예정일을 입력하세요.");
    //    f.wr_facebook_user.focus();
    //    return false;
    //}
    
    if(f.wr_twitter_user.value=='') {
        alert("접수번호 입력하세요.");
        f.wr_twitter_user.focus();
        return false;
    }
    
    //if(f.wr_5.value=='') {
    //    alert("AS담당자를 입력하세요.");
    //    f.wr_5.focus();
    //    return false;
    //}
    
    if(f.wr_9.value=='') {
        alert("접수경로를 선택하세요.");
        f.wr_9.focus();
        return false;
    }
    
    if(f.wr_10.value=='') {
        alert("상태값을 선택하세요.");
        f.wr_10.focus();
        return false;
    }

    <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}
</script>
