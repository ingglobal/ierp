<?php
$sub_menu = "960268";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],"r");

$fname = preg_replace("/_form/","",$g5['file_name']); // _list을 제외한 파일명


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
}


$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '개별발주'.$html_title;
//include_once('./_top_menu_company.php');
include_once('./_head.php');

?>
<style>

</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" >
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="ppt_idx" value="<?php echo $ppt["ppt_idx"] ?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>발주관리 페이지입니다.</p>
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
                <input type="text" id="prj_name" value="<?=$prj['prj_name']?>" readonly required class="frm_input readonly required" style="width:200px;<?=$data_bg_change?>">
                <?php if($w == ''){ ?>
                    <a href="javascript:" link="./_win_project_select.php" class="btn btn_02 prj_select">프로젝트선택</a>
                <?php } ?>
            </td>
            <th scope="row">공급업체선택</th>
            <td>
                <input type="hidden" name="com_idx" id="com_idx" value="<?=$ppt['com_idx']?>" required class="frm_input required" style="width:60px;">
                <input type="text" id="com_name" value="<?=$com['com_name']?>" readonly required class="frm_input readonly required" style="width:120px;">
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
        </tr>
        <tr>
            <th scope="row">발주날짜</th>
            <td>
                <input type="text" name="ppt_date" id="ppt_date" value="<?=$ppt['ppt_date']?>" class="frm_input" style="width:130px;">
            </td>
            <th scope="row">발주금액</th>
            <td>
                <input type="text" name="ppt_price" value="<?=$ppt['ppt_price']?>" class="frm_input" style="width:130px;text-align:right;">&nbsp;원
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01.tbl_wrap -->
</form><!--//#form01-->
<?php
include_once ('./_tail.php');