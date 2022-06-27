<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once($board_skin_path.'/add_column.skin.php');

//일차( dayoff_cnt )
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
//연차시작일의 오전/오후(start_ampm)
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
//출근일의 오전/오후(work_ampm)
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
//유형(dayoff_type)
if($board['bo_5_subj'] && $board['bo_5'] && preg_match("/,/",$board['bo_5']) && preg_match("/=/",$board['bo_5'])){
    $bo_values = explode(',', preg_replace("/\s+/", "", $board['bo_5']));
    $valname = $board['bo_5_subj'];
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
    <input type="hidden" name="page" value="<?php echo $page ?>">

    <div class="ovt_info">
        <ul class="ul_info">
            <li class="li_info">
                <?php
                if($w == ''){
                    $worker_id = $member['mb_id'];
                    $worker_nm = $member['mb_name'];
                }else{
                    $minfo = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$write['wr_mb_id_applicant']}' ");
                    $worker_id = $write['wr_mb_id_applicant'];
                    $worker_nm = $minfo['mb_name'];
                }
                ?>
                <label for="mb_worker_name">신청자<strong class="sound_only">필수</strong></label>
                <input type="hidden" name="wr_mb_id_applicant" id="wr_mb_id_worker" value="<?=$worker_id?>">
                <input type="text" id="mb_worker_name" value="<?=$worker_nm?>" link="<?=G5_USER_ADMIN_URL?>/_win_bbs_worker_select.php" required readonly class="frm_input readonly required">
                <script>
                $('#mb_worker_name').on('click',function(){
                    var worker_href = $(this).attr('link');
                    var win_worker_select = window.open(worker_href, "win_worker_select", "left=10,top=10,width=500,height=800");
                    win_worker_select.focus();
                    return false;
                });
                </script>
            </li>
            <li class="li_info">
                <?php
                if($w == ''){
                    $approver_id = '';
                    $approver_nm = '';
                }else{
                    $ainfo = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$write['wr_mb_id_approver']}' ");
                    $approver_id = $write['wr_mb_id_approver'];
                    $approver_nm = $ainfo['mb_name'];
                }
                ?>
                <label for="mb_approver_name">승인자<strong class="sound_only">필수</strong></label>
                <input type="hidden" name="wr_mb_id_approver" id="wr_mb_id_approver" value="<?=$approver_id?>">
                <input type="text" id="mb_approver_name" value="<?=$approver_nm?>" link="<?=G5_USER_ADMIN_URL?>/_win_bbs_approver_select.php" required readonly class="frm_input readonly required">
                <script>
                $('#mb_approver_name').on('click',function(){
                    var approver_href = $(this).attr('link');
                    var win_approver_select = window.open(approver_href, "win_approver_select", "left=10,top=10,width=500,height=800");
                    win_approver_select.focus();
                    return false;
                });
                </script>
            </li>
            <li class="li_info">
                <label for="wr_mb_part">부서<strong class="sound_only">필수</strong></label>
                <select name="wr_mb_part" id="wr_mb_part" title="부서선택" required class="frm_input required">
                    <?php echo $g5['set_department_name_value_options']?> 
                </select>
                <script>
                //$('select[name=wr_mb_part]').val("<?php //echo $write['wr_mb_part']; ?>").attr('selected','selected');
                <?php if($w == ''){ ?>
                    $('select[name="wr_mb_part"]').val($('select[name="wr_mb_part"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_mb_part"]').val("<?=$write['wr_mb_part']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info">
                <label for="wr_start_date">연차시작일<strong class="sound_only">필수</strong></label>
                <input type="text" name="wr_start_date" value="<?=(($w == '') ? G5_TIME_YMD : $write['wr_start_date'])?>" id="wr_start_date" required readonly class="frm_input readonly required">
                <script>
                $("input[name=wr_start_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99",onClose:function(selectedDate){$('#wr_work_date').datepicker('option','minDate',selectedDate);} });
                </script>
            </li>
            <li class="li_info">
                <label for="wr_start_ampm">시작시간<strong class="sound_only">필수</strong></label>
                <select name="wr_start_ampm" id="wr_start_ampm" required class="frm_input required">
                <?php echo $bo_start_ampm_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="wr_start_ampm"]').val($('select[name="wr_start_ampm"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_start_ampm"]').val("<?=$write['wr_start_ampm']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info">
                <label for="wr_dayoff_cnt">일차개수<strong class="sound_only">필수</strong></label>
                <select name="wr_dayoff_cnt" id="wr_dayoff_cnt" required class="frm_input required">
                <?php echo $bo_dayoff_cnt_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="wr_dayoff_cnt"]').val($('select[name="wr_dayoff_cnt"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_dayoff_cnt"]').val("<?=$write['wr_dayoff_cnt']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info">
                <label for="wr_work_date">출근일<strong class="sound_only">필수</strong></label>
                <input type="text" name="wr_work_date" value="<?=$write['wr_work_date']?>" id="wr_work_date" required readonly class="frm_input readonly required">
                <script>
                $("input[name=wr_work_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99",onClose:function(selectedDate){$('#wr_start_date').datepicker('option','maxDate',selectedDate);} });
                </script>
            </li>
            <li class="li_info">
                <label for="wr_work_ampm">출근시간<strong class="sound_only">필수</strong></label>
                <select name="wr_work_ampm" id="wr_work_ampm" required class="frm_input required">
                <?php echo $bo_work_ampm_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="wr_work_ampm"]').val($('select[name="wr_work_ampm"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_work_ampm"]').val("<?=$write['wr_work_ampm']?>");
                <?php } ?>
                </script>
            </li>
            <li class="li_info">
                <label for="wr_apply_status">신청상태<strong class="sound_only">필수</strong></label>
                <?php
                if($w == ''){
                    echo '<select name="wr_apply_status" id="wr_apply_status" required class="frm_input required">'.PHP_EOL;
                    echo $g5['set_apply_status_value_options'];
                    echo '</select>'.PHP_EOL;
                }else{
                    if($write['wr_mb_id_worker'] == $member['mb_id']){
                        if($write['wr_apply_status'] == 'reject' || $write['wr_apply_status'] == 'ok'){
                            echo '<input type="hidden" name="wr_apply_status" value="'.$write['wr_apply_status'].'">';
                            echo '<input type="text" id="wr_apply_status" readonly required value="'.$g5['set_approve_status_value'][$write['wr_apply_status']].'" class="frm_input readonly required" style="background:#eee !important;">';
                        }else{
                            echo '<select name="wr_apply_status" id="wr_apply_status" required class="frm_input required">'.PHP_EOL;
                            echo $g5['set_apply_status_value_options'];
                            echo '</select>'.PHP_EOL;
                        }
                    }else if($write['wr_mb_id_approver'] == $member['mb_id'] || $member['mb_6'] <= 2 || $member['mb_level'] >= 8){
                        echo '<select name="wr_apply_status" id="wr_apply_status" required class="frm_input required">'.PHP_EOL;
                        echo $g5['set_approve_status_value_options'];
                        echo '</select>'.PHP_EOL;
                    }else{
                        echo '<select name="wr_apply_status" id="wr_apply_status" required class="frm_input required">'.PHP_EOL;
                        echo $g5['set_apply_status_value_options'];
                        echo '</select>'.PHP_EOL;
                    }
                }
                ?>
                <script>
                //$('select[name=wr_apply_status]').val("<?php //echo $write['wr_apply_status']; ?>").attr('selected','selected');
                <?php if($w == ''){ ?>
                    $('select[name="wr_apply_status"]').val($('select[name="wr_apply_status"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_apply_status"]').val("<?=$write['wr_apply_status']?>");
                <?php } ?>
                </script>
            </li>
			<li class="li_info li_info100">
			<p style="color:red;text-align:left;position:relative;margin-top:-20px;margin-bottom:10px;">출근일,출근시간은 연차/휴가 종료후 출근하는 날짜와 시간으로 입력해 주세요.</p>
			</li>
            <li class="li_info">
                <label for="wr_subject">연차사유<strong class="sound_only">필수</strong></label>
                <input type="text" name="wr_subject" value="<?=$write['wr_subject']?>" id="wr_subject" required class="frm_input required">
            </li>
            <li class="li_info">
                <label for="wr_content">비고</label>
                <input type="text" name="wr_content" value="<?=$write['wr_content']?>" id="wr_content" class="frm_input">
            </li>
			<li class="li_info">
                <label for="wr_dayoff_type">연차및휴가유형<strong class="sound_only">필수</strong></label>
                <select name="wr_dayoff_type" id="wr_dayoff_type" required class="frm_input required">
                <?php echo $bo_dayoff_type_options; ?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="wr_dayoff_type"]').val($('select[name="wr_dayoff_type"]').find('option').eq(0).attr('value'));
                <?php } else { ?>
                    $('select[name="wr_dayoff_type"]').val("<?=$write['wr_dayoff_type']?>");
                <?php } ?>
                </script>
            </li>
        </ul>
    </div>

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
        if(!$('#mb_worker_name').val()){
            alert('신청자를 선택해 주세요');
            $('#mb_worker_name').focus();
            return false;
        }

        if(!$('#mb_approver_name').val()){
            alert('승인자를 선택해 주세요');
            $('#mb_approver_name').focus();
            return false;
        }

        if(!$('#wr_start_date').val()){
            alert('연차시작일을 선택해 주세요');
            $('#wr_start_date').focus();
            return false;
        }

        if(f.wr_mb_id_applicant.value == f.wr_mb_id_approver.value){
            alert('승인자를 신청자 본인으로 설정할 수 없습니다.');
            $('#mb_approver_name').focus();
            return false;
        }

        <?php if($w == 'u' && $member['mb_id'] == $worker_id && ($write['wr_apply_status'] == 'ok' || $write['wr_apply_status'] == 'reject')){ ?>
        alert('승인 또는 반려된 신청건은 수정할 수 없습니다.');
        return false;
        <?php } ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->