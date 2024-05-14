<?php
$sub_menu = "960300";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],"r");

if(!$type) alert('보고서 타입정보가 넘어오지 않았습니다.');
if(!$yy) alert('연도정보가 넘어오지 않았습니다.');
if(!$mm) alert('월정보가 넘어오지 않았습니다.');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'workreport';
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
    $om = sprintf('%02d',$mm);
    $sql_common = "";
    if($type == 'day'){
        $sql_common = " AND wrp_date = '{$date}'
                        AND wrp_type = '{$type}'
        ";
    }
    else if($type == 'week'){
        $weeknum = getWeekNumOfMonth($date);
        $sql_common = " AND wrp_type = '{$type}'
                        AND wrp_date LIKE '{$yy}-%'
                        AND wrp_week = '{$weeknum}'
                        AND wrp_month = '{$om}'
        ";
    }
    else if($type == 'month'){
        $sql_common = " AND wrp_type = '{$type}'
                        AND wrp_date LIKE '{$yy}-%'
                        AND wrp_month = '{$om}'
        ";
    }

    $csql = " SELECT COUNT(wrp_idx) AS cnt FROM {$g5['workreport_table']}
                    WHERE mb_id = '{$member['mb_id']}'
                        {$sql_common}
    ";
    // echo $csql;exit;
    $cres = sql_fetch($csql,1);
    if($cres['cnt']){
        alert($member['mb_name'].'님의 '.$g5['set_wrp_type_value'][$type].'서가 이미 존재합니다.');
    }




    $year = substr(G5_TIME_YMD,0,4);
    $yres = sql_fetch(" SELECT COUNT(wrp_idx) AS cnt FROM {$g5['workreport_table']} WHERE wrp_date LIKE '{$year}%' AND wrp_status = 'ok' ");
    $ycnt = $yres['cnt'];
    // $initnum = 1000;
    $initnum = 0;
    $this_doc_num = $initnum + ($ycnt + 1);
    $doc_num = sprintf("%04d",$this_doc_num);
    $wrp['wrp_code'] = 'W'.$year.'-CTR-'.$doc_num;

    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
	$wrp['wrp_status'] = 'ok';
}
else if($w == 'u'){
    $wrpsql = " SELECT wrp.*, mb_name, prj_name, mb_2, mb_3 FROM {$g5['workreport_table']} wrp
                    LEFT JOIN {$g5['project_table']} prj ON wrp.prj_idx = prj.prj_idx
                    LEFT JOIN {$g5['member_table']} mb ON wrp.mb_id = mb.mb_id
                WHERE wrp_idx = '{$wrp_idx}' ";
    $wrp = sql_fetch($wrpsql,1);

    if(!$super_ceo_admin && $wrp['mb_id'] != $member['mb_id']){
        alert('본인이 작성한 정보만 수정할 수 있습니다');
    }

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'wrp' AND fle_type = 'wrp' AND fle_db_id = '".$wrp['wrp_idx']."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $wrp['wrp_f_arr'] = array();
    $wrp['wrp_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
    $wrp['wrp_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($wrp['wrp_f_arr'],array('file'=>$file_down_del));
        @array_push($wrp['wrp_fidxs'],$row2['fle_idx']);
    }

    //회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
    if(@count($wrp['wrp_fidxs'])) $wrp['wrp_lst_idx'] = $wrp['wrp_fidxs'][0];
}
$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = $g5['set_wrp_type_value'][$type].'서'.$html_title;
include_once('./_head.php');

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
<input type="hidden" name="type" value="<?=$type?>">
<input type="hidden" name="yy" value="<?=$yy?>">
<input type="hidden" name="mm" value="<?=$mm?>">
<input type="hidden" name="wrp_idx" value="<?php echo $wrp["wrp_idx"] ?>">
<input type="hidden" name="wrp_code" value="<?php echo $wrp["wrp_code"] ?>">
<input type="hidden" name="wrp_status" value="<?php echo $wrp["wrp_status"] ?>">
<input type="hidden" name="list" value="<?=$list?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:none;">
    <p><?=$g5['set_wrp_type_value'][$type]?>서의 내용을 작성 및 수정하는 페이지입니다.</p>
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
                $mb_part_cd = ($w == '') ? $member['mb_2'] : $wrp['wrp_part'];
                echo ($g5['department_name'][$mb_part_cd]) ? $g5['department_name'][$mb_part_cd] : '소속없음';
                ?>
                <input type="hidden" name="wrp_part" value="<?=$mb_part_cd?>">
            </td>
            <th scope="row">작성자직급</th>
            <td>
                <?php
                $mb_rank_cd = ($w == '') ? $member['mb_3'] : $wrp['wrp_rank'];
                echo ($g5['set_mb_ranks_value'][$mb_rank_cd]) ? $g5['set_mb_ranks_value'][$mb_rank_cd] : '직급없음';
                ?>
                <input type="hidden" name="wrp_rank" value="<?=$mb_rank_cd?>">
            </td>
            <th scope="row">작성자명</th>
            <td>
                <?php
                $mb_name = ($w == '') ? $member['mb_name'] : $wrp['mb_name'];
                $mb_id = ($w == '') ? $member['mb_id'] : $wrp['mb_id'];
                echo $mb_name;
                ?>
                <input type="hidden" name="mb_id" value="<?=$mb_id?>">
            </td>
        </tr>
        <tr>
            <th scope="row">보고날짜</th>
            <td>
                <?php
                $wrp_date = ($wrp['wrp_date'])?$wrp['wrp_date']:$date;
                ?>
                <input type="<?=(($w != '')?'hidden':'text')?>" name="wrp_date" id="wrp_date" value="<?=$wrp_date?>" readonly class="frm_input" style="width:90px;text-align:center;">
                <?=(($w != '')?$wrp_date:'')?>
            </td>
            <th scope="row">주차</th>
            <td>
                <input type="hidden" name="wrp_month" id="wrp_month" value="<?=substr($wrp_date,5,2)?>">
                <input type="text" name="wrp_week" id="wrp_week" value="<?=getWeekNumOfMonth($wrp_date)?>" readonly class="frm_input" style="background:#ededed;width:30px;text-align:center;">&nbsp;주차
                <script>
                $('#wrp_date').on('change',function(){
                    let wrp_date = $(this).val();
                    let mon = wrp_date.substring(5,7);
                    $('#wrp_month').val(mon);
                    $('#wrp_week').val((getWeekNumOfMonth(wrp_date)));
                });
                </script>
            </td>
            <th scope="row">보고타입</th>
            <td>
                <?php
                $opt_str = ($w != '') ? ' style="background-color:#ededed;" readonly onFocus="this.initialSelect=this.selectedIndex;" onChange="this.selectedIndex=this.initialSelect;"' : '';
                ?>
                <select name="wrp_type" id="wrp_type"<?=$opt_str?>>
                    <?=$g5['set_wrp_type_value_options']?>
                </select>
                <script>
                <?php if($w != ''){ ?>
                $('#wrp_type').val('<?=$wrp['wrp_type']?>');
                <?php } else { ?>
                $('#wrp_type').val('<?=$type?>');
                <?php } ?>
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">프로젝트명</th>
            <td colspan="7">
                <input type="hidden" name="prj_idx" id="prj_idx" value="<?=$wrp['prj_idx']?>">
                <input type="text" id="prj_name" value="<?=$wrp['prj_name']?>" readonly class="frm_input" style="width:400px;">
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
        </tr>
        <tr>
            <th scope="row">제목</th>
            <td colspan="7">
                <input type="text" name="wrp_subject" value="<?=$wrp['wrp_subject']?>" class="frm_input" style="width:400px;">
            </td>
        </tr>
        <tr>
            <th scope="row">내용</th>
            <td colspan="5">
                <?php echo editor_html("wrp_content", get_text(html_purifier($wrp['wrp_content']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">업무보고 관련 파일</th>
            <td colspan="5">
            <?php echo help("업무보고 관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file_wrp" name="wrp_datas[]" multiple class="">
                <?php
                if(@count($wrp['wrp_f_arr'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($wrp['wrp_f_arr']);$i++) {
                        echo "<li>[".($i+1).']'.$wrp['wrp_f_arr'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01 .tbl_wrap-->


<script>
$(function(){
    //개별발주서 멀티파일
	$('#multi_file_wrp').MultiFile();
});
</script>
<div class="btn_fixed_top">
    <?php if($list){ ?>
    <a href="./workreport_list.php?ser_wrp_type=<?=$ser_wrp_type?>&amp;ser_mb_id=<?=$ser_mb_id?>&amp;ser_from_date=<?=$ser_from_date?>&amp;ser_to_date=<?=$ser_to_date?>" class="btn btn_02">목록</a>
    <?php } else { ?>
    <a href="./workreport_calendar.php?type=<?=$type?>&amp;yy=<?=$yy?>&amp;mm=<?=$mm?>" class="btn btn_02">달력</a>
    <?php } ?>
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
    
        <?php echo get_editor_js("wrp_content"); ?>
    
        if(!f.wrp_subject.value){
            alert('제목은 반드시 입력해 주세요.');
            f.wrp_subject.focus();
            return false;
        }
    
        if(!f.wrp_content.value){
            alert('업무보고서의 내용은 반드시 입력해 주세요.');
            f.wrp_content.focus();
            return false;
        }

    }

    // alert('여기까지 OK');
    return true;
}
</script>
<?php
include_once ('./_tail.php');