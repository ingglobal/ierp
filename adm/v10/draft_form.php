<?php
$sub_menu = '960270';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], "r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'draft';
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
    $yres = sql_fetch(" SELECT COUNT(drf_idx) AS cnt FROM {$g5['draft_table']} WHERE drf_date LIKE '{$year}%' AND drf_status != 'trash' ");
    $ycnt = $yres['cnt'];
    // $initnum = 1000;
    $initnum = 0;
    $this_doc_num = $initnum + ($ycnt + 1);
    $doc_num = sprintf("%04d",$this_doc_num);
    $drf['drf_code'] = 'D'.$year.'-CTR-'.$doc_num;

    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
	$drf['drf_status'] = 'pending';
}
else if($w == 'u'){
    $drfsql = " SELECT drf.*, mb_name, prj_name, mb_2, mb_3 FROM {$g5['draft_table']} drf
                    LEFT JOIN {$g5['project_table']} prj ON drf.prj_idx = prj.prj_idx
                    LEFT JOIN {$g5['member_table']} mb ON drf.mb_id = mb.mb_id
                WHERE drf_idx = '{$drf_idx}' ";
    $drf = sql_fetch($drfsql,1);

    if(!$super_ceo_admin && $drf['mb_id'] != $member['mb_id'] && $drf['mb_id_approval'] != $member['mb_id']){
        alert('본인이 작성한 정보 또는 승인자만 수정할 수 있습니다');
    }

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'drf' AND fle_type = 'drf' AND fle_db_id = '".$drf['drf_idx']."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $drf['drf_f_arr'] = array();
    $drf['drf_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
    $drf['drf_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($drf['drf_f_arr'],array('file'=>$file_down_del));
        @array_push($drf['drf_fidxs'],$row2['fle_idx']);
    }

    //회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
    if(@count($drf['drf_fidxs'])) $drf['drf_lst_idx'] = $drf['drf_fidxs'][0];
}


$msql = " SELECT mb_id,mb_name,mb_3 FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level <= 8 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name != '테스일' AND mb_name != '일정관리' ORDER BY mb_name ";
$mres = sql_query($msql,1);
$mb_opts = '';
for($i=0;$mrow=sql_fetch_array($mres);$i++){
    $mb_opts .= '<option value="'.$mrow['mb_id'].'">'.$mrow['mb_name'].'('.(($g5['set_mb_ranks_value'][$mrow['mb_3']])?$g5['set_mb_ranks_value'][$mrow['mb_3']]:'직함없음').')</option>'.PHP_EOL;
}


$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '기안서내용'.$html_title;
include_once('./_head.php');

//$drf['mb_2'] // 소속부서코드, $drf['mb_3'] // 직급코드
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
<input type="hidden" name="drf_idx" value="<?php echo $drf["drf_idx"] ?>">
<input type="hidden" name="drf_code" value="<?php echo $drf["drf_code"] ?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:none;">
    <p>기안서내용을 작성 및 수정하는 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:15%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:15%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:15%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:15%;">
	</colgroup>
	<tbody>
        <tr>
            <th scope="row">작성자소속</th>
            <td>
                <?php
                $mb_part_cd = ($w == '') ? $member['mb_2'] : $drf['drf_part'];
                echo ($g5['department_name'][$mb_part_cd]) ? $g5['department_name'][$mb_part_cd] : '소속없음';
                ?>
                <input type="hidden" name="drf_part" value="<?=$mb_part_cd?>">
            </td>
            <th scope="row">작성자직급</th>
            <td>
                <?php
                $mb_rank_cd = ($w == '') ? $member['mb_3'] : $drf['drf_rank'];
                echo ($g5['set_mb_ranks_value'][$mb_rank_cd]) ? $g5['set_mb_ranks_value'][$mb_rank_cd] : '직급없음';
                ?>
                <input type="hidden" name="drf_rank" value="<?=$mb_rank_cd?>">
            </td>
            <th scope="row">작성자명</th>
            <td>
                <?php
                $mb_name = ($w == '') ? $member['mb_name'] : $drf['mb_name'];
                $mb_id = ($w == '') ? $member['mb_id'] : $drf['mb_id'];
                echo $mb_name;
                ?>
                <input type="hidden" name="mb_id" value="<?=$mb_id?>">
            </td>
            <th scope="row">기안날짜</th>
            <td>
                <input type="text" name="drf_date" value="<?=(($drf['drf_date'])?$drf['drf_date']:G5_TIME_YMD)?>" readonly class="frm_input" style="width:90px;text-align:center;">
            </td>
        </tr>
        <tr>
            <th scope="row">프로젝트명</th>
            <td colspan="3">
                <input type="hidden" name="prj_idx" id="prj_idx" value="<?=$drf['prj_idx']?>">
                <input type="text" id="prj_name" value="<?=$drf['prj_name']?>" readonly class="frm_input" style="width:400px;">
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
            <th scope="row">승인요청</th>
            <td>
                <select name="mb_id_approval" id="mb_id_approval">
                    <option value="">::승인자선택::</option>
                    <?=$mb_opts?>
                </select>
                <?php if($w != ''){ ?>
                <script>
                    $('#mb_id_approval').val('<?=$drf['mb_id_approval']?>');
                </script>
                <?php } ?>
            </td>
            <th scope="row">상태</th>
            <td>
                <?php
                if($w == ''){
                    $skip_arr = array('repending','checking','ok','reject');
                } else {
                    if($member['mb_id'] == $drf['mb_id']){
                        $skip_arr = array('pending','checking','ok','reject');
                    }else if($member['mb_id'] == $drf['mb_id_approval']){
                        $skip_arr = array('pending','repending');
                    }else{
                        $skip_arr = array('pending');
                    }
                }
                $status_opts = '';
                foreach($g5['set_drf_status_value'] as $st_key => $st_val){
                    if(in_array($st_key,$skip_arr)) continue;

                    $status_opts .= '<option value="'.$st_key.'">'.$st_val.'</option>'.PHP_EOL;
                }
                ?>
                <select name="drf_status" id="drf_status">
                    <option value="">::상태::</option>
                    <?=$status_opts?>
                </select>
                <?php if($w != ''){ ?>
                <span style="margin-left:10px;">현재상태 : <?=(($g5['set_drf_status_value'][$drf['drf_status']]))?></span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">제목</th>
            <td colspan="7">
                <input type="text" name="drf_subject" value="<?=$drf['drf_subject']?>" class="frm_input" style="width:400px;">
            </td>
        </tr>
        <tr>
            <th scope="row">요청내용</th>
            <td colspan="7">
                <?php echo editor_html("drf_content", get_text(html_purifier($drf['drf_content']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">세부내용</th>
            <td colspan="7">
                <?php echo editor_html("drf_detail", get_text(html_purifier($drf['drf_detail']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">금액관련내용</th>
            <td colspan="7">
                <?php echo editor_html("drf_money", get_text(html_purifier($drf['drf_money']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">기타내용</th>
            <td colspan="7">
                <?php echo editor_html("drf_etc", get_text(html_purifier($drf['drf_etc']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">부서장/대표이사 응답</th>
            <td colspan="7">
                <?php 
                $edit_flag = ($w == '' || $member['mb_id'] == $drf['mb_id']) ? false : true;
                ?>
                <?php echo editor_html("drf_response", get_text(html_purifier($drf['drf_response']), 0), $edit_flag); ?>
                <script>
                let edit_flag = <?=(($edit_flag)?1:0)?>;
                $('#drf_response').attr('disabled', edit_flag);
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">기안서관련파일</th>
            <td colspan="7">
            <?php echo help("기안서관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file_drf" name="drf_datas[]" multiple class="">
                <?php
                if(@count($drf['drf_f_arr'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($drf['drf_f_arr']);$i++) {
                        echo "<li>[".($i+1).']'.$drf['drf_f_arr'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01 .tbl_wrap-->

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" name="act_button" value="삭제" onclick="document.pressed=this.value" class="btn_01 btn">
    <input type="submit" name="act_button" value="확인" onclick="document.pressed=this.value" class="btn_submit btn" accesskey='s'>
</div>
</form>
<script>
$(function(){
    //개별발주서 멀티파일
    $('#multi_file_drf').MultiFile();
});
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
    
        <?php echo get_editor_js("drf_content"); ?>
        <?php echo get_editor_js("drf_detail"); ?>
        <?php echo get_editor_js("drf_etc"); ?>
        <?php echo get_editor_js("drf_response"); ?>
    
        if(!f.drf_subject.value){
            alert('제목은 반드시 입력해 주세요.');
            f.drf_subject.focus();
            return false;
        }

        if(!f.mb_id_approval.value){
            alert('승인자를 반드시 선택해 주세요.');
            f.mb_id_approval.focus();
            return false;
        }

        if(!f.drf_status.value){
            alert('상태값을 반드시 선택해 주세요.');
            f.drf_status.focus();
            return false;
        }
    
        if(!f.drf_content.value){
            alert('기안내용은 반드시 입력해 주세요.');
            f.drf_content.focus();
            return false;
        }

    }

    // alert('여기까지 OK');
    return true;
}
</script>
<?php
include_once ('./_tail.php');