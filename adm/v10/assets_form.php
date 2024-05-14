<?php
$sub_menu = '960650';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'assets';
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

$msql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level <= 8 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name != '테스일' AND mb_name != '일정관리' ORDER BY mb_name ";
$mres = sql_query($msql,1);
$mb_opts = '';
for($i=0;$mrow=sql_fetch_array($mres);$i++){
    $mb_opts .= '<option value="'.$mrow['mb_id'].'">'.$mrow['mb_name'].'</option>'.PHP_EOL;
}

$part_arr = array();
$part_opts = '';
//'1' => 'ING', '5' => '지역사무소', '6' => '대리점', '7' => '울산TP'
foreach($g5['department_name'] as $dk=>$dv){
    if($dk == 1 || $dk == 5 || $dk == 6 || $dk == 7) continue;
    $part_arr[$dk] = $dv;
    $part_opts .= '<option value="'.$dk.'">'.$dv.'</option>'.PHP_EOL;
}

// print_r2($part_arr);

if($w == ''){
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
	$row['ppt_status'] = 'pending';
}
else if($w == 'u'){
    $astsql = " SELECT ast.*, mb_name FROM {$g5['assets_table']} ast
                    LEFT JOIN {$g5['member_table']} mb ON ast.mb_id_buy = mb.mb_id
                WHERE ast_idx = '{$ast_idx}' ";
    $ast = sql_fetch($astsql,1);
    $asmsql = " SELECT (ROW_NUMBER() OVER(ORDER BY asm_reg_dt)) AS num 
                    , asm_idx
                    , ast_idx
                    , asm.mb_id
                    , mb_id_mng
                    , asm_memo
                    , mb_name
                    , asm_given_date
                    , asm_return_date
                    , mb_id_acceptor
                    , asm_status
                    , asm_reg_dt
                    , asm_update_dt
                    FROM {$g5['assets_manager_table']} asm
                    LEFT JOIN {$g5['member_table']} wmb ON asm.mb_id = wmb.mb_id
                WHERE ast_idx = '{$ast_idx}' ORDER BY asm_reg_dt DESC ";
    $asmres = sql_query($asmsql,1);
}

