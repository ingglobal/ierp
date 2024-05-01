<?php
$sub_menu = "960257";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '카드관리';
include_once('./_top_menu_carduser.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
//$g5['set_card']
//$g5['set_card_status']

$sql_common = " FROM {$g5['card_table']}
";

$where = array();
$where[] = " crd_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ($sfl == 'crd_name') :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

if($sch_crd_code){
    $where[] = " crd_code = '{$sch_crd_code}' ";
}
if($sch_crd_expire_year){
    $where[] = " crd_expire_year = '{$sch_crd_expire_year}' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "crd_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(crd_idx) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$total_count = $row['cnt'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$sql = " SELECT *
		{$sql_common}
		{$sql_search} {$sql_com_type} {$sql_trm_idx_department}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
//echo $sql;
$result = sql_query($sql,1);


// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['card_table']} WHERE crd_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}

$colspan = 10;

// 검색어 확장
$qstr .= $qstr.'&sch_crd_code='.$sch_crd_code.'&sch_crd_expire_year='.$sch_crd_expire_year;
?>
<style>
#fcard_box{padding-bottom:10px;}
.td_chk{}
.td_crd_idx{}
.td_crd_code{width:60px;}
.td_crd_name{width:120px;}
.td_crd_no{width:150px;}
.td_crd_expire_month{width:50px;}
.td_crd_expire_year{width:50px;}
.td_crd_memo{}
.td_crd_status{width:80px;}
.td_crd_reg_dt{width:170px;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
</div>
<?php 
$xp_y = ($g5['setting']['set_cardexpire_startyear'])?$g5['setting']['set_cardexpire_startyear']:2020;
$xp_c = ($g5['setting']['set_cardexpire_yearcnt'])?$g5['setting']['set_cardexpire_yearcnt']:20;
?>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<select name="sch_crd_code" id="sch_crd_code">
    <option value="">::카드사선택::</option>
    <?=$g5['set_card_value_options']?>
</select>
<script>
$('#sch_crd_code').val('<?=$sch_crd_code?>');
</script>
<select name="sch_crd_expire_year" id="sch_crd_expire_year">
    <option value="">::만기년::</option>
    <?php for($y=0;$y<$xp_c;$y++){ ?>
    <option value="<?=substr(($xp_y+$y),-2)?>"><?=substr(($xp_y+$y),-2)?></option>
    <?php } ?>
</select>
<script>
$('#sch_crd_expire_year').val('<?=$sch_crd_expire_year?>');
</script>
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="crd_no"<?php echo get_selected($_GET['sfl'], "crd_no"); ?>>카드번호</option>
    <option value="crd_memo"<?php echo get_selected($_GET['sfl'], "crd_memo"); ?>>메모</option>
</select>
<script>
$('#sfl').val('<?=(($sfl)?$sfl:'crd_no')?>');
</script>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc">
    <p>카드사별 카드를 관리하는 페이지 입니다.</p>
</div>

<div id="fcard_box">
<form name="form_cardregist" id="form_cardregist" action="./card_regist_update.php" onsubmit="return form_cardregist_submit(this);" method="post">
<input type="hidden" name="crd_one" value="1">
<select name="crd_code">
    <option value="">::카드사선택::</option>
    <?=$g5['set_card_value_options']?>
</select>
<label for="crd_no" class="fc_label">
    <input type="text" name="crd_no" id="crd_no" placeholder="카드번호" class="frm_input card_no" value="" oninput="card_no_str(this);">
</label>

<select name="crd_expire_month" id="crd_expire_month">
    <option value="">::만기월::</option>
    <?php for($m=1;$m<=12;$m++){ ?>
    <option value="<?=sprintf("%02d",$m)?>"><?=sprintf("%02d",$m)?></option>
    <?php } ?>
</select>&nbsp;&nbsp;<span>/</span>&nbsp;
<select name="crd_expire_year" id="crd_expire_year">
    <option value="">::만기년::</option>
    <?php for($y=0;$y<$xp_c;$y++){ ?>
    <option value="<?=substr(($xp_y+$y),-2)?>"><?=substr(($xp_y+$y),-2)?></option>
    <?php } ?>
</select>
<label for="crd_memo" class="fc_label">
    <input type="text" name="crd_memo" id="crd_memo" placeholder="메모" class="frm_input" value="" style="width:200px;">
</label>
</select>
<!-- <select name="crd_status" id="crd_status">
    <?=$g5['set_card_status_options']?>
</select> -->
<input type="submit" class="btn btn_01 btn_register" value="등록">
</form>
</div><!--//#fcard_box-->

<form name="form01" id="form01" action="./card_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="sch_crd_code" value="<?php echo $sch_crd_code ?>">
<input type="hidden" name="sch_crd_expire_year" value="<?php echo $sch_crd_expire_year ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col" rowspan="2">
			<label for="chkall" class="sound_only">카드 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
		<th scope="col">번호</th>
		<th scope="col">카드사코드</th>
		<th scope="col">카드사명</th>
		<th scope="col">카드번호</th>
		<th scope="col">만기월</th>
		<th scope="col">만기년</th>
		<th scope="col">메모</th>
		<th scope="col">상태</th>
		<th scope="col">등록일</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
		// 삭제인 경우 그레이 표현
		if($row['crd_status'] == 'expire')
			$row['crd_status_expire_class']	= " tr_expire";

        $row['crd_no'] = preg_replace('/^(\d{4})(\d{4})(\d{4})(\d{4})/','$1-$2-$3-$4',$row['crd_no']);

        $row['crd_no'] = substr($row['crd_no'],0,19);
        
        $bg = 'bg'.($i%2);
    ?>

	<tr class="<?php echo $bg; ?> <?=$row['crd_status_expire_class']?>" tr_id="<?php echo $row['crd_idx'] ?>">
		<td class="td_chk">
			<input type="hidden" name="crd_idx[<?php echo $i ?>]" value="<?php echo $row['crd_idx'] ?>" id="crd_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['crd_code']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <td class="td_crd_idx"><!-- 번호 -->
            <?=$row['crd_idx']?>
        </td>
        <td class="td_crd_code"><!-- 카드코드 -->
            <?=$row['crd_code']?>
        </td>
		<td class="td_crd_name td_left"><!-- 업체명 -->
            <select name="crd_code[<?php echo $i ?>]" id="crd_code_<?=$i?>">
               <?=$g5['set_card_value_options']?> 
            </select>
            <script>
            $("#crd_code_<?=$i?>").val('<?=$row['crd_code']?>');
            </script>
		</td>
		<td class="td_crd_no"><!-- 카드번호 -->
            <input type="text" name="crd_no[<?php echo $i ?>]" value="<?=$row['crd_no']?>" id="crd_no_<?=$i?>" class="frm_input card_no" oninput="card_no_str(this);">
		</td>
		<td class="td_crd_expire_month"><!-- 만기월 -->
            <select name="crd_expire_month[<?php echo $i ?>]" id="crd_expire_month_<?=$i?>">
                <?php for($m=1;$m<=12;$m++){ ?>
                <option value="<?=sprintf("%02d",$m)?>"><?=sprintf("%02d",$m)?></option>
                <?php } ?>
            </select>
            <script>
            $("#crd_expire_month_<?=$i?>").val('<?=$row['crd_expire_month']?>');
            </script>
		</td>
        <td class="td_crd_expire_year"><!-- 만기년 -->
            <select name="crd_expire_year[<?php echo $i ?>]" id="crd_expire_year_<?=$i?>">
                <?php for($y=0;$y<$xp_c;$y++){ ?>
                <option value="<?=substr(($xp_y+$y),-2)?>"><?=substr(($xp_y+$y),-2)?></option>
                <?php } ?>
            </select>
            <script>
            $("#crd_expire_year_<?=$i?>").val('<?=$row['crd_expire_year']?>');
            </script>
        </td>
		<td class="td_com_type"><!-- 메모 -->
            <input type="text" name="crd_memo[<?php echo $i ?>]" value="<?=$row['crd_memo']?>" class="frm_input" style="width:100%;">
		</td>
        <td headers="list_crd_status" class="td_crd_status"><!-- 상태 -->
            <select name="crd_status[<?php echo $i ?>]" id="crd_status_<?php echo $i ?>">
                <?=$g5['set_card_status_value_options']?>
            </select>
            <script>
            $('#crd_status_<?php echo $i ?>').val('<?=$row['crd_status']?>');
            </script>
		</td>
		<td class="td_crd_reg_dt td_center font_size_14"><!-- 등록일 -->
			<?=$row['crd_reg_dt']?>
		</td>
	</tr>
	<?php
	}
	if ($i == 0)
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
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

<script>
$(function(e) {
    
    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            //stuff to do on mouse enter
            //console.log($(this).attr('od_id')+' mouseenter');
            //$(this).find('td').css('background','red');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#e6e6e6 ');

        },
        mouseleave: function () {
            //stuff to do on mouse leave
            //console.log($(this).attr('od_id')+' mouseleave');
            //$(this).find('td').css('background','unset');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }
    });

    // 담당자 클릭
    $(".btn_manager").click(function(e) {
        var href = "./company_member_list.php?com_idx="+$(this).attr('com_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=700,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_company_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_company_board = window.open(this_href,'win_company_board','left=100,top=100,width=770,height=650');
        win_company_board.focus();
	});

    //숫자만 입력
    // $('.card_no').on('keyup',function(){
    //     var num = $(this).val().replace(/[^0-9]/g,"").replace(/^(\d{0,4})(\d{0,4})(\d{0,4})$/g,"$1-$2-$3").replace(/(\-{1,2})/g,"");
    //     $(this).val(num);
    // });
});

function card_no_str(inp){
    if(inp.value.length <= 19){
        inp.value = inp.value.replace(/[^0-9]/g, '').replace(/^(\d{4})(\d{4})(\d{4})(\d{4})$/g, "$1-$2-$3-$4").replace(/(\-{1,2})$/g, "");
    }else{
        inp.value = inp.value.substr(0,19);
    }
}


function form_cardregist_submit(f){
    if(!f.crd_code.value){
        alert('카드사를 선택해 주세요.');
        f.crd_code.focus();
        return false;
    }
    if(!f.crd_no.value){
        alert('카드사번호를 입력해 주세요.');
        f.crd_no.focus();
        return false;
    }
    if(!f.crd_expire_month.value){
        alert('만기월을 선택해 주세요.');
        f.crd_expire_month.focus();
        return false;
    }
    if(!f.crd_expire_year.value){
        alert('만기년을 선택해 주세요.');
        f.crd_expire_year.focus();
        return false;
    }

    return true;
}

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
        // console.log($('input[name="chk[]"]').length);
        $('input[name="chk[]"]').each(function(){
            if($(this).is(':checked')){
                var tr = $(this).parent().parent();
                var crd_name = tr.find('.td_crd_name').find('select option:selected').val();
                var crd_no = tr.find('.td_crd_no').find('input').val();
                var crd_expire_month = tr.find('.td_crd_expire_month').find('select option:selected').val();
                var crd_expire_year = tr.find('.td_crd_expire_year').find('select option:selected').val();
                var crd_status = tr.find('.td_crd_status').find('select option:selected').val();
                
                if(!crd_name) {
                    alert('카드사명을 선택해 주세요');
                    return false;
                }
                if(!crd_no) {
                    alert('카드사 번호를 입력해 주세요');
                    return false;
                }
                if(!crd_expire_month){
                    alert('카드 만기월을 선택해 주세요');
                    return false;
                }
                if(!crd_expire_year) {
                    alert('카드 만기년을 선택해 주세요');
                    return false;
                }
                if(!crd_status) {
                    alert('카드상태를 선택해 주세요');
                    return false;
                }
            }
        });

		$('input[name="w"]').val('u');
        // return false;
	}

	else if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		}
	}
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
