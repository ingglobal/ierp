<?php
$sub_menu = "960230";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_schedule';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = rand(1,3);
    ${$pre}['prj_doc_no'] = 'ING-'.rand(131001,139999).'-'.rand(1,9).'a';
    ${$pre}['prj_belongto'] = 'first';
    ${$pre}['prj_price'] = rand(10000000,100000000);
    // ${$pre}[$pre.'_ask_date'] = date("Y-m-d");
    ${$pre}[$pre.'_ask_date'] = date("Y-m-d",time()-86400*3);
    ${$pre}[$pre.'_submit_date'] = date("Y-m-d",time()+86400*1);
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
	$prj = get_table_meta('project','prj_idx',${$pre}['prj_idx']);
	//print_r3($prj);
    $mb_company = get_table_meta('member','mb_id',${$pre}['mb_id_company']);
    $mb_saler = get_table_meta('member','mb_id',${$pre}['mb_id_saler']);
    $mb_account = get_table_meta('member','mb_id',${$pre}['mb_id_account']);

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = '".$pre."' AND fle_db_id = '".${$pre}[$pre.'_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
//	echo $sql;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		${$pre}[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_name'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_path'] : '' ;
		${$pre}[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							1 : 0 ;
	}
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '프로젝트일정 '.$html_title;
include_once('./_top_menu_project.php');
include_once ('./_head.php');
echo $g5['container_sub_title'];

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "prj_idx"=>array("프로젝트선택","required",60,0,'','',2)
    ,"prs_type"=>array("역할","required",100,0,'','',0)
    ,"mb_id_worker"=>array("담당자","required",100,0,'담당자 찾기','',2)
    ,"prs_task"=>array("작업","required",300,0,'','',0)
    ,"prs_start_date"=>array("작업시작일","",100,0,'','',2)
    ,"prs_end_date"=>array("작업종료일","",100,0,'','',0)
    ,"prs_graph_color"=>array("그래프색상","",100,0,'','',2)
    ,"prs_graph_thickness"=>array("그래프두께","",100,0,'','',0)
    ,"prs_graph_type"=>array("그래프타입","",100,0,'','',2)
    ,"prs_percent"=>array("진행율","",70,'%','','',0)
    ,"prs_content"=>array("내용","",70,0,'','',2)
);
?>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="url" value="<?php echo $url ?>">
<input type="hidden" name="gant" value="<?php echo $gant ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적추가 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
	</colgroup>
	<tbody>
    <tr style="display:2"><!-- 첫줄은 무조건 출력 -->
    <?php
    // 폼 생성 (폼형태에 따른 다른 구조)
    $skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
    foreach($items1 as $k1 => $v1) {
        if(in_array($k1,$skips)) {continue;}
//        echo $k1.'<br>';
//        print_r2($items1[$k1]).'<br>';
        // 폭
        $form_width = ($items1[$k1][2]) ? 'width:'.$items1[$k1][2].'px' : '';
        // 단위
        $form_unit = ($items1[$k1][3]) ? ' '.$items1[$k1][3] : '';
        // 설명
        $form_help = ($items1[$k1][4]) ? ' '.help($items1[$k1][4]) : '';
        // tr 숨김
        $form_none = ($items1[$k1][5]) ? 'display:'.$items1[$k1][5] : '';
        // 한줄 두항목
        $form_span = ($items1[$k1][6]) ? ' colspan="'.$items1[$k1][6].'"' : '';

        $item_name = $items1[$k1][0];
        // 기본적인 폼 구조 먼저 정의 
        $item_form = '<input type="text" name="'.$k1.'" value="'.${$pre}[$k1].'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;
		if(preg_match("/prj_idx$/",$k1)){
			if($w == 'u') $pj_nm = sql_fetch('SELECT prj_name FROM '.$g5['project_table'].' WHERE prj_idx = "'.${$pre}[$k1].'" ');
			$item_form = '<input type="hidden" name="'.$k1.'" id="'.$k1.'" value="'.${$pre}[$k1].'">'.PHP_EOL;
			$item_form .= '<input type="text" id="prj_name" value="'.$pj_nm['prj_name'].'" readonly required class="frm_input readonly required" style="width:200px;">'.PHP_EOL;
			$item_form .= '<a href="javascript:" link="./_win_project_select.php" class="btn btn_02 prj_select">프로젝트선택</a>'.PHP_EOL;
		}
        // 폼이 다른 구조를 가질 때 재정의
        else if(preg_match("/_price$/",$k1)||preg_match("/_receivable$/",$k1)||preg_match("/_percent$/",$k1)) {
            $item_form = '<input type="text" name="'.$k1.'" value="'.number_format(${$pre}[$k1]).'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.'">'.$form_unit;
        }
        else if(preg_match("/_memo$/",$k1)||preg_match("/_content$/",$k1)) {
            $item_form = '<textarea name="'.$k1.'" id="'.$k1.'" style="width:100%;">'.${$pre}[$k1].'</textarea>';
        }
		else if(preg_match("/prs_type$/",$k1)) {
			$item_form = '<select name="prs_type" id="prs_type">'.PHP_EOL;
			$item_form .= $g5['set_worker_type_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_type\"]').val('".${$pre}['prs_type']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
        }
		else if(preg_match("/_id_worker$/",$k1)) {
			if($w == 'u') $mbinfo = sql_fetch('SELECT mb_name,mb_3 FROM '.$g5['member_table'].' WHERE mb_id = "'.${$pre}[$k1].'" ');
			$item_form = '<input type="hidden" name="'.$k1.'" id="'.$k1.'" value="'.${$pre}[$k1].'">'.PHP_EOL;
			$item_form .= '<input type="text" id="mb_name" value="'.$mbinfo['mb_name'].'" readonly required class="frm_input readonly required" style="width:100px;">'.PHP_EOL;
			$item_form .= '<input type="text" id="mb_rank" value="'.$g5['set_mb_ranks_value'][$mbinfo['mb_3']].'" readonly class="frm_input readonly" style="width:100px;">'.PHP_EOL;
			$item_form .= '<a href="javascript:" link="./_win_worker_select.php" class="btn btn_02 wrk_select">찾기</a>'.PHP_EOL;
        }
        else if(preg_match("/_date$/",$k1)) {

        }
        else if(preg_match("/_dt$/",$k1)) {

        }
		else if(preg_match("/_graph_color$/",$k1)){
			
			$item_form = '<select name="prs_graph_color" id="prs_graph_color">'.PHP_EOL;
			$item_form .= $g5['set_gantt_color_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_graph_color\"]').val('".${$pre}['prs_graph_color']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
		}
		else if(preg_match("/_graph_thickness$/",$k1)){
			
			$item_form = '<select name="prs_graph_thickness" id="prs_graph_thickness">'.PHP_EOL;
			$item_form .= $g5['set_gantt_thickness_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_graph_thickness\"]').val('".${$pre}['prs_graph_thickness']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
			
		}
		else if(preg_match("/_graph_type$/",$k1)) {
			$item_form = '<select name="prs_graph_type" id="prs_graph_type">'.PHP_EOL;
			$item_form .= $g5['set_gantt_graphtype_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_graph_type\"]').val('".${$pre}['prs_graph_type']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
        }

        // 기종별 목표 설정
        if(preg_match("/shf_target_/",$k1) && $w!='') {
            $item_shf_no = substr($k1,-1);
            $item_btn = '<a href="javascript:" shf_idx = "'.$shf['shf_idx'].'" shf_no="'.$item_shf_no.'" class="btn btn_02 btn_item_target" style="margin-left:10px;">기종별목표</a>';
        }
        else {
            $item_btn = '';
        }

        // 이전(두줄 항목)값이 2인 경우 <tr>열지 않고 td 바로 연결
        if($span_old<=1) {
            echo '<tr style="'.$form_none.'">';
        }
        ?>
            <th scope="row"><?=$item_name?></th>
            <td<?=((preg_match("/_memo$/",$k1)||preg_match("/_content$/",$k1)) ? ' colspan="3"' : '')?>>
                <?=$form_help?>
                <?=$item_form?>
                <?=$item_btn?>
            </td>
            <?php
            // 현재(두줄 항목)값이 2가 아닌 경우만 </tr>닫기
            if($items1[$k1][6]<=1) {
                echo '</tr>'.PHP_EOL;
            }
            ?>
        <?php
        // 이전값 저장
        $span_old = $items1[$k1][6];
    }
    ?>
    </tr>
	<tr>
		<th scope="row"><label for="com_status">상태</label></th>
		<td colspan="3">
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_status_options']?>
			</select>
			<script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    $(document).on('click','.btn_item_target',function(e){
        var shf_idx = $(this).attr('shf_idx');
        var shf_no = $(this).attr('shf_no');
        // alert( shf_idx +'/'+ shf_no );
		var url = "./shift_item_goal_list.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
		win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_item_goal.focus();
    });

    $("input[name='prs_start_date'],input[name='prs_end_date']").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});
	
	$('.prj_select').on('click',function(){
		var href = $(this).attr('link');
		var win_prj_select = window.open(href, "win_prj_select", "left=10,top=10,width=500,height=800");
		win_prj_select.focus();
		return false;
	});
	$('.wrk_select').on('click',function(){
		var href = $(this).attr('link');
		var win_wrk_select = window.open(href, "win_wrk_select", "left=10,top=10,width=500,height=800");
		win_wrk_select.focus();
		return false;
	});
});

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
