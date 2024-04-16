<?php
$sub_menu = "960266";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],"r");


//data폴더에 ierp 폴더(각종파일을 저장하는 디렉토리) 생성
$data_ppc_dir_path = G5_DATA_PATH.'/ppc';
$ppc_permision_str = "chmod 707 -R ".$data_ppc_dir_path;

$mb_mng_flag = $member['mb_manager_yn'];

if(!is_dir($data_ppc_dir_path)){
	@mkdir($data_ppc_dir_path, G5_DIR_PERMISSION);
	@chmod($data_ppc_dir_path, G5_DIR_PERMISSION);

	exec($ppc_permision_str);
}

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase';
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
	$row['ppc_status'] = 'pending';
}
else if($w == 'u'){
    $ppc = get_table('project_purchase','ppc_idx',$ppc_idx);
    $prj = get_table('project','prj_idx',$ppc['prj_idx']);
    $com = get_table('company','com_idx',$ppc['com_idx']);
    if($ppc['ppc_status'] == 'trash' || !$ppc['ppc_idx']){
        alert('해당 개별발주항목이 존재하지 않습니다.','./project_purchase_list.php?'.$qstr);
    }

    //관련파일 추출
	$sql = "SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'ppc' AND fle_type = 'ppc' AND fle_db_id = '".$ppc['ppc_idx']."' ORDER BY fle_reg_dt DESC ";
    $rs = sql_query($sql,1);
    //echo $rs->num_rows;echo "<br>";
    $ppc['ppc_f_arr'] = array();
    $ppc['ppc_fidxs'] = array();//개별발주서 파일번호(fle_idx) 목록이 담긴 배열
    $ppc['ppc_lst_idx'] = 0;//개별발주서 파일중에 가장 최신버전의 파일번호
    for($i=0;$row2=sql_fetch_array($rs);$i++) {
		$file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$row2['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$row2['fle_type'].'_del['.$row2['fle_idx'].']" id="del_'.$row2['fle_idx'].'" value="1"> 삭제</label>':''.PHP_EOL;
		@array_push($ppc['ppc_f_arr'],array('file'=>$file_down_del));
		@array_push($ppc['ppc_fidxs'],$row2['fle_idx']);
	}

	//견적서파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
	if(@count($ppc['ppc_fidxs'])) $ppc['ppc_lst_idx'] = $ppc['ppc_fidxs'][0];

    //지출분배데이터 추출
    $sqld = " SELECT * FROM {$g5['project_purchase_divide_table']} WHERE ppc_idx = '{$ppc_idx}' AND ppd_status IN ('ok','complete')  ORDER BY ppd_type, ppd_idx ";
    $resd = sql_query($sqld,1);
}


$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '그룹발주'.$html_title;
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
<input type="hidden" name="ppc_idx" value="<?php echo $ppc["ppc_idx"] ?>">
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
                <input type="hidden" name="prj_idx" id="prj_idx" value="<?=$ppc['prj_idx']?>">
                <input type="text" id="prj_name" value="<?=$prj['prj_name']?>" readonly class="frm_input readonly" style="width:300px;">
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
                <input type="hidden" name="com_idx" id="com_idx" value="<?=$ppc['com_idx']?>" class="frm_input" style="width:60px;">
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
            <th scope="row">그룹발주날짜</th>
            <td>
                <input type="text" name="ppc_date" id="ppc_date" value="<?=$ppc['ppc_date']?>" class="frm_input" style="width:130px;">
            </td>
            <th scope="row">그룹발주금액</th>
            <td>
                <input type="text" name="ppc_price" value="<?=number_format($ppc['ppc_price'])?>"<?=(($w != '')?' readonly':'')?> class="frm_input" style="width:130px;text-align:right;">&nbsp;원
            </td>
        </tr>
        <tr>
            <th scope="row">제목(중요품목)</th>
            <td>
                <input type="text" name="ppc_subject" id="ppc_subject" value="<?=$ppc['ppc_subject']?>" class="frm_input" style="width:100%;">
            </td>
            <th scope="row">상태</th>
            <td>
                <select name="ppc_status" id="ppc_status">
                    <?=$g5['set_ppc_status_value_options']?>
                </select>
                <script>
                <?php if($w == ''){ ?>
                    $('select[name="ppc_status"]').val("ok");
                <?php } else { ?>
                    $('select[name="ppc_status"]').val("<?=$ppc['ppc_status']?>");
                <?php } ?>
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">내용</th>
            <td>
                <textarea name="ppc_content" rows="3"><?=$ppc['ppc_content']?></textarea>
            </td>
            <th scope="row"><label for="multi_file_ppt">그룹발주 관련파일</label></th>
            <td colspan="3">
                <?php echo help("그룹발주관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file_ppc" name="ppc_datas[]" multiple class="">
                <?php
                if(@count($ppc['ppc_f_arr'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($ppc['ppc_f_arr']);$i++) {
                        echo "<li>[".($i+1).']'.$ppc['ppc_f_arr'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01.tbl_wrap -->
<?php if($w != '' && $resd->num_rows){ ?>
<style>
.ul_ppd{margin-bottom:20px;}
.ul_ppd::after{display:block;visibility:hidden;clear:both;content:'';}
.ul_ppd li{float:left;margin-right:10px;}
.li_ppd_type{}
.li_ppd_content input{width:200px;}
.li_ppd_price input{width:100px;text-align:right;}
.li_ppd_plan_date input{width:100px;text-align:center;}
.li_ppd_done_date input{width:100px;text-align:center;}
.li_ppd_bank{}
.li_ppd_mng{padding-top:3px;}

.th_ppd_type{width:110px;}
.th_ppd_per{width:90px;}
.th_ppd_price{width:120px;}
.th_ppd_plan_date{width:100px;}
.th_ppd_done_date{width:100px;}
.th_ppd_bank{width:110px;}


.td_ppd_per{text-align:right}
.td_ppd_per::after{content:' %'}
.td_ppd_price input{text-align:right;padding:0 5px;}
</style>
<div class="">
    <h2>지출정보등록</h2>
    <ul class="ul_ppd">
        <li class="li_ppd_type">
            <select id="ppd_type">
                <?=$g5['set_ppd_type_value_options']?>
            </select>
        </li>
        <li class="li_ppd_content">
            <input type="text" id="ppd_content" placeholder="내용입력" class="frm_input">
        </li>
        <li class="li_ppd_price">
            <input type="text" id="ppd_price" placeholder="금액입력" class="frm_input">
        </li>
        <li class="li_ppd_plan_date">
            <input type="text" id="ppd_plan_date" placeholder="예정일" value="0000-00-00" readonly class="frm_input">
        </li>
        <li class="li_ppd_done_date">
            <input type="text" id="ppd_done_date" placeholder="지출일" value="0000-00-00" readonly class="frm_input">
        </li>
        <li class="li_ppd_bank">
            <select id="ppd_bank">
                <?=$g5['set_ppd_bank_value_options']?>
            </select>
        </li>
        <li class="li_ppd_mng">
            <a href="javascript:" class="btn btn_02 ppd_reg">등록</a>
        </li>
    </ul>
</div>
<script>
$("#ppd_plan_date").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99"});
$("#ppd_done_date").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99"});

$('.ppd_reg').on('click', function(){
    let ppc_idx = <?=$ppc_idx?>;
    let ppd_type = $('#ppd_type').val();
    let ppd_content = $.trim($('#ppd_content').val());
    let ppd_price = $('#ppd_price').val();
    let ppd_plan_date = $('#ppd_plan_date').val();
    let ppd_done_date = $('#ppd_done_date').val();
    let ppd_bank = $('#ppd_bank').val();

    if(!ppd_content){
        alert('내용을 반드시 입력해 주세요.');
        $('#ppd_content').focus();
        return false;
    }
    if(!ppd_price){
        alert('금액을 반드시 입력해 주세요.');
        $('#ppd_price').focus();
        return false;
    }
    if(!ppd_plan_date){
        alert('예정일을 반드시 입력해 주세요.');
        $('#ppd_plan_date').focus();
        return false;
    }

    let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/ppd_reg.php';
    $.ajax({
		type: 'POST',
		dataType: 'text',
		url: ajxurl,
		data: {'ppc_idx': ppc_idx,'ppd_type': ppd_type, 'ppd_content': ppd_content, 'ppd_price': ppd_price, 'ppd_plan_date': ppd_plan_date, 'ppd_done_date': ppd_done_date, 'ppd_bank': ppd_bank},
		success: function(res){
			if(res == 'ok'){
				location.reload();
			}
		},
		error: function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
});
</script>
<div class="tbl_head01 tbl_wrap">
    <h2>지출상세내용</h2>
    <table class="table table-bordered table-condensed">
    <caption>상세내용</caption>
    <thead>
        <tr>
            <th scope="col" class="th_ppd_type">타입</th>
            <th scope="col" class="th_ppd_content">내용</th>
            <th scope="col" class="th_ppd_per">비율</th>
            <th scope="col" class="th_ppd_price">금액</th>
            <th scope="col" class="th_ppd_plan_date">지출예정일</th>
            <th scope="col" class="th_ppd_done_date">지출확정일</th>
            <th scope="col" class="th_ppd_bank">지출방법</th>
            <th scope="col" class="th_mng">삭제</th>
        </tr>
    </thead>
    <tbody>
        <?php for($i=0;$row=sql_fetch_array($resd);$i++){ ?>
        <tr>
        <td scope="col" class="td_ppd_type">
            <input type="hidden" name="ppd_idx[<?=$i?>]" value="<?=$row['ppd_idx']?>">
            <select name="ppd_type[<?=$row['ppd_idx']?>]" class="ppd_type<?=$i?>">
                <?=$g5['set_ppd_type_value_options']?>
            </select>
            <script>
            $('.ppd_type<?=$i?>').val('<?=$row['ppd_type']?>');
            </script>
        </td>
        <td scope="col" class="td_ppd_content">
            <input type="text" name="ppd_content[<?=$row['ppd_idx']?>]" value="<?=$row['ppd_content']?>" class="frm_input">
        </td>
        <td scope="col" class="td_ppd_per">
            <?php
            $per = $row['ppd_price'] / $ppc['ppc_price'] * 100;
            echo number_format($per,1,'.','');
            ?>
        </td>
        <td scope="col" class="td_ppd_price">
            <input type="text" name="ppd_price[<?=$row['ppd_idx']?>]" value="<?=number_format($row['ppd_price'])?>" class="frm_input">
        </td>
        <td scope="col" class="td_ppd_plan_date">
            <input type="text" name="ppd_plan_date[<?=$row['ppd_idx']?>]" value="<?=$row['ppd_plan_date']?>" class="frm_input plan_date<?=$i?>" style="text-align:center;">
            <script>
            $(".plan_date<?=$i?>").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99"});
            </script>
        </td>
        <td scope="col" class="td_ppd_done_date">
            <input type="text" name="ppd_done_date[<?=$row['ppd_idx']?>]" value="<?=$row['ppd_done_date']?>" class="frm_input done_date<?=$i?>" style="text-align:center;">
            <script>
            $(".done_date<?=$i?>").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99"});
            </script>
        </td>
        <td scope="col" class="td_ppd_bank">
            <select name="ppd_bank[<?=$row['ppd_idx']?>]" class="ppd_bank<?=$i?>">
                <?=$g5['set_ppd_bank_value_options']?>
            </select>
            <script>
            $('.ppd_bank<?=$i?>').val('<?=$row['ppd_bank']?>');
            </script>
        </td>
        <td scope="col" class="td_mng">
            <a href="javascript:" class="btn btn_01 ppd_del" ppd_idx="<?=$row['ppd_idx']?>">삭제</a>
        </td>
        </tr>
        <?php } ?>
    </tbody>
    </table>
</div><!--//.tbl_head01-->
<?php } ?>
<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form><!--//#form01-->
<script>
$(function(){
    //날짜입력
    $("#ppc_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name*=_price], #ppd_price',function(e) {
        var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
        price = (price == '0') ? '' : price;
        $(this).val(price);
	});
    

    //그룹발주관련 멀티파일
	$('#multi_file_ppc').MultiFile();
});

$('.ppd_del').on('click', function(){
    let del_cnt = $('.ppd_del').length;
    let ppd_idx = $(this).attr('ppd_idx');
    if(del_cnt == 1){
        alert('지출정보를 전부 삭제할 수는 없습니다.');
        return false;
    }

    if(!confirm("복구는 불가능합니다.\n자료를 정말 삭제 하시겠습니까?")){
        return false;
    }

    // alert(ppd_idx);
    let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/ppd_del.php';
    $.ajax({
		type: 'POST',
		dataType: 'text',
		url: ajxurl,
		data: {'ppd_idx': ppd_idx},
		success: function(res){
			if(res == 'ok'){
				location.reload();
			}
		},
		error: function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
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

    if(!f.ppc_date.value){
        alert('그룹발주날짜를 선택해 주세요');
        $('#ppc_date').focus();
		return false;
    }

    if(!f.ppc_subject.value){
        alert('제목(중요품목)을 입력해 주세요.');
        $('#ppc_subject').focus();
		return false;
    }

	if(!f.ppc_status.value){
		alert('상태값을 선택해 주세요');
		f.prc_status.focus();
		return false;
	}

    return true;
}
</script>
<?php
include_once ('./_tail.php');