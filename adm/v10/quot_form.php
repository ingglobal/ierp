<?php
$sub_menu = "960210";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],'w');

//data폴더에 ierp 폴더(각종파일을 저장하는 디렉토리) 생성
$data_ierp_dir_path = G5_DATA_PATH.'/ierp';
$ierp_permision_str = "chmod 707 -R ".$data_ierp_dir_path;

$mb_mng_flag = $member['mb_manager_yn'];

if(!is_dir($data_ierp_dir_path)){
	@mkdir($data_ierp_dir_path, G5_DIR_PERMISSION);
	@chmod($data_ierp_dir_path, G5_DIR_PERMISSION);

	exec($ierp_permision_str);
}

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
	$row['prj_status'] = 'inprocess';

}
else if ($w == 'u') {
    $sql = " SELECT * FROM {$g5['project_table']} WHERE prj_idx = '{$prj_idx}' ";
	$row = sql_fetch($sql,1);
	$prc_sql = " SELECT prp_price, prp_type FROM {$g5['project_price_table']} WHERE prj_idx = '{$prj_idx}' AND prp_type IN ('submit','nego','order') AND prp_status = 'ok' ";
	$prc_result = sql_query($prc_sql,1);
	$row['prj_price_submit'] = 0;
	$row['prj_price_nego'] = 0;
	$row['prj_price_order'] = 0;
	for($i=0;$prow=sql_fetch_array($prc_result);$i++){
		//print_r3($prow);
		if($prow['prp_type'] == 'submit') $row['prj_price_submit'] = number_format($prow['prp_price']);
		else if($prow['prp_type'] == 'nego') $row['prj_price_nego'] = number_format($prow['prp_price']);
		else if($prow['prp_type'] == 'order') $row['prj_price_order'] = number_format($prow['prp_price']);
	}

	$csql = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '{$row['com_idx']}' ");
	$row['com_name'] = $csql['com_name'];
	//print_r3($row);

	//관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
			WHERE fle_db_table IN ('quot','project') AND fle_type IN ('quot','order','contract','ref') AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	//echo $rs->num_rows;echo "<br>";
	$row['prj_f_quot'] = array();
	$row['prj_quot_fidxs'] = array();//견적서 파일번호(fle_idx) 목록이 담긴 배열
	$row['prj_qf_lst_idx'] = 0;//견적서 파일중에 가장 최신버전의 파일번호(fle_idx);
	$row['prj_f_order'] = array();
	$row['prj_order_fidxs'] = array();
	$row['prj_f_contract'] = array();
	$row['prj_contract_fidxs'] = array();
	$row['prj_f_ref'] = array();
	$row['prj_ref_fidxs'] = array();
	for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
		@array_push($row['prj_f_'.$row2['fle_type']],array('file'=>$file_down_del));
		@array_push($row['prj_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
	}
	//견적서파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
	if(@count($row['prj_quot_fidxs'])) $row['prj_qf_lst_idx'] = $row['prj_quot_fidxs'][0];
}
else
	alert('제대로 된 값이 넘어오지 않았습니다.');

//print_r2($row['prj_quot_idxs']);
//echo $row['prj_qf_lst_idx'];
//exit;

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}
$w = ($copy) ? '' : $w;
$prj_idx = ($copy) ? '' : $prj_idx;
$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '견적 '.$html_title;
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
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<?php
//echo " UPDATE {$g5['project_table']} SET prj_order_price = '{$prj_price_order}' WHERE prj_idx = '".${$pre."_idx"}."' ";
//print_r3($row);
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}

