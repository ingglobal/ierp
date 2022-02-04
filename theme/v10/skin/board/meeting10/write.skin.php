<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/write.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
//print_r2($g5);

if($w == 'u'){
    //관련파일 추출
	$sql = " SELECT * FROM {$g5['file_table']} 
    WHERE fle_db_table = 'g5_write_{$bo_table}' AND fle_type = '{$bo_table}' AND fle_db_id = '".$wr_id."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $row['file_'.$bo_table] = array();
    $row['file_'.$bo_table.'_fidxs'] = array(); //여러파일번호(fle_idx) 목록이 담긴 배열
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($row['file_'.$row2['fle_type']],array('file'=>$file_down_del));
        @array_push($row['file_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
    }
    //견적서파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
    //if(@count($row['prj_quot_fidxs'])) $row['prj_qf_lst_idx'] = $row['prj_quot_fidxs'][0];
}
?>

<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
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
textarea#wr_content{min-height:600px;}
textarea#wr_link1{min-height:200px;}
/*멀티파일관련*/
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#ddd;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{font-size:14px;border:1px solid #ccc;background:#eee;padding:2px 5px;border-radius:3px;line-height:1.2em;}
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
    <input type="hidden" name="tmp_save" value="">

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
                    <input type="hidden" name="com_idx" id="com_idx" value="<?=$write['wr_2']?>" class="frm_input" style="width:60px;">
                    <input type="text" id="com_name" name="com_name" value="<?=$write['wr_1']?>" readonly class="frm_input readonly" style="width:120px;">
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
                <?php if ($is_category) { ?>
                <th>회의분류</th>
                <td>
                    <label for="ca_name"  class="sound_only">분류<strong>필수</strong></label>
                    <select name="ca_name" id="ca_name" required>
                        <option value="">분류를 선택하세요</option>
                        <?php echo $category_option ?>
                    </select>
                </td>
                <script>
                $('#ca_name option[value="공지"]').remove();    
                </script>
                <?php } ?>
                <th>회의날짜</th>
                <td<?=(($is_category) ? '':' 3')?>><input type="text" name="wr_homepage2" value="<?php echo $write['wr_homepage'] ?>" id="wr_homepage" readonly class="frm_input readonly"></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="bo_w_tit write_div">
        <label for="wr_subject">회의제목<strong class="sound_only">필수</strong></label>
        
        <div id="autosave_wrapper write_div">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" size="50" maxlength="255" placeholder="회의제목">
        </div>
        
    </div>

    <div class="write_div">
        <label for="wr_content">회의내용<strong class="sound_only">필수</strong></label>
        <textarea name="wr_content" id="wr_content"><?=$write['wr_content']?></textarea>

        <label for="wr_link1">참석자</label>
        <textarea name="wr_link1" id="wr_link1"><?=$write['wr_link1']?></textarea>
    </div>

    <?php if(false){ //for ($i=0; $is_file && $i<$file_count; $i++) { ?>
    <div class="bo_w_flie write_div">
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
    
    <!--//멀티파일업로드-->
    <div class="bo_w_multifile write_div">
        <?php echo help("회의록관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
        <input type="file" id="multi_file_mt" name="<?=$bo_table?>_datas[]" multiple class="">
        <?php
        if(@count($row['file_'.$bo_table])){
            echo '<ul>'.PHP_EOL;
            for($i=0;$i<count($row['file_'.$bo_table]);$i++) {
                echo "<li>[".($i+1).']'.$row['file_'.$bo_table][$i]['file']."</li>".PHP_EOL;
            }
            echo '</ul>'.PHP_EOL;
        }
        ?>
    </div>

    <?php if ($is_use_captcha) { //자동등록방지  ?>
    <div class="write_div">
        <?php echo $captcha_html ?>
    </div>
    <?php } ?>


    <div class="btn_fixed_top">
        <input type="submit" value="임시저장" id="btn_submit" accesskey="s" onclick="document.pressed=this.value" class="btn btn_03">
        <a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>&<?=$qstr?>" class="btn_cancel btn" style="background:#9eacc6;color:#fff;line-height:26px;">취소</a>
        <input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit btn">
    </div>
    </form>

</section>
<!-- } 게시물 작성/수정 끝 -->


<script>
$("#wr_homepage").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

//견적서 멀티파일
$('#multi_file_mt').MultiFile();

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
    //$('#wr_content').css('height','150px');

   $('#ca_name').on('change',function(){
       if($(this).val() == '내부회의'){
            $('#com_idx').val('');
            $('#com_name').val('');
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
    

    
    <?php //echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>
    <?php //echo get_editor_js('wr_link1'); ?>
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
    
    if(f.ca_name.value=='외부회의' && f.com_name.value=='') {
        alert("[외부회의]일 경우에는 업체를 선택해 주세요.");
        f.com_name.focus();
        return false;
    }
    

    if(f.wr_homepage2.value=='') {
        alert("회의날짜를 입력하세요.");
        f.wr_homepage2.focus();
        return false;
    }
    
    if(document.pressed == '임시저장'){
        f.tmp_save.value = 1;
    }
    <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}
</script>
