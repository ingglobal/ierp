<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script>
// 글자수 제한
var char_min = parseInt(<?php echo $comment_min ?>); // 최소
var char_max = parseInt(<?php echo $comment_max ?>); // 최대
</script>

<?php if ($is_comment_write) {
        if($w == '')
            $w = 'c';
?>
<aside id="bo_vc_w">
    <h2>댓글쓰기</h2>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off" class="bo_vc_w">
    <input type="hidden" name="w" value="<?php echo $w ?>" id="w">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="comment_id" value="<?php echo $c_id ?>" id="comment_id">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="is_good" value="">
    <div id="inf">
        <div class="inf_in inf_left">
            <div>
                <label for="wr_2" class="">정비날짜</label>
                <input type="text" name="wr_2" id="wr_2" value="" class="frm_input">
            </div>
            <div>
                <label for="wr_3" class="">주행거리</label>
                <input type="text" name="wr_3" id="wr_3" value="" class="frm_input">&nbsp;km
            </div>
            <div>
                <label for="wr_4" class="">정비비용</label>
                <input type="text" name="wr_4" id="wr_4" value="" class="frm_input">&nbsp;원
            </div>
            <div>
                <label for="wr_5" class="">담당</label>
                <input type="text" name="wr_5" id="wr_5" value="<?=$member['mb_name']?>" class="frm_input">
            </div>
            <div>
                <label for="parent_wr_1" class="">다음예정일(공통)</label>
                <input type="text" name="parent_wr_1" id="parent_wr_1" value="" class="frm_input">
            </div>
        </div>
        <div class="inf_in inf_right">
            <label for="wr_content">관리내용</label>
            <?php if (false){ //($comment_min || $comment_max) { ?><strong id="char_cnt"><span id="char_count"></span>글자</strong><?php } ?>
            <input type="text" name="wr_content" id="wr_content" value="" class="frm_input">
			
            <script>
            $(function(){
                $(document).on("keyup change", "textarea#wr_content[maxlength],textarea#wr_6[maxlength]", function() {
                    var str = $(this).val()
                    var mx = parseInt($(this).attr("maxlength"))
                    if (str.length > mx) {
                        $(this).val(str.substr(0, mx));
                        return false;
                    }
                });
                
                $("input[name=wr_2],input[name=parent_wr_1]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

                // 가격 입력 쉼표 처리
                $(document).on( 'keyup','input[name=wr_3],input[name=wr_4]',function(e) {
                    if(!isNaN($(this).val().replace(/,/g,'')))
                        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
                });
            });
            </script>
        </div>
    </div>     
    <div class="bo_vc_w_wr">
        <div class="btn_confirm">
            <button type="submit" id="btn_submit" class="btn_submit">정비내역등록</button>
        </div>
    </div>
    </form>
</aside>
<?php } ?>

<!-- 댓글 리스트 -->
<section id="bo_vc">
    <h2 class="bo_vc_tit">관리내역 <span><?php echo $view['wr_comment']; ?></span></h2>
    <?php
    for ($i=0; $i<count($list); $i++) {
        $comment_id = $list[$i]['wr_id'];
        $cmt_depth = ""; // 댓글단계
        $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 15;
        $str = $list[$i]['content'];
        $memo = $list[$i]['wr_6'];
        // if (strstr($list[$i]['wr_option'], "secret"))
            // $str = $str;
		$str = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $str);
        $memo = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $memo);
		$c_reply_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w';
		$c_edit_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
        $is_comment_reply_edit = ($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) ? 1 : 0;
    ?>
    <article id="c_<?php echo $comment_id ?>" <?php if ($cmt_depth) { ?>style="margin-left:<?php echo $cmt_depth ?>px;border-bottom-color:#f8f8f8"<?php } ?>>
        <div class="comment_inner">
            <header>
                <h2><?php echo get_text($list[$i]['wr_name']); ?>님의 댓글<?php if ($cmt_depth) { ?><span class="sound_only">의 댓글</span><?php } ?></h2>
                <?php echo $list[$i]['name'] ?>
                <?php if (false){ //($is_ip_view) { ?>
                <span class="sound_only">아이피</span>
                <span class="bo_vc_hdinfo">(<?php echo $list[$i]['ip']; ?>)</span>
                <?php } ?>
                <span class="sound_only">작성일</span>
                <span class="bo_vc_hdinfo"><i class="fa fa-clock-o" aria-hidden="true"></i> <time datetime="<?php echo date('Y-m-d\TH:i:s+09:00', strtotime($list[$i]['datetime'])) ?>"><?php echo $list[$i]['datetime'] ?></time></span>
                <?php
                //include(G5_SNS_PATH."/view_comment_list.sns.skin.php");
                ?>
                <?php if( $is_comment_reply_edit ){ ?>
                <div class="bo_vl_opt">
                    <button type="button" class="btn_cm_opt btn_b03 btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">댓글 옵션</span></button>
                    <ul class="bo_vc_act">
                        <?php if (false){ //($list[$i]['is_reply']) { ?><li><a href="<?php echo $c_reply_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'c'); return false;">답변</a></li><?php } ?>
                        <?php if ($list[$i]['is_edit']) { ?><li><a href="<?php echo $c_edit_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'cu'); return false;">수정</a></li><?php } ?>
                        <?php if ($list[$i]['is_del']) { ?><li><a href="<?php echo $list[$i]['del_link']; ?>" onclick="return comment_delete();">삭제</a></li><?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <script>
                $(function() {			    
                    // 댓글 옵션창 열기
                    $(".btn_cm_opt").on("click", function(){
                        $(this).parent("div").children(".bo_vc_act").show();
                    });
                            
                    // 댓글 옵션창 닫기
                    $(document).mouseup(function (e){
                        var container = $(".bo_vc_act");
                        if( container.has(e.target).length === 0)
                        container.hide();
                    });
                });
                </script>
            </header>
            <div class="cmt_contents">
                <!-- 댓글 출력 -->
                <ul>
                    <li>
                        <h4>정비날짜</h4>
                        <p><?=$list[$i]['wr_2']?></p>
                    </li>
                    <li>
                        <h4>주행거리</h4>
                        <p><?=number_format($list[$i]['wr_3'])?>km</p> 
                    </li>
                    <?php if($list[$i]['wr_4']){ ?>
                    <li>
                        <h4>정비비용</h4>
                        <p><?=number_format($list[$i]['wr_4'])?>원</p>
                    </li>
                    <?php } ?>
                    <li>
                        <h4>담당자</h4>
                        <p><?=$list[$i]['wr_5']?></p>
                    </li>
                    <?php if($view['wr_1']){ ?>
                    <li>
                        <h4>다음예정일(공통)</h4>
                        <p><?=$view['wr_1']?></p>
                    </li>
                    <?php } ?>
                </ul>
                <h4>관리내용</h4>
                <p>
                    <?php if (strstr($list[$i]['wr_option'], "secret")) echo "<img src=\"".$board_skin_url."/img/icon_secret.gif\" alt=\"비밀글\">"; ?>
                    <?php echo $str ?>
                </p>
                <?php if($list[$i]['wr_6']){ ?>
                <h4>메모</h4>
                <p><?php echo $memo; ?></p>
                <?php } ?>
                <?php if($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) {
                    if($w == 'cu') {
                        $sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '$c_id' and wr_is_comment = '1' ";
                        $cmt = sql_fetch($sql);
                        if (!($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id'])))
                            $cmt['wr_content'] = '';
                        $c_wr_content = $cmt['wr_content'];
                    }
                ?>
                <?php } ?>
            </div>
                <span id="edit_<?php echo $comment_id ?>"></span><!-- 수정 -->
                <span id="reply_<?php echo $comment_id ?>"></span><!-- 답변 -->
            <input type="hidden" id="secret_comment_<?php echo $comment_id ?>" value="<?php echo strstr($list[$i]['wr_option'],"secret") ?>">
            <input type="hidden" value="<?php echo $list[$i]['wr_2'] ?>" id="save_wr_2_<?php echo $comment_id ?>">
	        <input type="hidden" value="<?php echo number_format($list[$i]['wr_3']); ?>" id="save_wr_3_<?php echo $comment_id ?>">
	        <input type="hidden" value="<?php echo number_format($list[$i]['wr_4']); ?>" id="save_wr_4_<?php echo $comment_id ?>">
	        <input type="hidden" value="<?php echo $list[$i]['wr_5'] ?>" id="save_wr_5_<?php echo $comment_id ?>">
            <textarea id="save_comment_<?php echo $comment_id ?>" style="display:none"><?php echo get_text($list[$i]['content1'], 0) ?></textarea>
            <textarea id="save_memo_<?php echo $comment_id ?>" style="display:none"><?php echo get_text($list[$i]['wr_6'], 0) ?></textarea>
        </div>
    </article>
    <?php } ?>
    <?php if ($i == 0) { //댓글이 없다면 ?><p id="bo_vc_empty">등록된 관리내역이 없습니다.</p><?php } ?>

</section>

<?php if ($is_comment_write) {
        if($w == '')
            $w = 'c';
    ?>
    <script>
    var save_before = '';
    var save_html = document.getElementById('bo_vc_w').innerHTML;

    function good_and_write()
    {
        var f = document.fviewcomment;
        if (fviewcomment_submit(f)) {
            f.is_good.value = 1;
            f.submit();
        } else {
            f.is_good.value = 0;
        }
    }

    function fviewcomment_submit(f)
    {
        var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
        var dt_format = RegExp(/\d{4}-\d{2}-\d{2}/);
        f.is_good.value = 0;

        /*
        var s;
        if (s = word_filter_check(document.getElementById('wr_content').value))
        {
            alert("내용에 금지단어('"+s+"')가 포함되어있습니다");
            document.getElementById('wr_content').focus();
            return false;
        }
        */

        var subject = "";
        var content = "";
        $.ajax({
            url: g5_bbs_url+"/ajax.filter.php",
            type: "POST",
            data: {
                "subject": "",
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

        if (content) {
            alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
            f.wr_content.focus();
            return false;
        }

        // 양쪽 공백 없애기
        var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
        document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
        if (char_min > 0 || char_max > 0)
        {
            check_byte('wr_content', 'char_count');
            var cnt = parseInt(document.getElementById('char_count').innerHTML);
            if (char_min > 0 && char_min > cnt)
            {
                alert("댓글은 "+char_min+"글자 이상 쓰셔야 합니다.");
                return false;
            } else if (char_max > 0 && char_max < cnt)
            {
                alert("댓글은 "+char_max+"글자 이하로 쓰셔야 합니다.");
                return false;
            }
        }
        else if (!document.getElementById('wr_content').value)
        {
            alert("댓글을 입력하여 주십시오.");
            return false;
        }

        if (typeof(f.wr_name) != 'undefined')
        {
            f.wr_name.value = f.wr_name.value.replace(pattern, "");
            if (f.wr_name.value == '')
            {
                alert('이름이 입력되지 않았습니다.');
                f.wr_name.focus();
                return false;
            }
        }

        if (typeof(f.wr_password) != 'undefined')
        {
            f.wr_password.value = f.wr_password.value.replace(pattern, "");
            if (f.wr_password.value == '')
            {
                alert('비밀번호가 입력되지 않았습니다.');
                f.wr_password.focus();
                return false;
            }
        }

        if (!f.wr_2.value)
        {
            alert("정비날짜를 입력하여 주십시오.");
            f.wr_2.focus();
            return false;
        }
        else{
            if(!dt_format.test(f.wr_2.value)){
                alert('정비날짜의 데이터가 날짜형식에 맞지 않습니다.');
                f.wr_2.value = '';
                f.wr_2.focus();
                return false;
            }
        }
        
        if (!f.wr_3.value)
        {
            alert("주행거리를 입력하여 주십시오.");
            f.wr_3.focus();
            return false;
        }

        if (!f.wr_5.value)
        {
            alert("담당자의 이름을 입력하여 주십시오.");
            f.wr_5.focus();
            return false;
        }

        if(f.parent_wr_1.value){
            if(!dt_format.test(f.parent_wr_1.value)){
                alert('다음예정일 데이터가 날짜형식에 맞지 않습니다.');
                f.parent_wr_1.value == '';
                f.parent_wr_1.focus();
                return false;
            }
        }else{
            alert('다음 오일교환/정비등의 예정일을 반드시 입력하세요.');
            f.parent_wr_1.focus();
            return false;
        }

        <?php if($is_guest) echo chk_captcha_js(); ?>

        set_comment_token(f);

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }

    function comment_box(comment_id, work)
    {
        var el_id,
        form_el = 'fviewcomment',
        respond = document.getElementById(form_el);

        // 댓글 아이디가 넘어오면 답변, 수정
        if (comment_id)
        {
            if (work == 'c')
                el_id = 'reply_' + comment_id;
            else
                el_id = 'edit_' + comment_id;
        }
        else
            el_id = 'bo_vc_w';

        if (save_before != el_id)
        {
            if (save_before)
            {
                document.getElementById(save_before).style.display = 'none';
            }

            document.getElementById(el_id).style.display = '';
            document.getElementById(el_id).appendChild(respond);
            //입력값 초기화
            document.getElementById('wr_content').value = '';

            // 댓글 수정
            if (work == 'cu')
            {
                document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
                document.getElementById('wr_2').value = document.getElementById('save_wr_2_' + comment_id).value;
                document.getElementById('wr_3').value = document.getElementById('save_wr_3_' + comment_id).value;
                document.getElementById('wr_4').value = document.getElementById('save_wr_4_' + comment_id).value;
                document.getElementById('wr_5').value = document.getElementById('save_wr_5_' + comment_id).value;
                document.getElementById('parent_wr_1').value = '<?=$view['wr_1']?>';
                $('#btn_submit').text('정비내역수정');
                if (typeof char_count != 'undefined')
                    check_byte('wr_content', 'char_count');
                if (document.getElementById('secret_comment_'+comment_id).value)
                    document.getElementById('wr_secret').checked = true;
                else
                    document.getElementById('wr_secret').checked = false;
            }

            document.getElementById('comment_id').value = comment_id;
            document.getElementById('w').value = work;

            if(save_before)
                $("#captcha_reload").trigger("click");

            save_before = el_id;
        }
    }

    function comment_delete()
    {
        return confirm("이 댓글을 삭제하시겠습니까?");
    }

    comment_box('', 'c'); // 댓글 입력폼이 보이도록 처리하기위해서 추가 (root님)

    <?php if($board['bo_use_sns'] && ($config['cf_facebook_appid'] || $config['cf_twitter_key'])) { ?>
    $(function() {
    // sns 등록
        $("#bo_vc_send_sns").load(
            "<?php echo G5_SNS_URL; ?>/view_comment_write.sns.skin.php?bo_table=<?php echo $bo_table; ?>",
            function() {
                save_html = document.getElementById('bo_vc_w').innerHTML;
            }
        );


           
    });
    <?php } ?>

    $(function() {            
        //댓글열기
        $(".cmt_btn").click(function(){
            $(this).toggleClass("cmt_btn_op");
            $("#bo_vc").toggle();
        });
    });
    </script>
    <?php } ?>