//print_r3($g5[]);
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
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
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
		<th scope="row">견적업체담당자</th>
		<td>
			<?php
			$sbsql = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_company']}' ");
			?>
			<input type="hidden" name="mb_id_company" id="mb_id_company" value="<?=$row['mb_id_company']?>">
			<input type="text" value="<?=$sbsql['mb_name']?>" id="mb_name_sb" readonly class="frm_input readonly" style="width:60px;">
			<a href="javascript:" link="./_win_submitter_select.php" class="btn btn_02 submitter_select">담당자선택</a>
			<button type="button" class="btn btn_02 cid_del">삭제</button>
			<script>
			$('.submitter_select').on('click',function(){
				if(!$('#com_idx').val()){
					alert('업체를 먼저 선택해 주세요.');
					$('#com_idx').focus();
					return false;
				}
				var href = $(this).attr('link')+'?com_idx='+$('#com_idx').val();
				var win_submitter_name = window.open(href,"win_submitter_select","width=400,height=640");
				win_submitter_name.focus();
				return false;
			});
			$('.cid_del').on('click',function(){
				$('#mb_id_company').val('');
				$('#mb_name_sb').val('');
			});
			</script>
		</td>
	</tr>
	<tr>
		<th scope="row">업체회계담당자</th>
		<td>
			<?php
			$acsql = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_account']}' ");
			?>
			<input type="hidden" name="mb_id_account" id="mb_id_account" value="<?=$row['mb_id_account']?>">
			<input type="text" value="<?=$acsql['mb_name']?>" id="mb_name_ac" readonly class="frm_input readonly" style="width:60px;">
			<a href="javascript:" link="./_win_account_select.php" class="btn btn_02 account_select">회계담당자</a>
			<button type="button" class="btn btn_02 act_del">삭제</button>
			<script>
			$('.account_select').on('click',function(){
				if(!$('#com_idx').val()){
					alert('업체를 먼저 선택해 주세요.');
					$('#com_idx').focus();
					return false;
				}
				var href = $(this).attr('link')+'?com_idx='+$('#com_idx').val();
				var win_account_name = window.open(href,"win_account_select","width=400,height=640");
				win_account_name.focus();
				return false;
			});
			$('.act_del').on('click',function(){
				$('#mb_id_account').val('');
				$('#mb_name_ac').val('');
			});
			</script>
		</td>
		<th scope="row">아이엔지 영업담당</th>
		<td>
			<?php
			$slsql = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_saler']}' ");
			?>
			<input type="hidden" name="mb_id_saler" id="mb_id_saler" value="<?=$row['mb_id_saler']?>" required>
			<input type="text" value="<?=$slsql['mb_name']?>" id="mb_name_sl" required readonly class="frm_input required readonly" style="width:60px;">
			<a href="javascript:" link="./_win_saler_select.php" class="btn btn_02 saler_select">영업자선택</a>
			<script>
			$('.saler_select').on('click',function(){
				var href = $(this).attr('link');
				var win_saler_name = window.open(href,"win_saler_select","width=400,height=640");
				win_saler_name.focus();
				return false;
			});
			</script>
		</td>
	</tr>
	<tr>
		<th scope="row">프로젝트명</th>
		<td>
			<?php
			//$pjname_readonly = ($row['prj_status'] == 'ok' && $member['mb_manager_yn']) ? '' : ' readonly';
			//$pjname_readonly = ($member['mb_manager_yn']) ? '' : ' readonly';
			$preadonly = ($w != '' && $member['mb_level'] < 8) ? ' readonly' : '';
			?>
			<input type="text" name="prj_name" id="prj_name" value="<?=$row['prj_name']?>" required<?=$preadonly?> class="frm_input required<?=$preadonly?>" style="width:250px;">
		</td>
		<th scope="row">발행번호</th>
		<td>
			<input type="text" name="prj_doc_no" value="<?=$row['prj_doc_no']?>" class="frm_input" style="width:130px;">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="com_status">프로젝트타입</label></th>
		<td colspan="3">
			<select name="<?=$pre?>_type" id="<?=$pre?>_type">
				<?=$g5['set_prj_type2_options']?>
			</select>
			<script>$('select[name="prj_type"]').val("<?=$row['prj_type']?>");</script>
		</td>
	</tr>
	<tr>
		<th scope="row">최종고객</th>
		<td>
			<input type="text" name="prj_end_company" value="<?=$row['prj_end_company']?>" class="frm_input" style="width:250px;">
		</td>
		<th scope="row"><label for="com_status">상태</label></th>
		<td>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status">
				<?=$g5['set_prj_status_value_options']?>
			</select>
			<script>$('select[name="prj_status"]').val("<?=$row['prj_status']?>");</script>
		</td>
	</tr>
	<tr>
		<th scope="row">요청날짜</th>
		<td>
			<input type="text" name="prj_ask_date" id="prj_ask_date" value="<?=$row['prj_ask_date']?>" class="frm_input" style="width:130px;">
		</td>
		<th scope="row">견적제출날짜</th>
		<td>
			<input type="text" name="prj_submit_date" id="prj_submit_date" value="<?=$row['prj_submit_date']?>" class="frm_input" style="width:130px;">
			<?php if($prj_idx && $row['prj_qf_lst_idx']){ ?>
				<button type="button" class="btn btn_03 quot_email_send" fle_idx="<?=$row['prj_qf_lst_idx']?>">견적메일전송</button>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<th scope="row">수주날짜</th>
		<td>
			<input type="text" name="prj_contract_date" id="prj_contract_date" value="<?=$row['prj_contract_date']?>" class="frm_input" style="width:130px;">
		</td>
		<th scope="row">제출금액</th>
		<td>
			<input type="text" name="prj_price_submit" value="<?=$row['prj_price_submit']?>" class="frm_input" style="width:130px;text-align:right;">&nbsp;원
		</td>
	</tr>
	<tr>
		<th scope="row">NEGO금액</th>
		<td>
			<input type="text" name="prj_price_nego" value="<?=$row['prj_price_nego']?>" class="frm_input" style="width:130px;text-align:right;">&nbsp;원
		</td>
		<th scope="row">수주금액</th>
		<td>
			<input type="text" name="prj_price_order" value="<?=$row['prj_price_order']?>" class="frm_input" style="width:130px;text-align:right;">&nbsp;원
		</td>
	</tr>
	<tr>
		<!--th scope="row">미수금</th>
		<td>
			<input type="text" name="prj_receivable" value="<?=$row['prj_receivable']?>" class="frm_input" style="width:130px;">&nbsp;원
		</td-->
		<th scope="row">진행율</th>
		<td>
			<input type="text" name="prj_percent" value="<?=$row['prj_percent']?>" class="frm_input" style="width:130px;text-align:right;">&nbsp;%
		</td>
		<th scope="row">자사,타사</th>
		<td>
			<select name="prj_belongto" id="prj_belongto">
				<?=$g5['set_prj_belongto_value_options']?>
			</select>
			<script>$('select[name="prj_belongto"]').val("<?=$row['prj_belongto']?>");</script>
		</td>
	</tr>
	<tr>
		<th scope="row">프로젝트<br>지시사항</th>
		<td>
			<?php //echo editor_html('prj_content', get_text(html_purifier($row['prj_content']), 0)); ?>
			<textarea name="prj_content"><?=$row['prj_content']?></textarea>
		</td>
		<th scope="row">수입지출<br>지시사항</th>
		<td>
			<?php //echo editor_html('prj_content2', get_text(html_purifier($row['prj_content2']), 0)); ?>
			<textarea name="prj_content2"><?=$row['prj_content2']?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="multi_file_q">견적서 파일</label></th>
		<td colspan="3">
			<?php echo help("견적서 파일들을 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" id="multi_file_q" name="prj_q_datas[]" multiple class="">
			<?php
			if(@count($row['prj_f_quot'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_quot']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_quot'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="multi_file_o">발주서 파일</label></th>
		<td colspan="3">
			<?php echo help("발주서 파일들을 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" id="multi_file_o" name="prj_o_datas[]" multiple class="">
			<?php
			if(@count($row['prj_f_order'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_order']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_order'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="multi_file_c">계약서 파일</label></th>
		<td colspan="3">
			<?php echo help("계약서 파일들을 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" id="multi_file_c" name="prj_c_datas[]" multiple class="">
			<?php
			if(@count($row['prj_f_contract'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_contract']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_contract'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
		</td>
	</tr>
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
<form id="formemail">
	<input type="hidden" name="prj_idx" value="">
	<input type="hidden" name="com_idx" value="">
	<input type="hidden" name="mb_id" value="">
	<input type="hidden" name="fle_idx" value="">
</form>
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
	$("#prj_ask_date, #prj_submit_date, #prj_contract_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name*=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

	<?php if($prj_idx && $row['prj_qf_lst_idx']){ ?>
	$('.quot_email_send').on('click',function(){

		if(!confirm('설정한 금액으로 정말로 견적메일을 전송 하시겠습니까?\n신중하게 결정하세요~!')){
			return false;
		}
		
		var ajax_quot_mail_url = '<?=G5_USER_ADMIN_AJAX_URL?>/ajax_quot_email_send.php';
		var prj_idx = $('input[name="prj_idx"]').val();
		var com_idx = $('#com_idx').val();
		var mb_id = $('#mb_id_company').val();
		var fle_idx = $(this).attr('fle_idx');
		/*
		var frm = document.getElementById('formemail');
		frm.action = ajax_quot_mail_url;
		frm.method = "POST";
		frm.prj_idx.value = prj_idx;
		frm.com_idx.value = com_idx;
		frm.mb_id.value = mb_id;
		frm.fle_idx.value = fle_idx;
		frm.submit();
		return false;
		*/
		$.ajax({
			type: "POST",
			url: ajax_quot_mail_url,
			dataType: "text",
			data: {"prj_idx":prj_idx,"com_idx":com_idx,"mb_id":mb_id,"fle_idx":fle_idx},
			success:function(res) {
				//alert(res);
				if(res != 'email_success'){
					alert('메일전송에 실패했습니다. 이메일정보의 유무를 확인해 주세요.');
				}else{
					alert('메일전송에 성공했습니다.');
					$('#prj_submit_date').val(getFormattedDate(new Date()));
				}
			},
			error: function(req) {
				alert('Status: ' + req.status + ' \n\rstatusText: ' + req.statusText + ' \n\rresponseText: ' + req.responseText);
			}
		});

		//$('#prj_submit_date').val(getFormattedDate(new Date()));
	});
	<?php } //if($prj_idx && $row['prj_qf_lst_idx']){ ?>


	//견적서 멀티파일
	$('#multi_file_q').MultiFile();
	//발주서 멀티파일
	$('#multi_file_o').MultiFile();
	//계약서 멀티파일
	$('#multi_file_c').MultiFile();
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
		alert('상태값을 선택해 주세요');
		f.prj_status.focus();
		return false;
	}
	if(!f.prj_belongto.value){
		alert('자사/타사를 선택해 주세요');
		f.prj_belongto.focus();
		return false;
	}
    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
