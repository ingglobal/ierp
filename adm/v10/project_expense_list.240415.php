<?php
$sub_menu = "960245";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_exprice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '지출관리';
include_once('./_top_menu_exprice.php');
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
    ;//$where[] = " prj.com_idx = '".$member['mb_4']."' ";
}

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'prj.prj_name') :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
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
            , (SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = prj.prj_idx AND prx_status = 'ok' AND prx_done_date != '0000-00-00' ) AS prx_sum_exprice
            , (SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = prj.prj_idx AND prx_status = 'ok' AND prx_type = 'machine' AND prx_done_date != '0000-00-00' ) AS prx_mcn_exprice
            , (SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = prj.prj_idx AND prx_status = 'ok' AND prx_type = 'electricity' AND prx_done_date != '0000-00-00' ) AS prx_elt_exprice
            , (SELECT SUM(prx_price) FROM {$g5['project_exprice_table']} WHERE prj_idx = prj.prj_idx AND prx_status = 'ok' AND prx_type = 'etc' AND prx_done_date != '0000-00-00' ) AS prx_etc_exprice
            , (SELECT SUM(prn_price) FROM {$g5['project_inprice_table']} WHERE prj_idx = prj.prj_idx AND prn_status = 'ok' AND prn_type = 'etc' AND prn_done_date != '0000-00-00' ) AS prn_tot_inprice
            , (
                SELECT COUNT(*)
                    FROM {$g5['project_exprice_table']}
                WHERE prj_idx = prj.prj_idx
                    AND prx_plan_date >= CURDATE()
                    AND DATE_SUB(prx_plan_date, INTERVAL {$g5['setting']['set_expplan_alarmdays']} DAY) <= CURDATE()
                    AND prx_done_date = '0000-00-00'
                    AND prx_type = 'machine'
                    AND prx_status = 'ok'
            ) AS prx_alarm_machine_cnt
            , (
                SELECT COUNT(*)
                    FROM {$g5['project_exprice_table']}
                WHERE prj_idx = prj.prj_idx
                    AND prx_plan_date >= CURDATE()
                    AND DATE_SUB(prx_plan_date, INTERVAL {$g5['setting']['set_expplan_alarmdays']} DAY) <= CURDATE()
                    AND prx_done_date = '0000-00-00'
                    AND prx_type = 'electricity'
                    AND prx_status = 'ok'
            ) AS prx_alarm_electricity_cnt
            , (
                SELECT COUNT(*)
                    FROM {$g5['project_exprice_table']}
                WHERE prj_idx = prj.prj_idx
                    AND prx_plan_date >= CURDATE()
                    AND DATE_SUB(prx_plan_date, INTERVAL {$g5['setting']['set_expplan_alarmdays']} DAY) <= CURDATE()
                    AND prx_done_date = '0000-00-00'
                    AND prx_type = 'etc'
                    AND prx_status = 'ok'
            ) AS prx_alarm_etc_cnt
            , (
                SELECT COUNT(*)
                    FROM {$g5['project_exprice_table']}
                WHERE prj_idx = prj.prj_idx
                    AND prx_plan_date < CURDATE()
                    AND prx_done_date = '0000-00-00'
                    AND prx_type = 'machine'
                    AND prx_status = 'ok'
            ) AS prx_expire_machine_cnt
            , (
                SELECT COUNT(*)
                    FROM {$g5['project_exprice_table']}
                WHERE prj_idx = prj.prj_idx
                    AND prx_plan_date < CURDATE()
                    AND prx_done_date = '0000-00-00'
                    AND prx_type = 'electricity'
                    AND prx_status = 'ok'
            ) AS prx_expire_electricity_cnt
            , (
                SELECT COUNT(*)
                    FROM {$g5['project_exprice_table']}
                WHERE prj_idx = prj.prj_idx
                    AND prx_plan_date < CURDATE()
                    AND prx_done_date = '0000-00-00'
                    AND prx_type = 'etc'
                    AND prx_status = 'ok'
            ) AS prx_expire_etc_cnt
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
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

