<?php
$sub_menu = '960265';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], "r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'meeting';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명

foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}

if($w == ''){
    $year = substr(G5_TIME_YMD,0,4);
    $yres = sql_fetch(" SELECT COUNT(mtg_idx) AS cnt FROM {$g5['meeting_table']} WHERE mtg_date LIKE '{$year}%' AND mtg_status = 'ok' ");
    $ycnt = $yres['cnt'];
    // $initnum = 1000;
    $initnum = 0;
    $this_doc_num = $initnum + ($ycnt + 1);
    $doc_num = sprintf("%04d",$this_doc_num);
    $mtg['mtg_code'] = 'A'.$year.'-CTR-'.$doc_num;

    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
	$mtg['mtg_status'] = 'ok';
}
else if($w == 'u'){
    $mtgsql = " SELECT mtg.*, mb_name, prj_name, mb_2, mb_3 FROM {$g5['meeting_table']} mtg
                    LEFT JOIN {$g5['project_table']} prj ON mtg.prj_idx = prj.prj_idx
                    LEFT JOIN {$g5['member_table']} mb ON mtg.mb_id_writer = mb.mb_id
                WHERE mtg_idx = '{$mtg_idx}' ";
    $mtg = sql_fetch($mtgsql,1);

    if(!$super_ceo_admin && $mtg['mb_id_writer'] != $member['mb_id']){
        alert('본인이 작성한 정보만 수정할 수 있습니다');
    }

    $mtpsql = " SELECT (ROW_NUMBER() OVER(ORDER BY mtp_idx)) AS num 
                    , mtp_idx
                    , mtg_idx
                    , mtp_belong
                    , mtp_name
                    , mtp_rank
                    , mtp_phone
                    FROM {$g5['meeting_participant_table']}
                WHERE mtg_idx = '{$mtg_idx}' ORDER BY mtp_idx ";
    $mtpres = sql_query($mtpsql,1);

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'mtg' AND fle_type = 'mtg' AND fle_db_id = '".$mtg['mtg_idx']."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $mtg['mtg_f_arr'] = array();
    $mtg['mtg_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
    $mtg['mtg_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($mtg['mtg_f_arr'],array('file'=>$file_down_del));
        @array_push($mtg['mtg_fidxs'],$row2['fle_idx']);
    }

    //회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
    if(@count($mtg['mtg_fidxs'])) $mtg['mtg_lst_idx'] = $mtg['mtg_fidxs'][0];
}
$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '회의내용'.$html_title;
include_once('./_head.php');

//$mtg['mb_2'] // 소속부서코드, $mtg['mb_3'] // 직급코드
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/timepicker/timepicker_ko_KR.css">', 1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/timepicker/timepicker_ko_KR.js"></script>', 1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>', 1);
?>
<style>
input[type=text]{padding:0 5px;}
input.readonly{background:#ededed;}
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
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" >
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="mtg_idx" value="<?php echo $mtg["mtg_idx"] ?>">
<input type="hidden" name="mtg_code" value="<?php echo $mtg["mtg_code"] ?>">
<input type="hidden" name="mtg_status" value="<?php echo $mtg["mtg_status"] ?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:none;">
    <p>회의내용을 작성 및 수정하는 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:12%;">
		<col style="width:21%;">
		<col class="grid_4" style="width:12%;">
		<col style="width:22%;">
		<col class="grid_4" style="width:12%;">
		<col style="width:21%;">
	</colgroup>
	<tbody>
        <tr>
            <th scope="row">작성자소속</th>
            <td>
                <?php
                $mb_part_cd = ($w == '') ? $member['mb_2'] : $mtg['mtg_part'];
                echo ($g5['department_name'][$mb_part_cd]) ? $g5['department_name'][$mb_part_cd] : '소속없음';
                ?>
                <input type="hidden" name="mtg_part" value="<?=$mb_part_cd?>">
            </td>
            <th scope="row">작성자직급</th>
            <td>
                <?php
                $mb_rank_cd = ($w == '') ? $member['mb_3'] : $mtg['mtg_rank'];
                echo ($g5['set_mb_ranks_value'][$mb_rank_cd]) ? $g5['set_mb_ranks_value'][$mb_rank_cd] : '직급없음';
                ?>
                <input type="hidden" name="mtg_rank" value="<?=$mb_rank_cd?>">
            </td>
            <th scope="row">작성자명</th>
            <td>
                <?php
                $mb_name = ($w == '') ? $member['mb_name'] : $mtg['mb_name'];
                $mb_id = ($w == '') ? $member['mb_id'] : $mtg['mb_id_writer'];
                echo $mb_name;
                ?>
                <input type="hidden" name="mb_id_writer" value="<?=$mb_id?>">
            </td>
        </tr>
        <tr>
            <th scope="row">회의날짜</th>
            <td>
                <input type="text" name="mtg_date" value="<?=(($mtg['mtg_date'])?$mtg['mtg_date']:G5_TIME_YMD)?>" readonly class="frm_input" style="width:90px;text-align:center;">
            </td>
            <th scope="row">회의시간</th>
            <td>
                <select name="mtg_start_time" id="mtg_start_time"></select>&nbsp;&nbsp;&nbsp;~&nbsp;
                <select name="mtg_end_time" id="mtg_end_time"></select>
                <script>
                    timePicker($('#mtg_start_time'),12,7,20);
                    timePicker($('#mtg_end_time'),12,9,23);
                    <?php if($w != ''){ ?>
                    setTimeout(() => {
                    $('#mtg_start_time').val('<?=substr($mtg['mtg_start_time'],0,5)?>');
                    $('#mtg_end_time').val('<?=substr($mtg['mtg_end_time'],0,5)?>');
                    },50);
                    <?php } ?>
                </script>
            </td>
            <th scope="row">회의장소</th>
            <td>
                <input type="text" name="mtg_location" value="<?=$mtg['mtg_location']?>" class="frm_input" style="width:100%;">
            </td>
        </tr>
        <tr>
            <th scope="row">프로젝트명</th>
            <td colspan="3">
                <input type="hidden" name="prj_idx" id="prj_idx" value="<?=$mtg['prj_idx']?>">
                <input type="text" id="prj_name" value="<?=$mtg['prj_name']?>" readonly class="frm_input" style="width:400px;">
                <a href="javascript:" link="./_win_project_select.php" class="btn btn_02 prj_select">프로젝트선택</a>
                <script>
                $('.prj_select').on('click',function(){
                    var href = $(this).attr('link');
                    var win_prj_select = window.open(href, "win_prj_select", "left=10,top=10,width=500,height=800");
                    win_prj_select.focus();
                    return false;
                });   
                </script>
            </td>
            <th scope="row">회의타입</th>
            <td>
                <select name="mtg_type" id="mtg_type">
                    <?=$g5['set_mtg_type_value_options']?>
                </select>
                <script>
                <?php if($w != ''){ ?>
                $('#mtg_type').val('<?=$mtg['mtg_type']?>');
                <?php } ?>
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">주요안건</th>
            <td colspan="5">
                <input type="text" name="mtg_subject" value="<?=$mtg['mtg_subject']?>" class="frm_input" style="width:400px;">
            </td>
        </tr>
        <tr>
            <th scope="row">회의내용</th>
            <td colspan="5">
                <?php echo editor_html("mtg_content", get_text(html_purifier($mtg['mtg_content']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">회의결과</th>
            <td colspan="5">
                <?php echo editor_html("mtg_result", get_text(html_purifier($mtg['mtg_result']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">회의관련파일</th>
            <td colspan="5">
            <?php echo help("회의관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file_mtg" name="mtg_datas[]" multiple class="">
                <?php
                if(@count($mtg['mtg_f_arr'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($mtg['mtg_f_arr']);$i++) {
                        echo "<li>[".($i+1).']'.$mtg['mtg_f_arr'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01 .tbl_wrap-->

<style>
.mtp_box{}
.mtp_box .mtp_add{height:26px;line-height:25px;font-size:0.9em;margin-left:20px;}
.ul_mtp{margin-bottom:20px;}
.ul_mtp li{margin-top:5px;}

.ipli_mtp_belong{}
.ipli_mtp_name{}
.ipli_mtp_rank{}
.ipli_mtp_phone{}
.ipli_mtp_mng{}
</style>
<div class="mtp_box">
    <h2><b>참석자정보등록</b><a href="javascript:" class="btn btn_02 mtp_add">추가</a></h2>
    <ul class="ul_sample" style="display:none;">
        <li class="li_mtp">
            <strong></strong>
            <input type="hidden" name="" placeholder="참석자소속" value="" class="frm_input mtp_idx">
            <input type="text" name="" placeholder="참석자소속" value="" class="frm_input mtp_belong">
            <input type="text" name="" placeholder="참석자성명" value="" class="frm_input mtp_name">
            <input type="text" name="" placeholder="참석자직급" value="" class="frm_input mtp_rank">
            <input type="text" name="" placeholder="참석자연락처" value="" class="frm_input mtp_phone">
            <a href="javascript:" class="btn btn_01 mtp_del" mtp_idx="">삭제</a>
        </li>
    </ul>
    <ul class="ul_mtp">
        <?php for($i=0;$row=sql_fetch_array($mtpres);$i++){ ?>
        <li class="li_mtp">
            <strong><?=($i+1)?></strong>
            <input type="hidden" name="mtp_idx[<?=$i?>]" value="<?=$row['mtp_idx']?>" class="frm_input mtp_idx">
            <input type="text" name="mtp_belong[<?=$i?>]" placeholder="참석자소속" value="<?=$row['mtp_belong']?>" class="frm_input mtp_belong">
            <input type="text" name="mtp_name[<?=$i?>]" placeholder="참석자성명(필수)" value="<?=$row['mtp_name']?>" class="frm_input mtp_name">
            <input type="text" name="mtp_rank[<?=$i?>]" placeholder="참석자직급" value="<?=$row['mtp_rank']?>" class="frm_input mtp_rank">
            <input type="text" name="mtp_phone[<?=$i?>]" placeholder="참석자연락처" value="<?=$row['mtp_phone']?>" class="frm_input mtp_phone">
            <a href="javascript:" class="btn btn_01 mtp_del" mtp_idx="<?=$row['mtp_idx']?>">삭제</a>
        </li>
        <?php } ?>
    </ul>
</div>
<script>
$(function(){
    //개별발주서 멀티파일
	$('#multi_file_mtg').MultiFile();

    event_on();
});
function event_on(){
    $('.mtp_add').on('click', function(){
        let idx = $('.ul_mtp li').length;
        let li_mtp = $('.ul_sample li').clone();
        li_mtp.find('strong').text((idx+1));
        li_mtp.find('.mtp_idx').attr('name','mtp_idx['+idx+']');
        li_mtp.find('.mtp_belong').attr('name','mtp_belong['+idx+']');
        li_mtp.find('.mtp_name').attr('name','mtp_name['+idx+']');
        li_mtp.find('.mtp_rank').attr('name','mtp_rank['+idx+']');
        li_mtp.find('.mtp_phone').attr('name','mtp_phone['+idx+']');
        $('.ul_mtp').append(li_mtp);
        event_off();
        event_on();
    });

    $('.mtp_del').on('click', function(){
        if($(this).attr('mtp_idx')){
            let mtp_idx = $(this).attr('mtp_idx');
            if(!confirm("복구는 불가능합니다.\n기존 참석자 정보를 정말 삭제 하시겠습니까?")){
                return false;
            }

            let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/mtp_del.php';
            $.ajax({
                type: 'POST',
                dataType: 'text',
                url: ajxurl,
                data: {'mtp_idx': mtp_idx},
                success: function(res){
                    if(res == 'ok'){
                        location.reload();
                    }
                },
                error: function(xmlReq){
                    alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
                }
            });
        }
        else{
            $(this).parent().remove();
            $('.ul_mtp li').each(function(){
                let idx = $(this).index();
                $(this).find('strong').text((idx+1));
                $(this).find('.mtp_idx').attr('name','mtp_idx['+idx+']');
                $(this).find('.mtp_belong').attr('name','mtp_belong['+idx+']');
                $(this).find('.mtp_name').attr('name','mtp_name['+idx+']');
                $(this).find('.mtp_rank').attr('name','mtp_rank['+idx+']');
                $(this).find('.mtp_phone').attr('name','mtp_phone['+idx+']');
            });
            event_off();
            event_on();
        }
    });
}

function event_off(){
    $('.mtp_add').off('click');
    $('.mtp_del').off('click');
}
</script>
<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" name="act_button" value="삭제" onclick="document.pressed=this.value" class="btn_01 btn">
    <input type="submit" name="act_button" value="확인" onclick="document.pressed=this.value" class="btn_submit btn" accesskey='s'>
</div>
</form>
<script>
//날짜입력
$("input[name*=_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소',onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('0000-00-00');}} });

function form01_submit(f){
    if(document.pressed == "삭제"){
        if(!confirm("복구가 불가능 합니다. 정말 삭제하시겠습니까?")) {
            return false;
        }

        f.w.value = 'd';
    }
    else if(document.pressed == "확인"){
    
        <?php echo get_editor_js("mtg_content"); ?>
        <?php echo get_editor_js("mtg_result"); ?>
    
        if(!f.mtg_subject.value){
            alert('주요안건(제목)은 반드시 입력해 주세요.');
            f.mtg_subject.focus();
            return false;
        }
    
        if(!f.mtg_content.value){
            alert('회의내용은 반드시 입력해 주세요.');
            f.mtg_content.focus();
            return false;
        }
    
        if(!f.mtg_result.value){
            alert('회의결과는 반드시 입력해 주세요.');
            f.mtg_result.focus();
            return false;
        }
    
        if($('.ul_mtp li').length < 2){
            alert('참석자 정보는 적어도 2명이상 등록해 주세요.');
            return false;
        }
        
        let mtp_false = 0
        $('.ul_mtp li').each(function(){
            let mtp_belong = $.trim($(this).find('.mtp_belong').val());
            let mtp_name = $.trim($(this).find('.mtp_name').val());
            let mtp_rank = $.trim($(this).find('.mtp_rank').val());
            let mtp_phone = $.trim($(this).find('.mtp_phone').val());
    
            if(!mtp_name){
                mtp_false = 1;
            }
        });
        if(mtp_false){
            alert('참석자성명은 반드시 입력해 주세요.');
            return false;
        }

    }

    // alert('여기까지 OK');
    return true;
}
</script>
<?php
include_once ('./_tail.php');