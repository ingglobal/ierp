<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_schedule';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';

$g5['title'] = '일정미할당사원관리';
include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['project_schedule_table']} AS prs
                    LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
"; 
//echo $sql_common;
$where = array();

$sql_search = ' WHERE (1) ';
$where[] = "AND prs.prs_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만
if (!$member['mb_manager_yn']) {
    $where[] = " prj.com_idx = '".$member['mb_4']."' ";
}


if($fr_date && $to_date) {
    $where[] = " prs.prs_start_date not between '".$fr_date."' and '".$to_date."' ";
    $where[] = " prs.prs_end_date not between '".$fr_date."' and '".$to_date."' ";
    $where[] = " '".$fr_date."'  not between prs.prs_start_date and prs.prs_end_date ";
    $where[] = " '".$to_date."'  not between prs.prs_start_date and prs.prs_end_date ";
}
else if(!$fr_date && $to_date){
	$where[] = " '".$to_date."'  not between prs.prs_start_date and prs.prs_end_date ";
}
else if($fr_date && !$to_date){
	$where[] = " '".$fr_date."'  not between prs.prs_start_date and prs.prs_end_date ";
}
else{
	$where[] = " DATE(NOW()) NOT BETWEEN prs.prs_start_date AND prs.prs_end_date ";
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
		case ($sfl == 'prj_name' || $sfl == 'prj_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
//$sql_search = ' WHERE (1) ';
if ($where)
    $sql_search .= implode(' AND ', $where);
//###################### 날짜 검색조건 : 시작 ######################
/*
if ($fr_date && $to_date){
    $sql_search .= " AND ( ( prs.prs_start_date not between '".$fr_date."' and '".$to_date."' ) ";
    $sql_search .= " AND ( prs.prs_end_date not between '".$fr_date."' and '".$to_date."' ) ";
    $sql_search .= " OR prs.prs_start_date > '".$to_date."' ";
    $sql_search .= " OR prs.prs_end_date < '".$fr_date."' ) ";
}
else if($fr_date && !$to_date){
	
}
else if(!$fr_date && $to_date){
	
}
else{
	$sql_search .= " AND DATE(NOW()) NOT BETWEEN prs.prs_start_date AND prs.prs_end_date ";
}
*/
//###################### 날짜 검색조건 : 종료 ######################


if (!$sst) {
    //$sst = "prs.prj_idx";
    $sst = "prs.prs_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
            , (SELECT mb_hp FROM {$g5['member_table']} WHERE mb_id = prs.mb_id_worker ) AS prs_mb_hp
            , (SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = prs.mb_id_worker ) AS prs_mb_name
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


if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items1 = array(
    "prs_mb_name"=>array("담당자",0,0,0)
    ,"prs_type"=>array("일정타입",0,0,0)
    ,"prs_start_date"=>array("작업시작일",0,0,0)
    ,"prs_end_date"=>array("작업종료일",0,0,0)
    ,"com_name"=>array("업체명",0,0,0)
    ,"prj_name"=>array("프로젝트명",0,0,1)
    ,"prs_task"=>array("작업",0,2,0)
    //,"prs_content"=>array("내용",0,0,0)
);

?>
<style>
.local_desc ul li{margin:5px 0;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<!--form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    //$skips = array('prj_idx','prj_status','prj_set_output','prj_image','trm_idx_category','prj_idx2','prj_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item');
    //if(is_array($items)) {
    //    foreach($items as $k1 => $v1) {
    //        if(in_array($k1,$skips)) {continue;}
    //        echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
    //    }
    //}
    ?>
	<option value="prj.com_idx"<?php echo get_selected($_GET['sfl'], "prj.com_idx"); ?>>업체번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form-->
<form class="local_sch03 local_sch">
    <strong>일정범위</strong>
	<?php 
	if(!$fr_date && !$to_date){
		$fr_date = G5_TIME_YMD;
		$to_date = G5_TIME_YMD;
	}
	?>
    <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" placeholder="시작일" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" placeholder="종료일" class="frm_input" size="10" maxlength="10">
    <input type="submit" value="검색" class="btn_submit" style="height:28px;line-height:28px;">
</form>
<div class="local_desc01 local_desc">
    <p>일정범위 검색조건</p>
	<ul>
		<li>1)시작일/종료일 입력 : 검색 범위가 작업일정 범위안에 포함 되지 않으면서<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			또한 작업일정 범위가 검색일정 범위안에 포함 되지 않은 목록이 추출된다.
		</li>
		<li>2)시작일만 입력 : 검색 시작일이 작업일정 범위 안에 포함 되지 않은 목록이 추출된다.</li>
		<li>3)종료일만 입력 : 검색 종료일이 작업일정 범위 안에 포함 되지 않은 목록이 추출된다.</li>
		<li>4)기본 : 작업일정 범위 안에 오늘날짜가 포함 되지 않은 목록이 추출된다.</li>
	</ul>	
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
		<!--th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th-->
        <?php
        $skips = array();
        if(is_array($items1)) {
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                // 정렬 링크
                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
            }
        }
        ?>
		<!--th scope="col" id="mb_list_mng">관리</th-->
	</tr>
    <!-- 테이블 항목명 2번 라인 -->
	<tr>
        <?php
        $skips = array();
        if(is_array($items2)) {
            foreach($items2 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                
				// 정렬 링크
                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
            }
        }
        ?>
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row);
        
        // 수금결제 추출
        $sql = "SELECT * FROM {$g5['project_price_table']}
                WHERE prj_idx = '".$row['prj_idx']."'
                    AND prp_status NOT IN ('trash','delete')
                ";
        $row['ppr'] = sql_fetch($sql,1);

        // 관리 버튼
        $s_mod = '<a href="./project_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prs_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
        $s_view = '<a href="./'.$fname.'_view.popup.php?&'.$pre.'_idx='.$row['prs_idx'].'" class="btn_view">보기</a>';
		//$s_del = '<a href="./prj_form_update.php?'.$qstr.'&amp;w=d&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        $bg = 'bg'.($i%2);

        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        ?>
		<!--td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td-->
        <?php
        $skips = array();
        if(is_array($items1)) {
        //    print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // echo $k1.'<br>';
                // print_r2($v1);
                // 변수 재설정
                if($k1=='prj_reg_dt') {
                    $row[$k1] = substr($row[$k1],0,10);
                }
                else if($k1=='prj_parts') {
                    $row[$k1] = '<a href="./prj_parts_list.php?prj_idx='.$row['prj_idx'].'" class="btn_parts">'.$row['parts']['total_count'].'</a>';
                }
                else if($k1=='prj_maintain') {
                    $row[$k1] = '<a href="./maintain_list.php?prj_idx='.$row['prj_idx'].'" class="btn_maintain">'.$row['maintain']['total_count'].'</a>';
                }
                else if($k1=='trm_idx_category') {
                    $row[$k1] = ($row[$k1]) ? $g5['category_name'][$row[$k1]] : '-';
                }
				else if($k1=='prs_type'){
					$row[$k1] = $g5['set_prs_type_value'][$row[$k1]];
				}

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$row[$k1].'</td>';
            }
        }
        //echo '<td class="td_mngsmall">'.$s_mod.'</td>'.PHP_EOL;
		//echo $td_items[$i];
        echo '</tr>'.PHP_EOL;



    }
	if ($i == 0)
		echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <!--input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn"-->
        <a href="./project_form.php" id="btn_add" class="btn btn_01">일정추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>

<script>
$(function(e) {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 마우스 hover 설정
    //$(".tbl_head01 tbody tr").on({
    //    mouseenter: function () {
    //        //stuff to do on mouse enter
    //        //console.log($(this).attr('od_id')+' mouseenter');
    //        //$(this).find('td').css('background','red');
    //        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#e6e6e6 ');
    //        
    //    },
    //    mouseleave: function () {
    //        //stuff to do on mouse leave
    //        //console.log($(this).attr('od_id')+' mouseleave');
    //        //$(this).find('td').css('background','unset');
    //        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    //    }    
    //});
	
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
	$(document).on('click','.btn_prj_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_prj_board = window.open(this_href,'win_prj_board','left=100,top=100,width=770,height=650');
        win_prj_board.focus();
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