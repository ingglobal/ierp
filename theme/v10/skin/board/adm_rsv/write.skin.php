<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
//변수가 (,),(=)로 구분되어 있을때
//차량종류( carname )
if($board['bo_2_subj'] && $board['bo_2'] && preg_match("/,/",$board['bo_2']) && preg_match("/=/",$board['bo_2'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_2']));
    $valname = $board['bo_2_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
		${'bo_'.$valname.'_radios'} .= '<label for="'.$valname.'_'.$key.'" class="'.$valname.'"><input type="radio" id="'.$valname.'_'.$key.'" name="'.$valname.'" value="'.$key.'">'.$value.'('.$key.')</label>';
		//${'bo_'.$valname.'_value_options'} .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
		${'bo_'.$valname.'_options'} .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    }
}
//시간1( time1 )
if($board['bo_3_subj'] && $board['bo_3'] && preg_match("/,/",$board['bo_3']) && preg_match("/=/",$board['bo_3'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_3']));
    $valname = $board['bo_3_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
		${'bo_'.$valname.'_radios'} .= '<label for="'.$valname.'_'.$key.'" class="'.$valname.'"><input type="radio" id="'.$valname.'_'.$key.'" name="'.$valname.'" value="'.$key.'">'.$value.'('.$key.')</label>';
		//${'bo_'.$valname.'_value_options'} .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
		${'bo_'.$valname.'_options'} .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    }
}
//시간1( time2 )
if($board['bo_4_subj'] && $board['bo_4'] && preg_match("/,/",$board['bo_4']) && preg_match("/=/",$board['bo_4'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_4']));
    $valname = $board['bo_4_subj'];
    foreach ($bo_values as $bo_value){
        list($key, $value) = explode('=', $bo_value);
        ${'bo_'.$valname}[$key] = $value.' ('.$key.')';
		${'bo_'.$valname.'_value'}[$key] = $value;
		${'bo_'.$valname.'_reverse'}[$value] = $key;
		${'bo_'.$valname.'_arr'}[] = $key;
		${'bo_'.$valname.'_radios'} .= '<label for="'.$valname.'_'.$key.'" class="'.$valname.'"><input type="radio" id="'.$valname.'_'.$key.'" name="'.$valname.'" value="'.$key.'">'.$value.'('.$key.')</label>';
		//${'bo_'.$valname.'_value_options'} .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
		${'bo_'.$valname.'_options'} .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    }
}
if($w != ''){
    $wr_1_arr = explode(' ',$write['wr_1']);
    $wr_1_t = substr_replace($write['wr_1'],' ','T');
    $write['wr_1_date'] = $wr_1_arr[0];
    $write['wr_1_time'] = $wr_1_arr[1];
    
    $wr_2_arr = explode(' ',$write['wr_2']);
    $wr_2_t = substr_replace($write['wr_2'],' ','T');
    $write['wr_2_date'] = $wr_2_arr[0];
    $write['wr_2_time'] = $wr_2_arr[1];
}

$delete_href = '';
if (($member['mb_id'] && ($member['mb_id'] == $write['mb_id'])) || $is_admin) {
    set_session('ss_delete_token', $token = uniqid(time()));
    if($calendar)
        $delete_href = G5_USER_ADMIN_URL.'/bbs_delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token.'&amp;calendar='.$calendar;
    else
        $delete_href = G5_USER_ADMIN_URL.'/bbs_delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token.'&amp;page='.$page.urldecode($qstr);
} else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
    $delete_href = G5_USER_ADMIN_URL.'/bbs_password.php?w=d&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.$qstr;
}

if($target_dt && strpos($target_dt,'T') !== false){
    $target_dt_arr = explode('T',$target_dt);
    $target_dt = $target_dt_arr[0];
    $target_tm = substr($target_dt_arr[1],0,8);
    //echo $target_tm;
}
else if($target_dt && strpos($target_dt,'T') === false){

}

/*
print_r2($bo_carname);
print_r2($bo_carname_value);
print_r2($bo_carname_reverse);
print_r2($bo_carname_arr);
echo $bo_carname_radios."<br>";
echo '<select name="car_name">'.PHP_EOL;
echo $bo_carname_options."<br>";
echo '</select><br>'.PHP_EOL;
*/
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<style>
.container_wr{position:relative;}    
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
    <input type="hidden" name="calendar" value="<?php echo $calendar ?>">
    <div id="info_box">
        <ul class="ul_info">
            <li class="li_info li_top">
                <label for="wr_subject">차량선택<strong class="sound_only">필수</strong></label>
                <select name="wr_subject" id="wr_subject" required class="required">
                    <?php echo $bo_carname_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="wr_subject"]').val($('select[name="wr_subject"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_subject"]').val("<?=$write['wr_subject']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info li_top">
                <label for="wr_content">예약자<strong class="sound_only">필수</strong></label>
                <input type="text" name="wr_content" required class="frm_input required" value="<?=(($w=='') ? $member['mb_name'] : $write['wr_content'])?>">
            </li>
            <li class="li_info">
                <label for="wr_1_date">출발일<strong class="sound_only">필수</strong></label>
                <input type="text" name="wr_1_date" id="wr_1_date" required readonly class="frm_input required readonly" value="<?=(($w == '' && $target_dt) ? $target_dt : $write['wr_1_date'])?>">
            </li>
            <li class="li_info">
                <label for="wr_1_time">출발시간<strong class="sound_only">필수</strong></label>
                <select name="wr_1_time" id="wr_1_time" required class="required">
                    <?php echo $bo_time2_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    <?php if($target_tm){ ?>
                    $('select[name="wr_1_time"]').val('<?=$target_tm?>');
                    <?php } else { ?>
                    $('select[name="wr_1_time"]').val($('select[name="wr_1_time"]').find('option').eq(0).attr('value'));
                    <?php } ?>
                <?php } else { ?>
                    $('select[name="wr_1_time"]').val("<?=$write['wr_1_time']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info">
                <label for="wr_2_date">도착일<strong class="sound_only">필수</strong></label>
                <input type="text" name="wr_2_date" id="wr_2_date" required readonly class="frm_input required readonly" value="<?=(($w == '' && $target_dt) ? $target_dt : $write['wr_2_date'])?>">
            </li>
            <li class="li_info">
                <label for="wr_2_time">도착시간<strong class="sound_only">필수</strong></label>
                <select name="wr_2_time" id="wr_2_time" required class="required">
                    <?php echo $bo_time2_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="wr_2_time"]').val($('select[name="wr_2_time"]').find('option').eq(1).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_2_time"]').val("<?=$write['wr_2_time']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info li_last">
                <label for="wr_3">메모</label>
                <input type="text" name="wr_3" class="frm_input" value="<?=$write['wr_3']?>">
            </li>
        </ul>
    </div>

    <div class="btn_confirm write_div">
        <?php if($calendar){ ?>
            <a href="<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=<?=$bo_table?>&calendar=<?=$calendar?>" class="btn_cancel2 btn">취소</a>
        <?php } else { ?>
            <a href="<?=G5_USER_ADMIN_URL?>/bbs_board.php?bo_table=<?=$bo_table?>&amp;<?=$qstr?>" class="btn_cancel btn">취소</a>
        <?php } ?>
        <?php if ($w == 'u' && $delete_href) { ?><a href="<?php echo $delete_href ?>" class="btn_02 btn" style="background:gray;">삭제</a><?php } ?>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn">작성완료</button>
    </div>
    </form>

    <script>
    $(function(){
        $("input[name=wr_1_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=wr_2_date]").datepicker('option','minDate',selectedDate);} });

        $("input[name=wr_2_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){$("input[name=wr_1_date]").datepicker('option','maxDate',selectedDate); }});
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

        <?php echo $captcha_js; // 캡챠 사용시 자바스크립트에서 입력된 캡챠를 검사함  ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->