$html_title = ($w=='')?'추가':'수정';
$html_title = ($copy)?'복제':$html_title;
$g5['title'] = '물품'.$html_title;
include_once('./_head.php');
?>
<style>
input[type=text]{padding:0 5px;}
input.readonly{background:#ededed;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" >
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="ast_idx" value="<?php echo $ast["ast_idx"] ?>">
<?=$form_input?>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>사내물품관리 페이지입니다.</p>
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
            <th scope="row">품명</th>
            <td>
                <input type="text" name="ast_name" value="<?=$ast['ast_name']?>" class="frm_input" style="width:300px;">
            </td>
            <th scope="row">관리번호</th>
            <td>
                <?php
                $ast_no = ($w == '') ? get_uniqid() : $ast['ast_no'];
                ?>
                <input type="text" name="ast_no" value="<?=$ast_no?>" readonly class="frm_input readonly" style="text-align:center;">
            </td>
        </tr>
        <tr>
            <th scope="row">구매자</th>
            <td>
                <select name="mb_id_buy" id="mb_id_buy" class="frm_input">
                    <option value="">::구매자선택::</option>
                    <?=$mb_opts?>
                </select>
                <script>
                <?php if($w != ''){ ?>
                $('#mb_id_buy').val('<?=$ast['mb_id_buy']?>');
                <?php } ?>
                </script>
            </td>
            <th scope="row">구매일</th>
            <td>
                <input type="text" name="ast_date" value="<?=(($ast['ast_date'])?$ast['ast_date']:'0000-00-00')?>" readonly class="frm_input readonly" style="width:90px;text-align:center;">
            </td>
        </tr>
        <tr>
            <th scope="row">구매처</th>
            <td>
                <input type="text" name="ast_buycom" value="<?=$ast['ast_buycom']?>" class="frm_input" style="width:200px;">
            </td>
            <th scope="row">메모</th>
            <td>
                <input type="text" name="ast_memo" value="<?=$ast['ast_memo']?>" class="frm_input" style="width:100%;">
            </td>
        </tr>
        <tr>
            <th scope="row">관리부서</th>
            <td>
                <select name="ast_part" id="ast_part">
                    <option value="">::관리부서::</option>
                    <?=$part_opts?>
                </select>
                <script>
                <?php if($w != ''){ ?>
                $('#ast_part').val('<?=$ast['ast_part']?>');
                <?php } ?>
                </script>
            </td>
            <th scope="row">물품상태</th>
            <td>
                <select name="ast_status" id="ast_status">
                    <?=$g5['set_ast_status_value_options']?>
                </select>
                <script>
                <?php if($w != ''){ ?>
                $('#ast_status').val('<?=$ast['ast_status']?>');
                <?php } ?>
                </script>
            </td>
        </tr>
    </tbody>
    </table>
</div><!--//.tbl_frm01 .tbl_wrap-->
<?php if($w != ''){ ?>
<style>
.ul_asm{margin-bottom:20px;}
.ul_asm::after{display:block;visibility:hidden;clear:both;content:'';}
.ul_asm li{float:left;margin-right:10px;}
.li_asm_type{}
.li_asm_memo input{width:200px;}
.li_asm_price input{width:100px;text-align:right;}
.li_asm_given_date input{width:100px;text-align:center;}
.li_asm_return_date input{width:100px;text-align:center;}
.li_mb_id_acceptor{}
.li_asm_mng{padding-top:3px;}

.th_asm_type{width:110px;}
.th_asm_per{width:90px;}
.th_asm_price{width:120px;}
.th_asm_given_date{width:100px;}
.th_asm_return_date{width:100px;}
.th_asm_bank{width:110px;}

.td_asm_per{text-align:right}
.td_asm_per::after{content:' %'}
.td_asm_price input{text-align:right;padding:0 5px;}
</style>
<div class="">
    <h2>물품관리자정보등록</h2>
    <ul class="ul_asm">
        <li class="li_mb_id_mng">
            <select id="mb_id_mng">
                <option value="">::물품관리자::</option>
                <?=$mb_opts?>
            </select>
        </li>
        <li class="li_asm_memo">
            <input type="text" id="asm_memo" placeholder="메모입력" class="frm_input">
        </li>
        <li class="li_asm_given_date">
            <input type="text" id="asm_given_date" placeholder="지급일" value="0000-00-00" readonly class="frm_input">
        </li>
        <li class="li_asm_return_date">
            <input type="text" id="asm_return_date" placeholder="반납일" value="0000-00-00" readonly class="frm_input">
        </li>
        <li class="li_mb_id_acceptor">
            <select id="mb_id_acceptor">
                <option value="">::인수자::</option>
                <?=$mb_opts?>
            </select>
        </li>
        <li class="li_asm_status">
            <select id="asm_status">
                <?=$g5['set_asm_status_value_options']?>
            </select>
        </li>
        <li class="li_asm_mng">
            <a href="javascript:" class="btn btn_02 asm_reg">등록</a>
        </li>
    </ul>
</div>
<script>
//지급일
$("#asm_given_date").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99",closeText:'취소',onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('0000-00-00');$('#asm_return_date').val('0000-00-00');}} ,onSelect:function(selectedDate){$('#asm_return_date').datepicker('option','minDate',selectedDate); $('#asm_return_date').val('0000-00-00');}});

//반납일
$("#asm_return_date").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99" ,closeText:'취소',onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('0000-00-00');}},onSelect:function(selectedDate){if($('#asm_given_date').val() == '0000-00-00'){ $(this).val('0000-00-00'); }}});

$('.asm_reg').on('click', function(){
    let ast_idx = <?=$ast_idx?>;
    let mb_id_mng = $('#mb_id_mng').val();
    let asm_memo = $.trim($('#asm_memo').val());
    let asm_given_date = $('#asm_given_date').val();
    let asm_return_date = $('#asm_return_date').val();
    let mb_id_acceptor = $('#mb_id_acceptor').val();
    let asm_status = $('#asm_status').val();

    if(!mb_id_mng){
        alert('물품관리자를 선택해 주세요.');
        $('#mb_id_mng').focus();
        return false;
    }
    if(asm_given_date == '0000-00-00'){
        alert('지급일을 선택해 주세요.');
        $('#asm_given_date').focus();
        return false;
    }
    
    let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/asm_reg.php';
    $.ajax({
        type: 'POST',
        dataType: 'text',
        url: ajxurl,
        data: {'ast_idx': ast_idx,'mb_id_mng': mb_id_mng, 'asm_memo': asm_memo, 'asm_given_date': asm_given_date, 'asm_return_date': asm_return_date, 'mb_id_acceptor': mb_id_acceptor, 'asm_status': asm_status},
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

<?php } ?>

<?php if($w != '' && $asmres->num_rows){ ?>
<div class="tbl_head01 tbl_wrap">
    <h2>물품관리자 상세내용</h2>
    <table class="table table-bordered table-condensed">
    <caption>상세내용</caption>
    <thead>
        <tr>
            <th scope="col" class="th_no">번호</th>
            <th scope="col" class="th_mb_id_mng" style="width:100px;">물품관리자</th>
            <th scope="col" class="th_asm_memo">메모</th>
            <th scope="col" class="th_asm_given_date">지급일</th>
            <th scope="col" class="th_asm_return_date">반납일</th>
            <th scope="col" class="th_mb_id_acceptor" style="width:100px;">인수자</th>
            <th scope="col" class="th_asm_status" style="width:100px;">상태</th>
            <th scope="col" class="th_mng" style="width:80px;">삭제</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    for($i=0;$row=sql_fetch_array($asmres);$i++){ 
        // print_r2($row);
    ?>
        <tr>
            <td class="td_no"><?=$row['num']?></td><!--번호-->
            <td class="td_mb_id_mng">
                <select name="mb_id_mng[<?=$row['asm_idx']?>]" class="frm_input mb_id_mng_<?=$i?>">
                    <?=$mb_opts?>
                </select>
                <script>
                $('.mb_id_mng_<?=$i?>').val('<?=$row['mb_id_mng']?>');
                </script>
            </td><!--관리자-->
            <td class="td_asm_memo">
                <input type="text" name="asm_memo[<?=$row['asm_idx']?>]" value="<?=$row['asm_memo']?>" class="frm_input asm_memo_<?=$i?>">
            </td><!--메모-->
            <td class="">
                <input type="text" name="asm_given_date[<?=$row['asm_idx']?>]" value="<?=$row['asm_given_date']?>" class="frm_input asm_given_date<?=$i?>" style="text-align:center;">
                <script>
                $(".asm_given_date<?=$i?>").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99",onSelect:function(selectedDate){$('.asm_return_date<?=$i?>').datepicker('option','minDate',selectedDate); $('.asm_return_date<?=$i?>').val('0000-00-00');}});
                </script>
            </td><!--지급일-->
            <td class="">
                <input type="text" name="asm_return_date[<?=$row['asm_idx']?>]" value="<?=$row['asm_return_date']?>" class="frm_input asm_return_date<?=$i?>" style="text-align:center;">
                <script>
                $(".asm_return_date<?=$i?>").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99",closeText:'취소',onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('0000-00-00');}},onSelect:function(selectedDate){if($('.asm_given_date<?=$i?>').val() == '0000-00-00'){ $(this).val('0000-00-00'); }}});
                </script>
            </td><!--반납일-->
            <td class="">
                <select name="mb_id_acceptor[<?=$row['asm_idx']?>]" class="frm_input mb_id_acceptor_<?=$i?>">
                    <option value="">::인수자::</option>
                    <?=$mb_opts?>
                </select>
                <script>
                $('.mb_id_acceptor_<?=$i?>').val('<?=$row['mb_id_acceptor']?>');
                </script>
            </td><!--인수자-->
            <td class="">
                <select name="asm_status[<?=$row['asm_idx']?>]" class="frm_input asm_status_<?=$i?>">
                    <?=$g5['set_asm_status_value_options']?>
                </select>
                <script>
                $('.asm_status_<?=$i?>').val('<?=$row['asm_status']?>');
                </script>
            </td><!--상태-->
            <td class="">
                <a href="javascript:" class="btn btn_01 asm_del" asm_idx="<?=$row['asm_idx']?>">삭제</a>
            </td><!--삭제-->
        </tr>
    <?php } ?>
    </tbody>
    </table>
</div><!--//.tbl_head01-->
<?php } //if($w != '' && $asmres->num_rows) ?>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>


<script>
//날짜입력
$("input[name=ast_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소',onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('0000-00-00');}} });

$('.asm_del').on('click', function(){
    let asm_idx = $(this).attr('asm_idx');

    if(!confirm("복구는 불가능합니다.\n자료를 정말 삭제 하시겠습니까?")){
        return false;
    }

    // alert(ppd_idx);
    let ajxurl = '<?=G5_USER_ADMIN_AJAX_URL?>/asm_del.php';
    $.ajax({
		type: 'POST',
		dataType: 'text',
		url: ajxurl,
		data: {'asm_idx': asm_idx},
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

}
</script>
<?php
include_once ('./_tail.php');