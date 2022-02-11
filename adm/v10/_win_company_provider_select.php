<?php
include_once('./_common.php');

$sql_common = " FROM {$g5['company_table']} AS com";

$where = array();
$where[] = " com_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
//$where[] = " com_class = 'normal' ";   // 디폴트 검색조건
$where[] = " com_type = 'buyer' ";   // 기본 매입처(provider) 목록만 표시

if ($stx) {
    switch ($sfl) {
		case 'com_name' :
            $where[] = " ( com_name LIKE '%{$stx}%' OR com_names LIKE '%{$stx}%' ) ";
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
    $sst = "com_reg_dt";
    $sod = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common.$sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 6;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " SELECT 
			com_idx,
			com_name,
			com_president
		{$sql_common}
		{$sql_search}
		{$sql_order}
		LIMIT {$from_record}, {$rows} 
";
$result = sql_query($sql,1);
$rcnt = $result->num_rows;
// 등록 대기수
$sql = " SELECT count(*) AS cnt FROM {$g5['company_table']} AS com {$sql_join} WHERE com_status = 'pending' ";
$row = sql_fetch($sql);
$pending_count = $row['cnt'];

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

$g5['title'] = '공급업체목록';
//검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
include_once(G5_PATH.'/head.sub.php');
?>
<style>
html,body{overflow:hidden;}
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}

#new_com_add{position:absolute;top:0px;right:-10px;}
#new_com_add span{}
#new_com_add i{margin-left:5px;font-size:1.3em;}
#new_com_form{display:none;}
#new_com_form.focus{display:block;}
#new_com_form table{}
#new_com_form table th{background:#efefef;}
#new_com_form table th,#new_com_form table td{border:1px solid #ddd !important;padding:5px;}
#new_com_form table td{}
#new_com_form table td input[type="text"]{height:30px;line-height:30px;padding-left:5px;padding-right:5px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()">닫기</a>
	<?php } ?>
	<h1><?php echo $g5['title']; ?></h1>
	<div id="com_sch_list" class="new_win">
		<div class="local_ov01 local_ov">
			<?php echo $listall ?>
			<span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
			<span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
		</div>
		<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
		<label for="sfl" class="sound_only">검색대상</label>
		<select name="sfl" id="sfl" style="">
			<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
		</select>
		<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
		<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:130px;">
		<input type="submit" class="btn_submit" value="검색">
		<a href="javascript:" class="btn btn_02" id="new_com_add"><span>신규업체등록</span><i class="fa fa-angle-down" aria-hidden="true"></i></a>
		</form>
        <div id="new_com_form" class="tbl_wrap">
            <table>
                <caption>신규 공급업체 등록</caption>
                <colgroup>
                    <col style="width:30%;">
                    <col style="width:70%;">
                </colgroup>
                <tbody>
                <tr>
                    <th>업체명</th>
                    <td>
                        <input type="text" name="com_name" id="com_name" value="" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th>대표자명</th>
                    <td>
                        <input type="text" name="com_president" id="com_president" value="" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th>대표전화</th>
                    <td>
                        <?php echo help("'-'없이 숫자만 입력하세요."); ?>
                        <input type="text" name="com_tel" id="com_tel" value="" class="frm_input" onclick="javascript:chk_number(this)">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;padding:10px 0;">
                        <button type="button" class="btn btn_01" id="com_reg_btn">공급업체등록</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
		<div class="tbl_head01 tbl_wrap">
			<table class="table table-bordered table-condensed">
			<caption><?php echo $g5['title']; ?> 목록</caption>
			<thead>
				<th scope="col">회사명</th>
				<th scope="col">대표</th>
				<th scope="col">관리</th>
			</thead>
			<tbody>
			<?php
			for ($i=0; $row=sql_fetch_array($result); $i++){
				//print_r2($row);
				$choice = '<a href="javascript:" class="a_mag btn btn_02" com_idx="'.$row['com_idx'].'" com_name="'.$row['com_name'].'">선택</a>';
			?>
				<tr>
				<td class="td_com_name"><!-- 업체명 -->
					<b><?php echo get_text($row['com_name']); ?></b>
				</td>
				<td class="td_com_mgn"><!-- 마진 -->
					<b><?php echo get_text($row['com_president']); ?></b>
				</td>
				<td class="td_mng" style="text-align:center;"><!-- 관리 -->
					<?=$choice?>
				</td>
				</tr>
			<?php
			}
			if ($rcnt == 0){
				echo "<tr><td class='td_empty' colspan='3'>".PHP_EOL;
				echo "자료가 없습니다.<br>".PHP_EOL;
				echo "</td></tr>".PHP_EOL;
			}
			?>
			</tbody>
			</table>
		</div>
		<?php
		//echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page=');
		echo get_paging($config['cf_mobile_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page=');
		
		?>
	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(400,640)','onload':'parent.resizeTo(400,640)'});
$('.a_mag').on('click',function(){
	$(opener.cur_obj).find('input[name="com_idx"]').val($(this).attr('com_idx'));
	$(opener.cur_obj).find('input[name="com_name"]').val($(this).attr('com_name'));
	window.close();
});

$(function(){
    $('#new_com_add').on('click',function(){
        if($('#new_com_form').is(':visible')){
            $('#new_com_form').removeClass('focus');
            $(this).find('i').attr('class','fa fa-angle-down');
        }
        else{
            $('#new_com_form').addClass('focus');
            $(this).find('i').attr('class','fa fa-angle-up');
        }
    });

    $('#com_reg_btn').on('click',function(){
        var com_name = $('#com_name').val();
		var com_president = $('#com_president').val();
		var com_tel = $('#com_tel').val();

		if(!com_name){
			alert('업체명을 입력해 주세요');
			$('#com_name').focus();
			return false;
		}

		if(!com_president){
			alert('대표자명을 입력해 주세요.');
			$('#com_president').focus();
			return false;
		}

		if(!com_tel){
			alert('업체 대표전화번호를 입력해 주세요.');
			$('#com_tel').focus();
			return false;
		}

		com_reg(com_name,com_president,com_tel);
    });
});

function chk_number(obj){
	$(obj).keyup(function(){
		var num = $(this).val().replace(/[^0-9]/g,"");
		num = (num === '0') ? '' : num;
		$(this).val(num);
	});
}

function com_reg(com,name,tel){
	var link = '<?=G5_USER_ADMIN_URL?>/_win_company_add_update.php';
	
	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'com_name': com, 'com_president': name, 'com_tel': tel},
		success : function(res){
			if(res == 'dp'){
				alert('입력하신 대표전화번호와 일치하는 업체가 이미 등록되어 있습니다.');
				return false;
			}
			else{
				alert('공급업체를 성공적으로 등록했습니다.\n상세정보는 [업체관리]에서 수정하시기 바랍니다.');
				location.reload();
			}
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>