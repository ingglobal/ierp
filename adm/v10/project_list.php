<?php
$sub_menu = "960215";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '프로젝트관리';
//include_once('./_top_menu_project.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['project_table']} AS prj
									LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
"; 
//echo $sql_common;
$where = array();
//request=견적요청,inprocess=견적중, pending=보류, ng=수주취소, ok=수주완료,etc=기타, trash=삭제
// $where[] = " prj.prj_status NOT IN ('trash','delete','inprocess','pending','ng','ok') ";   // 디폴트 검색조건
$where[] = " prj.prj_status NOT IN ('trash','delete','inprocess','pending','ng') ";   // 디폴트 검색조건

// // 운영권한이 없으면 자기 업체만
// if (!$member['mb_manager_yn']) {
//     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
// }

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " (prj.{$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (prj.mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'prj_nick' ) :
            $where[] = " (prj.{$sfl} LIKE '{$stx}%') ";
            break;
		case ($sfl == 'com_name' ) :
            $where[] = " (com.{$sfl} LIKE '%{$stx}%') ";
            break;
        default :
            $where[] = " (prj.{$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    //$sst = "prs.prj_idx";
    $sst = "prj.prj_reg_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
/*
$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , (SELECT mb_hp FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_worker ) AS prj_mb_hp
            , (SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_worker ) AS prj_mb_name
        {$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
*/
$sql = " SELECT SQL_CALC_FOUND_ROWS * 
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

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
// arr0:name, arr1:colspan, arr2:rowspan, arr3:sort, arr4:width
$items = array(
    "prj_idx"=>array('번호',0,2,0)
    ,"prj_name"=>array("프로젝트명",0,0,0)
    // ,"prj_quot_yn"=>array("견적형",0,0,0)
    ,"prj_contract_date"=>array("수주일",0,0,0)
    ,"prj_percent"=>array("진행율",0,0,0)
    // ,"prj_type"=>array("타입",0,0,0,'50px')
    ,"com_name"=>array("업체명",0,0,0)
    ,"prj_end_company"=>array("최종고객",0,0,0)
    ,"prj_content"=>array("지시사항",0,0,0)
    ,"prj_status"=>array("상태",0,0,0)
    //,"prs_content"=>array("내용",0,0,0)
);
?>
<style>
    .td_prj_name {text-align:left !important;position:relative;}
    .td_prj_name .pm_req{position:absolute;top:-6px;right:0px;font-size:0.9em;color:red;}
    .td_com_name {text-align:left !important;}
    .td_prj_end_company {text-align:left !important;}
    .td_prj_content {text-align:left !important;}
	.file_box:after{display:block;visibility:hidden;clear:both;content:'';}
	.file_box a{display:block;float:left;width:50%;text-align:center;position:relative;}
	.file_box a > div{position:absolute;top:-25px;left:-100px;width:100px;height:auto;overflow:hidden;background:#fff;border:1px solid #ccc;line-height:1.2em;padding:3px;text-align:left;white-space:break-all;}
	.file_box a:hover > div{display:block !important;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>

<select name="sfl" id="sfl">
    <?php
    $skips = array('prj_status','prj_set_output','prj_image','trm_idx_category','prj_idx2','prj_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item');
    if(is_array($items)) {
        foreach($items as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
	<option value="prj.com_idx"<?php echo get_selected($_GET['sfl'], "prj.com_idx"); ?>>업체번호</option>
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
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;" style="width:30px;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <?php
        $skips = array();
        if(is_array($items)) {
            foreach($items as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                $row['width'] = ($v1[4]) ? ' style="width:'.$v1[4].'"' : '';   // width 설정
                // 정렬 링크
				//else if($k1 == 'prj_name') $th_wd = '';
				if($k1 == 'com_name') $th_wd = '97px';
				else if($k1 == 'prj_idx') $th_wd = '70px';
				else if($k1 == 'prj_name') $th_wd = '200px';
				else if($k1 == 'prj_quot_yn') $th_wd = '40px';
				else if($k1 == 'prj_contract_date') $th_wd = '60px';
				else if($k1 == 'prj_percent') $th_wd = '45px';
				else if($k1 == 'prj_content') $th_wd = '300px';
				else if($k1 == 'prj_status') $th_wd = '62px';
				

                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].$row['width'].' style="width:'.$th_wd.';">'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].$row['width'].' style="width:'.$th_wd.';">'.$v1[0].'</th>';
            }
        }
        ?>
		<th scope="col" id="mb_list_fle" style="width:60px;">파일</th>
		<th scope="col" id="mb_list_mng" style="width:60px;">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row);
		
		$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'project' AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
		$rs = sql_query($sql,1);
		//echo $sql."<br>";
        $cnt = $rs->num_rows;
		for($j=0;$row2=sql_fetch_array($rs);$j++) {
			$row[$row2['fle_type']][$row2['fle_sort']]['file'] = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? '<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'"><i class="fa fa-cloud-download" aria-hidden="true"></i><div style="display:none;">'.$row2['fle_name_orig'].'</div></a>':'';
			
		}
		
        // 관리 버튼
        $s_mod = ($row['prj_status'] == 'ok' && !$member['mb_manager_yn']) ? '':'<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
        
        $bg = 'bg'.($i%2);
		
        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        ?>
		<td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <?php
        $skips = array();
        if(is_array($items)) {
        //    print_r2($items);
            foreach($items as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // echo $k1.'<br>';
                // print_r2($v1);

                $list[$k1] = $row[$k1];

                // 변수 재설정
                if($k1=='prj_reg_dt') {
                    $list[$k1] = substr($row[$k1],0,10);
                }
                else if($k1=='prj_name') {
                    $list[$k1] = $row[$k1];
                    $list[$k1] .= ($row['prj_name_req']) ? '<span class="pm_req txt_redblink">수정요청</span>' : '';
                }
                else if($k1=='prj_contract_date'){
                    $row[$k1] = ($row[$k1] == '0000-00-00') ? '-' : $row[$k1];
                    $list[$k1] = $row[$k1];
                }
                else if($k1=='prj_percent') {
                    $prjpercent = ($row[$k1] < 100 || $row[$k1] != 100) ? 'style="color:blue;"' : '';
                    $per_link = ($row['prj_status'] == 'ok' && !$member['mb_manager_yn']) ? 'javascript:':'./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
                    $list[$k1] = ($row[$k1]) ? '<a href="'.$per_link.'" '.$prjpercent.'>'.$row[$k1].'%</a>' : '<span '.$prjpercent.'>-</span>';
                }
                else if($k1=='prj_type') {
                    $list[$k1] = ($row[$k1]) ? $g5['set_prj_type_value'][$row[$k1]] : '-';
                }
                else if($k1=='prj_quot_yn') {
                    $list[$k1] = ($row[$k1]) ? '<i class="fa fa-check"></i>' : '-';
                }
                else if($k1=='prj_status') {
                    $list[$k1] = ($row[$k1]) ? $g5['set_prj_status_value'][$row[$k1]] : '-';
                    if($row[$k1] == 'request'){
                        $list[$k1] = '<span class="txt_redblink">'.$g5['set_prj_status_value'][$row[$k1]].'</span>';
                    }
                }
                else if($k1=='prj_content') {
                    $list[$k1] = cut_str($row[$k1],45);
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
		?>
		<td>
			<div class="file_box">
			<?php
            echo ($cnt)?'<i class="fa fa-paperclip" aria-hidden="true"></i><span class="clip_cnt" style="margin-left:3px;">'.$cnt.'</span>':'-';
			// echo $row['prj_data'][0]['file'];
			// echo $row['prj_data'][1]['file'];
			// echo $row['prj_data'][2]['file'];
			// echo $row['prj_data'][3]['file'];
			?>
			</div>
		</td>
		<?php
        echo '<td class="td_mngsmall">'.$s_mod.'</td>'.PHP_EOL;
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
        <a href="./project_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">등록하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>

<script>
$(function(e) {
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