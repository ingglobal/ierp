<?php
$sub_menu = "960250";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_exprice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = 'PR지출관리';
//include_once('./_top_menu_company.php');
//include_once('./_top_menu_price.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
"; 

$where = array();
//$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " prj_status = 'ok' ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만
if (!$member['mb_manager_yn']) {
    $where[] = " prj.com_idx = '".$member['mb_4']."' ";
}

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
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
    $sst = "prj_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = 25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price
            , (SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = prj.prj_idx AND prx_type = 'pr' AND prx_status = 'ok' ) AS prx_sum_exprice
            , (SELECT mb_hp FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp
            , (SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_account ) AS prj_mb_name
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
//echo $sql;
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
.malp{position:absolute;right:0;}
.malp.jisi{top:-3px;}
.malp.misu{bottom:-3px;}
.malp .pungsun_input{position:absolute;top:0;right:0;z-index:-1;opacity:0;}
.malp .pungsun{position:absolute;top:0;right:-125px;width:120px;height:auto;background:#fff;border:1px solid #999;padding:3px;white-space:break-all;display:none;border-radius:5px;box-shadow:3px 3px 5px #ddd;text-align:left;line-height:1.2em;}
.malp:hover .pungsun{display:block;}
.malp.jisi .pungsun{background:#d8d8f5;}
.malp.misu .pungsun{background:#eceaa2;}

.per_bar{position:relative;height:10px;width:100%;min-width:60px;border-radius:5px;background:gray;overflow:hidden;}
.per_bar .bar_in{height:100%;background:#37c537;border-radius:5px;}

.file_box:after{display:block;visibility:hidden;clear:both;content:'';}
.file_in{float:left;width:50%;position:relative;}
.file_in:first-child::after{content:'/';position:absolute;top:50%;right:-3px;transform:translateY(-50%);}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="com.com_name"<?php echo get_selected($_GET['sfl'], "com.com_name"); ?>>업체명</option>
	<option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
	<option value="prj_idx"<?php echo get_selected($_GET['sfl'], "prj_idx"); ?>>프로젝트번호</option>
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

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <!-- 테이블 항목명 1번 라인 -->
	<tr>
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">번호</th>
        <th scope="col">의뢰기업</th>
        <th scope="col">공사프로젝트</th>
        <th scope="col">수주금액</th>
        <th scope="col">발주금액</th>
        <th scope="col">수주-발주차액</th>
        <th scope="col" style="width:40px;">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    /*
    [prj_idx] => 9
    [com_idx] => 1
    [mb_id_company] => test01
    [mb_id_saler] => test02
    [mb_id_account] => 
    [prj_doc_no] => ING-138169-9a
    [prj_name] => 4축 트랜스퍼
    [prj_end_company] => 이앤에프㈜
    [prj_content] => 
    [prj_belongto] => first
    [prj_receivable] => 0
    [prj_percent] => 10
    [prj_keys] => 
    [prj_status] => ok
    [prj_ask_date] => 2020-09-06
    [prj_submit_date] => 2020-09-10
    [prj_reg_dt] => 2020-09-09 11:51:28
    [prj_update_dt] => 2020-09-11 22:36:10
    [prp_order_price] => 78000000
    [com_name] => 아진산업
    [com_name_eng] => AJIN INDUSTRIAL Co.,LTD.
    [com_names] => , 아진산업(20-08-02~)
    [com_homepage] => www.wamc.co.kr
    [com_tel] => 053-856-9100
    [com_fax] => 053-856-9111
    [com_email] => master@wamc.co.kr
    [com_type] => carparts
    [com_class] => 
    [com_president] => 서중호
    [com_biz_no] => 000-00-00000
    [com_biz_type1] => 설비
    [com_biz_type2] => 자동차
    [com_zip1] => 384
    [com_zip2] => 62
    [com_addr1] => 경북 경산시 진량읍 공단8로26길 40
    [com_addr2] => 
    [com_addr3] =>  (신제리)
    [com_addr_jibeon] => R
    [com_b_zip1] => 
    [com_b_zip2] => 
    [com_b_addr1] => 
    [com_b_addr2] => 
    [com_b_addr3] => 
    [com_b_addr_jibeon] => 
    [com_latitude] => 
    [com_longitude] => 
    [com_memo] => 
    [com_keys] => 
    [com_status] => ok
    [com_reg_dt] => 2020-08-02 16:08:13
    [com_update_dt] => 2020-08-05 10:47:06
    */
    $misu1_price = 0;
    $misu2_price = 0;
    
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'&amp;group=1">수정</a>';
        $row['prp_dif_exprice'] = $row['prp_order_price'] - $row['prx_sum_exprice'];
        $bg = 'bg'.($i%2);
        ?>
        <tr class="<?=$bg?>">
            <td class="td_chk" rowspan="<?=$p_cnt?>" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
                <input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td rowspan="<?=$p_cnt?>"><?=$row['prj_idx']?></td><!-- 번호 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['com_name']?></td><!-- 의뢰기업 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['prj_name']?></td><!-- 공사프로젝트 -->
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prp_order_price'])?></td>
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prx_sum_exprice'])?></td>
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prp_dif_exprice'])?></td>
            <td class="td_mngsmall">
                <?=$s_mod?>
            </td>
        </tr>
    <?php
    }
	if ($i == 0)
        echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <a href="./project_pr_exprice_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <?php } ?>
    <?php if(false) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>

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

    // 장비보기 클릭
	$(document).on('click','.btn_view, .btn_image',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winMMSView = window.open(href, "winMMSView", "left=100,top=100,width=520,height=600,scrollbars=1");
        winMMSView.focus();
        return false;
    });

    // 부속품 클릭
	$(document).on('click','.btn_parts',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winParts = window.open(href, "winParts", "left=100,top=100,width=520,height=600,scrollbars=1");
        winParts.focus();
        return false;
    });

    // 기종 클릭
	$(document).on('click','.btn_item',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winItem = window.open(href, "winItem", "left=100,top=100,width=520,height=600,scrollbars=1");
        winItem.focus();
        return false;
    });

    // 정비 클릭
	$(document).on('click','.btn_maintain',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winMaintain = window.open(href, "winMaintain", "left=100,top=100,width=520,height=600,scrollbars=1");
        winMaintain.focus();
        return false;
    });

    // 점검기준 클릭
	$(document).on('click','.btn_checks',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winChecks = window.open(href, "winChecks", "left=100,top=100,width=520,height=600,scrollbars=1");
        winChecks.focus();
        return false;
    });

    // 담당자 클릭
    $(".btn_manager").click(function(e) {
        var href = "./prj_member_list.php?prj_idx="+$(this).attr('prj_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
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

    //말풍선 클립보드 복사
    $('.malp.jisi > i,.malp.misu > i').on('click',function(){
        var ctext = $(this).siblings('.pungsun_input');
        ctext.select();
        document.execCommand('Copy');
        alert('클립보드 복사완료');
    });
});

function form01_submit(f)
{
	if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/code/form.php');
        return false;
	}

    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}
	if(document.pressed == "선택삭제") {
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