if($super_admin) $colspan = 10;
else $colspan = 5;
//예정일알람일수 $g5['setting']['set_expplan_alarmdays']
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
.tbl_head01 .table td{padding:8px 5px;}
.td_grp{position:relative;}
.td_grp .prc{position:relative;z-index:3;}
.td_grp .per{position:absolute;top:-4px;right:0px;font-size:0.7em;}
.td_alarm{position:relative;}
.td_alarm .sp_alarm{position:absolute;top:-5px;left:2px;font-size:0.7em;}
.td_alarm .sp_expire{position:absolute;bottom:5px;left:2px;font-size:0.7em;}
.grp_box{display:block;position:absolute;bottom:2px;left:0px;width:100%;height:5px;background:#ccc;overflow:hidden;}
.grp_box .grp_in{display:block;position:absolute;top:0px;left:0px;height:5px;background:orange;}
.grp_box .grp_in_su{background:blue;}
.grp_box .grp_in_mi{background:red;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="com.com_name"<?php echo get_selected($_GET['sfl'], "com.com_name"); ?>>업체명</option>
	<option value="prj.prj_name"<?php echo get_selected($_GET['sfl'], "prj.prj_name"); ?>>프로젝트명</option>
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
        <?php if($super_admin){ ?>
        <th scope="col">수주금액</th>
        <th scope="col">수금액<br>(수주금액기준%)</th>
        <th scope="col">미수금<br>(수주금액기준%)</th>
        <th scope="col">지출상태<br>(수금합계-지출합계)</th>
        <?php } ?>
        <th scope="col">총지출액<?php if($super_admin){ ?><br>(총수입금기준%)<?php } ?></th>
        <?php if($super_admin){ ?><th scope="col">영업이익<br>(총수입-총지출액)<?php if($super_admin){ ?><br>(총수입금기준%)<?php } ?></th><?php } ?>
        <th scope="col" style="width:40px;">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;

    $misu1_price = 0;
    $misu2_price = 0;

    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'&amp;group=1">수정</a>';

        //수금완료 합계를 구한다
        $ssql = " SELECT SUM(prp_price) AS sum_price
                    FROM {$g5['project_price_table']}
                    WHERE prj_idx = '".$row['prj_idx']."'
                        AND prp_type NOT IN ('submit','nego','order','')
                        AND prp_pay_date != '0000-00-00'
                        AND prp_status = 'ok'
        ";
        //echo $ssql;
        $sugeum = sql_fetch($ssql);
        $row['tot_income'] = $row['prp_order_price'] + $row['prn_tot_inprice'];
        $row['prj_mi_price'] = $row['prp_order_price'] - $sugeum['sum_price'];
        $row['prj_su_price'] = $sugeum['sum_price'];
        $row['prj_stat_price'] = $sugeum['sum_price'] - $row['prx_sum_exprice'];//지출상태(수금-지출)
        $row['prp_dif_exprice'] = $row['tot_income'] - $row['prx_sum_exprice'];
        $bg = 'bg'.($i%2);
        $sug_per = ($row['prp_order_price'])?round($row['prj_su_price']/$row['prp_order_price']*100,1):0;
        $mis_per = ($row['prp_order_price'])?round($row['prj_mi_price']/$row['prp_order_price']*100,1):0;
        $exp_per = ($row['prp_order_price'])?round($row['prx_sum_exprice']/$row['tot_income']*100,1):0;
        $dif_per = ($row['prp_order_price'])?round($row['prp_dif_exprice']/$row['tot_income']*100,1):0;
        $mcn_per = ($row['prx_sum_exprice'])?round($row['prx_mcn_exprice']/$row['prx_sum_exprice']*100,1):0;
        $elt_per = ($row['prx_sum_exprice'])?round($row['prx_elt_exprice']/$row['prx_sum_exprice']*100,1):0;
        $etc_per = ($row['prx_sum_exprice'])?round($row['prx_etc_exprice']/$row['prx_sum_exprice']*100,1):0;
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
            <?php if($super_admin){ ?>
            <td rowspan="<?=$p_cnt?>" style="text-align:right;width:110px;"><?=number_format($row['prp_order_price'])?></td><!--수주금액-->
            <td rowspan="<?=$p_cnt?>" class="td_grp" style="text-align:right;width:110px;">
                <span class="prc"><?=number_format($row['prj_su_price'])?></span>
                <div class="grp_box"><div class="grp_in grp_in_su" style="width:<?=$sug_per?>%"></div></div>
                <span class="per">(<?=$sug_per?>%)</span>
            </td><!--수금금액-->
            <td rowspan="<?=$p_cnt?>" class="td_grp" style="text-align:right;width:110px;">
                <span class="prc"><?=number_format($row['prj_mi_price'])?></span>
                <div class="grp_box"><div class="grp_in grp_in_mi" style="width:<?=$mis_per?>%"></div></div>
                <span class="per">(<?=$mis_per?>%)</span>
            </td><!--미수금-->
            <td rowspan="<?=$p_cnt?>" style="text-align:right;width:110px;color:<?=(($row['prj_stat_price']<0)?'red':'')?>;">
                <?=number_format($row['prj_stat_price'])?>
            </td><!--지출상태-->
            <?php } ?>
            <td rowspan="<?=$p_cnt?>" class="td_grp" style="text-align:right;width:110px;">
                <span class="prc"><?=number_format($row['prx_sum_exprice'])?></span>
                <?php if($super_admin){ ?><div class="grp_box"><div class="grp_in" style="width:<?=$exp_per?>%"></div></div><?php } ?>
                <?php if($super_admin){ ?><span class="per">(<?=$exp_per?>%)</span><?php } ?>
            </td>
            <?php if($super_admin){ ?>
            <td rowspan="<?=$p_cnt?>" class="td_grp" style="text-align:right;width:100px;">
                <span class="prc"><?=number_format($row['prp_dif_exprice'])?></span>
                <?php if($super_admin){ ?><div class="grp_box"><div class="grp_in" style="width:<?=$dif_per?>%"></div></div><?php } ?>
                <?php if($super_admin){ ?><span class="per">(<?=$dif_per?>%)</span><?php } ?>
            </td>
            <?php } ?>
            <td class="td_mngsmall">
                <?=$s_mod?>
            </td>
        </tr>
    <?php
    }
	if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if(false){ //($member['mb_manager_yn']) { ?>
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
