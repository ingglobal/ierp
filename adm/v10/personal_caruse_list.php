<?php
$sub_menu = "960630";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'personal_caruse';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
// $qstr .= ($year_month) ? '&year_month='.$year_month : ''; // 추가로 확장해서 넘겨야 할 변수들
// $qstr .= ($mb_name) ? '&mb_name='.$mb_name : ''; // 추가로 확장해서 넘겨야 할 변수들

if(!isset($config['cf_perprice_gasoline'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_perprice_gasoline` INT(11) NOT NULL DEFAULT '0' AFTER `cf_recaptcha_secret_key`,
                    ADD `cf_perprice_diesel` INT(11) NOT NULL DEFAULT '0' AFTER `cf_perprice_gasoline`,
                    ADD `cf_perkm_gasoline` INT(11) NOT NULL DEFAULT '0' AFTER `cf_perprice_diesel`,
                    ADD `cf_perkm_diesel` INT(11) NOT NULL DEFAULT '0' AFTER `cf_perkm_gasoline` ", true);
}

$g5['title'] = '개인차량사용내역';
if($super_admin){
    include_once('./_top_menu_personalcaruse.php');
}
include_once('./_head.php');
echo $g5['container_sub_title'];

$mb_sql = " SELECT mb_id,mb_name FROM {$g5['member_table']} WHERE mb_level >= 6 AND mb_level < 8 AND mb_leave_date = '' AND mb_intercept_date = '' AND mb_name NOT IN('일정관리','테스트','테스일','최호기','허준영') ORDER BY mb_name ";
// echo $mb_sql;
$mb_result = sql_query($mb_sql,1);

$sql_common = " FROM {$g5['personal_caruse_table']} AS pcu
                    LEFT JOIN {$g5['member_table']} AS mb ON pcu.mb_id = mb.mb_id
";


$where = array();
//$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " pcu_status != 'trash' ";   // 디폴트 검색조건

if(!$super_admin) $where[] = " pcu.mb_id = '{$member['mb_id']}' ";   // 일반사원 디폴트 검색조건
if($year_month) $where[] = " pcu_date LIKE '".$year_month."-%' "; //연도-월 검색데이터가 있을 경우 조건
if($mb_name2) $where[] = " mb_name = '".$mb_name2."' "; //연도-월 검색데이터가 있을 경우 조건


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "mb_name";
    $sod = "";
}

if (!$sst2) {
    $sst2 = ", pcu_date";
    $sod2 = "DESC";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = 100;//25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
        , (pcu_arrival_km - pcu_start_km) AS pcu_diff_km
        , (SUM(pcu_price) OVER()) AS pcu_sum
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
else $colspan = 11;
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

#pcu_date{width:90px;}
.pcu_date{width:90px;}
#pcu_start_km{width:100px;text-align:right;padding-right:25px !important;}
#pcu_arrival_km{width:100px;text-align:right;padding-right:25px !important;}
.btn_register{height:35px;line-height:35px;padding:0 10px;background:#b51c50;color:#fff;border:0;
position:relative;top:0px;}
.lb_km{position:relative;}
.lb_km span{position:absolute;top:2px;right:5px;}
#form01 select{height:35px;line-height:35px;}
input[type="checkbox"].disable{opacity:0.3;}

.tr_even{background:#efefef !important;}

.td_pcu_date{width:90px;}
.td_pcu_why{width:170px;}
.td_pcu_reason{width:400px;}
.td_pcu_start_km{width:100px;}
.td_pcu_arrival_km{width:100px;}
.td_pcu_diff_km{width:80px;text-align:right !important;}
.td_pcu_oil_type{width:130px;}
.td_pcu_per_price{width:100px;}
.td_pcu_per_km{width:30px;}
.td_pcu_price{width:100px;text-align:right !important;}

#mng_box{position:fixed;top:0px;right:300px;z-index:1000;background:#342216;color:#fff;box-shadow:3px 3px 10px #897771;border-bottom-left-radius: 10px;border-bottom-right-radius: 10px;overflow:hidden;}
#mng_box .mng_tbl{display:table;border-collapse:collapse;border-spacing:0;}
#mng_box .mng_tbl th{text-align:center;background:#221113;}
#mng_box .mng_tbl th,#mng_box .mng_tbl td{border:1px solid #78666a;padding:5px 10px;color:#fff;}
#mng_box .mng_tbl td button{display:block;padding:6px 10px;background:#424783;color:#fff;}
#mng_box .mng_tbl td #mng_setting{margin-top:10px;background:#1f5237;color:#fff;}
#mng_box .mng_tbl td .mng_input{height:30px;line-height:30px;padding:0 5px;background:#564438;color:#fff;}
#mng_box .mng_tbl td span{position:relative;top:3px;margin-left:3px;}
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
    <p><?php if(!$super_admin){ echo '<span style="color:blue;">'.$member['mb_name'].'</span>님의 '; } ?>개인차량사용내역을 관리하는 페이지입니다.</p>
    <div id="tot_box">
        <strong>검색 총금액 : </strong>
        <div id="tot_price"></div>
    </div>
</div>
<div id="fper_box">
<?php if(!$super_ceo_admin){ ?>
<form name="form_personal" id="form_personal" action="./personal_caruse_update.php" onsubmit="return form_personal_submit(this);" method="post">
<input type="hidden" name="mb_id" value="<?=$member['mb_id']?>">
<label for="pcu_date" class="fp_label">
    <input type="text" name="pcu_date" placeholder="사용일" id="pcu_date" readonly class="frm_input readonly" value="">
</label>
<label for="pcu_reason" class="fp_label">
    <input type="text" name="pcu_reason" placeholder="사용목적" id="pcu_reason" class="frm_input" value="" style="width:200px;">
</label>
<label for="pcu_start_km" class="fp_label lb_km">
    <input type="text" name="pcu_start_km" placeholder="출발당시" id="pcu_start_km" class="frm_input" value="" num="">
    <span>km</span>
</label>
<label for="pcu_arrival_km" class="fp_label lb_km">
    <input type="text" name="pcu_arrival_km" placeholder="도착당시" id="pcu_arrival_km" class="frm_input" value="" num="">
    <span>km</span>
</label>
<label for="pcu_oil_type" class="fp_label">
    <select name="pcu_oil_type" id="pcu_oil_type">
        <option value="">없음</option>
        <?=$g5['set_mb_oiltype_options']?>
    </select>
</label>
<input type="submit" class="btn_register" value="등록">
<script>
    $('#pcu_oil_type').val('<?=$member['mb_oil_type']?>');
</script>
</form>
<?php } ?>
<?php if($super_admin){ ?>
<div id="status_change">
    <select name="pcu_status_change" id="pcu_status_change">
        <?=$g5['set_personal_carusestatus_options']?>
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
        <th scope="col">이름</th>
        <th scope="col">사용일</th>
        <th scope="col">목적</th>
        <th scope="col">출발당시<br>(km)</th>
        <th scope="col">도착당시<br>(km)</th>
        <th scope="col">이동거리<br>(km)</th>
        <th scope="col">유종</th>
        <?php if($super_admin){ ?>
        <th scope="col">리터당<br>주유비</th>
        <th scope="col">리터당<br>이동거리</th>
        <?php } ?>
        <th scope="col">금액</th>
        <th scope="col">상태</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=sql_fetch_array($result); $i++) {
        if($i == 0) $total_price = $row['pcu_sum'];

		$list_num = $total_count - ($page - 1) * $rows;
        $row['num'] = $list_num - $i;

		$tr_bg = ($i % 2 == 0)?'tr_even':'';
    ?>
    <tr class="<?=$tr_bg?>">
        <td class="td_chk">
            <input type="hidden" name="pcu_idx[<?=$row['pcu_idx']?>]" value="<?php echo $row['pcu_idx'] ?>" id="pcu_idx_<?=$row['pcu_idx']?>">
            <input type="hidden" name="mb_id[<?=$row['pcu_idx']?>]" value="<?php echo $row['mb_id'] ?>" id="mb_id_<?=$row['pcu_idx']?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['pcu_reason']); ?></label>
            <input type="checkbox" name="chk[]"<?=((!$super_admin && $row['pcu_status'] == 'ok')?" onclick='return false;'":"")?> class="<?=((!$super_admin && $row['pcu_status'] == 'ok')?'disable':'')?>" value="<?=$row['pcu_idx']?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_pcu_idx"><?=$row['num']?></td>
        <td class="td_mb_name"><?=$row['mb_name']?></td>
        <td class="td_pcu_date">
            <input type="text" name="pcu_date[<?=$row['pcu_idx']?>]" value="<?php echo $row['pcu_date'] ?>" readonly class="frm_input readonly pcu_date" style="width:100px;">
        </td>
        <td class="td_pcu_reason">
            <input type="text" name="pcu_reason[<?=$row['pcu_idx']?>]" value="<?php echo $row['pcu_reason'] ?>" class="frm_input" id="pcu_reason_<?=$i?>" style="width:width:100%;">
        </td>
        <td class="td_pcu_start_km">
            <label for="pcu_start_km_<?=$i?>" class="lb_km">
            <input type="text" name="pcu_start_km[<?=$row['pcu_idx']?>]" class="frm_input pcu_start_km" id="pcu_start_km_<?=$i?>" value="<?php echo number_format($row['pcu_start_km']); ?>" num="<?=$row['pcu_start_km']?>" class="frm_input" style="width:100px;text-align:right;padding-right:30px;">
            <span>km</span>
            </label>
        </td>
        <td class="td_pcu_arrival_km">
            <label for="pcu_arrival_km_<?=$i?>" class="lb_km">
            <input type="text" name="pcu_arrival_km[<?=$row['pcu_idx']?>]" class="frm_input pcu_arrival_km" id="pcu_arrival_km_<?=$i?>" value="<?php echo number_format($row['pcu_arrival_km']); ?>" num="<?=$row['pcu_arrival_km']?>" class="frm_input" style="width:100px;text-align:right;padding-right:30px;">
            <span>km</span>
            </label>
        </td>
        <td class="td_pcu_diff_km"><?=number_format($row['pcu_diff_km'])?> km</td>
        <td class="td_pcu_oil_type" style="width:160px;">
            <select name="pcu_oil_type[<?=$row['pcu_idx']?>]" id="pcu_oil_type_<?=$i?>">
                <?=$g5['set_mb_oiltype_options']?>
            </select>
            <script>
            $('#pcu_oil_type_<?=$i?>').val('<?=$row['pcu_oil_type']?>');
            </script>
        </td>
        <?php if($super_admin){ ?>
        <td class="td_pcu_per_price">
            <input type="text" name="pcu_per_price[<?=$row['pcu_idx']?>]" class="frm_input pcu_per_price" value="<?php echo number_format($row['pcu_per_price']); ?>" num="<?=$row['pcu_per_price']?>" id="pcu_per_price_<?=$i?>" class="frm_input" style="width:100px;text-align:right;">
        </td>
        <td class="td_pcu_per_km">
            <input type="text" name="pcu_per_km[<?=$row['pcu_idx']?>]" class="frm_input pcu_per_km" value="<?php echo number_format($row['pcu_per_km']); ?>" num="<?=$row['pcu_per_km']?>" id="pcu_per_km_<?=$i?>" class="frm_input" style="width:30px;text-align:right">
        </td>
        <?php } ?>
        <td class="td_pcu_price">
            <?php if($super_admin){ ?>
            <input type="text" name="pcu_price[<?=$row['pcu_idx']?>]" class="frm_input pcu_price" value="<?php echo number_format($row['pcu_price']); ?>" num="<?=$row['pcu_price']?>" id="pcu_price_<?=$i?>" style="width:100px;text-align:right;">
            <?php } else { ?>
            <input type="hidden" name="pcu_price[<?=$row['pcu_idx']?>]" value="<?php echo $row['pcu_price']; ?>">
            <?=number_format($row['pcu_price'])?>
            <?php } ?>
        </td>
        <td class="td_pcu_status">
            <?php if($super_admin){ ?>
            <select name="pcu_status[<?=$row['pcu_idx']?>]" id="pcu_status_<?=$i?>">
                <?=$g5['set_personal_carusestatus_options']?>
            </select>
            <script>
            $('#pcu_status_<?=$i?>').val('<?=$row['pcu_status']?>');
            </script>
            <?php } else { ?>
            <input type="hidden" name="pcu_status[<?=$row['pcu_idx']?>]" value="<?php echo $row['pcu_status']; ?>">
            <?=$g5['set_personal_carusestatus_value'][$row['pcu_status']]?>
            <?php } ?>
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

<?php if($super_admin){ ?>
<div id="mng_box">
    <table class="mng_tbl">
        <thead>
            <tr>
                <th>유종</th>
                <th>리터당금액</th>
                <th>리터당거리</th>
                <th>관리</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center;">휘발유</td>
                <td>
                    <input type="text" name="cf_perprice_gasoline" id="cf_perprice_gasoline" class="mng_input mng_num" value="<?=(($config['cf_perprice_gasoline'])?$config['cf_perprice_gasoline']:'')?>" style="width:80px;text-align:right"><span>원</span>
                </td>
                <td style="text-align:center;">
                    <input type="text" name="cf_perkm_gasoline" id="cf_perkm_gasoline" class="mng_input mng_num" value="<?=(($config['cf_perkm_gasoline'])?$config['cf_perkm_gasoline']:'')?>" style="width:30px;text-align:right"><span>km</span>
                </td>
                <td rowspan="2">
                    <button id="mng_save">설정값저장</button>
                    <button id="mng_setting">설정값셋팅</button>
                </td>
            </tr>
            <tr>
                <td style="text-align:center;">경유</td>
                <td>
                    <input type="text" name="cf_perprice_diesel" id="cf_perprice_diesel" class="mng_input mng_num" value="<?=(($config['cf_perprice_diesel'])?$config['cf_perprice_diesel']:'')?>" style="width:80px;text-align:right"><span>원</span>
                </td>
                <td style="text-align:center;">
                    <input type="text" name="cf_perkm_diesel" id="cf_perkm_diesel" class="mng_input mng_num" value="<?=(($config['cf_perkm_diesel'])?$config['cf_perkm_diesel']:'')?>" style="width:30px;text-align:right"><span>km</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php } ?>

<script>
// 가격 입력 쉼표 처리
$(document).on( 'keyup','#pcu_start_km, #pcu_arrival_km, .pcu_start_km, .pcu_arrival_km, .pcu_per_price, .pcu_per_km, .pcu_price',function(e) {
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

$('#mng_save').on('click',function(){
    if(!$('#cf_perprice_gasoline').val()){
        alert('리터당 휘발유가격을 입력해 주세요.');
        $('#cf_perprice_gasoline').focus();
        return false;
    }
    if(!$('#cf_perkm_gasoline').val()){
        alert('휘발유의 리터당 이동거리 입력해 주세요.');
        $('#cf_perkm_gasoline').focus();
        return false;
    }
    if(!$('#cf_perprice_diesel').val()){
        alert('리터당 경유가격을 입력해 주세요.');
        $('#cf_perprice_diesel').focus();
        return false;
    }
    if(!$('#cf_perkm_diesel').val()){
        alert('경유의 리터당 이동거리 입력해 주세요.');
        $('#cf_perkm_diesel').focus();
        return false;
    }

	var cf_perprice_gasoline = Number($('#cf_perprice_gasoline').val());
	var cf_perprice_diesel = Number($('#cf_perprice_diesel').val());
	var cf_perkm_gasoline = Number($('#cf_perkm_gasoline').val());
	var cf_perkm_diesel = Number($('#cf_perkm_diesel').val());

    var link = '<?=G5_USER_ADMIN_URL?>/personal_caruse_mng_update.php';
	$.ajax({
		type : "POST",
		url : link,
		dataType : "text",
		data : {'cf_perprice_gasoline': cf_perprice_gasoline, 'cf_perprice_diesel': cf_perprice_diesel, 'cf_perkm_gasoline': cf_perkm_gasoline, 'cf_perkm_diesel': cf_perkm_diesel},
		success : function(res){
			alert('유종별 기준정보를 저장했습니다.');
		},
		error : function(xmlReq){
			alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
		}
	});
});

$('#mng_setting').on('click',function(){
    if(!$('#cf_perprice_gasoline').val()){
        alert('리터당 휘발유가격을 입력해 주세요.');
        $('#cf_perprice_gasoline').focus();
        return false;
    }
    if(!$('#cf_perkm_gasoline').val()){
        alert('휘발유의 리터당 이동거리 입력해 주세요.');
        $('#cf_perkm_gasoline').focus();
        return false;
    }
    if(!$('#cf_perprice_diesel').val()){
        alert('리터당 경유가격을 입력해 주세요.');
        $('#cf_perprice_diesel').focus();
        return false;
    }
    if(!$('#cf_perkm_diesel').val()){
        alert('경유의 리터당 이동거리 입력해 주세요.');
        $('#cf_perkm_diesel').focus();
        return false;
    }

    if (!is_checked("chk[]")) {
        alert("세팅 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    var perprice_gasoline = $('#cf_perprice_gasoline').val();
    var perkm_gasoline = $('#cf_perkm_gasoline').val();
    var perprice_diesel = $('#cf_perprice_diesel').val();
    var perkm_diesel = $('#cf_perkm_diesel').val();
    var f = document.getElementById("form01");
    var chk = document.getElementsByName("chk[]");

    for (i=0; i<chk.length; i++){ //#pcu_oil_type_
        if(chk[i].checked){
            if($('#pcu_start_km_'+i).val() == ''){
                alert('출발당시(km)를 입력해 주세요.');
                $('#pcu_start_km_'+i).focus();
                return false;
                break;
            }

            if($('#pcu_arrival_km_'+i).val() == ''){
                alert('도착당시(km)를 입력해 주세요.');
                $('#pcu_arrival_km_'+i).focus();
                return false;
                break;
            }

            var diff_km = $('#pcu_arrival_km_'+i).attr('num') - $('#pcu_start_km_'+i).attr('num');
            if(diff_km <= 0){
                alert('도착당시(km)값이 출발당시(km)값이하의 수치로 입력하시면 안됩니다.\n옳바른 수치를 입력해 주세요.');
                $('#pcu_arrival_km_'+i).val('');
                $('#pcu_arrival_km_'+i).focus();
                return false;
                break;
            }
            var oil_type = $('#pcu_oil_type_'+i).val();
            var perprice = eval('perprice_'+oil_type);
            var perprice_comma = thousand_comma(eval('perprice_'+oil_type));
            var perkm = eval('perkm_'+oil_type);
            var perkm_comma = thousand_comma(eval('perkm_'+oil_type));
            var price = (diff_km / perkm) * perprice;//(이동거리 / 리터당이동거리) x 리터당유류비
            var price_comma = thousand_comma(price);

            $('#pcu_per_price_'+i).val(perprice_comma).attr('num',perprice);
            $('#pcu_per_km_'+i).val(perkm_comma).attr('num',perkm);
            $('#pcu_price_'+i).val(price_comma).attr('num',price);
        }
    }
});


$("#pcu_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", closeText:'취소', onClose:function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){$(this).val('');}} });
$(".pcu_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

<?php if(!$super_ceo_admin){ ?>
function form_personal_submit(f){

    if(!f.pcu_date.value){
        alert('사용일을 입력해 주세요.');
        f.pcu_date.focus();
        return false;
    }
    if(!f.pcu_reason.value){
        alert('사용목적을 입력해 주세요.');
        f.pcu_reason.focus();
        return false;
    }
    if(!f.pcu_start_km.value){
        alert('출발당시(km)정보를 입력해 주세요.');
        f.pcu_start_km.focus();
        return false;
    }
    if(!f.pcu_arrival_km.value){
        alert('도착당시(km)정보를 입력해 주세요.');
        f.pcu_arrival_km.focus();
        return false;
    }
    if(f.pcu_arrival_km.getAttribute('num') - f.pcu_start_km.getAttribute('num') <= 0){
        alert('도착당시(km)값이 출발당시(km)값이하의 수치로 입력하시면 안됩니다.\n옳바른 수치를 입력해 주세요.');
        f.pcu_arrival_km.value = '';
        f.pcu_arrival_km.focus();
        return false;
    }
    if(!f.pcu_oil_type.value){
        alert('유종을 선택해 주세요.');
        f.pcu_oil_type.focus();
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
    var pcu_status = document.getElementById('pcu_status_change').value;

    for (i=0; i<chk.length; i++){ //#pcu_oil_type_
        if(chk[i].checked){
            $('#pcu_status_'+i).val(pcu_status);
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
            if($('#pcu_reason_'+i).val() == ''){
                alert('자가사용 목적을 입력해 주세요.');
                $('#pcu_reason_'+i).focus();
                return false;
                break;
            }

            if($('#pcu_start_km_'+i).val() == ''){
                alert('출발당시(km)를 입력해 주세요.');
                $('#pcu_start_km_'+i).focus();
                return false;
                break;
            }

            if($('#pcu_arrival_km_'+i).val() == ''){
                alert('도착당시(km)를 입력해 주세요.');
                $('#pcu_arrival_km_'+i).focus();
                return false;
                break;
            }

            if($('#pcu_arrival_km_'+i).attr('num') - $('#pcu_start_km_'+i).attr('num') <= 0){
                alert('도착당시(km)값이 출발당시(km)값이하의 수치로 입력하시면 안됩니다.\n옳바른 수치를 입력해 주세요.');
                $('#pcu_arrival_km_'+i).val('');
                $('#pcu_arrival_km_'+i).focus();
                return false;
                break;
            }
        }
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

</script>
<?php
include_once ('./_tail.php');
?>
