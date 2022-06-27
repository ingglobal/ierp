<?php
$sub_menu = "960240";
include_once('./_common.php');
//include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_price';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

//print_r3($fields);

if ($w == '') {
	${$pre}['prj_idx'] = ($prj_idx) ? $prj_idx : '';
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}['com_idx'] = rand(1,3);
    ${$pre}['prj_doc_no'] = 'ING-'.rand(131001,139999).'-'.rand(1,9).'a';
    ${$pre}['prj_belongto'] = 'first';
    ${$pre}['prj_price'] = rand(10000000,100000000);
    // ${$pre}[$pre.'_ask_date'] = date("Y-m-d");
    ${$pre}[$pre.'_ask_date'] = date("Y-m-d",time()-86400*3);
    ${$pre}[$pre.'_submit_date'] = date("Y-m-d",time()+86400*1);
    ${$pre}[$pre.'_status'] = 'pending';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
	//$row = ${$pre};
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
    $mb_company = get_table_meta('member','mb_id',${$pre}['mb_id_company']);
    $mb_saler = get_table_meta('member','mb_id',${$pre}['mb_id_saler']);
    $mb_account = get_table_meta('member','mb_id',${$pre}['mb_id_account']);

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
			WHERE fle_db_table = '".$pre."' AND fle_db_id = '".${$pre."_idx"}."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	//echo $sql."<br>";
	//echo $pre;exit;
	//print_r2($rs);exit;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		${$pre}[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ?
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ?
							$row['fle_name'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ?
							$row['fle_path'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ?
							1 : 0 ;
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
$g5['title'] = '수입관리 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부

$items1 = array(
    "prj_idx"=>array("프로젝트번호","required",60,0,'','',2)
	,"prp_type"=>array("금액타입","",130,0,'','',0)
	,"prp_pay_no"=>array("중도금차수","",130,0,'','',2)
	,"prp_price"=>array("금액","",130,0,'','',0)
	,"prp_content"=>array("지시내용","",130,0,'','',2)
	,"prp_content2"=>array("미수내용","",130,0,'','',0)
	,"prp_doc_deal"=>array("거래명세표","",130,0,'','',2)
	,"prp_plan_date"=>array("발행예정일","",130,0,'','',0)
	,"prp_issue_date"=>array("계산서발행일","",130,0,'','',2)
	,"prp_planpay_date"=>array("수금예정일","",130,0,'','',0)
	,"prp_pay_date"=>array("수금완료일","",130,0,'','',0)
	,"prp_status"=>array("상태","",130,0,'','',2)
);

/*
${$pre}['prp_idx'] => 59
${$pre}['prj_idx'] => 1
${$pre}['prp_type'] => remainder
${$pre}['prp_pay_no'] => 1
${$pre}['prp_price'] => 10000000
${$pre}['prp_content'] =>
${$pre}['prp_content2'] =>
${$pre}['prp_doc_deal'] =>
${$pre}['prp_plan_date'] => 0000-00-00
${$pre}['prp_issue_date'] => 0000-00-00
${$pre}['prp_pay_date'] => 0000-00-00
${$pre}['prp_status'] => ok
${$pre}['prp_reg_dt'] => 2020-09-11 22:15:32
${$pre}['prp_update_dt'] => 2020-09-11 22:15:32
print_r3(${$pre});
*/
?>
<?php if($w == ''){  //신규등록페이지 ===============================================================
//submit=제출금액,nego=NEGO금액,order=수주금액,deposit=계약금,middle1=중도금1,middle2=중도금2,middle3=중도금3,middle4=중도금4,middle5=중도금5,remainder=잔금,all=전체금액,manday=인건비,buy=매입대금,etc=기타비용
$ord_price = sql_fetch(" SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = '{$prj_idx}' AND prp_type = 'order' AND prp_status = 'ok' ");
//$prp_order_price = number_format($ord_price['prp_price']);
$psql = "   SELECT *
		, IF( prp_type IN ('manday','buy','etc'), prp_price*-1, prp_price ) AS prp_price2
		, IF( prp_type IN ('manday','buy','etc'), 2, 1 ) AS prp_sort
		FROM {$g5['project_price_table']}
		WHERE prj_idx = '".$prj_idx."'
			AND prp_type NOT IN ('submit','nego','order')
			AND prp_status NOT IN ('trash','delete')
		ORDER BY prp_sort, prp_type, prp_reg_dt
";
$p_result = sql_query($psql);
$p_cnt = $p_result->num_rows;
?>
<h2>이전 수금등록내역</h2>
<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption>이전 등록현황</caption>
	<thead>
		<tr>
			<th scope="col">타입</th>
			<th scope="col">금액</th>
			<th scope="col">수금예정일</th>
			<th scope="col">수금완료일</th>
		</tr>
	</thead>
		<?php for($i=0;$row=sql_fetch_array($p_result);$i++){ ?>
		<tr>
			<td><?=$g5['set_price_type_value'][$row['prp_type']]?></td>
			<td style="text-align:right;"><?=number_format($row['prp_price'])?>원</td>
			<td><?=(($row['prp_planpay_date'] != '0000-00-00') ? $row['prp_planpay_date'] : '-')?></td>
			<td><?=(($row['prp_pay_date'] != '0000-00-00') ? $row['prp_pay_date'] : '-')?></td>
		</tr>
		<?php } ?>
		<?php if($p_cnt == 0){ ?>
		<tr><td class="td_empty" colspan="4">등록된 이전 데이터가 없습니다.</td></tr>
		<?php } ?>
	</table>
</div>
<?php
}
$p2sql = "   SELECT *
	, IF( prp_type IN ('manday','buy','etc'), prp_price*-1, prp_price ) AS prp_price2
	, IF( prp_type IN ('manday','buy','etc'), 2, 1 ) AS prp_sort
	FROM {$g5['project_price_table']}
	WHERE prj_idx = '".$prj_idx."'
	AND prp_type NOT IN ('submit','nego','order')
	AND prp_status NOT IN ('trash','delete')
	ORDER BY prp_sort, prp_type, prp_reg_dt
";
$p2_result = sql_query($p2sql);
$p2_cnt = $p2_result->num_rows;
$prc_arr = array();
for($i=0;$row2=sql_fetch_array($p2_result);$i++){
	//array_push(array('prp_idx'=>$row2['prp_idx'],'no'=>$i),$prc_arr);
	array_push($prc_arr,$row2['prp_idx']);
}
//echo $p2sql;
// print_r2($prc_arr);
// echo $prp_idx;
$del_flag = (${$pre}["prp_status"] != 'ok' && ${$pre}["prp_pay_date"] == '0000-00-00' && $prp_idx && $prc_arr[count($prc_arr)-1] == $prp_idx) ? 1 : 0;
?>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="grp" value="<?php echo $grp ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="g" value="<?php echo $g ?>">
<input type="hidden" name="prj_idx" value="<?php echo $prj_idx ?>">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<!--input type="hidden" name="<?=$pre?>_pay_no" value="<?php //echo ${$pre}['prp_pay_no'] ?>"-->

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
		<th scope="row"><label for="prj_idx">프로젝트명</label></th>
		<td>
			<?php
			//print_r2(${$pre});
			$pj_field = sql_fetch('SELECT prj_name,prj_reg_dt,prj_type,prj_content2 FROM '.$g5['project_table'].' WHERE prj_idx = "'.${$pre}['prj_idx'].'" ');
			${$pre}['prj_name'] = $pj_field['prj_name'];
			// 수주금액 추출
			$prs1 = sql_fetch('SELECT prp_price FROM '.$g5['project_price_table'].' WHERE prj_idx = "'.${$pre}['prj_idx'].'" AND prp_type = "order" ');
			// print_r2($prs1);
			?>
			<input type="hidden" name="prj_idx" value="<?=${$pre}['prj_idx']?>" required class="frm_input required" style="width:60px;">
			<input type="text" value="<?=${$pre}['prj_name']?>" readonly required class="frm_input" style="width:100%;border:none;"><br>
			<span>수주금액: <?=number_format($prs1['prp_price'])?></span>
			<span style="color:#818181;margin-left:10px;">(등록일 : <?=substr($pj_field['prj_reg_dt'],0,10)?>)</span>
		</td>
		<th scope="row"><label for="prp_type">금액타입</label></th>
		<td>
			<?php //echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="prp_type" id="prp_type">
				<option value="">금액타입선택</option>
				<?=$g5['set_price_type2_options_value']?>
			</select>
			<script>$('select[name="prp_type"]').val('<?=${$pre}["prp_type"]?>');</script>
		</td>
    </tr>
	<tr>
		<th scope="row"><label for="prp_price">금액</label></th>
		<td>
			<input type="text" name="prp_price" value="<?=number_format(${$pre}['prp_price'])?>" required class="frm_input required" style="width:150px;text-align:right;padding-left:5px;padding-right:5px;">
		</td>
		<th scope="row"><label for="prp_doc_deal">거래명세표여부</label></th>
		<td>
			<select name="prp_doc_deal" id="prp_doc_deal">
				<option value="">거래명세표여부</option>
				<?=$g5['set_yes_no_value_options']?>
			</select>
			<script>$('select[name="prp_doc_deal"]').val('<?=${$pre}["prp_doc_deal"]?>');</script>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="prp_data_0">거래명세서 파일</label></th>
		<td>
			<?php echo help("거래명세서 파일을 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" name="prp_data_file[0]" class="">
			<?=${$pre}['prp_data'][0]['file']?>
		</td>
		<th scope="row"><label for="prp_plan_date">발행예정일</label></th>
		<td>
			<input type="text" name="prp_plan_date" id="prp_plan_date" value="<?=${$pre}['prp_plan_date']?>" required class="date frm_input required" style="width:130px;">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="prp_issue_date">계산서발행일</label></th>
		<td>
			<input type="text" name="prp_issue_date" id="prp_issue_date" value="<?=${$pre}['prp_issue_date']?>" class="date frm_input" style="width:130px;">
		</td>
		<th scope="row"><label for="prp_planpay_date">수금예정일</label></th>
		<td>
			<input type="text" name="prp_planpay_date" id="prp_planpay_date" value="<?=${$pre}['prp_planpay_date']?>" required class="date frm_input required" style="width:130px;">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="prp_pay_date">수금완료일</label></th>
		<td colspan=3>
			<?php echo help("수금인 경우 수금완료일자, 인건비, 매입대금, 기타비용인 경우는 적용완료일자를 입력하세요."); ?>
			<input type="text" name="prp_pay_date" id="prp_pay_date" value="<?=${$pre}['prp_pay_date']?>" class="date frm_input" style="width:130px;">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="prj_content2">수입지출 지시사항<br>(프로젝트견적)</label></th>
		<td colspan="3">
			<?=$pj_field['prj_content2']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="prp_content">지시내용</label></th>
		<td>
			<textarea name="prp_content" rows="5"><?=${$pre}['prp_content']?></textarea>
		</td>
		<th scope="row"><label for="prp_content2">미수내용</label></th>
		<td>
			<textarea name="prp_content2" rows="5"><?=${$pre}['prp_content2']?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="com_status">상태</label></th>
		<td>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if ($member['mb_6'] > 2){//(auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_prp_status_options']?>
			</select>
			<script>$('select[name="<?=$pre?>_status"]').val("<?=${$pre}[$pre.'_status']?>");</script>
		</td>
		<th scope="row"><label for="prp_vat_yn">VAT미납여부</label></th>
		<td>
			<input type="checkbox" id="prp_vat_yn" name="prp_vat_yn" value="1"<?=((${$pre}[$pre.'_vat_yn'])?' checked':'')?>><label for="prp_vat_yn">&nbsp;&nbsp;VAT미납</label>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./project_group_price_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
let del_flag = <?=$del_flag?>;
$(function() {
    $(document).on('click','.btn_item_target',function(e){
        var shf_idx = $(this).attr('shf_idx');
        var shf_no = $(this).attr('shf_no');
        // alert( shf_idx +'/'+ shf_no );
		var url = "./shift_item_goal_list.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
		win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_item_goal.focus();
    });

	//alert($(".date").length);
	//$(".date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	$("#prp_plan_date,#prp_issue_date,#prp_planpay_date,#prp_pay_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });


    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});
	//alert(del_flag == false);
	if(!del_flag){
		$('select[name="prp_status"] option[value="trash"]').remove();
	}
});

function form01_submit(f) {
	<?php //echo get_editor_js('prp_content'); ?>
	<?php //echo get_editor_js('prp_content2'); ?>
	if(f.prp_type.value == ''){
		alert('금액타입을 반드시 선택해 주세요.');
		f.prp_type.focus();
		return false;
	}

	if(f.prp_plan_date.value == '0000-00-00' || f.prp_plan_date.value == '' || !f.prp_plan_date.value){
		alert('[발행예정일]을 입력해 주세요.');
		f.prp_plan_date.focus();
		return false;
	}

	if(f.prp_planpay_date.value == '0000-00-00' || f.prp_planpay_date.value == '' || !f.prp_planpay_date.value){
		alert('[수금예정일]을 입력해 주세요.');
		f.prp_planpay_date.focus();
		return false;
	}

	if(f.prp_status.value == 'ok' && (f.prp_pay_date.value == '0000-00-00' || f.prp_pay_date.value == '' || !f.prp_pay_date.value)){
		alert('상태값이 완료이면 [수금완료일]을 입력해 주세요.');
		f.prp_pay_date.focus();
		return false;
	}

	if((f.prp_status.value == 'pending' && f.prp_pay_date.value != '0000-00-00' && f.prp_pay_date.value != '') || (f.prp_status.value == '' && f.prp_pay_date.value != '0000-00-00' && f.prp_pay_date.value != '')){
		alert('수금완료일을 입력하셨으면 상태값을 [완료]로 선택하세요.');
		f.prp_status.focus();
		return false;
	}

	if(f.prp_status.value == 'trash'){
		if(!confirm("신중하게 결정하세요. 정말로 삭제 하시겠습니까?"))
			return false;
	}

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
