<?php
$sub_menu = "960266";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_purchase';
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

$sql_common = " FROM {$g5_table_name} ppc
                    LEFT JOIN {$g5['company_table']} com ON com.com_idx = ppc.com_idx
                    LEFT JOIN {$g5['project_table']} prj ON ppc.prj_idx = prj.prj_idx
                    LEFT JOIN {$g5['member_table']} mb ON ppc.mb_id = mb.mb_id
";

$where = array();
$where[] = " ppc_status != 'trash' ";   // 디폴트 검색조건

if($stx) {
    switch($sfl){
        case ( $sfl == 'ppc.com_idx' || $sfl == 'ppc.prj_idx' || $sfl == 'ppc_idx' || $sfl == 'ppc.mb_id' ):
            $where[] = " {$sfl} = '{$stx}' ";
            break;
        case ( $sfl == 'com_name' || $sfl == 'prj_name' || $sfl == 'ppc_subject' || $sfl == 'mb_name' ):
            $where[] = " {$sfl} LIKE '%{$stx}%' ";
            break;
    }
}

// 발주일 검색
if ($ser_ppc_date != "") {
    $where[] = " ppc_date = '".trim($ser_ppc_date)."' ";
}


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);



if (!$sst) {
    $sst = "ppc_date";
    $sod = "DESC";
}

if (!$sst2) {
    $sst2 = ", ppc_idx";
    $sod2 = "DESC";
}


$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , ppc_idx
            , ppc.com_idx
            , com_name
            , ppc.prj_idx
            , prj_name
            , ppc.mb_id
            , mb_name
            , ppc_date
            , ppc_subject
            , ppc_price
            , ppc_status
            , ppc_reg_dt
            , ppc_update_dt
            , ( SELECT SUM(ppd_price) FROM {$g5['project_purchase_divide_table']} WHERE ppc_idx = ppc.ppc_idx AND ppd_status IN ('ok','complete') ) AS ppd_sum_price 
            , ( SELECT GROUP_CONCAT(ppt_idx) FROM {$g5['project_purchase_tmp_table']} WHERE ppc_idx = ppc.ppc_idx AND ppt_status = 'ok' ) AS ppt_idxs
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
// echo $sql;exit;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$g5['title'] = '그룹발주관리';
//include_once('./_top_menu_company.php');
include_once('./_head.php');


$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<style>
.td_ppc_idx{}
.td_com_name{}
.td_prj_idx{}
.td_mb_name{}
.td_ppc_subject{}
.td_ppc_price{width:120px;text-align:right !important;}
.td_ppc_price::after{content:' 원'}
.td_ppc_price .sp_red{color:red;}
.td_ppc_price .sp_blue{color:blue;}
.td_ppc_date{width:90px;}
.td_ppc_date input{text-align:center;}
.td_ppc_status{width:120px;}
</style>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="text" name="ser_ppc_date" placeholder="그룹발주일검색" value="<?php echo $ser_ppc_date ?>" readonly id="ser_ppc_date" class="frm_input readonly" style="width:100px;">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="ppc.com_idx"<?php echo get_selected($_GET['sfl'], "ppc.com_idx"); ?>>공급업체번호</option>
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>공급업체명</option>
	<option value="ppc_idx"<?php echo get_selected($_GET['sfl'], "ppc_idx"); ?>>그룹발주번호</option>
	<option value="ppt_subject"<?php echo get_selected($_GET['sfl'], "ppt_subject"); ?>>그룹발주제목</option>
	<option value="prj_idx"<?php echo get_selected($_GET['sfl'], "prj_idx"); ?>>프로젝트번호</option>
	<option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
	<option value="ppc_idx"<?php echo get_selected($_GET['sfl'], "ppc_idx"); ?>>그룹발주번호</option>
	<option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>발주자명</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>
