<?php
$sub_menu = "960268";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase_tmp';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명

// 아래 foreach블록은 XXX_form.php파일에 제일 상단에도 서술하자
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

$sql_common = " FROM {$g5_table_name} ppt
                    LEFT JOIN {$g5['company_table']} com ON com.com_idx = ppt.com_idx
                    LEFT JOIN {$g5['project_table']} prj ON ppt.prj_idx = prj.prj_idx
                    LEFT JOIN {$g5['member_table']} mb ON ppt.mb_id = mb.mb_id
";

$where = array();
$where[] = " ppt_status != 'trash' ";   // 디폴트 검색조건
// 일반사원이면 모든 목록을 확인(대표와 경리가 아닌면) 정보등급이 $member['mb_6'] != '1'이 아니면
if(!$super_admin){
    $where[] = " mb.mb_id = '{$member['mb_id']}' "; 
}

if($stx) {
    switch($sfl){
        case ( $sfl == 'ppt.com_idx' || $sfl == 'ppt.prj_idx' || $sfl == 'ppt_idx' || $sfl == 'ppc_idx' || $sfl == 'ppt.mb_id' ):
            $where[] = " {$sfl} = '{$stx}' ";
            break;
        case ( $sfl == 'com_name' || $sfl == 'prj_name' || $sfl == 'ppt_subject' || $sfl == 'mb_name' ):
            $where[] = " {$sfl} LIKE '%{$stx}%' ";
            break;
    }
}

// 발주일 검색
if ($ser_ppt_date != "") {
    $where[] = " ppt_date = '".trim($ser_ppt_date)."' ";
}

// 발주일 검색
if ($ser_ppc_has != "") {
    if($ser_ppc_has == 'no')
        $where[] = ($sfl == 'ppc_idx' && $stx) ? " ppc_idx = '{$stx}' "  : " ppc_idx = '0' ";
    else if($ser_ppc_has = 'ok'){
        $where[] = ($sfl == 'ppc_idx' && $stx) ? " ppc_idx = '{$stx}' "  : " ppc_idx != '0' ";
    }
}


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);



if (!$sst) {
    $sst = "ppt_date";
    $sod = "DESC";
}

if (!$sst2) {
    $sst2 = ", ppt_idx";
    $sod2 = "DESC";
}


$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , ppt_idx
            , ppt.com_idx
            , com_name
            , ppc_idx
            , ppt.prj_idx
            , prj_name
            , ppt.mb_id
            , mb_name
            , ppt_date
            , ppt_subject
            , ppt_price
            , ppt_status
            , ppt_reg_dt
            , ppt_update_dt
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


$g5['title'] = '개별발주관리';
//include_once('./_top_menu_company.php');
include_once('./_head.php');


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>
.td_ppt_idx{}
.td_com_name{}
.td_prj_idx{}
.td_ppc_idx{}
.td_mb_name{}
.td_ppt_subject{}
.td_ppt_price{width:120px;}
.td_ppt_price input{text-align:right;}
.td_ppt_date{width:90px;}
.td_ppt_date input{text-align:center;}
.td_ppt_status{width:120px;}
</style>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="text" name="ser_ppt_date" placeholder="발주일검색" value="<?php echo $ser_ppt_date ?>" readonly id="ser_ppt_date" class="frm_input readonly" style="width:90px;">
<input type="text" name="ser_prj_idx" value="<?php echo $ser_prj_idx ?>" placeholder="프로젝트번호" id="ser_prj_idx" class="frm_input" style="width:100px;text-align:right;">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="ppt.com_idx"<?php echo get_selected($_GET['sfl'], "ppt.com_idx"); ?>>공급업체번호</option>
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>공급업체명</option>
	<option value="ppt_idx"<?php echo get_selected($_GET['sfl'], "ppt_idx"); ?>>개별발주번호</option>
	<option value="ppt_subject"<?php echo get_selected($_GET['sfl'], "ppt_subject"); ?>>개별발주제목</option>
	<option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
	<option value="ppc_idx"<?php echo get_selected($_GET['sfl'], "ppc_idx"); ?>>그룹발주번호</option>
	<option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>발주자명</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<select name="ser_ppc_has" id="ser_ppc_has">
    <option value="">그룹발주상관없이</option>
    <option value="no">그룹발주없음</option>
    <option value="ok">그룹발주있음</option>
