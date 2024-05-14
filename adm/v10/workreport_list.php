<?php
$sub_menu = "960300";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 아래 foreach블록은 XXX_form.php파일에 제일 상단에도 서술하자
foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
                $form_input .= '<input type="hidden" name="'.$key.'[]" value="'.$v2.'" class="frm_input">'.PHP_EOL;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
            $form_input .= '<input type="hidden" name="'.$key.'" value="'.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value).'" class="frm_input">'.PHP_EOL;
        }
    }
}


$mb0 = sql_fetch(" SELECT mb_id FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level < 9 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name NOT IN('일정관리','테스트','테스일','최호기','허준영','손지식','이병구') ORDER BY mb_name LIMIT 1 ");
$default_mb_id = $mb0['mb_id'];


$ser_wrp_type = ($ser_wrp_type) ? $ser_wrp_type : 'day';
$ser_mb_id = ($ser_mb_id) ? $ser_mb_id : $default_mb_id;
$ser_from_date = ($ser_from_date) ? $ser_from_date : substr(G5_TIME_YMD,0,8).'01';
$ser_to_date = ($ser_to_date) ? $ser_to_date : date("Y-m-t", strtotime(G5_TIME_YMD));


$sql = " SELECT wrp.*, mb_name, mb_2, mb_3, prj_name FROM {$g5['workreport_table']} wrp
            LEFT JOIN {$g5['member_table']} mb ON wrp.mb_id = mb.mb_id
            LEFT JOIN {$g5['project_table']} prj ON wrp.prj_idx = prj.prj_idx
        WHERE wrp_type = '{$ser_wrp_type}'
            AND wrp.mb_id = '{$ser_mb_id}'
            AND wrp_date >= '{$ser_from_date}'
            AND wrp_date <= '{$ser_to_date}'
            AND wrp_status != 'trash'
        ORDER BY wrp_date, wrp_reg_dt
";
$res = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">기본목록</a>';

$g5['title'] = '업무보고리스트';
include_once('./_top_menu_workreport.php');
include_once('./_head.php');
if($super_mng_admin) echo $g5['container_sub_title'];

$mb_sql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level < 9 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name NOT IN('일정관리','테스트','테스일','최호기','허준영','손지식','이병구') ORDER BY mb_name ";
// echo $mb_sql;
$mb_result = sql_query($mb_sql,1);

// echo $ser_mb_id."<br>";
// echo $ser_from_date."<br>";
// echo $ser_to_date;
?>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($res->num_rows) ?></span></span>
</div>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="ser_wrp_type" value="<?=$ser_wrp_type?>">
<input type="hidden" name="ser_mb_id" value="<?=$ser_mb_id?>">
<div class="sch_box">
    <ul class="sch_type">
        <?php foreach($g5['set_wrp_type_value'] as $tk => $tv){ ?>
        <li class="tli<?=(($ser_wrp_type == $tk)?' focus':'')?>" ser_wrp_type="<?=$tk?>"><?=$tv?></li>
        <?php } ?>
    </ul>
    <ul class="sch_name">
        <?php
        for($v=0;$mrow=sql_fetch_array($mb_result);$v++){
        ?>
            <li class="bli<?=(($ser_mb_id == $mrow['mb_id'])?' focus':'')?>" ser_mb_id="<?=$mrow['mb_id']?>"><?=$mrow['mb_name']?></li>
        <?php } ?>
    </ul>
    <div id="sch_box">
        <input type="text" name="ser_from_date" class="ser_date ser_from_date" placeholder="검색시작일" value="<?=$ser_from_date?>" readonly class="frm_input"> ~
        <input type="text" name="ser_to_date" class="ser_date ser_to_date" placeholder="검색종료일" value="<?=$ser_to_date?>" readonly class="frm_input">
        <input type="submit" class="btn_submit" value="검색">
    </div>
</div>
</form>
<script>
//시작일
$(".ser_from_date").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99" ,onSelect:function(selectedDate){$('.ser_to_date').datepicker('option','minDate',selectedDate); $('.ser_to_date').val('');}, closeText:'취소', onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val(''); $('.ser_to_date').val(''); }}});

//종료일
$(".ser_to_date").datepicker({changeMonth:true, changeYear:true, dateFormat:"yy-mm-dd", showButtonPanel:true, yearRange:"c-99:c+99" ,onSelect:function(selectedDate){if($('.ser_from_date').val() == ''){ $(this).val(''); }}, closeText:'취소', onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}}});

$('.tli').on('click',function(){
    $('.tli').removeClass('focus');
    $(this).addClass('focus');
    $('input[name="ser_wrp_type"]').val($(this).attr('ser_wrp_type'));
});

