<?php
$sub_menu = "960215";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
	$row['prj_type'] = 'normal';
    
}
else if ($w == 'u') {
	//request=견적요청,inprocess=견적중, pending=보류, ng=수주취소, ok=수주완료,etc=기타, trash=삭제
    $sql = " SELECT * FROM {$g5['project_table']} WHERE prj_idx = '{$prj_idx}' ";
	$row = sql_fetch($sql,1);
	$prc_sql = " SELECT prp_price, prp_type FROM {$g5['project_price_table']} WHERE prj_idx = '{$prj_idx}' AND prp_type IN ('submit','nego','order') AND prp_status = 'ok' ";
	$prc_result = sql_query($prc_sql,1);
	
	$csql = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '{$row['com_idx']}' ");
	$row['com_name'] = $csql['com_name'];
	//print_r3($row);
	
	/*
	// 관련 파일(post_file) 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'project' AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	//echo $sql;
	for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$row[$row2['fle_type']][$row2['fle_sort']]['file'] = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_sort'].']" value="1"> 삭제'
							:'';
		$row[$row2['fle_type']][$row2['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? 
							$row2['fle_name'] : '' ;
		$row[$row2['fle_type']][$row2['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? 
							$row2['fle_path'] : '' ;
		$row[$row2['fle_type']][$row2['fle_sort']]['exists'] = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? 
							1 : 0 ;
	}
	*/
	//관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'project' AND fle_type = 'ref' AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	//echo $rs->num_rows;echo "<br>";
	$row['prj_f_ref'] = array();
	$row['prj_ref_fidxs'] = array();//견적서 파일번호(fle_idx) 목록이 담긴 배열
	for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
		@array_push($row['prj_f_'.$row2['fle_type']],array('file'=>$file_down_del));
		@array_push($row['prj_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
	}
	


	$exsc_sql = " SELECT prs_task, prs_content, mb_id_worker, mb_name, prs_start_date, prs_end_date FROM {$g5['project_schedule_table']} prs
					LEFT JOIN {$g5['member_table']} mb ON prs.mb_id_worker = mb.mb_id
					WHERE prj_idx = '{$row['prj_idx']}' 
						AND prs_status != 'trash'
					ORDER BY prs_start_date
	";
	// echo $exsc_sql;exit;
	$exscres = sql_query($exsc_sql,1);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '프로젝트 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];
/*
Array
(
    [prj_idx] => 9
    [com_idx] => 1
    [mb_id_company] => test01
    [mb_id_saler] => test02
    [mb_id_account] => 
    [prj_doc_no] => ING-138169-9a
    [prj_name] => 4축 트랜스퍼
    [prj_end_company] => 이앤에프㈜
    [prj_content] => 
    [prj_belongto] => first
    [prj_receivable] => 0
    [prj_percent] => 0
    [prj_keys] => 
    [prj_status] => ok
    [prj_ask_date] => 2020-09-06
    [prj_submit_date] => 2020-09-10
    [prj_reg_dt] => 2020-09-09 11:51:28
    [prj_update_dt] => 2020-09-10 15:30:45
)
*/
//print_r3($row);
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<?php
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>
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
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo $prj_idx; ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적추가 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
	</colgroup>
	<tbody>
    <tr>
		<th scope="row">업체선택</th>
		<td>
			<input type="hidden" name="com_idx" id="com_idx" value="<?=$row['com_idx']?>" required class="frm_input required" style="width:60px;">
			<input type="text" id="com_name" value="<?=$row['com_name']?>" readonly required class="frm_input readonly required" style="width:120px;">
			<a href="javascript:" link="./_win_company_select.php" class="btn btn_02 com_select">업체선택</a>
			<script>
			$('.com_select').on('click',function(){
				var href = $(this).attr('link');
				var win_com_name = window.open(href,"win_com_select","width=400,height=640");
				win_com_select.focus();
				return false;
			});
			</script>
		</td>
		<th scope="row">프로젝트타입</th>
		<td>
			<select name="<?=$pre?>_type" id="<?=$pre?>_type">
				<option value="">타입선택</option>
				<?=$g5['set_prj_type_options']?>
			</select>
			<script>$('select[name="prj_type"]').val("<?=$row['prj_type']?>");</script>
		</td>
	</tr>
	<tr>
		<th scope="row">프로젝트명</th>
		<td>
			<?php $preadonly = ($w != '' && $member['mb_level'] < 8) ? ' readonly' : ''; ?>
			<input type="text" name="prj_name" value="<?=$row['prj_name']?>" required<?=$preadonly?> class="frm_input required<?=$preadonly?>" style="width:250px;">&nbsp;&nbsp;<span style="color:red;">프로젝트명은 수정불가</span>
		</td>
		<th scope="row">진행율</th>
		<td>
			<input type="text" name="prj_percent" value="<?=$row['prj_percent']?>" class="frm_input" style="width:130px;">&nbsp;%
		</td>
	</tr>
	<?php if($w != '') { ?>
	<tr>
		<th scope="row">프로젝트명 수정요청</th>
		<td colspan="3">
			<?php echo help("프로젝트명을 아래와 같이 수정 요청드립니다.<br>(수정하셨으면 아래 입력란은 <span style='color:red;'>공란</span>으로 만들어 주셔야 <span style='color:red;'>알람이 사라집니다</span>.)"); ?>
			<input type="text" name="prj_name_req" value="<?=$row['prj_name_req']?>" class="frm_input" style="width:250px;">
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th scope="row">최종고객</th>
		<td<?=(($super_admin || in_array($member['mb_id'],$super_mng_arr))?'':' colspan="3"')?>>
			<input type="text" name="prj_end_company" value="<?=$row['prj_end_company']?>" class="frm_input" style="width:250px;">
		</td>
		<?php if($super_admin || in_array($member['mb_id'],$super_mng_arr)){ ?>
		<th scope="row"><label for="com_status">상태</label></th>
		<td>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status">
				<?=$g5['set_prj_status_options']?>
			</select>
			<script>$('select[name="prj_status"]').val("<?=$row['prj_status']?>");</script>
		</td>
		<?php } ?>
	</tr>
	<tr>
		<th scope="row">프로젝트 지시사항</th>
		<td colspan="3">
			<?php //echo editor_html('prj_content', get_text(html_purifier($row['prj_content']), 0)); ?>
			<textarea name="prj_content"><?=$row['prj_content']?></textarea>
		</td>
	</tr>
	<?php if($super_admin || in_array($member['mb_id'],$super_mng_arr)){ ?>
	<tr>
		<th scope="row">수입지출 지시사항</th>
		<td colspan="3">
			<?php //echo editor_html('prj_content2', get_text(html_purifier($row['prj_content2']), 0)); ?>
			<textarea name="prj_content2"><?=$row['prj_content2']?></textarea>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th scope="row"><label for="prj_ref_file">기초자료파일</label></th>
		<td colspan="3">
			<?php echo help("프로젝트 관련해서 참고 할 자료가 있으면 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" id="prj_ref_file" name="prj_ref_files[]" multiple class="">
			<?php
			if(@count($row['prj_f_ref'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_ref']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_ref'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<?php if($exscres->num_rows){ ?>
<div id="ex_con" class="tbl_frm01 tbl_wrap">
<h2>업무일정담당자 업무기록</h2>
<table>
	<caption>업무일정담당자 업무기록</caption>
	<colgroup>
		<col class="grid_4" style="width:20%;">
		<col style="width:80%;">
	</colgroup>
	<tbody>
		<?php for($i=0;$row=sql_fetch_array($exscres);$i++){ ?>
		<tr>
			<th>
			<p class="p_ttl" style="color:darkred;"><?=cut_str($row['prs_task'],30,'...')?></p>
			<p class="p_mb" style="color:darkblue;"><?=$row['mb_name']?></p>
			<p class="p_scd"><?=$row['prs_start_date']?> ~ <?=$row['prs_end_date']?></p>
			</th>
			<td style="vertical-align:top;text-align:left;"><?=nl2br($row['prs_content'])?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
</div>
<?php } ?>

<script>
$(function() {
    //$(document).on('click','.btn_item_target',function(e){
    //    var shf_idx = $(this).attr('shf_idx');
    //    var shf_no = $(this).attr('shf_no');
    //    // alert( shf_idx +'/'+ shf_no );
	//	var url = "./shift_item_goal_list.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
	//	win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
    //    win_item_goal.focus();
    //});
	$("#prj_ask_date, #prj_submit_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name*=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

	//기초자료 멀티파일
	$('#prj_ref_file').MultiFile();
});

function form01_submit(f) {
	<?php //echo get_editor_js('prj_content'); ?>
	
	if(!f.prj_type.value){
		alert('프로젝트타입을 선택하세요.');
		f.prj_type.focus();
		return false;
	}
	
	if(!f.prj_status.value){
		alert('상태값을 선택하세요.');
		f.prj_status.focus();
		return false;
	}
	
    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
