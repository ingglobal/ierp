<?php
$sub_menu = "960240";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'project_price';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '수입관리';
//include_once('./_top_menu_company.php');
//include_once('./_top_menu_price.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['project_table']} AS prj
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = prj.com_idx
"; 

$where = array();
//$where[] = " prj_status NOT IN ('trash','delete') ";   // 디폴트 검색조건
$where[] = " prj_status = 'ok' ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만
if (!$member['mb_manager_yn']) {
    $where[] = " prj.com_idx = '".$member['mb_4']."' ";
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
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }

    if($misu){
        $where[] = " ((SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
            - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0))) 
            FROM {$g5['project_price_table']} AS prp WHERE prp.prj_idx = prj.prj_idx) > 0) ";
    }
}
else{
	if($sfl == 'misu'){
		$where[] = " ((SELECT (SUM(IF(prp.prp_type = 'order' AND prp.prp_status = 'ok',prp.prp_price,0))
            - SUM(IF(prp.prp_type NOT IN ('submit','nego','order','') AND prp.prp_pay_date != '0000-00-00' AND prp.prp_status = 'ok',prp.prp_price,0))) 
            FROM {$g5['project_price_table']} AS prp WHERE prp.prj_idx = prj.prj_idx) > 0) ";
	}
	else if($sfl = 'suok'){
		//$where[] = " (prp_paid_date NOT IN ('0000-00-00','')) ";
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

$rows = 25;//$config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
            , com.com_idx AS com_idx
            , (SELECT prp_pay_date FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_paid_date
            , (SELECT prp_price FROM {$g5['project_price_table']} WHERE prj_idx = prj.prj_idx AND prp_type = 'order' AND prp_status = 'ok' ) AS prp_order_price
            , (SELECT mb_hp FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_account ) AS prj_mb_hp
            , (SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = prj.mb_id_account ) AS prj_mb_name
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


$cur_url = ($_SERVER['SERVER_PORT'] != '80' ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$cur_url = (preg_match("/\?/",$cur_url)) ? $cur_url.'&' : $cur_url.'?';
$cur_url = preg_replace('/frm_date=([0-9]{4})-([0-9]{2})-([0-9]{2})/i','',$cur_url);
$cur_url = str_replace('?&','?',$cur_url);
$cur_url = str_replace('&&','&',$cur_url);


$misu_sql = " SELECT prj.prj_idx, prj_name
                , SUM(IF(prp_type = 'order' AND prp_status = 'ok',prp_price,0)) AS prj_order_price
                , SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0)) AS sugum
                ,(SUM(IF(prp_type = 'order' AND prp_status = 'ok',prp_price,0)) - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0))) AS suju_misu
                ,SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0)) AS gesan_misu
                ,(SUM((SUM(IF(prp_type = 'order' AND prp_status = 'ok',prp_price,0)) - SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_pay_date != '0000-00-00' AND prp_status = 'ok',prp_price,0)))) OVER(ORDER BY prj_idx)) AS nu_suju_misu
                ,(SUM(SUM(IF(prp_type NOT IN ('submit','nego','order','') AND prp_issue_date != '0000-00-00' AND prp_pay_date = '0000-00-00',prp_price,0))) OVER(ORDER BY prj_idx)) AS nu_gesan_misu
            FROM {$g5['project_table']} AS prj
                LEFT JOIN {$g5['project_price_table']} AS prp ON prp.prj_idx = prj.prj_idx
            WHERE prj_status IN ('ok')
            GROUP BY prj_idx
            ORDER BY prj_idx DESC LIMIT 1