</select>
<input type="submit" class="btn_submit" value="검색">
</form>
<script>
$('#ser_ppc_has').val('<?=$ser_ppc_has?>');
//날짜입력
$("#ser_ppt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99",closeText:'취소', onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}} });
</script>
<div class="local_desc01 local_desc" style="display:none;">
    <p>발주관리 페이지입니다.</p>
</div>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<?=$form_input?>

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <tr>
        <th scope="col" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col" class="th_ppt_idx">개별발주번호</th>
        <th scope="col" class="th_com_name">공급업체번호</th>
        <th scope="col" class="th_com_name">공급업체</th>
        <th scope="col" class="th_prj_idx">프로젝트번호</th>
        <th scope="col" class="th_prj_idx">프로젝트</th>
        <th scope="col" class="th_ppc_idx">그룹발주번호</th>
        <th scope="col" class="th_mb_name">발주자</th>
        <th scope="col" class="th_ppt_subject">주요품목</th>
        <th scope="col" class="th_ppt_price">금액</th>
        <th scope="col" class="th_ppt_date">발주일</th>
        <th scope="col" class="th_has_files">파일유무</th>
        <th scope="col" class="th_ppt_status">상태</th>
        <th scope="col" class="th_mng">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for($i=0;$row=sql_fetch_array($result);$i++){ 
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['ppt_idx'].'">수정</a>';

        //관련파일 추출
        $fsql = " SELECT COUNT(*) AS cnt FROM {$g5['file_table']}
                WHERE fle_db_table = 'ppt' AND fle_type = 'ppt' AND fle_db_id = '".$row['ppt_idx']."' ORDER BY fle_reg_dt DESC ";
	    $fres = sql_fetch($fsql,1);
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>">
        <td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="ppt_idx[<?=$i?>]" value="<?=$row['ppt_idx']?>" id="ppt_idx_<?=$i?>">
			<label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['ppt_subject'])?></label>
			<input type="checkbox" name="chk[]" ppc_idx="<?=$row['ppc_idx']?>" com_idx="<?=$row['com_idx']?>" ppt_idx="<?=$row['ppt_idx']?>" prj_idx="<?=$row['prj_idx']?>" value="<?=$i?>" id="chk_<?=$i?>">
		</td>
        <td class="td_ppt_idx"><?=$row['ppt_idx']?></td>
        <td class="td_com_idx"><?=$row['com_idx']?></td>
        <td class="td_com_name"><?=$row['com_name']?></td>
        <td class="td_prj_idx"><?=$row['prj_idx']?></td>
        <td class="td_prj_name"><?=$row['prj_name']?></td>
        <td class="td_ppc_idx"><?=$row['ppc_idx']?></td>
        <td class="td_mb_name"><?=$row['mb_name']?></td>
        <td class="td_ppt_subject">
            <input type="text" name="ppt_subject[<?=$i?>]" value="<?=$row['ppt_subject']?>" class="frm_input">
        </td>
        <td class="td_ppt_price">
            <input type="text" name="ppt_price[<?=$i?>]" value="<?=number_format($row['ppt_price'])?>" class="frm_input">
        </td>
        <td class="td_ppt_date">
            <input type="text" name="ppt_date[<?=$i?>]" readonly value="<?=$row['ppt_date']?>" class="frm_input readonly ppt_date_<?=$i?>">
            <script>
            $(".ppt_date_<?=$i?>").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
            </script>
        </td>
        <td class="td_has_files">
            <?php if($fres['cnt']){ ?>
            <i class="fa fa-file" aria-hidden="true"></i>
            <?php } else { ?>
            -
            <?php } ?>
        </td>
        <td class="td_ppt_status">
            <select name="ppt_status[<?=$i?>]" class="ppt_status_<?=$i?>">
                <?=$g5['set_ppt_status_options']?>
            </select>
            <script>$('.ppt_status_<?=$i?>').val('<?=$row['ppt_status']?>');</script>
        </td>
        <td class="td_mng"><?=$s_mod?></td>
    </tr>
    <?php } 
    if($i == 0){
        echo '<tr><td colspan="14" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div><!--//.tbl_head01.tbl_wrap-->
<div class="btn_fixed_top">
    <?php if(false){ ?>
        <a href="./pri_purchase_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <?php } ?>
    <?php if($super_admin){ ?>
        <a href="javascript:" id="ppt_in_ppc" class="btn btn_03">그룹발주연결</a>
        <a href="javascript:" id="ppt_to_ppc" class="btn btn_04">그룹발주등록</a>
    <?php } ?>
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">발주추가</a>
    <?php } ?>
</div>
</form><!--//#form01-->

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script defer>
// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name*=_price]',function(e) {
    var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
    price = (price == '0') ? '' : price;
    $(this).val(price);
});

