<?php
$sub_menu = "960640";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'personal_expenses';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
// $qstr .= ($year_month) ? '&year_month='.$year_month : ''; // 추가로 확장해서 넘겨야 할 변수들
// $qstr .= ($mb_name) ? '&mb_name='.$mb_name : ''; // 추가로 확장해서 넘겨야 할 변수들
//$g5['file_table']

$g5['title'] = '개인지출내역';
if($super_admin){
    include_once('./_top_menu_personalexpenses.php');
}
include_once('./_head.php');
echo $g5['container_sub_title'];

$mb_sql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level < 8 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name NOT IN('일정관리','테스트','테스일','최호기','허준영') ORDER BY mb_name ";
// echo $mb_sql;
$mb_result = sql_query($mb_sql,1);

$sql_common = " FROM {$g5['personal_expenses_table']} AS pep
                    LEFT JOIN {$g5['member_table']} AS mb ON pep.mb_id = mb.mb_id
";


$where = array();
//$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " pep_status != 'trash' ";   // 디폴트 검색조건

if(!$super_admin) $where[] = " pep.mb_id = '{$member['mb_id']}' ";   // 일반사원 디폴트 검색조건
if($year_month) $where[] = " pep_date LIKE '".$year_month."-%' "; //연도-월 검색데이터가 있을 경우 조건
if($mb_name2) $where[] = " mb_name = '".$mb_name2."' "; //연도-월 검색데이터가 있을 경우 조건


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "mb_name";
    $sod = "";
}

