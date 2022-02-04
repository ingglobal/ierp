<?php
$sub_menu = '960230';
include_once('./_common.php');
// include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$g5['title'] = '프로젝트 일정 문자 통지';

include_once(G5_PATH.'/head.sub.php');
?>
<style>
/* html,body{overflow:hidden;} */
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_submit btn_close" onclick="window.close()">닫기</a>
	<?php } ?>

	<h1><?php echo $g5['title']; ?></h1>
    <div class="local_desc01 local_desc" style="display:no ne;">
        <p>해당 날짜 이후 프로젝트 일정을 완료하지 못한 담당자들에게 문자를 발송합니다.</p>
    </div>
	<div id="com_sch_list" class="new_win">

		<form name="form01" id="form01" action="./_win_sms_project_schedule_send.php" onsubmit="return form01_check(this);" method="post">
		<input type="hidden" name="w" value="<?php echo $w ?>">
		<input type="hidden" name="token" value="">
		<div class=" new_win_con">
			<div class="tbl_frm01 tbl_wrap">
				<table>
				<caption><?php echo $g5['title']; ?></caption>
				<colgroup>
					<col class="grid_1" style="width:22%;">
					<col class="grid_3">
				</colgroup>
				<tbody>
				<tr>
					<th scope="row">기준날짜</th>
					<td>
						<input type="text" name="prs_date" value="<?=G5_TIME_YMD?>" class="frm_input required" required style="width:100%;">
					</td>
				</tr>
				</tbody>
				</table>
			</div>
		</div>
		<div class="win_btn ">
			<input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
		</div>
		</form>

	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(450,640)','onload':'parent.resizeTo(450,640)'});

$(function(e) {

	$("input[name$=_date]").datepicker({
		closeText: "닫기",
		currentText: "오늘",
		monthNames: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
		monthNamesShort: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
		dayNamesMin:['일','월','화','수','목','금','토'],
		changeMonth: true,
		changeYear: true,
		dateFormat: "yy-mm-dd",
		showButtonPanel: true,
		yearRange: "c-99:c+99",
		//maxDate: "+0d"
	});

});

function form01_check(f) {
    
	// if (f.mb_hp.value=='') {
	// 	alert("휴대폰을 입력하세요.");
	// 	f.mb_hp.select();
	// 	return false;
	// }

    return true;
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>