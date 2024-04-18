<?php
$sub_menu = "960244";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

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
// 변수 설정, 필드 구조 및 prefix 추출
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명

$g5['title'] = '매입관리';
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['project_purchase_table']} AS ppc
                    LEFT JOIN {$g5['project_table']} AS prj ON ppc.prj_idx = prj.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = ppc.com_idx
";

$where = array();
$where[] = " prj_status IN ('ok','complete') ";   // 디폴트 검색조건
// $where[] = " prj_status IN ('empty') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'ppc.com_idx' || $sfl == 'ppc.prj_idx' || $sfl == 'ppc_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

if($ser_com_name){
    $where[] = " com.com_name LIKE '%{$ser_com_name}%' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "ppc_idx";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = 25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$limit_common = " LIMIT {$from_record}, {$rows} ";

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , ppc.com_idx AS com_idx
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = ppc.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price
            , ( SELECT SUM(ppd_price) FROM {$g5['project_purchase_divide_table']} WHERE ppc_idx = ppc.ppc_idx AND ppd_status = 'ok' ) AS mp_price
            , ( SELECT SUM(ppd_price) FROM {$g5['project_purchase_divide_table']} WHERE ppc_idx = ppc.ppc_idx AND ppd_status = 'complete' ) AS cp_price
            , ( SELECT SUM(ppd_price) FROM {$g5['project_purchase_divide_table']} WHERE ppc_idx = ppc.ppc_idx AND ppd_status IN ('ok','complete') ) AS tp_price
        {$sql_common}
		{$sql_search}
        {$sql_order}
		{$limit_common} 
";
// echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$cur_url = ($_SERVER['SERVER_PORT'] != '80' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$cur_url = (preg_match("/\?/",$cur_url)) ? $cur_url.'&' : $cur_url.'?';
$cur_url = preg_replace('/frm_date=([0-9]{4})-([0-9]{2})-([0-9]{2})/i','',$cur_url);
$cur_url = str_replace('?&','?',$cur_url);
$cur_url = str_replace('&&','&',$cur_url);

?>
<style>
.td_per{position:relative;}
.td_per .p_per{position:absolute;width:100%;height:16px;padding:4px 2px;left:0;bottom:0;background:none;}
.td_per .p_per .t_per{display:block;width:100%;height:8px;background:gray;border-radius:4px;overflow:hidden;}
.td_per .p_per .t_per .s_per{display:block;width:0%;height:100%;background:red;border-radius:4px;}
.td_last{padding:20px 0 !important;font-size:1.2em;}
.td_total_price{font-size:1.2em;color:red;}
.td_total_per{font-size:1.1em;color:red;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get" autocomplete="off">
    <label for="ser_com_name" class="sound_only">검색업체</label>
    <input type="text" name="ser_com_name" placeholder="매입업체명" value="<?php echo $ser_com_name ?>" id="ser_com_name" class="frm_input">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
        <option value="prj.prj_idx"<?php echo get_selected($_GET['sfl'], "prj.prj_idx"); ?>>프로젝트번호</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" class="btn_submit" value="검색">
</form>
<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
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
        <th scope="col">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">번호</th>
        <th scope="col">매입업체</th>
        <th scope="col" style="width:150px;">공사프로젝트</th>
        <th scope="col">발주금액</th>
        <th scope="col">미지급금</th>
        <th scope="col">미지급상태</th>
        <th scope="col">지급금액</th>
        <th scope="col">금액타입</th>
        <th scope="col">지출예정일</th>
        <th scope="col">지출일</th>
        <th scope="col">지급방법</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $ppc_total_price = 0;
    $mi_total_price = 0;
    for ($i=0; $row=sql_fetch_array($result); $i++){
        $s_mod = '<a href="./prj_purchase_form.php?'.$qstr.'&amp;w=u&amp;ppc_idx='.$row['ppc_idx'].'&amp;order=1">수정</a>';
        // $s_add = '<a href="./prj_purchase_form.php?'.$qstr.'&amp;g=1&amp;ppc_idx='.$row['ppc_idx'].'">추가</a>';
        $dsql = " SELECT * FROM {$g5['project_purchase_divide_table']}
                    WHERE ppc_idx = '{$row['ppc_idx']}'
                        AND ppd_status IN ('ok','complete')
                    ORDER BY ppd_type, ppd_idx
        ";
        $d_res = sql_query($dsql,1);
        $d_cnt = $d_res->num_rows;

        $ppc_total_price += $row['ppc_price'];
        $mi_total_price += $row['mp_price'];
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>">
        <td class="td_chk" rowspan="<?=$d_cnt?>">
            <input type="hidden" name="ppc_idx[<?php echo $i ?>]" value="<?php echo $row['ppc_idx'] ?>" id="ppc_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['ppc_subject']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td rowspan="<?=$d_cnt?>"><?=$row['ppc_idx']?></td><!-- 번호 -->
        <td rowspan="<?=$d_cnt?>" class="td_left"><?=$row['com_name']?></td><!-- 매입업체 -->
        <td rowspan="<?=$d_cnt?>" class="td_left">[<?=$row['prj_idx']?>]<br><?=$row['prj_name']?></td><!-- 공사프로젝트 -->
        <td rowspan="<?=$d_cnt?>" class="td_right"><?=number_format($row['ppc_price'])?></td><!-- 발주금액 -->
        <td rowspan="<?=$d_cnt?>" class="td_right"><?=number_format($row['mp_price'])?></td><!-- 미지급금 -->
        <td rowspan="<?=$d_cnt?>" class="td_per">
            <?php 
                $mp_per = number_format(($row['mp_price']/$row['ppc_price'])*100,1,'.','');
                echo $mp_per;
            ?> % 
            <p class="p_per"><strong class="t_per"><span class="s_per" style="width:<?=$mp_per?>%;"></span></strong></p>
        </td><!-- 미지급상태 -->
        <?php for($j=0;$drow=sql_fetch_array($d_res);$j++){
        // d_res가 2개이상일때
        if($j >= 1) echo '<tr class="'.$bg.'">'.PHP_EOL;
        ?>
        <td class="td_right"><?=number_format($drow['ppd_price'])?></td>
        <td class=""><?=$g5['set_ppd_type_value'][$drow['ppd_type']]?></td>
        <td class=""><?=$drow['ppd_plan_date']?></td>
        <td class=""><?=$drow['ppd_done_date']?></td>
        <td class=""><?=$g5['set_ppd_bank_value'][$drow['ppd_bank']]?></td>
        <td class=""><?=$s_mod?></td>
    </tr>
    <?php } ?>
    <?php
    }
    if($i == 0){
        echo '<tr><td colspan="13" class="empty_table">데이터가 없습니다.</td></tr>'.PHP_EOL;
    }
    else{
    ?>
    <tr>
        <td class="td_last" colspan="5">미지급금 총합계</td>
        <td class="td_last td_right td_total_price"><?=number_format($mi_total_price)?></td>
        <td class="td_last td_per td_total_per">
            <?php 
                $mpt_per = number_format(($mi_total_price/$ppc_total_price)*100,1,'.','');
                echo $mpt_per;
            ?> % 
            <p class="p_per"><strong class="t_per" style="background:#cccccc;"><span class="s_per" style="width:<?=$mpt_per?>%;background:darkred;"></span></strong></p>
        </td>
        <td class="td_last" colspan="6"></td>
    </tr>
    <?php
    }
    ?>
    </tbody>
    </table>
</div><!--//.tbl_head01 .tbl_wrap-->
</form>

<script>

</script>
<?php
include_once ('./_tail.php');
?>