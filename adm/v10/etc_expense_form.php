<?php
$sub_menu = "960255";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'etc_exprice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
// $qstr .= '&from_date='.$from_date.'&to_date='.$to_date; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
}
else if ($w == 'u') {
    $sql = " SELECT prx.*, com.com_name FROM {$g5['etc_exprice_table']} AS prx
                LEFT JOIN {$g5['companyetc_table']} AS com ON prx.com_idx = com.com_idx
                WHERE prx_idx = '{$prx_idx}' AND prx_status = 'ok'
    ";
    $row = sql_fetch($sql,1);


    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
        WHERE fle_db_table = 'etc_exprice' AND fle_type = 'etcexpense' AND fle_db_id = '".$row['prx_idx']."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $row['prx_f_etcexpense'] = array();
    $row['prx_etcexpense_fidxs'] = array();//견적서 파일번호(fle_idx) 목록이 담긴 배열
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
        $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
        @array_push($row['prx_f_'.$row2['fle_type']],array('file'=>$file_down_del));
        @array_push($row['prx_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
    }
}


$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '기타지출 '.$html_title;
include_once ('./_head.php');
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>

<style>
.tbl_frm01 td .btn{height:35px;line-height:35px;}
/*멀티파일관련*/
input[name$=price]{text-align:right;}
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
<input type="hidden" name="from_date" value="<?php echo $from_date ?>">
<input type="hidden" name="to_date" value="<?php echo $to_date ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo $prx_idx; ?>">

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
			<input type="text" name="com_name" id="com_name" value="<?=$row['com_name']?>" readonly required class="frm_input readonly required" style="width:120px;">
			<a href="javascript:" link="./_win_companyetc_select.php" class="btn btn_02 com_select">업체선택</a>
			<script>
			$('.com_select').on('click',function(){
				var href = $(this).attr('link');
				var win_com_name = window.open(href,"win_com_select","width=400,height=640");
				win_com_select.focus();
				return false;
			});
			</script>
		</td>
		<th scope="row">기타지출제목</th>
		<td>
			<input type="text" name="prx_name" value="<?=$row['prx_name']?>" required class="frm_input required" style="width:250px;">
		</td>
	</tr>
	<tr>
        <th scope="row">지출금액</th>
		<td>
            <input type="text" name="prx_price" value="<?=(($row['prx_price'])?number_format($row['prx_price']):'')?>" required class="frm_input required" style="width:120px;padding-right:10px;">
		</td>
        <th scope="row">지출내용</th>
		<td>
            <input type="text" name="prx_content" value="<?=$row['prx_content']?>" class="frm_input" style="width:250px;">
		</td>
	</tr>
    <tr>
        <th scope="row">지출예정일</th>
		<td>
            <input type="text" name="prx_plan_date" value="<?=$row['prx_plan_date']?>" required class="frm_input required" style="width:95px;">
		</td>
        <th scope="row">지출완료일</th>
		<td>
            <input type="text" name="prx_done_date" value="<?=(($row['prx_done_date'] == '0000-00-00')?'':$row['prx_done_date'])?>" class="frm_input" style="width:95px;">
		</td>
    </tr>
	<tr>
		<th scope="row"><label for="prx_etcexpense_file">기초자료파일</label></th>
		<td colspan="3">
			<?php echo help("프로젝트 관련해서 참고 할 자료가 있으면 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" id="prx_etcexpense_file" name="prx_etcexpense_files[]" multiple class="">
			<?php
			if(@count($row['prx_f_etcexpense'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prx_f_etcexpense']);$i++) {
					echo "<li>[".($i+1).']'.$row['prx_f_etcexpense'][$i]['file']."</li>".PHP_EOL;
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

<script>
$(function() {
	$("input[name$=_date").datepicker({ changeMonth: true, changeYear: true, closeText:'취소', dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){$(this).val(''); }} });
	
    // 가격 입력 쉼표 처리
	//지출금액 숫자만 입력 출력은 천단위 콤마로표시
    $('input[name="prx_price"]').on('keyup',function(){
        var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
        price = (price == '0') ? '' : price;
        $(this).val(price);
    });

	//기초자료 멀티파일
	$('#prx_etcexpense_file').MultiFile();
});

function form01_submit(f) {
	<?php //echo get_editor_js('prj_content'); ?>
	
	if(!f.prx_name.value){
		alert('지출명을 입력해 주세요.');
		f.prj_type.focus();
		return false;
	}
	
	if(!f.prx_price.value){
		alert('지출금액을 입력해 주세요.');
		f.prx_price.focus();
		return false;
	}

	if(!f.prx_plan_date.value){
		alert('지출예정일을 입력해 주세요.');
		f.prx_plan_date.focus();
		return false;
	}
	
    return true;
}

</script>
<?php
include_once ('./_tail.php');