$('.bli').on('click',function(){
    $('.bli').removeClass('focus');
    $(this).addClass('focus');
    $('input[name="ser_mb_id"]').val($(this).attr('ser_mb_id'));
});
</script>
<?php
$mres = sql_fetch("SELECT mb_name,mb_2,mb_3 FROM {$g5['member_table']} WHERE mb_id = '{$ser_mb_id}' ");
$pdf_ttl = $mres['mb_name'].'_'.$g5['set_wrp_type_value'][$ser_wrp_type].'_'.$ser_from_date.'_'.$ser_to_date;
?>
<article id="mt_v">
<div id="pdf_v">
<div class="ttl_box">
    <h1><?=$mres['mb_name'].'의 '.$g5['set_wrp_type_value'][$ser_wrp_type]?></h1>
    <p><?=$ser_from_date?> ~ <?=$ser_to_date?></p>
</div>
<form name="form01" id="form01" action="./workreport_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<?=$form_input?>
<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <tr>
        <th scope="col" class="th_wrp_idx">번호</th>
        <th scope="col" class="th_prj_idx">프로젝트번호</th>
        <th scope="col" class="th_prj_name">프로젝트</th>
        <th scope="col" class="th_mb_name">작성자</th>
        <th scope="col" class="th_wrp_subject">제목</th>
        <th scope="col" class="th_wrp_date">날짜</th>
        <th scope="col" class="th_mng">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for($i=0;$row=sql_fetch_array($res);$i++){ 
        $yy = substr($row['wrp_date'],0,4);
        $mm = substr($row['wrp_date'],5,2);
        // 관리 버튼
        $s_mod = '<a href="./workreport_form.php?w=u&wrp_idx='.$row['wrp_idx'].'&type='.$ser_wrp_type.'&yy='.$yy.'&mm='.$mm.'&ser_wrp_type='.$ser_wrp_type.'&ser_mb_id='.$ser_mb_id.'&ser_from_date='.$ser_from_date.'&ser_to_date='.$ser_to_date.'&list=1">수정</a>';
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?=$bg?>">
        <td class="td_wrp_idx"><?=$row['wrp_idx']?></td>
        <td class="td_prj_idx"><?=(($row['prj_idx'])?$row['prj_idx']:'기타')?></td>
        <td class="td_prj_name"><?=(($row['prj_name'])?$row['prj_name']:'기타업무')?></td>
        <td class="td_mb_name"><?=$row['mb_name']?></td>
        <td class="td_wrp_subject">
            <a href="./workreport_view.php?wrp_idx=<?=$row['wrp_idx']?>&type=<?=$ser_wrp_type?>&yy=<?=$yy?>&mm=<?=$mm?>&date=<?=$row['wrp_date']?>&ser_wrp_type=<?=$ser_wrp_type?>&ser_mb_id=<?=$ser_mb_id?>&ser_from_date=<?=$ser_from_date?>&ser_to_date=<?=$ser_to_date?>&list=1" class="a_wrp_subject">
            <?=$row['wrp_subject']?>
            </a>
        </td>
        <td class="td_wrp_date"><?=$row['wrp_date']?></td>
        <td class="td_mng"><?=$s_mod?></td>
    </tr>
    <tr class="<?=$bg?> tr_content nofocus">
        <td colspan="8" class="td_content"><?=nl2br(strip_tags($row['wrp_content']))?></td>
    </tr>
    <?php }
    if($i == 0){
        echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div><!--//.tbl_head01.tbl_wrap-->
<div class="btn_fixed_top">
    <a href="javascript:" class="btn con_view nofocus">일괄내용보기</a>
    <a href="javascript:" class="btn btn_03 pdf_btn">PDF다운로드</a>
    <a href="./workreport_calendar.php" class="btn btn_04">달력</a>
</div>
</form>
</div><!--//#pdf_v-->
</article>
<script>
$('.con_view').on('click', function(){
    if($(this).hasClass('nofocus')){
        $(this).removeClass('nofocus');
        $('.tr_content').removeClass('nofocus');
    }
    else{
        $(this).addClass('nofocus');
        $('.tr_content').addClass('nofocus');
    }
});

//pdf다운로드 버튼을 클릭하면
$('.pdf_btn').on('click',function(){
    //pdf_wrap을 canvas객체로 변환
    html2canvas($('#pdf_v')[0]).then(function(canvas) {
        var doc = new jsPDF('p', 'mm', 'a4'); //jspdf객체 생성
        var imgData = canvas.toDataURL('image/png'); //캔버스를 이미지로 변환
        var imgWidth = 200; // 이미지 가로 210길이(mm) A4 기준
        var pageHeight = imgWidth * 1.414;  // 출력 페이지 세로 길이 계산 A4 기준
        var imgHeight = canvas.height * imgWidth / canvas.width;
        var heightLeft = imgHeight;
        var pos_x = 5;
        var pos_y = 5;

        doc.addImage(imgData, 'PNG', pos_x, pos_y, imgWidth, imgHeight); //이미지를 기반으로 pdf생성

        //한 페이지 이상일 경우 루프 돌면서 출력
        /*
        while(heightLeft >= 20) {
            position = heightLeft - imgHeight;
            doc.addPage();
            doc.addImage(imgData, 'PNG', pos_x, pos_y, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }
        */
        doc.save('<?php echo get_text($pdf_ttl) ?>.pdf'); //pdf저장
    });
});
</script>
<?php
include_once ('./_tail.php');