<?php
$sub_menu = "960255";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'etc_exprice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '기타지출관리';
// include_once('./_top_menu_etcexprice.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['etc_exprice_table']} AS prx
                    LEFT JOIN {$g5['companyetc_table']} AS com ON prx.com_idx = com.com_idx
"; 

$where = array();
//$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " prx_status = 'ok' ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prx.com_idx' || $sfl == 'prx_idx') :
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
    $sst = "prx_idx";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = 25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS prx.*
            , com_name
            , (
                CASE WHEN prx_plan_date >= CURDATE()
                            AND DATE_SUB(prx_plan_date, INTERVAL {$g5['setting']['set_etpplan_alarmdays']} DAY) <= CURDATE()
                            AND prx_done_date = '0000-00-00'
                            AND prx_status = 'ok'
                    THEN 1
                    ELSE 0
                END
            ) AS prx_alarm_flag
            , (
                CASE WHEN prx_plan_date < CURDATE()
                            AND prx_done_date = '0000-00-00'
                            AND prx_status = 'ok'
                    THEN 1
                    ELSE 0
                END
            ) AS prx_expire_flag
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

if($super_admin) $colspan = 13;
else $colspan = 7;
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
.td_alarm .sp_done{position:absolute;top:-5px;left:2px;font-size:0.7em;}
.td_alarm .sp_alarm{position:absolute;top:-5px;left:2px;font-size:0.7em;}
.td_alarm .sp_expire{position:absolute;bottom:5px;left:2px;font-size:0.7em;}
.grp_box{display:block;position:absolute;bottom:2px;left:0px;width:100%;height:5px;background:#ccc;overflow:hidden;}
.grp_box .grp_in{display:block;position:absolute;top:0px;left:0px;height:5px;background:orange;}
.grp_box .grp_in_mi{background:red;}
.tr_last{border-bottom:2px solid #000000;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
	<option value="com.com_name"<?php echo get_selected($_GET['sfl'], "com.com_name"); ?>>업체명</option>
	<option value="prx_name"<?php echo get_selected($_GET['sfl'], "prx_name"); ?>>지출명</option>
	<option value="prx_content"<?php echo get_selected($_GET['sfl'], "prx_content"); ?>>지출내용</option>
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
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">번호</th>
        <th scope="col">지출업체명</th>
        <th scope="col">지출제목</th>
        <th scope="col">해당지출금액</th>
        <th scope="col" style="width:40px;">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php

    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        // 관리 버튼
        $s_mod = '<a href="./etc_expense_form.php?'.$qstr.'&amp;w=u&amp;prj_idx='.$row['prj_idx'].'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'&amp;group=1">수정</a>';
        $bg = 'bg'.($i%2);

        ?>
        <tr class="<?=$bg?>">
            <td class="td_chk" rowspan="<?=$p_cnt?>" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
                <input type="hidden" name="prx_idx[<?php echo $i ?>]" value="<?php echo $row['prx_idx'] ?>" id="prx_idx_<?php echo $i ?>">
                <input type="hidden" name="prx_type[<?php echo $i ?>]" value="<?php echo $row['prx_type'] ?>" id="prx_type_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prx_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td rowspan="<?=$p_cnt?>"><?=$row['prx_idx']?></td><!-- 번호 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['com_name']?></td><!-- 의뢰기업 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['prx_name']?></td><!-- 지출제목 -->
            <td rowspan="<?=$p_cnt?>" class="td_grp td_alarm" style="text-align:right;width:100px;">
                <span class="prc"><?=number_format($row['prx_price'])?></span>
                <?php
                if($row['prx_alarm_flag']){
                    $dt_plan_class = ' txt_blueblink';
                    echo '<span class="sp_alarm'.$dt_plan_class.'">예정</span>';
                }
                if($row['prx_expire_flag']){
                    $dt_expire_class = ' txt_redblink';
                    echo '<span class="sp_expire'.$dt_expire_class.'">만기</span>';
                }
                if($row['prx_done_date'] != '0000-00-00'){
                    $dt_done_class = ' txt_gray';
                    echo '<span class="sp_done'.$dt_gray_class.'">완료</span>';
                }
                ?>
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
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
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

});

function form01_submit(f)
{

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