";
$misu_result = sql_fetch($misu_sql);
$total_suju_misu = number_format($misu_result['nu_suju_misu']);
$total_gesan_misu = number_format($misu_result['nu_gesan_misu']);
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
.td_percent{position:relative;}
.prj_per{}
.prj_per_input{position:absolute;top:50%;left:50%;width:40px;margin-left:-23px;margin-top:-12px;text-align:right;}
.per_bar{position:relative;height:10px;width:100%;min-width:60px;border-radius:5px;background:gray;overflow:hidden;}
.per_bar .bar_in{height:100%;background:#37c537;border-radius:5px;}

.file_box:after{display:block;visibility:hidden;clear:both;content:'';}
.file_in{float:left;width:50%;position:relative;}
.file_in:first-child::after{content:'/';position:absolute;top:50%;right:-3px;transform:translateY(-50%);}

#price_fix_box{position:fixed;left:0;bottom:0;width:240px;z-index:1000;background:rgba(255,255,255,0.8);padding:5px 5px;}
#price_fix_box h4{font-size:0.8em;}
#price_fix_box table{border-collapse:collapse;border-spacing:0;margin-top:4px;}
#price_fix_box th{text-align:center;font-weight:300;font-size:0.85em;}
#price_fix_box th,#price_fix_box td{border:1px solid #ddd;}
#price_fix_box td{text-align:right;font-size:0.9em;padding-right:4px;}
#price_fix_box td.td_misu{color:#f56b08;}
#price_fix_box td.td_geab{color:#073ed8;}

#fsearch:after{display:block;visibility:hidden;clear:both;content:'';}
#misu_btn{float:right;}
#misu_btn.btn_02{background:#ccc;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    
    <select name="sfl" id="sfl">
        <option value="com.com_name"<?php echo get_selected($_GET['sfl'], "com.com_name"); ?>>업체명</option>
        <option value="prj_name"<?php echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
        <option value="prj_idx"<?php echo get_selected($_GET['sfl'], "prj_idx"); ?>>프로젝트번호</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <input type="submit" class="btn_submit" value="검색">
    <a href="javascript:misu_sch($('#fsearch'));" class="btn <?=(($sfl == 'misu') ? 'btn_03':'btn_02')?>" id="misu_btn">미수금항목만보기</a>
</form>
<script>
function misu_sch(f){
    if(f.find('#stx').val()){
        $('<input type="hidden" name="misu" value="1">').appendTo('#fsearch');
        f.find('.btn_submit').trigger('click');
    }
    else{
        f.find('#sfl').find('option').attr('selected',false);
        $('<option value="misu" selected="selected">미수</option>').appendTo('#sfl');
        f.find('#sfl').val('misu');
        //console.log(f.find('#sfl').val());
        f.find('.btn_submit').trigger('click');
        //return false;
    }
}
</script>
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
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <th scope="col">번호</th>
        <th scope="col">의뢰기업</th>
        <th scope="col" style="width:150px;">공사프로젝트</th>
        <th scope="col">수주금액</th>
        <th scope="col">수금상태</th>
        <th scope="col">프로젝트<br>진행율</th>
        <th scope="col">미수금</th>
        <th scope="col">미수금(발)</th>
        <!--th scope="col">회계담당자</th-->
        <th scope="col">발주서/계약서</th>
        <th scope="col" style="width:50px;">코멘트</th>
        <th scope="col">%</th>
        <th scope="col">수입예정</th>
        <th scope="col">VAT미납여부</th>
        <th scope="col">금액타입</th>
        <th scope="col" style="width:80px;">발행예정일</th>
        <th scope="col" style="width:80px;">계산서발행일</th>
        <th scope="col" style="width:80px;">수금예정일</th>
        <th scope="col" style="width:80px;">수금완료일</th>
        <th scope="col" style="width:40px;">수정</th>
        <th scope="col" id="mb_list_mng">추가</th>
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    /*
    [prj_idx] => 9
    [com_idx] => 1
    [mb_id_company] => test01
    [mb_id_saler] => test02
    [mb_id_account] => 
    [prj_doc_no] => ING-138169-9a
    [prj_name] => 4축 트랜스퍼
    [prj_end_company] => 이앤에프㈜
    [prj_content] => 
    [prj_belongto] => first
    [prj_receivable] => 0
    [prj_percent] => 10
    [prj_keys] => 
    [prj_status] => ok
    [prj_ask_date] => 2020-09-06
    [prj_submit_date] => 2020-09-10
    [prj_reg_dt] => 2020-09-09 11:51:28
    [prj_update_dt] => 2020-09-11 22:36:10
    [prp_order_price] => 78000000
    [com_name] => 아진산업
    [com_name_eng] => AJIN INDUSTRIAL Co.,LTD.
    [com_names] => , 아진산업(20-08-02~)
    [com_homepage] => www.wamc.co.kr
    [com_tel] => 053-856-9100
    [com_fax] => 053-856-9111
    [com_email] => master@wamc.co.kr
    [com_type] => carparts
    [com_class] => 
    [com_president] => 서중호
    [com_biz_no] => 000-00-00000
    [com_biz_type1] => 설비
    [com_biz_type2] => 자동차
    [com_zip1] => 384
    [com_zip2] => 62
    [com_addr1] => 경북 경산시 진량읍 공단8로26길 40
    [com_addr2] => 
    [com_addr3] =>  (신제리)
    [com_addr_jibeon] => R
    [com_b_zip1] => 
    [com_b_zip2] => 
    [com_b_addr1] => 
    [com_b_addr2] => 
    [com_b_addr3] => 
    [com_b_addr_jibeon] => 
    [com_latitude] => 
    [com_longitude] => 
    [com_memo] => 
    [com_keys] => 
    [com_status] => ok
    [com_reg_dt] => 2020-08-02 16:08:13
    [com_update_dt] => 2020-08-05 10:47:06
    */
    $misu1_price = 0;
    $misu2_price = 0;
    
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row['prp_paid_date']);
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'&amp;group=1">수정</a>';
        //$s_view = '<a href="./'.$fname.'_view.popup.php?&'.$pre.'_idx='.$row['prj_idx'].'" class="btn_view">보기</a>';
        $s_add = '<a href="./project_price_form.php?'.$qstr.'&amp;g=1&amp;prj_idx='.$row['prj_idx'].'&amp;grp=1">추가</a>';
        
        $psql = "   SELECT *
                        , IF( prp_type IN ('manday','buy','etc'), prp_price*-1, prp_price ) AS prp_price2
                        , IF( prp_type IN ('manday','buy','etc'), 2, 1 ) AS prp_sort
                    FROM {$g5['project_price_table']}
                    WHERE prj_idx = '".$row['prj_idx']."'
                        AND prp_type NOT IN ('submit','nego','order')
                        AND prp_status NOT IN ('trash','delete')
                    ORDER BY prp_sort, prp_type, prp_reg_dt
        ";
        // echo $psql.'<br>';
        $p_result = sql_query($psql);
        $p_cnt = $p_result->num_rows;
        
        // 코멘트 갯수
		$sql3 = " 	SELECT count(wr_id) AS cnt_total
						, SUM( if( TIMESTAMPDIFF( HOUR, wr_datetime ,now() ) < '".(int)$g5['setting']['set_new_icon_hour']."', 1, 0 ) ) AS cnt_new
					FROM g5_write_price1
                    WHERE wr_is_comment = 0 
                        AND wr_4 = '".$row['prj_idx']."'
		";
        $row['board'] = sql_fetch($sql3,1);
        //print_r3($row['board']);
        $row['board']['cnt_total_text'] = ($row['board']['cnt_total']) ? $row['board']['cnt_total']:'코멘트';
        $row['board']['cnt_new_text'] = ($row['board']['cnt_new']) ? '<span class="comment_new">('.$row['board']['cnt_new'].')</span>':'';
        
        //수금완료 합계를 구한다
        $ssql = " SELECT SUM(prp_price) AS sum_price
                    FROM {$g5['project_price_table']}
                    WHERE prj_idx = '".$row['prj_idx']."'
                        AND prp_type NOT IN ('submit','nego','order','')
                        AND prp_pay_date != '0000-00-00'
                        AND prp_status = 'ok'
        ";
        //echo $ssql;
        $sugeum = sql_fetch($ssql);
        $row['prj_collect_price'] = $sugeum['sum_price'];
        $row['prj_collect_percent'] = ($row['prp_order_price'] > 0) ? round($row['prj_collect_price'] / $row['prp_order_price'] * 100) : 0;
        $row['prj_mi_price'] = $row['prp_order_price'] - $sugeum['sum_price'];
        $row['prj_misu_price'] = number_format($row['prp_order_price'] - $sugeum['sum_price']);
        $misu1_price += $row['prp_order_price'] - $sugeum['sum_price'];
        //계산서발행 미수금(계산서발행일은 있으나 수금완료일이 없는 항목)의 합계를 구한다
        $gsql = " SELECT SUM(prp_price) AS sum_price
                    FROM {$g5['project_price_table']}
                    WHERE prj_idx = '".$row['prj_idx']."'
                        AND prp_type NOT IN ('submit','nego','order')
                        AND prp_issue_date != '0000-00-00'
                        AND prp_pay_date = '0000-00-00'
                        AND prp_status = 'pending'
        ";
        //echo $gsql;
        $gmisu = sql_fetch($gsql);
        $row['prj_gemisu_price'] = $gmisu['sum_price'];
        $row['prj_gemusu_format'] = number_format($row['prj_gemisu_price']);
        $misu2_price += $row['prj_gemisu_price'];
        

        //관련파일 추출
        $sql = " SELECT * FROM {$g5['file_table']} 
            WHERE fle_db_table = 'quot' AND fle_type IN ('quot','order','contract') AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_reg_dt DESC ";
        $rs = sql_query($sql,1);
        //echo $rs->num_rows;echo "<br>";
        $row['prj_f_quot'] = array();
        $row['prj_f_order'] = array();
        $row['prj_f_contract'] = array();
        for($j=0;$row2=sql_fetch_array($rs);$j++) {
            $file_down = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? '<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" title="'.$row2['fle_name_orig'].'"><i class="fa fa-file" aria-hidden="true"></i></a>':''.PHP_EOL;

            @array_push($row['prj_f_'.$row2['fle_type']],array('file'=>$file_down));
        }
        $bg = 'bg'.($i%2);
        
        if($p_cnt){ //가격레코드가 존재할 경우
        ?>
        <tr class="<?=$bg?>">
            <td class="td_chk" rowspan="<?=$p_cnt?>" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
                <input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td rowspan="<?=$p_cnt?>"><?=$row['prj_idx']?></td><!-- 번호 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['com_name']?></td><!-- 의뢰기업 -->
            <td rowspan="<?=$p_cnt?>" class="td_left"><?=$row['prj_name']?></td><!-- 공사프로젝트 -->
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=number_format($row['prp_order_price'])?></td>
            <td rowspan="<?=$p_cnt?>" style="position:relative;"><?=$row['prj_collect_percent']?>%<div class="per_bar"><div class="bar_in" style="width:<?=$row['prj_collect_percent']?>%;"></div></div></td>
            <td rowspan="<?=$p_cnt?>" class="td_percent">
                <?php
                $prjpercent = ($row['prj_percent'] < 100 || $row['prj_percent'] != 100) ? 'style="color:blue;"':'';
                $row['prj_percent'] = '<span '.$prjpercent.' class="prj_per" prj_idx="'.$row['prj_idx'].'" prj_percent="'.$row['prj_percent'].'">'.$row['prj_percent'].'</span><span '.$prjpercent.'>%</span>';
                echo $row['prj_percent'];
                ?>
            </td><!-- 프로젝트 진행율 -->
            <td rowspan="<?=$p_cnt?>" style="text-align:right;"><?=(($row['prj_mi_price'] > 0) ? '<span style="color:orange;">'.$row['prj_misu_price'].'</span>' : '<span style="color:#333;">'.$row['prj_misu_price'].'</span>')?></td>
            <td rowspan="<?=$p_cnt?>" style="text-align:right;color:blue;"><?=$row['prj_gemusu_format'] ?></td>
            <!--td rowspan="<?php //$p_cnt?>"><?php //$row['prj_mb_name']?><br><?php //$row['prj_mb_hp']?></td-->
            <td rowspan="<?=$p_cnt?>"><!-- 발주/계약 -->
                <div class="file_box">
                    <div class="file_in">
                        <?php if(count($row['prj_f_order'])){
                            echo $row['prj_f_order'][0]['file'];
                        }else{ echo '-'; } ?>
                    </div>
                    <div class="file_in">
                        <?php if(count($row['prj_f_contract'])){
                            echo $row['prj_f_contract'][0]['file'];
                        }else{ echo '-'; } ?>
                    </div>
                </div>
            </td>
            <td rowspan="<?=$p_cnt?>" style="position:relative;"><!-- 코멘트 -->
                <a href="<?php echo G5_BBS_URL?>/board.php?bo_table=price1&ser_prj_idx=<?php echo $row['prj_idx']?>" target="_blank" class="btn_company_comment">
                <?php echo $row['board']['cnt_total_text'].$row['board']['cnt_new_text'] ?>
                </a>

                <?php if($row['prj_contract2']){ ?>
                <div class="malp jisi">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                    <input type="text" class="pungsun_input" value="<?=$row['prj_content2']?>">
                    <div class="pungsun"><?=$row['prj_content2']?></div>
                </div>
                <?php } ?>
            </td>
        <?php            
            for($j=0;$prow=sql_fetch_array($p_result);$j++){
                $c_mod = '<a href="./project_price_form.php?'.$qstr.'&amp;w=u&amp;g=1&amp;prj_idx='.$prow['prj_idx'].'&amp;prp_idx='.$prow['prp_idx'].'&amp;grp=1">수정</a>';
                if($p_cnt > 1){ //가격레코드가 1개 이상일경우
                    if($j != 0) echo '<tr class="'.$bg.'">'.PHP_EOL;
        ?>
            <td style="position:relative;">
                <?php
                $prc_percent = ($row['prp_order_price']) ? @floor(($prow['prp_price'] / $row['prp_order_price']) * 100).'%' : '0%';
                echo ($prc_percent == '100%') ? '<span style="color:blue;">'.$prc_percent.'</span>' : $prc_percent;
                ?>
                
                <?php if($prow['prp_content']){ ?>
                <div class="malp jisi">
                    <i class="fa fa-microphone" aria-hidden="true"></i>
                    <input type="text" class="pungsun_input" value="<?=$prow['prp_content']?>">
                    <div class="pungsun"><?=$prow['prp_content']?></div>
                </div>
                <?php } ?>
                <?php if($prow['prp_content2']){ ?>
                <div class="malp misu">
                    <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    <input type="text" class="pungsun_input" value="<?=$prow['prp_content2']?>">
                    <div class="pungsun"><?=$prow['prp_content2']?></div>
                </div>
                <?php } ?>
            </td><!-- % -->
            <td style="text-align:right;"><?=number_format($prow['prp_price2'])?></td><!-- 수입예정 -->
            <td><?=(($prow['prp_vat_yn'])?'<i class="fa fa-check"></i>':'-')?></td>
            <td>
                <?php
                    // print_r2($g5['set_price_type']);
                    echo $g5['set_price_type_value'][$prow['prp_type']].(($prow['prp_type'] == 'middle') ? '('.$prow['prp_pay_no'].')' : '');
                ?>
            </td>
            <td>
                <?php
                $dt_plan_class = '';
                if(strpos($prow['prp_plan_date'],'0000-00-00') === false){
                    $date_alarm = strtotime(get_dayAddDate($prow['prp_plan_date'],-($g5['setting']['set_plan_alarmdaycnt'])));
                    $date_plan = strtotime($prow['prp_plan_date']);
                    $date_today = strtotime(G5_TIME_YMD);
                    if($date_today >= $date_alarm && $date_today <= $date_plan && strpos($prow['prp_issue_date'],'0000-00-00') !== false) $dt_plan_class = 'txt_blueblink';
                    else if($date_today > $date_plan) $dt_plan_class = 'txt_gray';
                }
                echo '<span class="'.$dt_plan_class.'">'.((strpos($prow['prp_plan_date'],'0000-00-00') === false) ? $prow['prp_plan_date'] : '').'</span>';
                ?>
            </td>
            <td><?php echo ((strpos($prow['prp_issue_date'],'0000-00-00') === false) ? $prow['prp_issue_date'] : '')?></td>
            <td>
                <?php
                $dt_planpay_class = '';
                $payflag = (strpos($prow['prp_pay_date'],'0000-00-00') === false) ? true : false;
                $date_planpay = strtotime($prow['prp_planpay_date']);
                $date_today2 = strtotime(G5_TIME_YMD);
                if(!$payflag && $date_today2 > $date_planpay){
                    $dt_planpay_class = 'txt_redblink';
                }else if($payflag){
                    $dt_planpay_class = 'txt_green';
                }
                echo '<span class="'.$dt_planpay_class.'">'.((strpos($prow['prp_planpay_date'],'0000-00-00') === false) ? $prow['prp_planpay_date'] : '').'</span>';
                ?>
            </td>
            <td><?=(($prow['prp_pay_date'] != '0000-00-00') ? $prow['prp_pay_date'] : '-')?></td>
            <td><?=$c_mod?></td>
            <?php if($j == 0){ ?>
            <td class="td_mngsmall" rowspan="<?=$p_cnt?>">
                <?=$s_add?>
            </td></tr>
            <?php }else{ ?>
            </tr>
            <?php } ?>
        <?php
                }else{ //---------가격레코드가 1개뿐일 경우
        ?>
            <td style="position:relative;">
                <?php
                $prc_percent = ($row['prp_order_price']) ? @floor(($prow['prp_price'] / $row['prp_order_price']) * 100).'%' : '0%';
                echo ($prc_percent == '100%') ? '<span style="color:blue;">'.$prc_percent.'</span>' : $prc_percent;
                ?>
                
                <?php if($prow['prp_content']){ ?>
                <div class="malp jisi">
                    <i class="fa fa-microphone" aria-hidden="true"></i>
                    <input type="text" class="pungsun_input" value="<?=$prow['prp_content']?>">
                    <div class="pungsun"><?=$prow['prp_content']?></div>
                </div>
                <?php } ?>
                <?php if($prow['prp_content2']){ ?>
                <div class="malp misu">
                    <i class="fa fa-question-circle-o" aria-hidden="true"></i>
                    <input type="text" class="pungsun_input" value="<?=$prow['prp_content2']?>">
                    <div class="pungsun"><?=$prow['prp_content2']?></div>
                </div>
                <?php } ?>
            </td>
            <td style="text-align:right;"><?=number_format($prow['prp_price'])?></td><!-- 수입예정 -->
            <td><?=(($prow['prp_vat_yn'])?'<i class="fa fa-check"></i>':'-')?></td>
            <td><?=$g5['set_price_type_value'][$prow['prp_type']].(($prow['prp_type'] == 'middle') ? '('.$prow['prp_pay_no'].')' : '')?></td>
            <td>
                <?php
                $dt_plan_class = '';
                if(strpos($prow['prp_plan_date'],'0000-00-00') === false){
                    $date_alarm = strtotime(get_dayAddDate($prow['prp_plan_date'],-($g5['setting']['set_plan_alarmdaycnt'])));
                    $date_plan = strtotime($prow['prp_plan_date']);
                    $date_today = strtotime(G5_TIME_YMD);
                    if($date_today >= $date_alarm && $date_today <= $date_plan && strpos($prow['prp_issue_date'],'0000-00-00') !== false) $dt_plan_class = 'txt_blueblink';
                    else if($date_today > $date_plan) $dt_plan_class = 'txt_gray';
                }
                echo '<span class="'.$dt_plan_class.'">'.((strpos($prow['prp_plan_date'],'0000-00-00') === false) ? $prow['prp_plan_date'] : '').'</span>';
                ?>
            </td>
            <td>
                <?php
                echo (strpos($prow['prp_issue_date'],'0000-00-00') === false) ? $prow['prp_issue_date'] : '-';
                ?>
            </td>
            <td>
                <?php
                $dt_planpay_class = '';
                $payflag = (strpos($prow['prp_pay_date'],'0000-00-00') === false) ? true : false;
                $date_planpay = strtotime($prow['prp_planpay_date']);
                $date_today2 = strtotime(G5_TIME_YMD);
                if(!$payflag && $date_today2 > $date_planpay){
                    $dt_planpay_class = 'txt_redblink';
                }else if($payflag){
                    $dt_planpay_class = 'txt_green';
                }
                echo '<span class="'.$dt_planpay_class.'">'.((strpos($prow['prp_planpay_date'],'0000-00-00') === false) ? $prow['prp_planpay_date'] : '').'</span>';
                ?>
            </td>
            <td><?=(($prow['prp_pay_date'] != '0000-00-00') ? $prow['prp_pay_date'] : '-')?></td>
            <td><?=$c_mod?></td>
            <td class="td_mngsmall">
                <?=$s_add?>
            </td></tr>
        <?php
                }
            }
        }
        //-----가격레코드가 없을 경우
        else {
    ?>
        <tr class="<?=$bg?>">
            <td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':'none'?>;">
                <input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
            </td>
            <td><?=$row['prj_idx']?></td><!-- 번호 -->
            <td class="td_left"><?=$row['com_name']?></td><!-- 의뢰기업 -->
            <td class="td_left"><?=$row['prj_name']?></td><!-- 공사프로젝트 -->
            <td style="text-align:right;"><?=number_format($row['prp_order_price'])?></td>
            <td style="position:relative;"><?=$row['prj_collect_percent']?>%<div class="per_bar"><div class="bar_in" style="width:<?=$row['prj_collect_percent']?>%;"></div></div></td>
            <td class="td_percent">
                <?php
                $prjpercent = ($row['prj_percent'] < 100 || $row['prj_percent'] != 100) ? 'style="color:blue;"':'';
                $prj_percent = '<span '.$prjpercent.' class="prj_per" prj_idx="'.$row['prj_idx'].'" prj_percent="'.$row['prj_percent'].'">'.$row['prj_percent'].'</span><span '.$prjpercent.'>%</span>';
                echo $prj_percent;
                ?>
            </td><!-- 프로젝트 진행율 -->
            <td style="text-align:right;"><?=(($row['prj_mi_price'] > 0) ? '<span style="color:orange;">'.$row['prj_misu_price'].'</span>' : '<span style="color:#333;">'.$row['prj_misu_price'].'</span>')?></td>
            <td style="text-align:right;color:blue;"><?=$row['prj_gemusu_format'] ?></td>
            <!--td><?php //$row['com_tel']?></td-->
            <td>
                <div class="file_box">
                    <div class="file_in">
                        <?php if(count($row['prj_f_order'])){
                            echo $row['prj_f_order'][0]['file'];
                        }else{ echo '-'; } ?>
                    </div>
                    <div class="file_in">
                        <?php if(count($row['prj_f_contract'])){
                            echo $row['prj_f_contract'][0]['file'];
                        }else{ echo '-'; } ?>
                    </div>
                </div>
            </td>
            <td style="position:relative;"><!-- 코멘트 -->
                <a href="<?php echo G5_BBS_URL?>/board.php?bo_table=price1&ser_prj_idx=<?php echo $row['prj_idx']?>" target="_blank" class="btn_company_comment">
                <?php echo $row['board']['cnt_total_text'].$row['board']['cnt_new_text'] ?>
                </a>

                <?php if($row['prj_content2']){ ?>
                <div class="malp jisi">
                    <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                    <input type="text" class="pungsun_input" value="<?=$row['prj_content2']?>">
                    <div class="pungsun"><?=$row['prj_content2']?></div>
                </div>
                <?php } ?>
            </td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td class="td_mngsmall">
                <?=$s_add?>
            </td>
        </tr>
    <?php
        }
    }
	if ($i == 0)
        echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
    if ($i == $result->num_rows){
    ?>
    <tr style="background:#dedede;">
        <td colspan="5">페이지 (수주미수금 / 계산서발행미수금) 합계</td>
        <td style="color:#f56b08;font-size:1.2em;text-align:right;"><?=number_format($misu1_price)?></td>
        <td style="color:#073ed8;font-size:1.2em;text-align:right;"><?=number_format($misu2_price)?></td>
        <td colspan="13"></td>
    </tr>
    <tr style="background:#dddddd">
    <td colspan="5">전체 (수주미수금 / 계산서발행미수금) 합계</td>
        <td style="color:#f56b08;font-size:1.2em;text-align:right;"><?=$total_suju_misu?></td>
        <td style="color:#073ed8;font-size:1.2em;text-align:right;"><?=$total_gesan_misu?></td>
        <td colspan="13"></td>
    </tr>
    <?php
    }
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <a href="./project_group_price_list_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <?php } ?>
    <?php if(false) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>
