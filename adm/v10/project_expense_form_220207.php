<?php
$sub_menu = "960250";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');


// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_exprice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
// $qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	//$row = ${$pre};
    if (!$prj_idx)
		alert('존재하지 않는 자료입니다.');

	
	//관련파일 추출
	/*
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'expense' AND fle_type IN ('prexp_con','prexp_ord') AND fle_db_id = '".$prj_idx."' ORDER BY fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	//echo $rs->num_rows;echo "<br>";
	$row['prj_f_prexp_con'] = array();
	$row['prj_prexp_con_fidxs'] = array();
	$row['prj_f_prexp_ord'] = array();
	$row['prj_prexp_ord_fidxs'] = array();
	for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
		@array_push($row['prj_f_'.$row2['fle_type']],array('file'=>$file_down_del));
		@array_push($row['prj_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
	}
	*/
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = '';//($w=='')?'추가':'수정'; 
$g5['title'] = '지출관리 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
/*
$g5['setting']['set_exprice_type']	machine=기계지출,electricity=전기지출,etc=기타지출
$g5['setting']['set_exprice_status'] pending=대기,ok=정상
$super_admin
*/
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
		<td colspan="3">
			<?php
			//print_r2(${$pre});
			$pj_field = sql_fetch('SELECT prj_name,prj_reg_dt,prj_type FROM '.$g5['project_table'].' WHERE prj_idx = "'.$prj_idx.'" ');
			$prj_name = $pj_field['prj_name'];
			// 수주금액 추출
			$prs1 = sql_fetch('SELECT prp_price FROM '.$g5['project_price_table'].' WHERE prj_idx = "'.$prj_idx.'" AND prp_type = "order" ');
			// print_r2($prs1);
			?>
			<input type="hidden" name="prj_idx" value="<?=$prj_idx?>" required class="frm_input required" style="width:60px;">
			<input type="text" value="<?=$prj_name?>" readonly required class="frm_input" style="width:100%;border:none;"><br>
			<span>수주금액: <?=number_format($prs1['prp_price'])?></span>
			<span style="color:#818181;margin-left:10px;">(등록일 : <?=substr($pj_field['prj_reg_dt'],0,10)?>)</span>
		</td>
    </tr>
	<tr>
		<th scope="row"><label for="multi_file_c">계약서 파일</label></th>
		<td colspan="3">
			<?php echo help("계약서 파일들을 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" id="multi_file_c" name="prj_prexp_con_datas[]" multiple class="">
			<?php
			if(@count($row['prj_f_prexp_con'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_prexp_con']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_prexp_con'][$i]['file']."</li>".PHP_EOL;
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
			<input type="file" id="multi_file_o" name="prj_prexp_ord_datas[]" multiple class="">
			<?php
			if(@count($row['prj_f_prexp_ord'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_prexp_ord']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_prexp_ord'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
		</td>	
	</tr>
	</tbody>
	</table>
	
	
	<div class="bal_top">
		<h3>발주처 목록</h3>
		<button type="button" class="btn btn_03 btn_create">발주처 생성</button>
	</div>
	<div class="bal_box">
		<ul class="bal_tul">
			<li class="bal_tli bli_no">번호</li>
			<li class="bal_tli bli_com">발주업체</li>
			<li class="bal_tli bli_ttl">지출제목</li>
			<li class="bal_tli bli_jprice">지출금액</li>
			<li class="bal_tli bli_jpdate">지출예정일</li>
			<li class="bal_tli bli_jcdate">지출완료일</li>
			<li class="bal_tli bli_jdelete">삭제</li>
		</ul>
		<div class="bal_cont">

		</div>
	</div>
</div>

<form name="form01" id="form01" action="./project_pr_expense_form_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <!-- 테이블 항목명 1번 라인 -->
	<tr>
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">번호</th>
        <th scope="col">발주업체</th>
        <th scope="col">지출제목</th>
        <th scope="col">지출금액</th>
        <th scope="col">지출예정일</th>
        <th scope="col">지출완료일</th>
        <th scope="col" style="width:40px;">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    /*
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
    [prj_percent] => 10
    [prj_keys] => 
    [prj_status] => ok
    [prj_ask_date] => 2020-09-06
    [prj_submit_date] => 2020-09-10
    [prj_reg_dt] => 2020-09-09 11:51:28
    [prj_update_dt] => 2020-09-11 22:36:10
    [prp_order_price] => 78000000
    [com_name] => 아진산업
    [com_name_eng] => AJIN INDUSTRIAL Co.,LTD.
    [com_names] => , 아진산업(20-08-02~)
    [com_homepage] => www.wamc.co.kr
    [com_tel] => 053-856-9100
    [com_fax] => 053-856-9111
    [com_email] => master@wamc.co.kr
    [com_type] => carparts
    [com_class] => 
    [com_president] => 서중호
    [com_biz_no] => 000-00-00000
    [com_biz_type1] => 설비
    [com_biz_type2] => 자동차
    [com_zip1] => 384
    [com_zip2] => 62
    [com_addr1] => 경북 경산시 진량읍 공단8로26길 40
    [com_addr2] => 
    [com_addr3] =>  (신제리)
    [com_addr_jibeon] => R
    [com_b_zip1] => 
    [com_b_zip2] => 
    [com_b_addr1] => 
    [com_b_addr2] => 
    [com_b_addr3] => 
    [com_b_addr_jibeon] => 
    [com_latitude] => 
    [com_longitude] => 
    [com_memo] => 
    [com_keys] => 
    [com_status] => ok
    [com_reg_dt] => 2020-08-02 16:08:13
    [com_update_dt] => 2020-08-05 10:47:06
    */
    $misu1_price = 0;
    $misu2_price = 0;
    
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'&amp;group=1">수정</a>';
        $row['prp_dif_exprice'] = $row['prp_order_price'] - $row['prx_sum_exprice'];
        $bg = 'bg'.($i%2);
        ?>
        <tr class="<?=$bg?>">
            <td class="td_chk" rowspan="<?=$p_cnt?>" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
                <input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td rowspan="<?=$p_cnt?>"><?=$row['prj_idx']?></td><!-- 번호 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['com_name']?></td><!-- 의뢰기업 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['prj_name']?></td><!-- 공사프로젝트 -->
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prp_order_price'])?></td>
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prx_sum_exprice'])?></td>
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prp_dif_exprice'])?></td>
            <td class="td_mngsmall">
                <?=$s_mod?>
            </td>
        </tr>
    <?php
    }
	if ($i == 0)
        echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
	</tbody>
	</table>
</div>


<div class="btn_fixed_top">
    <a href="./project_pr_exprice_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
	<input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
	<a href="./_win_project_expense_form.php" id="btn_add" class="btn btn_01">지출추가</a>
</div>
</form>

<script>
function form01_submit(f) {

}
/*
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

	//계약서 멀티파일
	$('#multi_file_c').MultiFile();
	//발주서 멀티파일
	$('#multi_file_o').MultiFile();
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
*/
</script>

<?php
include_once ('./_tail.php');