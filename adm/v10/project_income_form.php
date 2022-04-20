<?php
$sub_menu = "960248";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$etc = array();
$prn = array();
$exp_sql = " SELECT
				SUM(prx_price) AS total
			FROM {$g5['project_exprice_table']}
			GROUP BY prj_idx
			HAVING prj_idx = '{$prj_idx}'

";
$exp = sql_fetch($exp_sql); //$exp['total']
// 변수 설정, 필드 구조 및 prefix 추출
$sql = " SELECT prn.*, 
				com.com_name 
			FROM {$g5['project_inprice_table']} AS prn
			LEFT JOIN {$g5['company_table']} AS com ON prn.com_idx = com.com_idx
			WHERE prj_idx = '{$prj_idx}' AND prn_status = 'ok'
";
$res = sql_query($sql,1);
$intotal = 0;
for($i=0;$row=sql_fetch_array($res);$i++){
	$row['prn_prc'] = $row['prn_price'];
	$row['prn_price'] = number_format($row['prn_price']);
	array_push($prn,$row['prn_idx']);
	if($row['prn_type'] == 'etc'){
		$etc[$row['prn_idx']] = $row;
		if($row['prn_done_date'] != '0000-00-00')
			$intotal += $row['prn_prc'];
	}
	else{
		;
	}
}

$sqlf = "SELECT * FROM {$g5['file_table']}
			WHERE fle_db_table = 'project_inprice'
				AND fle_type = 'etc'
				AND fle_db_id IN (".((@count($prn))?implode(',',$prn):0).")
			ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sqlf,1);
// print_r3($sqlf);
//파일배열
$etc_fles = array();
$etc_idxs = array();
for($i=0;$row2=sql_fetch_array($rs);$i++){
	// print_r2($row2);
	$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="fle_del['.$row2['fle_idx'].']" class="fle_del" no="'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
	if(!is_array(${$row2['fle_type'].'_fles'}[$row2['fle_db_id']])) ${$row2['fle_type'].'_fles'}[$row2['fle_db_id']] = array();
	@array_push(${$row2['fle_type'].'_fles'}[$row2['fle_db_id']],array('file'=>$file_down_del));
	if(!is_array(${$row2['fle_type'].'_fidxs'}[$row2['fle_db_id']])) ${$row2['fle_type'].'_fidxs'}[$row2['fle_db_id']] = array();
	@array_push(${$row2['fle_type'].'_fidxs'}[$row2['fle_db_id']],$row2['fle_idx']);
}
/*
print_r3($file_fles);
print_r3($prn);
*/
if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	//$row = ${$pre};
    if (!$prj_idx)
		alert('존재하지 않는 자료입니다.');

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');




//해당 프로젝트 정보 추출
$pj_field = sql_fetch('SELECT prj_name,prj_reg_dt,prj_type,prj_mng_rate FROM '.$g5['project_table'].' WHERE prj_idx = "'.$prj_idx.'" ');
$prj_name = $pj_field['prj_name'];
// 수주금액 추출
$prs1 = sql_fetch('SELECT prp_price FROM '.$g5['project_price_table'].' WHERE prj_idx = "'.$prj_idx.'" AND prp_type = "order" ');


$prj_mng_rate = $pj_field['prj_mng_rate'];
$prj_mng_price = round(($pj_field['prj_mng_rate']*$prs1['prp_price'])/100);

//수금완료 합계를 구한다
$ssql = " SELECT SUM(prp_price) AS sum_price
FROM {$g5['project_price_table']}
WHERE prj_idx = '".$prj_idx."'
	AND prp_type NOT IN ('submit','nego','order','')
	AND prp_pay_date != '0000-00-00'
	AND prp_status = 'ok'
";

//기타수입관련
$intotal_per = ($intotal)?round($intotal / $prs1['prp_price'] * 100,2):0;

//미수금관련
$sugeum = sql_fetch($ssql);
$mis_price = $prs1['prp_price'] - $sugeum['sum_price'];
$mis_per = ($prs1['prp_price'])?round($mis_price / $prs1['prp_price'] * 100,2):0;



