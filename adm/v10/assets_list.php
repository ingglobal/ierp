<?php
$sub_menu = '960650';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '사내물품관리';
// include_once('./_top_menu_reseller.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];

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

$msql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level <= 8 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name != '테스일' AND mb_name != '일정관리' ORDER BY mb_name ";
$mres = sql_query($msql,1);
$mbopt = '';
for($i=0;$mrow=sql_fetch_array($mres);$i++){
    $mbopt .= '<option value="'.$mrow['mb_id'].'">'.$mrow['mb_name'].'</option>'.PHP_EOL;
}

$part_arr = array();
$part_opts = '';
//'1' => 'ING', '5' => '지역사무소', '6' => '대리점', '7' => '울산TP'
foreach($g5['department_name'] as $dk=>$dv){
    if($dk == 1 || $dk == 5 || $dk == 6 || $dk == 7) continue;
    $part_arr[$dk] = $dv;
    $part_opts .= '<option value="'.$dk.'">'.$dv.'</option>'.PHP_EOL;
}

$sql_common = " FROM {$g5['assets_table']} ast
                LEFT JOIN {$g5['member_table']} mbb ON ast.mb_id_buy = mbb.mb_id 
";

$where = array();
$where[] = " ast_status != 'trash' ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ($sfl == 'ast_no') :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}
/*
SELECT GROUP_CONCAT(DISTINCT a.ast_idx) AS ast_idxs 
    FROM g5_1_assets_manager a 
    INNER JOIN ( 
        SELECT ast_idx 
            , MAX(asm_reg_dt) AS max_reg_dt 
        FROM g5_1_assets_manager 
        WHERE asm_status != 'trash' 
        GROUP BY ast_idx 
    ) b ON b.max_reg_dt = asm_reg_dt 
WHERE mb_id_mng = 'lbk1130'
*/
if($ser_mb_id_mng){
    $sub_asmsql = " SELECT ast_idx
                        , MAX(asm_reg_dt) AS max_reg_dt 
                    FROM {$g5['assets_manager_table']} WHERE asm_status != 'trash' 
                    GROUP BY ast_idx ";
    // echo $sub_asmsql;
    $asmsql = " SELECT GROUP_CONCAT(DISTINCT a.ast_idx) AS ast_idxs 
                FROM {$g5['assets_manager_table']} a
                INNER JOIN ( {$sub_asmsql} ) b ON b.max_reg_dt = asm_reg_dt
                WHERE mb_id_mng = '{$ser_mb_id_mng}' ";
    // echo $asmsql;
    $asmres = sql_fetch($asmsql); 

    $where[] = ($asmres['ast_idxs']) ? " ast.ast_idx IN ({$asmres['ast_idxs']}) " : " ast.ast_idx IN ('') ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "ast_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(ast_idx) as cnt " . $sql_common;
$row = sql_fetch($sql);

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$total_count = $row['cnt'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$sql = " SELECT *
            , ( SELECT asm_idx FROM {$g5['assets_manager_table']} WHERE ast_idx = ast.ast_idx AND asm_status != 'trash' ORDER BY asm_given_date DESC, asm_reg_dt DESC LIMIT 1  ) AS asm_idx
            , ( SELECT mb_id_mng FROM {$g5['assets_manager_table']} WHERE ast_idx = ast.ast_idx AND asm_status != 'trash' ORDER BY asm_given_date DESC, asm_reg_dt DESC LIMIT 1  ) AS mb_id_mng
            , ( SELECT asm_status FROM {$g5['assets_manager_table']} WHERE ast_idx = ast.ast_idx AND asm_status != 'trash' ORDER BY asm_given_date DESC, asm_reg_dt DESC LIMIT 1  ) AS asm_status
            , ( SELECT asm_given_date FROM {$g5['assets_manager_table']} WHERE ast_idx = ast.ast_idx AND asm_status != 'trash' ORDER BY asm_given_date DESC, asm_reg_dt DESC LIMIT 1  ) AS asm_given_date
            , ( SELECT asm_return_date FROM {$g5['assets_manager_table']} WHERE ast_idx = ast.ast_idx AND asm_status != 'trash' ORDER BY asm_given_date DESC, asm_reg_dt DESC LIMIT 1  ) AS asm_return_date
		{$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";

$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 15;
?>
<style>

</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<select name="ser_mb_id_mng" id="ser_mb_id_mng">
    <option value="">::관리자선택::</option>
    <?=$mbopt?>
</select>
<script>
<?php if($ser_mb_id_mng){ ?>
$('#ser_mb_id_mng').val('<?=$ser_mb_id_mng?>');
<?php } ?>
</script>
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="ast_no"<?php echo get_selected($_GET['sfl'], "ast_no"); ?>>시리얼번호</option>
    <option value="mb_id_buy"<?php echo get_selected($_GET['sfl'], "mb_id_buy"); ?>>구매자ID</option>
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>구매자명</option>
    <option value="ast_memo"<?php echo get_selected($_GET['sfl'], "ast_memo"); ?>>메모</option>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>


<div class="local_desc01 local_desc">
    <p>사내물품을 관리하는 페이지 입니다.</p>
</div>


<form name="form01" id="form01" action="./assets_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
	<tr class="success">
		<th scope="col" rowspan="2">
			<label for="chkall" class="sound_only">물품목록 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
		<th scope="col">번호</th>
		<th scope="col">품명</th>
		<th scope="col">시리얼번호</th>
		<th scope="col">관리부서</th>
		<th scope="col">구매자</th>
		<th scope="col">구매일</th>
		<th scope="col">구매처</th>
		<th scope="col">메모</th>
		<th scope="col">최종관리자</th>
		<th scope="col">관리자상태</th>
		<th scope="col">지급일</th>
		<th scope="col">반납일</th>
		<th scope="col">물품상태</th>
		<th scope="col">등록일</th>
		<th scope="col">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $s_mod = '<a href="./assets_form.php?'.$qstr.'&amp;w=u&amp;ast_idx='.$row['ast_idx'].'">수정</a>';
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>" tr_id="<?=$row['ast_idx']?>">
        <td class="td_chk">
			<input type="hidden" name="ast_idx[<?=$i?>]" value="<?=$row['ast_idx']?>" id="ast_idx_<?=$i?>">
			<label for="chk_<?=$i;?>" class="sound_only"><?=get_text($row['ast_no'])?></label>
			<input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
		</td>
        <td class="td_ast_idx"><?=$row['ast_idx']?></td><!--번호-->
        <td class="td_ast_name"><?=$row['ast_name']?></td><!--품명-->
        <td class="td_ast_no"><?=$row['ast_no']?></td><!--시리얼번호-->
        <td class="td_ast_part">
            <?=$part_arr[$row['ast_part']]?>
        </td><!--관리부서-->
        <td class="td_mb_id_buy"><?=$row['mb_name']?></td><!--구매자-->
        <td class="td_ast_date"><?=substr($row['ast_date'],2,8)?></td><!--구매일-->
        <td class="td_ast_buycom" style="width:120px;">
            <!-- <input type="text" name="ast_buycom[<?=$i?>]" value="<?=$row['ast_buycom']?>" class="frm_input"> -->
            <?=$row['ast_buycom']?>
        </td><!--구매처-->
        <td class="td_ast_memo">
            <!-- <input name="ast_memo[<?=$i?>]" value="<?=$row['ast_memo']?>" class="frm_input"> -->
            <?=$row['ast_memo']?>
        </td><!--메모-->
        <td class="td_mb_id_mng">
            <input type="hidden" name="asm_idx[<?=$i?>]" value="<?=$row['asm_idx']?>">
            <?php
            $mb = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_mng']}' ");
            ?>
            <?=(($mb['mb_name'])?$mb['mb_name']:'-')?>
        </td><!--관리자-->
        <td class="td_asm_status" style="width:80px;">
            <?php if($mb['mb_name']){ ?>
            <select name="asm_status[<?=$i?>]" class="frm_input asm_status_<?=$i?>">
                <?=$g5['set_asm_status_value_options']?>
            </select>
            <script>
            $('.asm_status_<?=$i?>').val('<?=$row['asm_status']?>');
            </script>
            <?php } else { echo '-'; echo '<input type="hidden" name="asm_status['.$i.']" value="">'; } ?>
        </td><!--관리자상태-->
        <td class="td_asm_given_date" style="width:90px;">
            <?=(($mb['mb_name'])?$row['asm_given_date']:'-')?>
        </td><!--지급일-->
        <td class="td_asm_return_date" style="width:100px;">
            <?php if($mb['mb_name']){ ?>
            <input type="text" name="asm_return_date[<?=$i?>]" value="<?=$row['asm_return_date']?>" class="frm_input asm_return_date<?=$i?>" style="width:80px;text-align:center;">
            <script>
            $(".asm_return_date<?=$i?>").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99"});
            </script>
            <?php } else { echo '-'; echo '<input type="hidden" name="asm_return_date['.$i.']" value="">'; } ?>
        </td>
        <td class="td_ast_status" style="width:80px;">
            <select name="ast_status[<?=$i?>]" class="frm_input ast_status_<?=$i?>">
                <?=$g5['set_ast_status_value_options']?>
            </select>
            <script>
            $('.ast_status_<?=$i?>').val('<?=$row['ast_status']?>');
            </script>
        </td><!--물품상태-->
        <td class="td_ast_reg_dt"><?=substr($row['ast_reg_dt'],2,8)?></td><!--등록일-->
        <td class="td_mng"><?=$s_mod?></td><!--관리-->
    </tr>
    <?php
    }
    if($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php //if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <?php if($super_admin) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <a href="./assets_form.php" id="btn_add" class="btn btn_01">물품추가</a>
    <?php } ?>
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>


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
?>