if (!$sst2) {
    $sst2 = ", pep_date";
    $sod2 = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = 100;//25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
        , (ROW_NUMBER() OVER(PARTITION BY mb_name, pep_date ORDER BY pep_idx)) AS snum
        , (DENSE_RANK() OVER(ORDER BY mb_name, pep_date)) AS rnk
        , (COUNT(*) OVER(PARTITION BY mb_name, pep_date)) AS scnt
        , (SUM(pep_price) OVER(PARTITION BY mb_name, pep_date)) AS pep_small_sum
        , (SUM(pep_price) OVER()) AS pep_sum
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


$colspan = 13;
$total_price = 0;
?>
<style>
#container{min-width:1800px !important;}
#fper_box{padding-bottom:10px;}
#fper_box:after{display:block;visibility:hidden;clear:both;content:'';}
#form_personal{padding-bottom:10px;float:left;}
#status_change{float:right;}
.btn_status_change{height:35px;line-height:35px;padding:0 10px;background:#b51c50;color:#fff;border:0;}
input[type="text"]{padding:0 5px;}

.sch_name_box{text-align:left;position:relative;}
.sch_name{display:inline-block;}
.sch_name:after{display:block;visibility:hidden;clear:both;content:'';}
.sch_name li{float:left;border:1px solid #ddd;padding:0 5px;height:30px;line-height:30px;margin-right:5px;margin-top:5px;cursor:pointer;border-radius:3px;font-size:1em;background:#eee;}
.sch_name li.focus{background:#337350;color:#fff;}

.sch_month_box{text-align:left;position:relative;}
.sch_month_box .btn_submit{position:absolute;left:933px;top:1px;}
.sch_month{display:inline-block;}
.sch_month:after{display:block;visibility:hidden;clear:both;content:'';}
.sch_month li{float:right;border:1px solid #ddd;padding:0 5px;height:30px;line-height:30px;margin-right:5px;cursor:pointer;border-radius:3px;font-size:1em;}
.sch_month li:after{content:'월'}
.sch_month li.focus{background:#5d51be;color:#fff;}

#tot_box{position:absolute;display:none;top:10px;right:10px;font-size:1.3em;}
#tot_box:after{display:block;visibility:hidden;clear:both;content:'';}
#tot_box strong{color:#555;float:left;font-weight:500;}
#tot_box #tot_price{float:left;margin-left:10px;font-weight:700;color:darkblue;font-size:1.2em;}

#pep_date{width:90px;}
.pep_date{width:90px;}
.btn_register{height:35px;line-height:35px;padding:0 10px;background:#b51c50;color:#fff;border:0;
position:relative;top:0px;}
.lb_w{position:relative;}
.lb_w span{position:absolute;top:2px;right:5px;}
#form01 select{height:35px;line-height:35px;}
input[type="checkbox"].disable{opacity:0.3;}

.tr_last{border-bottom:2px solid #000;}

.td_pep_date{width:90px;}
.td_pep_subject{width:170px;}
.td_pep_content{width:400px;}
.td_pep_price{width:100px;text-align:right !important;}

.a_view{color:orange;font-size:1.2em;}
.a_down{color:#000;font-size:1.2em;}

#pep_modal{position:fixed;top:0;left:0;width:100%;height:100%;z-index:2000;display:none;}
#pep_modal #pep_modal_tbl{border:0px solid red;display:table;width:100%;height:100%;}
#pep_modal #pep_modal_tbl #pep_modal_td{position:relative;border:0px solid blue;display:table-cell;vertical-align:middle;text-align:center;}
#pep_modal #pep_modal_tbl #pep_modal_td #pep_modal_bg{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:0;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile{background:#fff;display:inline-block;width:500px;position:relative;z-index: 10;text-align:left;border-radius:10px;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile .pep_modal_close{cursor:pointer;font-size:3em;color:#fff;position:absolute;top:-40px;right:-40px;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_in{}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_in #pep_modal_hd{padding:10px;border-top-left-radius:10px;border-top-right-radius:10px;background:#5d51be;color:#fff;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_in #pep_modal_cont{padding:10px;border-bottom:1px solid #ccc;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_in #pep_modal_ft{padding:10px;border-top:1px solid #efefef;text-align:right;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_view{display:none;padding:10px;border-top:1px solid #ddd;background:#f1f1f1;border-bottom-left-radius:10px;border-bottom-right-radius:10px;}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_view .a_download{}
#pep_modal #pep_modal_tbl #pep_modal_td #fpepfile #pep_modal_view .pep_img_del{}


#pep_modal2{position:fixed;top:0;left:0;width:100%;height:100%;z-index:2000;display:none;}
#pep_modal2 #pep_modal_tbl2{border:0px solid red;display:table;width:100%;height:100%;}
#pep_modal2 #pep_modal_tbl2 #pep_modal_td2{position:relative;border:0px solid blue;display:table-cell;vertical-align:middle;text-align:center;}
#pep_modal2 #pep_modal_tbl2 #pep_modal_td2 #pep_modal_bg2{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:0;}
#pep_modal2 #pep_modal_tbl2 #pep_modal_td2 #image_box{background:#fff;display:inline-block;width:500px;position:relative;z-index: 10;text-align:left;border-radius:10px;}
#pep_modal2 #pep_modal_tbl2 #pep_modal_td2 #image_box .pep_modal_close2{cursor:pointer;font-size:3em;color:#fff;position:absolute;top:-40px;right:-40px;}
#pep_modal2 #pep_modal_tbl2 #pep_modal_td2 #image_box{width:600px;text-align:center;background:#000;}
#pep_modal2 #pep_modal_tbl2 #pep_modal_td2 #image_box #pep_modal_image{max-width:600px;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<?php if($year_month){ ?>
<input type="hidden" name="year_month" value="<?=$year_month?>">
<?php } ?>
<?php if($mb_name2){ ?>
<input type="hidden" name="mb_name2" value="<?=$mb_name2?>">
<?php } ?>
<?php if($super_admin){ ?>
<div class="sch_name_box">
    <ul class="sch_name">
        <?php
        for($v=0;$mrow=sql_fetch_array($mb_result);$v++){
        ?>
            <li class="bli<?=(($mb_name2 == $mrow['mb_name'])?' focus':'')?>" mb_name2="<?=$mrow['mb_name']?>"><?=$mrow['mb_name']?></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
<?php
$mcnt = 12;
$m_arr = months_range(G5_TIME_YMD,$mcnt); //예) 2022-05-22, 12, 'asc'
?>
<div class="sch_month_box">
    <ul class="sch_month">
        <?php for($m=0;$m<count($m_arr);$m++){ ?>
        <li class="mli<?=(($year_month == $m_arr[$m])?' focus':'')?>" year_month="<?=$m_arr[$m]?>"><?=$m_arr[$m]?></li>
        <?php } ?>
    </ul>
    <input type="submit" class="btn_submit" value="검색">
</div>
</form>
<script>
$('.mli').on('click',function(){
    if($(this).hasClass('focus')){
        $(this).removeClass('focus');
        $('#fsearch').find('input[name="year_month"]').remove();
    } else {
        $('.mli').removeClass('focus');
        $(this).addClass('focus');
        if($('input[name="year_month"]').length){
            $('input[name="year_month"]').val($(this).attr('year_month'));
        }
        else{
            $('<input type="hidden" name="year_month" value="'+$(this).attr('year_month')+'">').prependTo('#fsearch');
        }
    }
});
$('.bli').on('click',function(){
    if($(this).hasClass('focus')){
        $(this).removeClass('focus');
        $('#fsearch').find('input[name="mb_name2"]').remove();
    } else {
        $('.bli').removeClass('focus');
        $(this).addClass('focus');
        if($('input[name="mb_name2"]').length){
            $('input[name="mb_name2"]').val($(this).attr('mb_name2'));
        }
        else{
            $('<input type="hidden" name="mb_name2" value="'+$(this).attr('mb_name2')+'">').prependTo('#fsearch');
        }
    }
});
</script>
<div class="local_desc01 local_desc" style="display:no ne;position:relative;">
    <p><?php if(!$super_admin){ echo '<span style="color:blue;">'.$member['mb_name'].'</span>님의 '; } ?>개인지출내역을 관리하는 페이지입니다.</p>
    <div id="tot_box">
        <strong>검색 총금액 : </strong>
        <div id="tot_price"></div>
    </div>
</div>
<div id="fper_box">
<?php if(!$super_ceo_admin){ ?>
<form name="form_personal" id="form_personal" action="./personal_expenses_update.php" onsubmit="return form_personal_submit(this);" method="post">
<input type="hidden" name="mb_id" value="<?=$member['mb_id']?>">
<label for="pep_date" class="fp_label">
    <input type="text" name="pep_date" placeholder="사용일" id="pep_date" readonly class="frm_input readonly" value="">
</label>
<label for="pep_subject" class="fp_label">
    <input type="text" name="pep_subject" placeholder="목적지" id="pep_subject" class="frm_input" value="" style="width:200px;">
</label>
<label for="pep_content" class="fp_label">
    <input type="text" name="pep_content" placeholder="사용내용" id="pep_content" class="frm_input" value="" style="width:400px;">
</label>
<label for="pep_price" class="fp_label lb_w">
    <input type="text" name="pep_price" placeholder="사용금액" id="pep_price" class="frm_input" value="" style="width:100px;text-align:right;padding-right:20px;">
    <span>원</span>
</label>
<input type="submit" class="btn_register" value="등록">
</form>
<?php } ?>
<?php if($super_admin){ ?>
<div id="status_change">
    <select name="pep_status_change" id="pep_status_change">
        <?=$g5['set_personal_expensesstatus_options']?>
    </select>
    <button type="button" onclick="slet_input(document.getElementById('form01'));" class="btn_status_change">상태일괄변경</button>
</div>
<?php } ?>
</div><!--//#fper_box-->
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<?php if($year_month){ ?>
<input type="hidden" name="year_month" value="<?=$year_month?>">
<?php } ?>
<?php if($mb_name2){ ?>
<input type="hidden" name="mb_name2" value="<?=$mb_name2?>">
<?php } ?>
<?php if($super_admin){ ?>
<input type="hidden" name="adm" value="1">
<?php } ?>

<div class="tbl_head01 tbl_wrap">
    <table class="table table-bordered table-condensed">
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all2(this.form)">
		</th>
        <th scope="col">번호</th>
        <th scope="col">소번호</th>
        <th scope="col">이름</th>
        <th scope="col">사용일</th>
        <th scope="col">목적지</th>
        <th scope="col">사용내용</th>
        <th scope="col">금액</th>
        <th scope="col">소계금액</th>
        <th scope="col">상태</th>
        <th scope="col">보기</th>
        <th scope="col">다운로드</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
        $fle = sql_fetch("SELECT * FROM {$g5['file_table']} 
                WHERE fle_db_table = 'personal_expenses' AND fle_db_id = '".$row['pep_idx']."' ");
        $mng_btn = '<button type="button" exist="0" a_download_url="" class="btn btn_02 btn_receipt" pep_idx="'.$row['pep_idx'].'">영수증</button>';
        $a_view = '';
        $a_down = '';
        if($fle){
            if(is_file(G5_PATH.$fle['fle_path'].'/'.$fle['fle_name'])){
                $a_file_url = G5_URL.$fle['fle_path'].'/'.$fle['fle_name'];
                $a_download_url = G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$fle['fle_path'].'/'.$fle['fle_name']).'&file_name_orig='.$fle['fle_name_orig'];
                $mng_btn = '<button type="button" exist="1" ex="0" a_download_url="'.$a_download_url.'" class="btn btn_02 btn_receipt" fle_name_orig="'.cut_str($fle['fle_name_orig'],40,'...').'" pep_idx="'.$row['pep_idx'].'">영수증</button>';
                $a_view = '<a class="a_view" view="'.$a_file_url.'" wd="'.$fle['fle_width'].'" ht="'.$fle['fle_height'].'" type="'.$fle['fle_mime_type'].'" href="javascript:"><i class="fa fa-search" aria-hidden="true"></i></a>';
                $a_down = '<a class="a_down" href="'.$a_download_url.'"><i class="fa fa-download" aria-hidden="true"></i></a>';
            }
        }
        // print_r2($fle);

        if($i == 0) $total_price = $row['pep_sum'];
        $list_num = $total_count - ($page - 1) * $rows;
        $row['num'] = $list_num - $i;

        $tr_bg = ($row['rnk'] % 2 == 0)?'':'';
    ?>
    <tr class="<?=(($row['snum'] == $row['scnt'])?'tr_last':'')?>">
        <td class="td_chk">
            <input type="hidden" name="pep_idx[<?=$row['pep_idx']?>]" value="<?php echo $row['pep_idx'] ?>" id="pep_idx_<?=$row['pep_idx']?>">
            <input type="hidden" name="mb_id[<?=$row['pep_idx']?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?=$row['pep_idx']?>">
            <input type="hidden" name="snum[<?=$row['pep_idx']?>]" value="<?php echo $row['snum'] ?>" id="snum_<?=$row['pep_idx']?>">
            <input type="hidden" name="scnt[<?=$row['pep_idx']?>]" value="<?php echo $row['scnt'] ?>" id="scnt_<?=$row['pep_idx']?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['pep_subject']); ?></label>
            <input type="checkbox" name="chk[]"<?=((!$super_admin && $row['pep_status'] == 'ok')?" onclick='return false;'":"")?> class="<?=((!$super_admin && $row['pep_status'] == 'ok')?'disable':'')?>" value="<?=$row['pep_idx']?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_pep_idx"><?=$row['num']?></td>
        <td class="td_pep_idx"><?=$row['snum']?></td>
        <td class="td_mb_name" style="background:#ddd;"><?=$row['mb_name']?></td>
        <td class="td_pep_date">
            <input type="text" name="pep_date[<?=$row['pep_idx']?>]" value="<?php echo $row['pep_date'] ?>" readonly class="frm_input readonly pep_date" style="width:100px;">
        </td>
        <td class="td_pep_subject">
            <input type="text" name="pep_subject[<?=$row['pep_idx']?>]" value="<?php echo $row['pep_subject'] ?>" class="frm_input" id="pep_subject_<?=$i?>" style="width:width:200px;">
        </td>
        <td class="td_pep_content">
            <input type="text" name="pep_content[<?=$row['pep_idx']?>]" value="<?php echo $row['pep_content'] ?>" class="frm_input" id="pep_content_<?=$i?>" style="width:width:100%;">
        </td>
        <td class="td_pep_price">
            <input type="text" name="pep_price[<?=$row['pep_idx']?>]" class="frm_input pep_price" value="<?php echo number_format($row['pep_price']); ?>" num="<?=$row['pep_price']?>" id="pep_price_<?=$i?>" style="width:100px;text-align:right;">
        </td>
        <td class="td_pep_price" style="text-align:right;background:#ddd;">
            <?=number_format($row['pep_small_sum'])?>원</td>
        <td class="td_pep_status">
            <?php if($super_admin){ ?>
            <select name="pep_status[<?=$row['pep_idx']?>]" id="pep_status_<?=$i?>">
                <?=$g5['set_personal_carusestatus_options']?>
            </select>
            <script>
            $('#pep_status_<?=$i?>').val('<?=$row['pep_status']?>');
            </script>
            <?php } else { ?>
            <input type="hidden" name="pep_status[<?=$row['pep_idx']?>]" value="<?php echo $row['pep_status']; ?>">
            <?=$g5['set_personal_expensesstatus_value'][$row['pep_status']]?>
            <?php } ?>
        </td>
        <td class="td_a_view">
            <?=$a_view?>
        </td>
        <td class="td_a_down">
            <?=$a_down?>
        </td>
        <td class="td_pep_mng">
            <?=$mng_btn?>
        </td>
    </tr>
    <?php
    }
    if($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div><!--//.tbl_head01-->
<?php if($total_price){ ?>
<script>
$('#tot_box').css('display','block');
$('#tot_price').text('<?=number_format($total_price)?>원');
</script>
<?php } ?>
<?php if(!auth_check($auth[$sub_menu],"w",1)) { ?>
<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn">
</div>
<?php } ?>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;year_month='.$year_month.'&amp;mb_name2='.$mb_name2.'&amp;page='); ?>

<div id="pep_modal">
	<div id="pep_modal_tbl">
		<div id="pep_modal_td">
			<div id="pep_modal_bg"></div>
		    <form name="fpepfile" id="fpepfile" action="<?=G5_USER_ADMIN_URL?>/personal_expenses_file_update.php" method="post" enctype="multipart/form-data" onsubmit="return fpepfile_submit(this);">
                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                <input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
                <input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                <input type="hidden" name="page" value="<?php echo $page ?>">
                <input type="hidden" name="token" value="">
                <?php if($year_month){ ?>
                <input type="hidden" name="year_month" value="<?=$year_month?>">
                <?php } ?>
                <?php if($mb_name2){ ?>
                <input type="hidden" name="mb_name2" value="<?=$mb_name2?>">
                <?php } ?>
                <?php if($super_admin){ ?>
                <input type="hidden" name="adm" value="1">
                <?php } ?>

		        <i class="fa fa-times pep_modal_close" aria-hidden="true"></i>
		        <div id="pep_modal_in">
		            <div id="pep_modal_hd">
		                <h2>영수증파일 등록</h2>
		            </div>
		            <div id="pep_modal_cont"> 
		                <input type="hidden" id="md_pep_idx" name="pep_idx" value="">  
		                <input type="file" name="pep_img" id="pep_img">  
		            </div>
		            <div id="pep_modal_ft">
		                <button type="submit" class="btn btn_02" id="btn_file_submit">확인</button>
		            </div>
		        </div>
				<div id="pep_modal_view">
                    <a href="" class="a_download">[파일다운로드]<span class="sp_filename"></span></a>
                    <label for="pep_img_del"><input type="checkbox" name="pep_img_del" id="pep_img_del" value="1"> 삭제</label>
				</div>
            </form>
	    </div>
    </div>
</div>

<div id="pep_modal2">
	<div id="pep_modal_tbl2">
		<div id="pep_modal_td2">
			<div id="pep_modal_bg2"></div>
		    <div id="image_box">
                <i class="fa fa-times pep_modal_close2" aria-hidden="true"></i>
                <img src="" id="pep_modal_image">
            </div>
	    </div>
    </div>
</div>
<script>
//영수증버튼 클릭시 팝업 표시
$('.btn_receipt').on('click',function(){
    $('#pep_modal').show();
    $('#md_pep_idx').val($(this).attr('pep_idx'));
    if($(this).attr('exist') == '1'){
        console.log('in');
        $('#pep_modal_view').show();
        $('.a_download').attr('href',$(this).attr('a_download_url'));
        $('.sp_filename').text($(this).attr('fle_name_orig'));
    }
});
//모달의 close 또는 배경을 클릭하면 팝업 닫는다
$('.pep_modal_close,#pep_modal_bg').on('click',function(){
    $('#md_pep_idx').val('');
    $('#pep_img').val('');
    $('.a_download').attr('href','');
    $('.sp_filename').text('');
    $('#pep_modal_view').hide();
    $('#pep_modal').hide();
});

//이미지 보기 클릭시 팝업 표시
$('.a_view').on('click',function(){
    $('#pep_modal2').show();
    $('#pep_modal_image').attr('src',$(this).attr('view'));
});
//이미지모달의 close 또는 배경을 클릭하면 팝업 닫는다
$('.pep_modal_close2,#pep_modal_bg2').on('click',function(){
    $('#pep_modal_image').attr('src','');
    $('#pep_modal2').hide();
});


function fpepfile_submit(f){
    if(!f.pep_idx.value){
        alert('해당데이터의 고유번호가 제대로 넘어오지 않았습니다.');
        return false;
    }
    // console.log(f.pep_img_del.checked);return false;
    if(!f.pep_img_del.checked && !f.pep_img.value){
        alert('파일을 선택해 주세요.');
        f.pep_img.focus();
        return false;
    }

    return true;
}

// 가격 입력 쉼표 처리
$(document).on( 'keyup','#pep_price, .pep_price',function(e) {
    var price = thousand_comma($(this).val().replace(/[^0-9]/g,""));
    var price2 = $(this).val().replace(/[^0-9]/g,"")
    price = (price == '0') ? '' : price;
    $(this).val(price);
    $(this).attr('num',price2);
});
$(document).on( 'keyup','.mng_num',function(e) {
    var price = $(this).val().replace(/[^0-9]/g,"");
    price = (price == '0') ? '' : price;
    $(this).val(price);
});



$("#pep_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소', onClose:function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){$(this).val('');}} });
$(".pep_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

<?php if(!$super_ceo_admin){ ?>
function form_personal_submit(f){

    if(!f.pep_date.value){
        alert('사용일을 입력해 주세요.');
        f.pep_date.focus();
        return false;
    }
    if(!f.pep_subject.value){
        alert('목적지 입력해 주세요.');
        f.pep_subject.focus();
        return false;
    }
    if(!f.pep_content.value){
        alert('사용내용을 입력해 주세요.');
        f.pep_content.focus();
        return false;
    }
    if(!f.pep_price.value){
        alert('사용금액을 입력해 주세요.');
        f.pep_price.focus();
        return false;
    }

    return true;
}

<?php } ?>

function slet_input(f){
    if (!is_checked("chk[]")) {
        alert("세팅 하실 항목을 하나 이상 선택하세요.");
        return false;
    }
    var chk = document.getElementsByName("chk[]");
    var pep_status = document.getElementById('pep_status_change').value;

    for (i=0; i<chk.length; i++){ //#pep_oil_type_
        if(chk[i].checked){
            $('#pep_status_'+i).val(pep_status);
        }
    }
}

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    var chk = document.getElementsByName("chk[]");
    for (i=0; i<chk.length; i++){
        if(chk[i].checked){
            if($('#pep_subject_'+i).val() == ''){
                alert('목적지를 입력해 주세요.');
                $('#pep_subject_'+i).focus();
                return false;
                break;
            }

            if($('#pep_content_'+i).val() == ''){
                alert('사용내용 입력해 주세요.');
                $('#pep_content_'+i).focus();
                return false;
                break;
            }

            if($('#pep_price_'+i).val() == ''){
                alert('사용금액을 입력해 주세요.');
                $('#pep_price_'+i).focus();
                return false;
                break;
            }
        }
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 내역을 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

</script>
<?php
include_once ('./_tail.php');
?>