//계약금에 대한 총지출금액 비율
$exp_per = ($prs1['prp_price'])?round($exp['total'] / $prs1['prp_price'] * 100,2):0;

$dif_price = ($prs1['prp_price'] + $intotal) - $exp['total'];
$dif_per = ($prs1['prp_price'])?round($dif_price / ($prs1['prp_price'] + $intotal) * 100,2):0;


$html_title = '';//($w=='')?'추가':'수정';
$g5['title'] = '['.$prj_name.'] 기타수입관리 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
/*
$g5['setting']['set_exprice_type']	machine=기계지출,electricity=전기지출,etc=기타지출
$g5['setting']['set_exprice_status'] pending=대기,ok=정상
$super_admin
*/
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>

<style>
.tbl_frm01 td .btn{height:35px;line-height:35px;}
.th_inprice{position:relative;}
.th_inprice i{position:absolute;bottom:15px;right:10px;font-size:1.2em;cursor:pointer;}
.lst_inp{margin-top:5px;}
.lst_inp strong{display:inline-block;width:40px;}
.prn_price{text-align:right;width:90px;}
.prn_name{width:130px;}
.prn_content{width:150px;}
.com_name{background:#ddd;cursor:pointer;width:100px;}
.inp_box input[type="text"]{padding:0 5px;}
.prn_price{width:110px;}
.prn_plan_date{width:90px;}
.prn_done_date{width:90px;}

.lst_up{background:#f1f1f1;padding:10px;}
.lst_fle i{font-size:1.3em;margin-left:5px;}
.lst_down{padding:0px 20px 20px;display:none;background:#f1f1f1;position:relative;}
.lst_down.focus{padding-bottom:20px;display:block;}
/*멀티파일관련*/
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#ddd;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
.btn_file{position:absolute;top:53px;left:275px;background:#463cc5;color:#fff;margin-bottom:5px;border:1px solid #ddd;padding:3px 5px;border-radius:3px;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{font-size:14px;border:1px solid #ccc;background:#eee;padding:2px 5px;border-radius:3px;line-height:1.2em;}

.sm_tbl{width:300px;}
.sm_tbl th{width:190px;background:none;font-weight:400;}
.sm_tbl th,.sm_tbl td{padding:5px;border-top:1px dotted #ddd;border-bottom:1px dotted #ddd;}
.sm_tbl td{text-align:right;position:relative;}
.sm_tbl .th_ord{font-weight:600;}
.sm_tbl .td_ord{font-weight:600;color:orange;}
.sm_tbl .th_tot{color:darkred;}
.sm_tbl .td_tot{color:red;}
.sm_tbl .th_mis{color:red;}
.sm_tbl .td_mis{color:red;}
.sm_tbl .th_dif{color:darkblue;}
.sm_tbl .td_dif{color:blue;}
.sm_tbl .th_etc{}
.sm_tbl .td_etc{}

#td_info{position:relative;}

.grp_box{display:block;position:absolute;bottom:4px;left:0px;width:100%;height:5px;background:#ccc;}
.grp_box .grp_in{display:block;position:absolute;top:0px;left:0px;height:5px;background:orange;}
.grp_box .grp_in_it{background:blue;}
.grp_box .grp_in_mi{background:red;}
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

<div class="local_desc01 local_desc" style="display:none;">
    <p>프로젝트별 기타수입내역을 관리하는 페이지 입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:40%;">
	</colgroup>
	<tbody>
    <tr>
		<th scope="row"><label for="prj_idx">수입&지출정보</label></th>
		<td colspan="3" id="td_info">
			<table class="sm_tbl">
				<tbody>
					<?php if($super_admin){ ?>
					<tr>
						<th class="th_ord">수주금액</th>
						<td class="td_ord"><?=number_format($prs1['prp_price'])?>원</td>
					</tr>
					<tr>
						<th class="th_intot">기타수입(<?=$intotal_per?>%)<br>(수주금액기준%)</th>
						<td class="td_intot">
							<div class="grp_box"><div class="grp_in grp_in_it" style="width:<?=$intotal_per?>%"></div></div>
							<?=number_format($intotal)?>원
						</td>
					</tr>
					<tr>
						<th class="th_mis">미수금(<?=$mis_per?>%)<br>(수주금액기준%)</th>
						<td class="td_mis">
							<div class="grp_box"><div class="grp_in grp_in_mi" style="width:<?=$mis_per?>%"></div></div>
							<?=number_format($mis_price)?>원
						</td>
					</tr>
					<?php } ?>
					<tr>
						<th class="th_tot">총지출금액<?php if($super_admin){ ?>(<?=$exp_per?>%)<br>(수주금액기준%)<?php } ?></th>
						<td class="td_tot">
							<?php if($super_admin){ ?><div class="grp_box"><div class="grp_in" style="width:<?=$exp_per?>%"></div></div><?php } ?>
							<?=number_format($exp['total'])?>원
						</td>
					</tr>
					<?php if($super_admin){ ?>
					<tr>
						<th class="th_dif">잔액(<?=$dif_per?>%)<br>(수주금액 + 기타수입 기준%)</th>
						<td class="td_dif">
							<div class="grp_box"><div class="grp_in" style="width:<?=$dif_per?>%"></div></div>
							<?=number_format($dif_price)?>원
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<span style="color:#818181;">(등록일 : <?=substr($pj_field['prj_reg_dt'],0,10)?>)</span>
		</td>
    </tr>
	<tr>
		<th scope="row" class="th_inprice" id="th_etc"><label for="etc_inprice">기타수입</label><i id="i_etc" typ="etc" class="fa fa-plus-square-o i_inp" aria-hidden="true"></i></th>
		<td colspan="3">
			<?php echo help("해당 프로젝트관련 기타 수입을 관리하는 영역입니다."); ?>
			<div class="inp_box" id="inp_etc">
				<?php
				if(@count($etc)){
				$i=0;
				foreach($etc as $k=>$v){
					$i++;
				?>
				<div class="lst_inp lst_<?=$v['prn_type']?> lst_<?=$v['prn_type']?>_<?=$i?>">
					<div class="lst_up">
						<span><input type="hidden" name="prn_idx" value="<?=$k?>"><strong class="">[<?=$i?>]</strong></span>
						<span>
							<input type="hidden" name="com_idx" value="<?=$v['com_idx']?>">
							<input type="text" name="com_name" placeholder="업체명" value="<?=$v['com_name']?>" link="./_win_company_provider_select.php?file_name=${file_name}" readonly class="frm_input com_name">
						</span>
						<span><input type="text" name="prn_name" placeholder="수입제목" value="<?=$v['prn_name']?>" class="frm_input prn_name"></span>
						<span><input type="text" name="prn_price" placeholder="수입금액" value="<?=$v['prn_price']?>" class="frm_input prn_price" onclick="javascript:only_number_comma(this)"></span>
						<span><input type="text" name="prn_plan_date" placeholder="수입예정일" value="<?=$v['prn_plan_date']?>" readonly class="frm_input prn_plan_date"></span>
						<span><input type="text" name="prn_done_date" placeholder="수입완료일" value="<?=$v['prn_done_date']?>" readonly class="frm_input prn_done_date"></span>
						<span><input type="text" name="prn_content" placeholder="메모" value="<?=$v['prn_content']?>" class="frm_input prn_content"></span>
						<span><button type="button" class="btn btn_02 lst_mod" prn_idx="<?=$k?>" typ="<?=$v['prn_type']?>">수정</button></span>
						<span><button type="button" class="btn btn_00 lst_del" prn_idx="<?=$k?>" typ="<?=$v['prn_type']?>">삭제</button></span>
						<span><button type="button" class="btn btn_03 lst_fle" prn_idx="<?=$k?>" typ="<?=$v['prn_type']?>">파일<i class="fa fa-angle-down" aria-hidden="true"></i></button></span>
						<?php if(@count(${$v['prn_type'].'_fles'}[$k])){ ?>
						<span>(<?=@count(${$v['prn_type'].'_fles'}[$k])?>)</span>
						<?php } ?>
					</div>
					<div class="lst_down">
						<?php
						if(@count(${$v['prn_type'].'_fles'}[$k])){
							echo '<ul>'.PHP_EOL;
							for($j=0;$j<count(${$v['prn_type'].'_fles'}[$k]);$j++) {
								echo "<li>[".($j+1).']'.${$v['prn_type'].'_fles'}[$k][$j]['file']."</li>".PHP_EOL;
							}
							echo '</ul>'.PHP_EOL;
						}
						?>
					</div>
				</div>
				<?php
				}
				}
				?>
			</div>
		</td>
	</tr>
	</tbody>
	</table>
</div>


<div class="btn_fixed_top">
    <a href="./project_income<?=$divid?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
</div>
</form>
<script>
var prj_idx = <?=$prj_idx?>;
var cur_obj; //현재 지출그룹객체
var multifile;//현재 멀티파일 객체
$(function(){ //###########################################################
//최초로딩시 모든 이벤트 활성화
events_reg();

//지출추가버튼 이벤트
$('.i_inp').on('click',function(){
	var typ = $(this).attr('typ');
	//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
	if($('.reg_prev').length || $('.lst_down.focus').length){
		alert('이전 작업을 먼저 진행 또는 취소 후에 실행해 주세요.');
		return false;
	}

	//우선 모든 이벤트 비활성화
	events_del();
	var cnt = $('#inp_'+typ).find('.lst_inp').length+1;
	var tag = `<div class="lst_inp lst_${typ} lst_${typ}_${cnt}">
		<div class="lst_up">
			<span><input type="hidden" name="prn_idx" value=""><strong class="reg_prev">[${cnt}]</strong></span>
			<span>
				<input type="hidden" name="com_idx" value="">
				<input type="text" name="com_name" placeholder="업체명" value="" link="./_win_company_provider_select.php?file_name=${file_name}" readonly class="frm_input com_name">
			</span>
			<span><input type="text" name="prn_name" placeholder="수입제목" value="" class="frm_input prn_name"></span>
			<span><input type="text" name="prn_price" placeholder="수입금액" value="" class="frm_input prn_price" onclick="javascript:only_number_comma(this)"></span>
			<span><input type="text" name="prn_plan_date" placeholder="수입예정일" value="" readonly class="frm_input prn_plan_date"></span>
			<span><input type="text" name="prn_done_date" placeholder="수입완료일" value="" readonly class="frm_input prn_done_date"></span>
			<span><input type="text" name="prn_content" placeholder="메모" value="" class="frm_input prn_content"></span>
			<span><button type="button" class="btn btn_01 lst_reg" typ="${typ}">등록</button></span>
			<span><button type="button" class="btn btn_00 lst_del" typ="${typ}">삭제</button></span>
		</div>
	</div>`;
	$(tag).appendTo('#inp_'+typ);
	//새로운 구성으로 다시 모든 이벤트 활성화
	events_reg();
});

}); //#####################################################################
var prj_price = <?=$prs1['prp_price']?>;
function events_reg(){
	//업체명 선택팝업
	$('.com_name').on('click',function(e){
		e.preventDefault();
		cur_obj = $(this).closest('.lst_inp');
		var href = $(this).attr('link');
		winProviderSelect = window.open(href, "winProviderSelect","left=600,top=150,width=550,height=600,scrollbars=1");
		winProviderSelect.focus();
	});
	//지출완료일
	// $(".prn_done_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	// $(".prn_done_date").datepicker('option','disabled',false);
	$("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", closeText:'취소',showButtonPanel: true, yearRange: "c-99:c+99", onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val(''); } } });
	$("input[name$=_date]").datepicker('option','disabled',false);

	//등록버튼
	$('.lst_reg').on('click',function(){
		cur_obj = $(this).closest('.lst_inp');
		var prj_idx = <?=$prj_idx?>;
		var com_idx = cur_obj.find('input[name="com_idx"]').val();
		var com_name = $.trim(cur_obj.find('input[name="com_name"]').val());
		var prn_name = $.trim(cur_obj.find('input[name="prn_name"]').val());
		var prn_content = $.trim(cur_obj.find('input[name="prn_content"]').val());
		var prn_price = $.trim(cur_obj.find('input[name="prn_price"]').val());
		var prn_plan_date = $.trim(cur_obj.find('input[name="prn_plan_date"]').val());
		var prn_done_date = $.trim(cur_obj.find('input[name="prn_done_date"]').val());
		var type = $(this).attr('typ');
		if(!prj_idx){
			alert('프로젝트 고유번호가 제대로 넘어오지 않았습니다.');
			return false;
		}
		if(!com_name){
			alert('업체명을 선택해 주세요.');
			cur_obj.find('input[name="com_name"]').focus();
			return false;
		}
		if(!prn_name){
			alert('수입제목을 입력해 주세요.');
			cur_obj.find('input[name="prn_name"]').focus();
			return false;
		}
		if(!prn_price){
			alert('수입금액을 입력해 주세요.');
			cur_obj.find('input[name="prn_price"]').focus();
			return false;
		}
		if(!prn_plan_date){
			alert('수입예정일을 입력해 주세요.');
			cur_obj.find('input[name="prn_plan_date"]').focus();
			return false;
		}
		inp_reg(prj_idx,type,com_idx,prn_name,prn_content,prn_price,prn_plan_date,prn_done_date);
	});

	//수정버튼
	$('.lst_mod').on('click',function(e){
		e.stopImmediatePropagation();
		//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
		if($('.reg_prev').length || $('.lst_down.focus').length){
			alert('이전 등록작업 또는 파일작업을 먼저 진행한 후에 실행해 주세요d.');
			return false;
		}
		cur_obj = $(this).closest('.lst_inp');
		var prn_idx = $(this).attr('prn_idx');
		var prj_idx = <?=$prj_idx?>;
		var com_idx = cur_obj.find('input[name="com_idx"]').val();
		var com_name = $.trim(cur_obj.find('input[name="com_name"]').val());
		var prn_name = $.trim(cur_obj.find('input[name="prn_name"]').val());
		var prn_content = $.trim(cur_obj.find('input[name="prn_content"]').val());
		var prn_price = $.trim(cur_obj.find('input[name="prn_price"]').val());
		var prn_plan_date = $.trim(cur_obj.find('input[name="prn_plan_date"]').val());
		var prn_done_date = $.trim(cur_obj.find('input[name="prn_done_date"]').val());
		var type = $(this).attr('typ');
		if(!prj_idx){
			alert('프로젝트 고유번호가 제대로 넘어오지 않았습니다.');
			return false;
		}
		if(!com_name){
			alert('업체명을 선택해 주세요.');
			cur_obj.find('input[name="com_name"]').focus();
			return false;
		}
		if(!prn_name){
			alert('수입내용을 입력해 주세요.');
			cur_obj.find('input[name="prn_name"]').focus();
			return false;
		}
		if(!prn_price){
			alert('수입금액을 입력해 주세요.');
			cur_obj.find('input[name="prn_price"]').focus();
			return false;
		}
		if(!prn_plan_date){
			alert('수입예정일을 입력해 주세요.');
			cur_obj.find('input[name="prn_plan_date"]').focus();
			return false;
		}
		inp_upd(prn_idx,prj_idx,type,com_idx,prn_name,prn_content,prn_price,prn_plan_date,prn_done_date);
	});


	//삭제버튼
	$('.lst_del').on('click',function(e){
		e.preventDefault();
		cur_obj = $(this).closest('.lst_inp');
		if(cur_obj.find('strong').hasClass('reg_prev')){
			//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
			if($('.reg_prev').length > 1 || $('.lst_down.focus').length){
				alert('이전 등록작업 또는 파일작업을 먼저 진행한 후에 실행해 주세요.');
				return false;
			}
			cur_obj.remove();
		}
		else{
			//이전에 새로 등록하려는 작업이 있거나, 파일등록작업하려는 상황이 있으면 이전 작업을 종료한 후에 하도록 유도함
			if($('.reg_prev').length || $('.lst_down.focus').length){
				if($(this).attr('prn_idx') != cur_obj.find('.btn_file').attr('prn_idx')){
					alert('이전 등록작업 또는 파일작업을 먼저 진행한 후에 실행해 주세요.');
					return false;
				}
			}
			if(!confirm('수입정보와 관련파일 전부 삭제되어 복구가 불가능합니다.\n정말로 삭제하시겠습니까?')){
				return false;
			}
			var prn_idx = $(this).attr('prn_idx');
			var	type = $(this).attr('typ');
			inp_del(prn_idx,type);
		}
		events_del();
		events_reg();
	});

	//파일열기/닫기 버튼
	$('.lst_fle').on('click',function(){
		if($('.reg_prev').length){
			alert('이전 신규수입 등록작업을 먼저 진행한 후에 파일작업을 실행해 주세요.');
			return false;
		}
		cur_obj = $(this).closest('.lst_inp');
		var cur_down = $(this).parent().parent().siblings('.lst_down');


		events_del();
		if(cur_down.hasClass('focus')){
			//기존 모든 멀티파일객체를 제거
			multifile_remove();
		}
		else{
			//기존 모든 멀티파일객체를 제거
			multifile_remove();
			//해당 파일열기버튼의 .lst_down안에만 멀티파일객체 생성한다.
			multifile_insert($(this));
		}
		events_reg();
	});
}

function events_del(){
	$('.com_name').off('click');
	$("input[name$=_date]").datepicker('option','disabled',true);
	$('.lst_reg').off('click');
	$('.lst_del').off('click');
	$('.lst_fle').off('click');
}

//파일 열기버튼을 누르면 해당 .lst_down안에 multifile객체를 넣는 함수
function multifile_insert(btn){
	//멀티파일영역을 닫거나 다른 영역을 열려고 하면 기존에 파일등록하려던 작업을 전부 초기화(삭제) 한다.
	multifile_remove();
	$('.lst_fle').find('i').attr('class','fa fa-angle-down');
	$('.lst_down').removeClass('focus');
	btn.find('i').attr('class','fa fa-angle-up');
	btn.parent().parent().siblings('.lst_down').addClass('focus');
	var lst_down = btn.parent().parent().siblings('.lst_down');
	var prn_idx = btn.attr('prn_idx');
	var type = btn.attr('typ');
	var lst_down = btn.parent().parent().siblings('.lst_down');
	var btn_file = btn.parent().parent().siblings('.lst_down').find('.btn_file');
	// console.log(prn_idx);return false;
	var form_obj = `<form name="multi_file" id="mfile" method="post" enctype="multipart/form-data">
	<button type="button" class="btn_file" prn_idx="${prn_idx}" typ="${type}">파일등록/삭제</button>
	<input type="file" name="prn_files[]" multiple class="multi_files" id="prn_files">
	</form>`;
	$(form_obj).prependTo(lst_down);
	$('#prn_files').MultiFile();
	$('.btn_file').on('click',function(){
		btn_file_click($(this));
	});
}
//파일등록 버튼 클릭시 호출하는 함수
function btn_file_click(btn){
	var prn_idx = btn.attr('prn_idx');
	var typ = btn.attr('typ');
	var fileObj = btn.siblings('.MultiFile-wrap');
	file_update(prn_idx,typ,fileObj);
}
//파일 닫기버튼을 누르면 multifile을 제거하는 함수
function multifile_remove(){
	$('.lst_fle').find('i').attr('class','fa fa-angle-down');
	$('.lst_down').removeClass('focus');
	$('#mfile').remove();
}


//관리비 0~100까지의 숫자만 입력 가능한 함수
function only_number(inp){ //inp = #mng_rate
	$(inp).keyup(function(){
		var rate = $.trim($(this).val());
		rate = $(this).val().replace(/[^0-9]/g,"");
		if(rate > 100) rate = 100;
		else if(rate < 0) rate = 0;
		else if(rate == '') rate = 10;
		$(this).val(rate);
	});
}

//수입금액 숫자만 입력 출력은 천단위 콤마로표시
function only_number_comma(inp){
	$(inp).keyup(function(){
		var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
		price = (price == '0') ? '' : price;
		$(this).val(price);
	});
}

//새로운 수입내역 등록
function inp_reg(prj_idx,type,com_idx,prn_name,prn_content,prn_price,prn_plan_date,prn_done_date){
	var link = '<?=G5_USER_ADMIN_URL?>/project_income_form_update.php';

	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prj_idx': prj_idx, 'prn_type': type, 'com_idx': com_idx, 'prn_name': prn_name, 'prn_content': prn_content, 'prn_price': prn_price, 'mode': 'r', 'prn_plan_date': prn_plan_date, 'prn_done_date': prn_done_date},
		success : function(res){
			if(res == 'reg'){
				alert('기타수입내역을 성공적으로 등록했습니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}

//새로운 수입내역 수정
function inp_upd(prn_idx,prj_idx,type,com_idx,prn_name,prn_content,prn_price,prn_plan_date,prn_done_date){
	var link = '<?=G5_USER_ADMIN_URL?>/project_income_form_update.php';

	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prn_idx': prn_idx,'prj_idx': prj_idx, 'prn_type': type, 'com_idx': com_idx, 'prn_name': prn_name, 'prn_content': prn_content, 'prn_price': prn_price, 'mode': 'u', 'prn_plan_date': prn_plan_date, 'prn_done_date': prn_done_date},
		success : function(res){
			if(res == 'upd'){
				alert('기타수입내역을 성공적으로 수정했습니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}

//새로운 수입내역 수정
function inp_del(prn_idx,type){
	var link = '<?=G5_USER_ADMIN_URL?>/project_income_form_update.php';

	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'prn_idx': prn_idx, 'prn_type': type, 'mode': 'd'},
		success : function(res){
			if(res == 'del'){
				alert('기타수입내역을 성공적으로 삭제하였습니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}

function file_update(prn_idx,typ,fObj){
	var prn_idx = prn_idx;
	var type = typ;
	var fobj = fObj;
	var btn_file = fobj.siblings('.btn_file');
	var form = $('#mfile');
	var up_ul = btn_file.parent().siblings('ul');
	var fle_idxs = '';
	//삭제할 fle_idx저장
	up_ul.find('.fle_del').each(function(){
		if($(this).is(':checked')){
			if(fle_idxs == ''){
				fle_idxs = $(this).attr('no');
			}
			else{
				fle_idxs += ','+$(this).attr('no');
			}
		}
	});

	if(form.find('.MultiFile-list').find('.MultiFile-label').length == 0 && !fle_idxs){
		alert('업로드할 파일 또는 삭제할 항목을 최소 한 개 이상 선택해 주세요.');
		// multifile_remove();
		return false;
	}

	var formData = new FormData();
	formData.append("prn_idx",prn_idx);
	formData.append("type",type);
	formData.append("dels",fle_idxs);

	// console.log(form.find('.multi_files').attr('name'));return false;
	// console.log(form.find('.multi_files')[0].files);return false;
	// console.log(form.find('.multi_files').attr('name'));return false;

	$(form.find('.multi_files')[0].files).each(function(index,file){
		formData.append(form.find('.multi_files').attr('name'),file);
	});

	var link = '<?=G5_USER_ADMIN_URL?>/project_income_file_update.php';
	// console.log(formData);return false;
	// console.log(prn_idx);return false;
	$.ajax({
		type : "POST",
		url : link,
		processData : false, //file전송시 필수
		contentType : false, //file전송시 필수
		data : formData,
		success : function(res){
			if(res == 'ok'){
				alert('성공적으로 반영되었습니다..');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
			multifile_remove();
		}
	});
}
</script>

<?php
include_once ('./_tail.php');