<div id="price_fix_box" style="display:none;">
    <h4>현재페이지</h4>
    <table>
        <tbody>
            <tr>
                <th>수주미수금합계</th>
                <th>계산서발생미수금합계</th>
            </tr>
            <tr>
                <td class="td_misu"><?=number_format($misu1_price)?></td>
                <td class="td_geab"><?=number_format($misu2_price)?></td>
            </tr>
        </tbody>
    </table>
    <h4 style="margin-top:4px;">전체</h4>
    <table>
        <tbody>
            <tr>
                <th>수주미수금합계</th>
                <th>계산서발생미수금합계</th>
            </tr>
            <tr>
                <td class="td_misu"><?=$total_suju_misu?></td>
                <td class="td_geab"><?=$total_gesan_misu?></td>
            </tr>
        </tbody>
    </table>
</div>
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
	$(document).on('click','.btn_company_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_company_board = window.open(this_href,'win_company_board','left=100,top=100,width=770,height=650');
        win_company_board.focus();
	});

    //말풍선 클립보드 복사
    $('.malp.jisi > i,.malp.misu > i').on('click',function(){
        var ctext = $(this).siblings('.pungsun_input');
        ctext.select();
        document.execCommand('Copy');
        alert('클립보드 복사완료');
    });

    //프로젝트 진행율 수정
    $('.prj_per').on('click', function(){
        $('.prj_per_input').remove();
        var td = $(this).parent();
        $('<input class="prj_per_input" name="prj_percent" value="'+$(this).attr('prj_percent')+'">').appendTo(td);
        $('.prj_per_input').focus();
        prj_per_event_on();
    });

});

