<?php
$sub_menu = "960210";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '프로젝트견적';
//include_once('./_top_menu_company.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];
// print_r2($_REQUEST['stx']);
/*
$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
";
*/
$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
                    LEFT JOIN {$g5['member_table']} AS mbc ON mbc.mb_id = prj.mb_id_company
                    LEFT JOIN {$g5['member_table']} AS mbs ON mbs.mb_id = prj.mb_id_saler
"; 
$where = array();
$where[] = " prj_status IN ('request','inprocess','pending','ng','ok','etc') ";   // 디폴트 검색조건

// // 운영권한이 없으면 자기 업체만
// if (!$member['mb_manager_yn']) {
//     $where[] = " prj.com_idx = '".$member['mb_4']."' ";
// }

if ($stx) {
    switch ($sfl) {
		case ( $sfl == 'prj.com_idx' || $sfl == 'prj_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler') :
			$where[] = " (mbs.mb_name LIKE '%{$stx}%' ) ";
            break;
		case ($sfl == 'mb_id_company' ) :
			$where[] = " (mbc.mb_name LIKE '%{$stx}%' ) ";
            break;
		case ($sfl == 'prj_name' || $sfl == 'prj_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
            break;
		case ($sfl == 'prj_status') :
			$stx = $g5['set_prj_status_reverse'][$stx];
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
    $sst = "prj_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'submit' AND prp_status = 'ok' ) AS prp_submit_price
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'nego' AND prp_status = 'ok' ) AS prp_nego_price
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price
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

if ($stx) {
    if ($sfl == 'prj_status') {
		$stx = $g5['set_prj_status_value'][$stx];
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
$items1 = array(
    "prj_idx"=>array('번호',0,2,0,0)
    ,"com_name"=>array("업체명",0,0,0)
    ,"prj_name"=>array("프로젝트명",0,0,1)
    ,"prj_ask_date"=>array("요청날짜",0,0,0)
    ,"mb_id_saler"=>array("영업담당자",0,0,0)
    ,"prj_doc_no"=>array("발행번호",0,0,0)
    ,"prj_quot_file"=>array("견적서",0,0,0)
    ,"prj_board_count"=>array("코멘트",0,2,0)
    // ,"prj_reg_dt"=>array("등록일",0,0,0)
    ,"prj_contract_date"=>array("수주일",0,0,0)
);
$items2 = array(
    "prj_end_company"=>array("최종고객",0,0,1)
    ,"mb_id_company"=>array("견적업체담당자",0,0,0)
    ,"prj_submit_date"=>array("제출날짜",0,0,0)
    ,"prp_submit_price"=>array("제출금액",0,0,0)
    //,"prp_nego_price"=>array("NEGO금액",0,0,0)
    ,"prp_order_price"=>array("수주금액",0,0,0)
    ,"prj_order_file"=>array("발주서/계약서",0,0,0)
    ,"prj_status"=>array("상태",0,0,0)
);
$items = array_merge($items1,$items2);
?>
<style>
.td_prj_name {text-align:left !important;}
.td_com_name {text-align:left !important;}
.td_prj_end_company {text-align:left !important;}
.td_mb_id_company {text-align:left !important;}
.file_box:after{display:block;visibility:hidden;clear:both;content:'';}
.file_box a{display:block;float:left;width:50%;text-align:center;position:relative;}
.file_box a > div{position:absolute;top:-25px;left:-100px;width:100px;height:auto;overflow:hidden;background:#fff;border:1px solid #ccc;line-height:1.2em;padding:3px;text-align:left;white-space:break-all;}
.file_box a:hover > div{display:block !important;}
.file_b:after{display:block;visibility:hidden;clear:both;content:'';}
.file_box_b{float:left;width:50%;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    $skips = array('prj_set_output','prj_image','trm_idx_category','prj_idx2','prp_submit_price','prp_nego_price','prp_submit_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item','imp_order_file');
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
		<th scope="col" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <?php
        $skips = array();
        if(is_array($items1)) {
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                // 정렬 링크
                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
            }
        }
        ?>
		<th scope="col" id="mb_list_mng" rowspan="2">관리</th>
	</tr>
    <!-- 테이블 항목명 2번 라인 -->
	<tr>
        <?php
        $skips = array();
        if(is_array($items2)) {
            foreach($items2 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                $row['width'] = ($v1[4]) ? ' style="width:'.$v1[4].'"' : '';   // width 설정
                // 정렬 링크
                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].$row['width'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].$row['width'].'>'.$v1[0].'</th>';
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
        // print_r2($row);
        $sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'quot' AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
		$rs = sql_query($sql,1);
		//echo $sql."<br>";
		for($j=0;$row2=sql_fetch_array($rs);$j++) {
			$row[$row2['fle_type']][$row2['fle_sort']]['file'] = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? 
								'<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'"><i class="fa fa-cloud-download" aria-hidden="true"></i><div style="display:none;">'.$row2['fle_name_orig'].'</div></a>'
								:'';
			
		}
        
        
        
        $mb1 = get_member($row['mb_id_saler'],'mb_name');
        $mb2 = get_member($row['mb_id_company'],'mb_name');
        if($mb2['mb_name']) {
            $sql = "SELECT cmm_title FROM {$g5['company_member_table']}
                    WHERE mb_id = '".$row['mb_id_company']."'
                        -- AND com_idx = '".$row['com_idx']."'
                    ORDER BY cmm_reg_dt DESC
                    LIMIT 1
            ";
            // echo $sql.'<br>';
            $mb3 = sql_fetch($sql,1);
            // print_r2($mb3);
            $mb2['mb_rank'] = $g5['set_mb_ranks_value'][$mb3['cmm_title']];
        }
        // print_r2($mb2);
        
        // 수금결제 추출
        $sql = "SELECT * FROM {$g5['project_price_table']}
                WHERE prj_idx = '".$row['prj_idx']."'
                    AND prp_status NOT IN ('trash','delete')
                ";
        $row['ppr'] = sql_fetch($sql,1);

        // 코멘트 갯수
		$sql3 = " 	SELECT count(wr_id) AS cnt_total
						, SUM( if( TIMESTAMPDIFF( HOUR, wr_datetime ,now() ) < '".(int)$g5['setting']['set_new_icon_hour']."', 1, 0 ) ) AS cnt_new
					FROM g5_write_quot1
                    WHERE wr_is_comment = 0 
                        AND wr_4 = '".$row['prj_idx']."'
		";
        $row['board'] = sql_fetch($sql3,1);
        //print_r3($row['board']);
        $row['board']['cnt_total_text'] = ($row['board']['cnt_total']) ? $row['board']['cnt_total']:'코멘트';
        $row['board']['cnt_new_text'] = ($row['board']['cnt_new']) ? '<span class="comment_new">('.$row['board']['cnt_new'].')</span>':'';

        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
        $s_cop = '<a href="./'.$fname.'_form.php?copy=1&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'" class="btn_cop">복제</a>';
		//$s_del = '<a href="./prj_form_update.php?'.$qstr.'&amp;w=d&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        //관련파일 추출
        $sql = "SELECT * FROM {$g5['file_table']} 
                    WHERE fle_db_table = 'quot' AND fle_type IN ('quot','order','contract') AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_reg_dt DESC ";
        $rs = sql_query($sql,1);
        //echo $rs->num_rows;echo "<br>";
        $row['prj_quot_fidxs'] = array();
        $row['prj_order_fidxs'] = array();
        $row['prj_contract_fidxs'] = array();
        for($j=0;$row2=sql_fetch_array($rs);$j++) {
            @array_push($row['prj_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
        }


        $bg = 'bg'.($i%2);
        
        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        ?>
		<td class="td_chk" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <?php
        $skips = array();
        if(is_array($items1)) {
        //    print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // echo $k1.'<br>';
                // print_r2($v1);

                $list[$k1] = $row[$k1];

                // 변수 재설정
                if($k1=='prj_contract_date'){ //($k1=='prj_reg_dt') {
                    // $list[$k1] = substr($row[$k1],0,10);
                    $row[$k1] = ($row[$k1] == '0000-00-00') ? '-':$row[$k1];
                    $list[$k1] = $row[$k1];
                }
				else if($k1=='com_name') {
                    $list[$k1] = '<span style="font-weight:bold;">'.$row[$k1].'</span>';
                }
				else if($k1=='prj_name') {
                    $list[$k1] = '<span style="font-weight:bold;">'.$row[$k1].'</span>';
                }
                else if($k1=='mb_id_saler') {
                    $list[$k1] = $mb1['mb_name'];
                }
                else if($k1=='prj_parts') {
                    $list[$k1] = '<a href="./prj_parts_list.php?prj_idx='.$row['prj_idx'].'" class="btn_parts">'.$row['parts']['total_count'].'</a>';
                }
                else if($k1=='prj_maintain') {
                    $list[$k1] = '<a href="./maintain_list.php?prj_idx='.$row['prj_idx'].'" class="btn_maintain">'.$row['maintain']['total_count'].'</a>';
                }
                else if($k1=='trm_idx_category') {
                    $list[$k1] = ($row[$k1]) ? $g5['category_name'][$row[$k1]] : '-';
                }
                else if($k1=='prj_ask_date'){
                    $list[$k1] = ($row[$k1] != '0000-00-00') ? $row[$k1] : '-';
                }
                else if($k1=='prj_board_count'){
                    $list[$k1] = '<a href="'.G5_BBS_URL.'/board.php?bo_table=quot1&ser_prj_idx='.$row['prj_idx'].'" target="_blank" class="btn_prj_comment">
                                '.$row['board']['cnt_total_text'].$row['board']['cnt_new_text'].'
                                </a>';
                }
                else if($k1=='prj_quot_file'){
                    //$quot_path = G5_DATA_PATH.'/ierp/'.$row['prj_idx'].'/'.$row['prj_quot_file'];
                    //$list[$k1] = (is_file($quot_path)) ? '<a href="'.G5_BBS_URL.'/download2.php?file='.$quot_path.'" download><i class="fa fa-cloud-download" aria-hidden="true"></i> '.$row['prj_quot_file'].'</a>' : '';
                    //echo $row['prj_q_data'][0]['file'];
                    
                    $list[$k1] = '<div class="file_box">견('.count($row['prj_quot_fidxs']).')</div>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        //echo '<td class="td_mngsmall" rowspan="2">'.$s_mod.'<br>'.$s_view.'</td>'.PHP_EOL;
        echo '<td class="td_mngsmall" rowspan="2">'.$s_mod.'<br>'.$s_cop.'</td>'.PHP_EOL;
		//echo $td_items[$i];
        echo '</tr>'.PHP_EOL;


        // 2번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        $skips = array();
        if(is_array($items2)) {
            foreach($items2 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // 변수 재설정
                if($k1=='prj_checks') {
                    $list[$k1] = '<a href="./prj_checks_list.php?prj_idx='.$row['prj_idx'].'" class="btn_checks">'.$row['checks']['total_count'].'</a>';
                }
                else if($k1=='mb_id_company') {
                    $list[$k1] = $mb2['mb_name'].' '.$mb2['mb_rank'];
                }
                else if($k1=='prj_submit_date'){
                    $list[$k1] = ($row[$k1] != '0000-00-00') ? $row[$k1] : '-';
                }
                else if($k1=='prp_submit_price') {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if($k1=='prp_nego_price') {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if($k1=='prp_order_price') {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if($k1=='prj_item') {
                    $list[$k1] = '<a href="./prj_item_list.php?prj_idx='.$row['prj_idx'].'" class="btn_checks">'.$row['item']['total_count'].'</a>';
                }
                else if($k1=='prj_set_output') {
                    $list[$k1] = (!$row[$k1]) ? $g5['set_prj_set_data_value']['shift'] : $g5['set_prj_set_data_value'][$row[$k1]];
                }
                else if($k1=='prj_set_error') {
                    $list[$k1] = (!$row[$k1]) ? $g5['set_prj_set_data_value']['shift'] : $g5['set_prj_set_data_value'][$row[$k1]];
                }
                else if($k1=='prj_status') {
                    if($row[$k1] == 'ok') {
                        $list[$k1] = '<span style="color:blue;font-weight:bold;">'.$g5['set_prj_status_value'][$row[$k1]].'</span>';
                    } else if($row[$k1] == 'request') {
                        $list[$k1] = '<span class="txt_redblink">'.$g5['set_prj_status_value'][$row[$k1]].'</span>';
                    } else {
                        $list[$k1] = $g5['set_prj_status_value'][$row[$k1]];
                    }
                }
                else if($k1=='prj_order_file'){$list[$k1] = '<div class="file_b">';
                    $list[$k1] .= '<div class="file_box_b">발('.count($row['prj_order_fidxs']).')</div>';
                    $list[$k1] .= '<div class="file_box_b">계('.count($row['prj_contract_fidxs']).')</div>';
                    $list[$k1] .= '</div>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        echo '</tr>'.PHP_EOL;


    }
	if ($i == 0)
		echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <a href="./quot_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">견적추가</a>
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