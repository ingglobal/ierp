<?php
$sub_menu = '960265';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '회의록';
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


$sql_common = " FROM {$g5['meeting_table']} mtg
                LEFT JOIN {$g5['project_table']} prj ON mtg.prj_idx = prj.prj_idx
                LEFT JOIN {$g5['member_table']} mb on mtg.mb_id_writer = mb.mb_id ";


$where = array();
$where[] = " mtg_status != 'trash' ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ($sfl == 'mtg_idx' || $sfl == 'prj.prj_idx') :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "mtg_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(mtg_idx) as cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$total_count = $row['cnt'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산


$sql = " SELECT *
		{$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";

$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$colspan = 13;
?>
<style>
.a_mtg_subject{font-weight:bold;color:darkblue !important;text-decoration:underline !important;text-underline-offset: 4px;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="mtg_subject"<?php echo get_selected($_GET['sfl'], "mtg_subject"); ?>>안건제목</option>
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>작성자명</option>
    <option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>회의록을 관리하는 페이지 입니다.</p>
</div>

<form name="form01" id="form01" action="./meeting_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
		<th scope="col">
			<label for="chkall" class="sound_only">회의목록 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
		<th scope="col">번호</th>
		<th scope="col">프로제트번호</th>
		<th scope="col">프로제트명</th>
		<th scope="col">회의타입</th>
		<th scope="col">주요안건</th>
		<th scope="col">작성자</th>
		<th scope="col">회의날짜</th>
		<th scope="col">시작시간</th>
		<th scope="col">종료시간</th>
		<th scope="col">등록일</th>
		<th scope="col">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $s_mod = '<a href="./meeting_form.php?'.$qstr.'&amp;w=u&amp;mtg_idx='.$row['mtg_idx'].'">수정</a>';
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>" tr_id="<?=$row['mtg_idx']?>">
        <td class="td_chk">
			<input type="hidden" name="mtg_idx[<?=$i?>]" value="<?=$row['mtg_idx']?>" id="mtg_idx_<?=$i?>">
			<label for="chk_<?=$i;?>" class="sound_only"><?=get_text($row['mtg_subject'])?></label>
			<input type="checkbox" name="chk[]" value="<?=$i?>" id="chk_<?=$i?>">
		</td>
        <td class="td_mtg_idx"><?=$row['mtg_idx']?></td><!--번호-->
        <td class="td_prj_idx"><?=(($row['prj_idx'])?$row['prj_idx']:'기타')?></td><!--프로젝트번호-->
        <td class="td_prj_name"><?=(($row['prj_name'])?$row['prj_name']:'기타회의')?></td><!--프로젝트명-->
        <td class="td_mtg_type"><?=$g5['set_mtg_type_value'][$row['mtg_type']]?></td><!--회의타입-->
        <td class="td_mtg_subject">
            <a href="./meeting_view.php?<?=$qstr?>&mtg_idx=<?=$row['mtg_idx']?>" class="a_mtg_subject"><?=cut_str($row['mtg_subject'],80,'...')?></a>
        </td><!--주요안건-->
        <td class="td_mb_name"><?=$row['mb_name']?></td><!--작성자-->
        <td class="td_mtg_date"><?=$row['mtg_date']?></td><!--회의날짜-->
        <td class="td_mtg_start_time"><?=substr($row['mtg_start_time'],0,5)?></td><!--회의시작시간-->
        <td class="td_mtg_end_time"><?=substr($row['mtg_end_time'],0,5)?></td><!--회의종료시간-->
        <td class="td_mtg_reg_dt"><?=substr($row['mtg_reg_dt'],2,8)?></td><!--회의종료시간-->
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
    <?php if($is_member) { ?>
    <!-- <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;"> -->
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <a href="./meeting_form.php" id="btn_add" class="btn btn_01">회의록작성</a>
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
