<?php
$sub_menu = "960200";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = '판매업체 '.$html_title;
include_once('./_top_menu_company.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    $com_idx = 0;
    $com['com_class'] = 'normal';	// buyer(매입업체), project, goverment...
    $com['com_status'] = 'ok';
    $html_title = '추가';
    
    // 권한이 없는 경우
    if(auth_check($auth[$sub_menu],"d",1)) {
        $style_mb_id = 'background-color:#dadada !important;';
        $style_mb_id_saler = 'background-color:#dadada !important;';
        $style_mb_name = 'background-color:#dadada !important;';
        $style_mb_name_saler = 'background-color:#dadada !important;';
    }
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	$com = get_table_meta('companyreseller','com_idx',$com_idx);
	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');
	
	$style_mb_id = 'background-color:#dadada !important;';
	$style_mb_id_saler = 'background-color:#dadada !important;';
	$style_mb_name = 'background-color:#dadada !important;';
	$style_mb_name_saler = 'background-color:#dadada !important;';
	$html_title = '수정';
	
	$com['com_name'] = get_text($com['com_name']);
	$com['com_tel'] = get_text($com['com_tel']);
	$com['com_homepage'] = get_text($com['com_homepage']);
	$com['com_addr3'] = get_text($com['com_addr3']);
	
	// 관련 파일(post_file) 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'companyreseller' AND fle_db_id = '".$com['com_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
	
	//echo $sql;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		$com[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		$com[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_name'] : '' ;
		$com[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_path'] : '' ;
		$com[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							1 : 0 ;
	}
	
	// 영업자 정보 변경 불가항목!
	if($w=='u' && auth_check($auth[$sub_menu],"d",1)) {
		$saler_readonly = 'readonly';
		$saler_mark = '<span style="color:darkorange;">★</span>';
	}
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$com[$check_array[$i]]} = ' checked';
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
// add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/company_form.js"></script>', 10);
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>

<form name="form01" id="form01" action="./companyreseller_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs ?>">
<input type="hidden" name="ser_com_type" value="<?php echo $ser_com_type ?>">
<input type="hidden" name="ser_trm_idx_salesarea" value="<?php echo $ser_trm_idx_salesarea ?>">

<div class="local_desc01 local_desc">
    <p>업체명이 변경되는 경우 기존 정보와 혼란이 생길 수 있으므로 업체명이 바뀌면 히스토리에 저장됩니다. (히스토리 항목은 수정할 수 없습니다.)</p>
	<?php
	$level_opt = '';
	// $g5['set_com_level_value'], $g5['set_com_dc_rate_value']
	foreach($g5['set_com_level_value'] as $idx => $val){
		$level_opt .= '<option value="'.$idx.'">'.$val.'('.$g5['set_com_dc_rate_value'][$idx].'%)</option>'.PHP_EOL;
	}
	?>
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
		<th scope="row">업체명</th>
		<td>
			<input type="text" name="com_name" value="<?php echo $com['com_name'] ?>" id="com_name" required class="frm_input required" style="width:200px;" <?=$saler_readonly?>>
				<?=$saler_mark?>
		</td>
		<th scope="row">업체등급</th>
		<td>
			<?php echo help("괄호 안의 수치는 할인률을 의미합니다."); ?>
			<select name="com_level" id="com_level" title="업체등급" required class="">
				<option value="">업체등급을 선택하세요.</option>
				<?php echo $level_opt?>
			</select>
			<script>$('select[name=com_level]').val("<?=$com['com_level']?>").attr('selected','selected');</script>
		</td>
	</tr>
	<tr>
		<th scope="row">영문회사명</th>
		<td colspan="3">
			<input type="text" name="com_name_eng" value="<?php echo $com['com_name_eng'] ?>" id="com_name_eng" class="frm_input" style="width:300px;">
		</td>
		
	</tr>
	<tr>
		<th scope="row">업체명 히스토리</th>
		<td colspan="3">
			<?php echo help("업체명이 바뀌면 자동으로 히스토리가 기록됩니다. 업체명 검색 시 나타나지 않는 경우가 있어서 자동으로 기록을 남깁니다."); ?>
			<input type="<?=($is_admin=='super')?'text':'hidden';?>" name="com_names" value="<?php echo $com['com_names'] ?>" id="com_names" class="frm_input" style="width:65%" <?=($is_admin!='super')?'readonly':''?>>
            <span style="display:<?=($is_admin=='super')?'none':'';?>"><?php echo $com['com_names'] ?></span>
		</td>
	</tr>
	<tr> 
		<th scope="row">대표이메일</th>
		<td colspan="3">
			<?php echo help("세금계산서, 계약서, 약정서 등 모든 거래 시 소통할 수 있는 이메일 정보를 필수로 등록하세요."); ?>
			<input type="text" name="com_email" value="<?php echo $com['com_email'] ?>" id="com_email" class="frm_input" style="width:30%;" <?=$saler_readonly?>>
			<?=$saler_mark?>
		</td>
	</tr>
	<tr> 
		<th scope="row">홈페이지주소</th>
		<td colspan="3">
			<?php echo help("http(s):// 없이 그냥 홈페이지 주소만 입력해 주세요. ex. www.naver.com "); ?>
			<input type="text" name="com_homepage" value="<?php echo $com['com_homepage'] ?>" id="com_homepage" class="frm_input" style="width:30%">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="com_president">대표자<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="com_president" value="<?php echo $com['com_president'] ?>" id="com_president" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row"><label for="com_tel">업체전화<strong class="sound_only">필수</strong></label></th>
		<td>
			<input type="text" name="com_tel" value="<?php echo $com['com_tel'] ?>" id="com_tel" class="frm_input" size="20" minlength="2" maxlength="30" <?=$saler_readonly?>>
			<?=$saler_mark?>
		</td>
	</tr>
	<tr>
		<th scope="row">사업자등록번호</th>
		<td>
			<input type="text" name="com_biz_no" value="<?=$com['com_biz_no']?>" class="frm_input" size="20" minlength="2" maxlength="30" <?=$saler_readonly?>>
			<?=$saler_mark?>

		</td>
		<th scope="row">팩스</th>
		<td>
			<input type="text" name="com_fax" value="<?php echo $com['com_fax'] ?>" id="com_fax" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<th scope="row">업태</th>
		<td>
			<input type="text" name="com_biz_type1" value="<?=$com['com_biz_type1']?>" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
		<th scope="row">업종</th>
		<td>
			<input type="text" name="com_biz_type2" value="<?=$com['com_biz_type2']?>" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>	
	<tr>
		<th scope="row">사업장 주소 <?=$saler_mark?></th>
		<td colspan="3" class="td_addr_line" style="line-height:280%;">
			<?php echo help("사업장 주소가 명확하지 않은 경우 [주소검색]을 통해 정확히 입력해 주세요."); ?>
			<label for="com_zip" class="sound_only">우편번호</label>
			<input type="text" name="com_zip" value="<?php echo $com['com_zip1'].$com['com_zip2']; ?>" id="com_zip" class="frm_input readonly" maxlength="6" style="width:65px;" <?=$saler_readonly?>>
			<?php if(!auth_check($auth[$sub_menu],'d',1) || $w=='') { ?>
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'com_zip', 'com_addr1', 'com_addr2', 'com_addr3', 'com_addr_jibeon');">주소 검색</button>
			<?php } ?>
			<br>
			<input type="text" name="com_addr1" value="<?php echo $com['com_addr1'] ?>" id="com_addr1" class="frm_input readonly" size="40" <?=$saler_readonly?>>
			<label for="com_addr1">기본주소</label><br>
			<input type="text" name="com_addr2" value="<?php echo $com['com_addr2'] ?>" id="com_addr2" class="frm_input" size="40" <?=$saler_readonly?>>
			<label for="com_addr2">상세주소</label>
			<br>
			<input type="text" name="com_addr3" value="<?php echo $com['com_addr3'] ?>" id="com_addr3" class="frm_input" size="40" <?=$saler_readonly?>>
			<label for="com_addr3">참고항목</label>
			<input type="hidden" name="com_addr_jibeon" value="<?php echo $com['com_addr_jibeon']; ?>" id="com_addr_jibeon" <?=$saler_readonly?>>
		</td>
	</tr>
	<tr style="display:none;">
		<th scope="row">우편물 발송 주소 <?=$saler_mark?></th>
		<td colspan="3" class="td_addr_line" style="line-height:280%;">
			<?php echo help("사업장 주소와 동일한 경우 아래 항목을 반드시 체크하여 우편물 발송주소를 입력하세요."); ?>
			<label for="com_b_zip" class="sound_only">우편번호</label>
			<input type="text" name="com_b_zip" value="<?php echo $com['com_b_zip1'].$com['com_b_zip2']; ?>" id="com_b_zip" class="frm_input readonly" maxlength="6" style="width:65px;" <?=$saler_readonly?>>
			<?php if(!auth_check($auth[$sub_menu],'d',1) || $w=='') { ?>
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'com_b_zip', 'com_b_addr1', 'com_b_addr2', 'com_b_addr3', 'com_b_addr_jibeon');">주소 검색</button>
			&nbsp;&nbsp;
			<input type="checkbox" value="<?php echo date("Ymd"); ?>" id="check_same_address" onclick="">
			<label for="check_same_address">사업장주소와동일</label>
			<?php } ?>
			<br>
			<input type="text" name="com_b_addr1" value="<?php echo $com['com_b_addr1'] ?>" id="com_b_addr1" class="frm_input readonly" size="40" <?=$saler_readonly?>>
			<label for="com_b_addr1">기본주소</label><br>
			<input type="text" name="com_b_addr2" value="<?php echo $com['com_b_addr2'] ?>" id="com_b_addr2" class="frm_input" size="40" <?=$saler_readonly?>>
			<label for="com_b_addr2">상세주소</label>
			<br>
			<input type="text" name="com_b_addr3" value="<?php echo $com['com_b_addr3'] ?>" id="com_b_addr3" class="frm_input" size="40" <?=$saler_readonly?>>
			<label for="com_b_addr3">참고항목</label>
			<input type="hidden" name="com_b_addr_jibeon" value="<?php echo $com['com_b_addr_jibeon']; ?>" <?=$saler_readonly?>>
		</td>
	</tr>	
	<tr style="display:<?=(!$member['mb_manager_account_yn'])?'none':''?>">
		<th scope="row"><label for="com_memo">메모</label></th>
		<td colspan="3"><textarea name="com_memo" id="com_memo"><?php echo $com['com_memo'] ?></textarea></td>
	</tr>
	<?php if($w == 'u') { ?>
	<tr>
		<th scope="row"><label for="license_img_0">사업자등록증 파일</label></th>
		<td colspan="3">
			<div style="float:left;margin-right:8px;"><?=$com['license_img'][0]['thumbnail_img']?></div>
			<?php echo help("사업자 등록증 이미지 파일을 등록해 주세요."); ?>
			<input type="file" name="license_img_file[0]" class="">
			<?=$com['license_img'][0]['file']?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th scope="row"><label for="company_data_0">첨부 파일#1</label></th>
		<td colspan="3">
			<?php echo help("업체와 관련해서 추가로 관리해야 할 자료가 있으면 등록하고 관리해 주시면 됩니다."); ?>
			<input type="file" name="company_data_file[0]" class="">
			<?=$com['company_data'][0]['file']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="company_data_1">첨부 파일#2</label></th>
		<td colspan="3">
			<input type="file" name="company_data_file[1]" class="">
			<?=$com['company_data'][1]['file']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="com_status">상태</label></th>
		<td colspan="3">
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="com_status" id="com_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_com_status_options']?>
			</select>
			<script>$('select[name="com_status"]').val('<?=$com['com_status']?>');</script>
			<?=$saler_mark?>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./companyreseller_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
	
	$(document).on('click','#check_same_address',function(e) {
		// 체크인 경우
		if( $(this).prop('checked') == true ) {
			$('input[name=com_b_zip]').val( $('input[name=com_zip]').val() );
			$('input[name=com_b_addr1]').val( $('input[name=com_addr1]').val() );
			$('input[name=com_b_addr2]').val( $('input[name=com_addr2]').val() );
			$('input[name=com_b_addr3]').val( $('input[name=com_addr3]').val() );
			$('input[name=com_b_addr_jibeon]').val( $('input[name=com_addr_jibeon]').val() );
		}
		// 아닌 경우
		else {
			$('input[name=com_b_zip]').val('');
			$('input[name=com_b_addr1]').val('');
			$('input[name=com_b_addr2]').val('');
			$('input[name=com_b_addr3]').val('');
			$('input[name=com_b_addr_jibeon]').val('');
		}
	});

});

function form01_submit(f) {

    // 이메일 검증에 사용할 정규식
    var regExp = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
    if (f.com_email.value.match(regExp) != null) {
        //alert('Good!');
    }
    else {
        ;
        /*
        alert("올바른 이메일 주소가 아닙니다.");
        f.com_email.focus();
        return false;
        */ 
    }

    if(!f.com_level.value){
        alert('업체등급을 선택해 주세요.');
        f.com_level.focus();
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
