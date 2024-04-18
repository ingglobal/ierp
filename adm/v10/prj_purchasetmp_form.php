<?php
$sub_menu = "960268";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],"r");


//data폴더에 ierp 폴더(각종파일을 저장하는 디렉토리) 생성
$data_ppt_dir_path = G5_DATA_PATH.'/ppt';
$ppt_permision_str = "chmod 707 -R ".$data_ppt_dir_path;

$mb_mng_flag = $member['mb_manager_yn'];

if(!is_dir($data_ppt_dir_path)){
	@mkdir($data_ppt_dir_path, G5_DIR_PERMISSION);
	@chmod($data_ppt_dir_path, G5_DIR_PERMISSION);

	exec($ppt_permision_str);
}

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase_tmp';
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
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
	$row['ppt_status'] = 'pending';
}
else if($w == 'u'){
    $ppt = get_table('project_purchase_tmp','ppt_idx',$ppt_idx);
    $prj = get_table('project','prj_idx',$ppt['prj_idx']);
    $com = get_table('company','com_idx',$ppt['com_idx']);
    if($ppt['ppt_status'] == 'trash' || !$ppt['ppt_idx']){
        alert('해당 개별발주항목이 존재하지 않습니다.','./project_purchasetmp_list.php?'.$qstr);
    }

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'ppt' AND fle_type = 'ppt' AND fle_db_id = '".$ppt['ppt_idx']."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $ppt['ppt_f_arr'] = array();
    $ppt['ppt_fidxs'] = array();//개별발주서 파일번호(fle_idx) 목록이 담긴 배열
    $ppt['ppt_lst_idx'] = 0;//개별발주서 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
		@array_push($ppt['ppt_f_arr'],array('file'=>$file_down_del));
		@array_push($ppt['ppt_fidxs'],$row2['fle_idx']);
	}

	//견적서파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
	if(@count($ppt['ppt_fidxs'])) $ppt['ppt_lst_idx'] = $ppt['ppt_fidxs'][0];
}


$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '개별발주'.$html_title;
//include_once('./_top_menu_company.php');
include_once('./_head.php');

?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<style>
.tbl_frm01 td .btn{height:35px;line-height:35px;}
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
<input type="hidden" name="ppt_idx" value="<?php echo $ppt["ppt_idx"] ?>">
<input type="hidden" name="ppc_idx" value="<?php echo $ppt["ppc_idx"] ?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>개별발주관리 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:13%;">
		<col style="width:37%;">
	</colgroup>
	<tbody>
        <tr>
            <th scope="row">관련프로젝트선택</th>
            <td>
                <input type="hidden" name="prj_idx" id="prj_idx" value="<?=$ppt['prj_idx']?>">
                <input type="text" id="prj_name" value="<?=$prj['prj_name']?>" readonly class="frm_input readonly" style="width:200px;">
                <?php if($w == ''){ ?>
                    <a href="javascript:" link="./_win_project_select.php" class="btn btn_02 prj_select">프로젝트선택</a>
                    <script>
                    // 프로젝트선택
                    $('.prj_select').on('click',function(){
                        var href = $(this).attr('link');
                        var win_prj_select = window.open(href, "win_prj_select", "left=10,top=10,width=500,height=800");
                        win_prj_select.focus();
                        return false;
                    });
                    </script>
                <?php } ?>
            </td>
            <th scope="row">공급업체선택</th>
            <td>
                <input type="hidden" name="com_idx" id="com_idx" value="<?=$ppt['com_idx']?>" class="frm_input" style="width:60px;">
                <input type="text" id="com_name" value="<?=$com['com_name']?>" readonly class="frm_input readonly" style="width:120px;">
                <?php if($w == ''){ ?>
                    <a href="javascript:" link="./_win_company_select.php" class="btn btn_02 com_select">업체선택</a>
                    <script>
                    $('.com_select').on('click',function(){
                        var href = $(this).attr('link');
                        var win_com_name = window.open(href,"win_com_select","width=400,height=640");
                        win_com_select.focus();
                        return false;
                    });
                    </script>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">발주날짜</th>
            <td>
                <input type="text" name="ppt_date" id="ppt_date" value="<?=$ppt['ppt_date']?>" class="frm_input" style="width:130px;">
            </td>
            <th scope="row">발주금액</th>
            <td>
                <input type="text" name="ppt_price" value="<?=number_format($ppt['ppt_price'])?>" class="frm_input" style="width:130px;text-align:right;">&nbsp;원
            </td>
        </tr>
        <tr>
            <th scope="row">제목(중요품목)</th>
            <td>
                <input type="text" name="ppt_subject" id="ppt_subject" value="<?=$ppt['ppt_subject']?>" class="frm_input" style="width:100%;">
            </td>
            <th scope="row">상태</th>
            <td>
                <select name="ppt_status" id="ppt_status">
                    <?=$g5['set_ppt_status_value_options']?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="ppt_status"]').val("ok");
                <?php } else { ?>
                    $('select[name="ppt_status"]').val("<?=$ppt['ppt_status']?>");
                <?php } ?>
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">내용</th>
            <td>
                <textarea name="ppt_content" rows="3"><?=$ppt['ppt_content']?></textarea>
            </td>
            <th scope="row"><label for="multi_file_ppt">개별발주 관련파일</label></th>
            <td colspan="3">
                <?php echo help("개별발주관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file_ppt" name="ppt_datas[]" multiple class="">
                <?php
                if(@count($ppt['ppt_f_arr'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($ppt['ppt_f_arr']);$i++) {
                        echo "<li>[".($i+1).']'.$ppt['ppt_f_arr'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01.tbl_wrap -->
<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form><!--//#form01-->
<script>
$(function(){
    //날짜입력
    $("#ppt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name*=_price]',function(e) {
        var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
        price = (price == '0') ? '' : price;
        $(this).val(price);
	});
    

    //개별발주서 멀티파일
	$('#multi_file_ppt').MultiFile();
});

function form01_submit(f) {
	<?php //echo get_editor_js('prj_content'); ?>

    if(!f.prj_idx.value){
        alert('관련프로젝트를 선택해 주세요.');
        $('#prj_name').focus();
		return false;
    }

    if(!f.com_idx.value){
        alert('공급업체를 선택해 주세요.');
        $('#com_name').focus();
		return false;
    }

    if(!f.ppt_date.value){
        alert('발주날짜를 선택해 주세요');
        $('#ppt_date').focus();
		return false;
    }

    if(!f.ppt_subject.value){
        alert('제목(중요품목)을 입력해 주세요.');
        $('#ppt_subject').focus();
		return false;
    }

	if(!f.ppt_status.value){
		alert('상태값을 선택해 주세요');
		f.prt_status.focus();
		return false;
	}

    return true;
}
</script>
<?php
include_once ('./_tail.php');