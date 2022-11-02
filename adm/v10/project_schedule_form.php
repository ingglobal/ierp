<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_schedule';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

$inf_grade_ok = true;

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = rand(1,3);
    ${$pre}['prj_doc_no'] = 'ING-'.rand(131001,139999).'-'.rand(1,9).'a';
    ${$pre}['prj_belongto'] = 'first';
    ${$pre}['prj_price'] = rand(10000000,100000000);
    // ${$pre}[$pre.'_ask_date'] = date("Y-m-d");
    ${$pre}[$pre.'_ask_date'] = date("Y-m-d",time()-86400*3);
    ${$pre}[$pre.'_submit_date'] = date("Y-m-d",time()+86400*1);
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
	$inf_grade_ok = ($member['mb_6'] < 5) ? true : false;
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
	$prj = get_table_meta('project','prj_idx',${$pre}['prj_idx']);
	//print_r3($prj);
    $mb_worker = get_table_meta('member','mb_id',${$pre}['mb_id_worker']);
    $mb_saler = get_table_meta('member','mb_id',${$pre}['mb_id_saler']);
    $mb_account = get_table_meta('member','mb_id',${$pre}['mb_id_account']);

	$prjc = sql_fetch(" SELECT prj_content FROM {$g5['project_table']} WHERE prj_idx = {$prs['prj_idx']} ");
	${$pre}['prj_content'] = $prjc['prj_content'];

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'project' AND fle_type = 'ref' AND fle_db_id = '".$prs['prj_idx']."' ORDER BY fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	//echo $sql;
	//echo $rs->num_rows;echo "<br>";exit;
	$row['prj_f_ref'] = array();
	$row['prj_ref_fidxs'] = array();//견적서 파일번호(fle_idx) 목록이 담긴 배열
	for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'" style="color:blue;">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt']:''.PHP_EOL;
		@array_push($row['prj_f_'.$row2['fle_type']],array('file'=>$file_down_del));
		@array_push($row['prj_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
	}
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '프로젝트일정 '.$html_title;
include_once('./_top_menu_project.php');
include_once ('./_head.php');
echo $g5['container_sub_title'];

//정보등급 5이상의 맴버는 일정의 진행율만 수정가능하다.
$select_disabled = (!$inf_grade_ok && !$copy) ? ' readonly style="background:#efefef;color:#999;" onFocus="this.initialSelect=this.selectedIndex;" onChange="this.selectedIndex=this.initialSelect;"' : '';
$date_class_change = (!$inf_grade_ok && !$copy) ? '_' : '';
$data_readonly = (!$inf_grade_ok && !$copy) ? ' readonly' : '';
$data_bg_change = (!$inf_grade_ok && !$copy) ? 'background:#efefef !important;color:#999;' : '';


//print_r3($prs);
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="url" value="<?php echo $url ?>">
<input type="hidden" name="gant" value="<?php echo $gant ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">

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
		<th scope="row">프로젝트</th>
		<td>
            <input type="hidden" name="prj_idx" id="prj_idx" value="<?=$prs['prj_idx']?>">
			<input type="text" id="prj_name" value="<?=$prj['prj_name']?>" readonly required class="frm_input readonly required" style="width:200px;<?=$data_bg_change?>">
			<?php if($inf_grade_ok){ ?>
				<a href="javascript:" link="./_win_project_select.php" class="btn btn_02 prj_select">프로젝트선택</a>
			<?php } ?>
		</td>
		<th scope="row">역할</th>
		<td>
			<select name="prs_role" id="prs_role" title="역할" required class=""<?=$select_disabled?>>
				<option value="">역할선택</option>
				<?php echo $g5['set_worker_type_value_options']?>
			</select>
			<script>
			$('select[name=prs_role]').val("<?=$prs['prs_role']?>").attr('selected','selected');
			</script>
		</td>
	</tr>
	<tr>
		<th scope="row">담당자</th>
		<td>
            <input type="hidden" name="mb_id_worker" id="mb_id_worker" value="<?=$prs['mb_id_worker']?>">
            <input type="text" id="mb_name" value="<?=$mb_worker['mb_name']?>" readonly required class="frm_input readonly required" style="width:100px;<?=$data_bg_change?>">
			<?php if($inf_grade_ok){ ?>
			<a href="javascript:" link="./_win_worker_select.php" class="btn btn_02 wrk_select">찾기</a>
			<?php } ?>
		</td>
		<th scope="row">일정타입</th>
		<td>
			<select name="prs_type" id="prs_type" title="일정타입" required class=""<?=$select_disabled?>>
				<option value="">일정타입을 선택하세요.</option>
				<?php echo $g5['set_prs_type_value_options']?>
			</select>
            <span id="span_prs_percent" style="margin-left:5px;display:<?=(!in_array($prs['prs_type'],$g5['setting']['set_prs_rate_display_array']))?'none':''?>;">
                <input type="text" name="prs_percent" value="<?=number_format($prs['prs_percent'])?>"
                class="frm_input" style="width:28px;text-align:center;">%
            </span>
            <script>
            $('select[name=prs_type]').val("<?=$prs['prs_type']?>").attr('selected','selected');
			var arr1 = ['<?=implode("','",$g5['setting']['set_prs_rate_display_array'])?>'];
            $(document).on('change','#prs_type',function(e){
                // alert($(this).val());
                // if($(this).val()=='mine'||$(this).val()=='gov') {
                if($.inArray($(this).val(),arr1)!=-1) {
                    $('#span_prs_percent').show();
                }
                else {
                    $('#span_prs_percent').hide();
                }
            });
            </script>
		</td>
	</tr>
	<tr>
		<th scope="row">시작일</th>
		<td>
			<input type="text" name="prs_start_date" value="<?=$prs['prs_start_date']?>" required<?=$data_readonly?> class="frm_input required" style="width:90px;<?=$data_bg_change?>">
		</td>
		<th scope="row">부서선택</th>
		<td>
			<select name="prs_department" id="prs_department" title="부서선택" class=""<?=$select_disabled?>>
				<option value="">부서선택</option>
				<?php echo $g5['set_department_name_value_options']?>
			</select>
			<script>
			$('select[name=prs_department]').val("<?=$prs['prs_department']?>").attr('selected','selected');
			</script>
		</td>
	</tr>
	<tr>
		<th scope="row">종료일</th>
		<td colspan="3">
            <input type="text" name="prs_end_date" value="<?=$prs['prs_end_date']?>" required<?=$data_readonly?> class="frm_input required" style="width:90px;<?=$data_bg_change?>">
		</td>
	</tr>	
	<tr>
		<th scope="row">업무내용</th>
		<td colspan="3">
			<input type="text" name="prs_task" value="<?=$prs['prs_task']?>"<?=$data_readonly?> class="frm_input" style="width:60%;<?=$data_bg_change?>">
		</td>
	</tr>
	<?php if($w == 'u'){ ?>
 	<tr>
		<th scope="row">프로젝트<br>지시사항</th>
		<td colspan="3" <?=(($super_ceo_admin)?'':'style="white-space:pre-line;"')?>>
			<?php if($super_ceo_admin){ ?>
				<textarea name="prj_content" style="height:400px;"><?php echo $prs['prj_content'] ?></textarea>
			<?php } else { ?>
				<?php echo $prs['prj_content'] ?>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<th scope="row">프로젝트<br>기초자료파일</th>
		<td colspan="3" style="">
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
	<?php } ?>
	<tr>
		<th scope="row">담당<br>업무기록</th>
		<td colspan="3"><textarea name="prs_content"<?=$data_readonly?> id="prs_content" style="<?=$data_bg_change?>"><?php echo $prs['prs_content'] ?></textarea></td>
	</tr>
	<tr>
		<th scope="row"><label for="com_status">상태</label></th>
		<td colspan="3">
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"<?=$select_disabled?>>
				<?=$g5['set_prs_status_options']?>
			</select>
			<script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>
<!--
project_schedule_form.php?gant=1&w=u&prs_idx=	
-->
<?php
$gant_val = ($gant) ? '&amp;gant=1' : '';
?>
<div class="btn_fixed_top">
	<?php if($w == 'u'){ ?>
	<?php if(!($member['mb_level'] < 8 && $member['mb_2'] != 3) && !$copy){ ?>
	<input type="submit" name="btn_submit" value="삭제" class="btn btn_02" onclick="document.pressed=this.value">
	<?php } ?>
	<?php if(!$copy){ ?>
    <a href="./project_schedule_form.php?copy=1<?=$gant_val?>&amp;w=u&amp;prs_idx=<?=$prs_idx?><?=(($qstr) ? '&amp;'.$qstr : '')?>" class="btn btn_03">복제모드</a>
	<?php } else { ?>
	<a href="./project_schedule_form.php?w=u<?=$gant_val?>&amp;prs_idx=<?=$prs_idx?><?=(($qstr) ? '&amp;'.$qstr : '')?>" class="btn btn_02">복제취소</a>
	<?php } ?>
	<?php } ?>
    <a href="./project_gantt.php" class="btn btn_02">일정</a>
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="<?=(($copy) ? '복제':'확인')?>" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    $(document).on('click','.btn_item_target',function(e){
        var shf_idx = $(this).attr('shf_idx');
        var shf_no = $(this).attr('shf_no');
        // alert( shf_idx +'/'+ shf_no );
		var url = "./shift_item_goal_list.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
		win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_item_goal.focus();
    });
	<?php if($inf_grade_ok){ ?>
	$("input[name='prs_start_date'],input[name='prs_end_date']").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	<?php } ?>
		
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});
	
	$('.prj_select').on('click',function(){
		var href = $(this).attr('link');
		var win_prj_select = window.open(href, "win_prj_select", "left=10,top=10,width=500,height=800");
		win_prj_select.focus();
		return false;
	});
	$('.wrk_select').on('click',function(){
		var href = $(this).attr('link');
		var win_wrk_select = window.open(href, "win_wrk_select", "left=10,top=10,width=500,height=800");
		win_wrk_select.focus();
		return false;
	});

	<?php if($w == 'u' && $copy) { ?>
	$('input[name="w"]').val('');
	$('input[name="prs_idx"]').val('');
	<?php } ?>
});
//2021.06.25 이후 수정 버전
function form01_submit(f) {
	if(f.mb_id_worker.value == 'iljung' && f.mb_name.value == '일정관리'){
		if(f.prs_department.value){
			$('select[name=prs_department]').val('').attr('selected','selected');
			alert('[일정관리(iljung)]는 부서를 선택할 필요없습니다.');
			return false;
		}
	}else{
		if(!f.prs_department.value){
			alert('부서를 선택해 주세요.');
			return false;
		}
	}
	
	if(document.pressed == "삭제") {
		if (!confirm("게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다."))
			return false;

		$('input[name="w"]').val('d');
	}

    return true;
}
/*
2021.06.25 이전 버전
function form01_submit(f) {
	if(f.prs_role.value == 'pm'){
		if(f.mb_id_worker.value != 'iljung' && f.mb_name.value != '일정관리'){
			alert('PM역할은 [일정관리(iljung)]라는 담당자만 맡을 수 있습니다.');
			return false;
		}

		if(f.prs_department.value){
			$('select[name=prs_department]').val('').attr('selected','selected');
			alert('PM역할은 부서를 선택할 필요없습니다.');
			return false;
		}
	}else{
		
		if(f.mb_id_worker.value == 'iljung' && f.mb_name.value == '일정관리'){
			if(f.prs_department.value){
				$('select[name=prs_department]').val('').attr('selected','selected');
				alert('[일정관리(iljung)]는 부서를 선택할 필요없습니다.');
				return false;
			}
		}else{
			if(!f.prs_department.value){
				alert('부서를 선택해 주세요.');
				return false;
			}
		}

	}
	
	if(document.pressed == "삭제") {
		if (!confirm("게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다."))
			return false;

		$('input[name="w"]').val('d');
	}

    return true;
}
*/
</script>

<?php
include_once ('./_tail.php');
?>