function prj_per_event_on(){
    $('.prj_per_input').on('keyup',function(e){ //esc = 27, enter = 13
        var ask = e.keyCode;
        var prj_idx = $(this).siblings('.prj_per').attr('prj_idx');
        var prj_percent = $(this).siblings('.prj_per').attr('prj_percent');
        
        if(ask == 27){//esc눌렀을때 input박스 제거
            $(this).remove();
            prj_per_event_off();
        }
        else if(ask == 13){
            if(prj_percent == $(this).val()){
                $(this).remove();
                prj_per_event_off();
                return false;
            }
            
            var prj_per_url = '<?=G5_USER_ADMIN_AJAX_URL?>/prj_percent_change.php';
            $.ajax({
                type : "GET",
                url : prj_per_url,
                dataType : "text",
                data : {"prj_idx":prj_idx,"prj_percent":$(this).val()},
                success : function(res){
                    alert(res+'%로 수정되었습니다.');
                    $('.prj_per_input').siblings('.prj_per').attr('prj_percent',res).text(res);
                    $('.prj_per_input').remove();
                    prj_per_event_off();
                },
                error : function(req){
                    alert('Status: ' + req.status + ' \n\rstatusText: ' + req.statusText + ' \n\rresponseText: ' + req.responseText);
                    $('.prj_per_input').remove();
                    prj_per_event_off();
                }
            });
        }
        else{
            var num = $(this).val().replace(/[^0-9]/g,"");
            if(num.charAt(0) == '0' && num.length > 1) num = num.substring(1);
            num = (num == '') ? '0' : num;
            if(Number(num) > 100) num = 100;
            $(this).val(num);
        }
    });
}
function prj_per_event_off(){
    $('.prj_per_input').off('keyup');
}

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