<script>
//날짜입력
$("#ser_ppc_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99",closeText:'취소', onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}} });
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
        <th scope="col" class="th_ppc_idx">그룹발주번호</th>
        <!-- <th scope="col" class="th_com_name">공급업체번호</th> -->
        <th scope="col" class="th_com_name">공급업체</th>
        <th scope="col" class="th_prj_idx">프로젝트번호</th>
        <th scope="col" class="th_prj_idx">프로젝트</th>
        <th scope="col" class="th_mb_name">발주자</th>
        <th scope="col" class="th_ppc_subject">주요품목</th>
        <th scope="col" class="th_ppc_price">금액</th>
        <th scope="col" class="th_ppc_date">그룹발주일</th>
        <th scope="col" class="th_has_files">파일유무</th>
        <th scope="col" class="th_ppc_status">상태</th>
        <th scope="col" class="th_mng">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for($i=0;$row=sql_fetch_array($result);$i++){ 
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['ppc_idx'].'">수정</a>';

        //관련파일 추출
        $fsql = " SELECT COUNT(*) AS cnt FROM {$g5['file_table']}
                WHERE fle_db_table = 'ppc' AND fle_type = 'ppc' AND fle_db_id = '".$row['ppc_idx']."' ORDER BY fle_reg_dt DESC ";
	    $fres = sql_fetch($fsql,1);
        
        //관련파일 추출2
        $fsql2 = " SELECT COUNT(*) AS cnt FROM {$g5['file_table']}
                WHERE fle_db_table = 'ppt' AND fle_type = 'ppt' AND fle_db_id IN (".$row['ppt_idxs'].") ORDER BY fle_reg_dt DESC ";
        // echo $fsql2;
	    $fres2 = sql_fetch($fsql2,1);
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>">
        <td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="ppc_idx[<?=$i?>]" value="<?=$row['ppc_idx']?>" id="ppc_idx_<?=$i?>">
			<label for="chk_<?=$i?>" class="sound_only"><?=get_text($row['ppt_subject'])?></label>
			<input type="checkbox" name="chk[]" ppc_idx="<?=$row['ppc_idx']?>" com_idx="<?=$row['com_idx']?>" prj_idx="<?=$row['prj_idx']?>" value="<?=$i?>" id="chk_<?=$i?>">
		</td>
        <td class="td_ppc_idx"><?=$row['ppc_idx']?></td>
        <!-- <td class="td_com_idx"><?=$row['com_idx']?></td> -->
        <td class="td_com_name"><?=$row['com_name']?></td>
        <td class="td_prj_idx"><?=$row['prj_idx']?></td>
        <td class="td_prj_name"><?=$row['prj_name']?></td>
        <td class="td_mb_name"><?=$row['mb_name']?></td>
        <td class="td_ppc_subject">
            <input type="text" name="ppc_subject[<?=$i?>]" value="<?=$row['ppc_subject']?>" class="frm_input">
        </td>
        <td class="td_ppc_price">
            <?php
            $price_class = '';
            if($row['ppc_price'] - $row['ppd_sum_price'] < 0){
                $price_class = 'sp_red';
            } else if($row['ppc_price'] - $row['ppd_sum_price'] > 0){
                $price_class = 'sp_blue';
            }
            ?>
            <span class="<?=$price_class?>"><?=number_format($row['ppc_price'])?></span>
        </td>
        <td class="td_ppc_date">
            <input type="text" name="ppc_date[<?=$i?>]" readonly value="<?=$row['ppc_date']?>" class="frm_input readonly ppc_date_<?=$i?>">
            <script>
            $(".ppc_date_<?=$i?>").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
            </script>
        </td>
        <td class="td_has_files">
            <?php if($fres['cnt'] || $fres2['cnt']){ ?>
            <i class="fa fa-file" aria-hidden="true"></i>
            <?php } else { ?>
            -
            <?php } ?>
        </td>
        <td class="td_ppc_status">
            <select name="ppc_status[<?=$i?>]" class="ppc_status_<?=$i?>">
                <?=$g5['set_ppc_status_options']?>
            </select>
            <script>$('.ppc_status_<?=$i?>').val('<?=$row['ppc_status']?>');</script>
        </td>
        <td class="td_mng"><?=$s_mod?></td>
    </tr>
    <?php } 
    if($i == 0){
        echo '<tr><td colspan="13" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div><!--//.tbl_head01.tbl_wrap-->
<div class="btn_fixed_top">
    <?php if(false){ ?>
        <a href="./pri_purchase_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <?php } ?>
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">그룹발주추가</a>
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

</script>
<?php
include_once ('./_tail.php');