function form01_submit(f){
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

// 정식발주연결 버튼을 누르면 기존등록된 정식발주목록이 표시되는 윈도우창이 열린다.
$('#ppt_in_ppc').on('click',function(){

});

// 정식발주등록 버튼을 누르면 모달창이 열린다.
$('#ppt_to_ppc').on('click', function(){
    // const f = document.getElementById('form01');
    const chks = document.querySelectorAll('input[name="chk[]"]:checked');
    
    let com_idx = '';
    let prj_idx = '';
    let ppt_idxs = '';
    let ppc_msg = '';
    let com_msg = '';
    let prj_msg = '';

    if (!is_checked("chk[]")) {
        alert("정식발주 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    chks.forEach(function(chk){
        let pc_idx = chk.getAttribute('ppc_idx');
        let c_idx = chk.getAttribute('com_idx');
        let p_idx = chk.getAttribute('ppt_idx');
        let pj_idx = chk.getAttribute('prj_idx');
        
        if(pc_idx != '0'){
            ppc_msg = '이미 그룹발주에 등록된 항목이 있습니다.';
        }
        if(com_idx != '' && com_idx !== c_idx){
            com_msg = '동일한 공급업체의 항목으로만 구성해서 등록해 주세요.';
        }
        if(prj_idx != '' && prj_idx !== pj_idx){
            prj_msg = '동일한 프로젝트의 항목으로만 구성해서 등록해 주세요.';
        }
        
        com_idx = c_idx;
        prj_idx = pj_idx;
        ppt_idxs += (ppt_idxs == '') ? p_idx : ',' + p_idx;
    });

    if(ppc_msg){
        alert(ppc_msg);
        return false;
    }
    if(com_msg){
        alert(com_msg);
        return false;
    }
    if(prj_msg){
        alert(prj_msg);
        return false;
    }
    
    mdl_open();
    $('#prj_purchasetmp_list_modal').find('#ppt_idxs').val(ppt_idxs);
    $('#prj_purchasetmp_list_modal').find('#com_idx').val(com_idx);
    $('#prj_purchasetmp_list_modal').find('#prj_idx').val(prj_idx);
});

// 모달 닫는 이벤트
$('.mdl_bg, .mdl_close').on('click', function(){
    mdl_close();
});

// 모달여는 함수
function mdl_open(){
    $('#prj_purchasetmp_list_modal').removeClass('mdl_hide');
}
// 모달닫는 함수
function mdl_close(){
    $('#prj_purchasetmp_list_modal').find('#ppt_idxs').val('');
    $('#prj_purchasetmp_list_modal').find('#com_idx').val('');
    $('#prj_purchasetmp_list_modal').find('#prj_idx').val('');
    $('#prj_purchasetmp_list_modal').find('#ppc_date').val('');
    $('#prj_purchasetmp_list_modal').find('#ppc_price').val('');
    $('#prj_purchasetmp_list_modal').find('#ppc_subject').val('');
    $('#prj_purchasetmp_list_modal').find('#ppc_content').val('');
    $('#prj_purchasetmp_list_modal').find('#multi_file_ppc').MultiFile('reset');
    $('#prj_purchasetmp_list_modal').addClass('mdl_hide');
}
</script>
<?php
include_once ('./_tail.php');