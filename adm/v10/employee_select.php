<?php
// 직원검색은 mb_level=6이상 직원을 검색하는 페이지입니다. (법인별 분리)
// 회원검색(회원 및 업체회원)은 member_select.php 페이지에서 검색합니다.
// 호출 페이지들
// /adm/v10/company_form.php: (고객)업체 추가시 영업자검색 (2개 버튼)
// /adm/v10/share_rate_form.php: 직원아이디 추가 시 직원검색
// /adm/v10/order_cart_form.php: 주문서 작성 페이지에서 영업자 검색
// /adm/v10/order_list.php: 매출내역 리스트에서 영업자 선택
// /adm/v10/item_share_setting.php: 분배추가시 영업자검색
// /adm/v10/site_list.php: 각 담당자 찾기 (원고접수자, 기획자, 디자이너, 개발자)
// /adm/v10/sales_adjust.php: 매출조정시 담당자 선택
include_once('./_common.php');

if($member['mb_level']<6)
    alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['member_table']} ";

// 디폴트 검색기준
$sql_where = " WHERE mb_leave_date = '' AND mb_level >= 6 ";

// 운영권한을 가진 사람
if($member['mb_manager_yn']) {
    $sql_where .= " AND mb_level <= 8 ";
}
// 소속 조직 조건 레벨 조건이 특별히 없으면 직원인 경우로 봄
// 팀장이상 vs 팀원, 팀원은 자기 팀(한단계 위조직)만 보이고, 팀장 이상은 자기 조직 하부가 다 보임)
else if(get_dept_idxs()) {
    if($member['mb_id'] != 'iljung'){
        $sql_where .= " AND mb_2 IN (".get_dept_idxs().") ";
    }else{
        $sql_where .= " AND mb_level < 8 ";
    }
}

// 추가 검색 조건
if($mb_where) {
    $sql_where .= " AND ".stripslashes($mb_where)." ";
}

// 검색어
if($sch_word) {
    $sch_word   = clean_xss_tags($_GET['sch_word']);
    $sql_where .= " AND (mb_name LIKE '%$sch_word%' OR mb_id LIKE '%$sch_word%') ";
}
// 정렬기준
$sql_order = " ORDER BY mb_2 DESC,mb_datetime DESC ";


// 테이블의 전체 레코드수
$sql = " SELECT COUNT(*) AS cnt " . $sql_common . $sql_where;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


// 리스트 쿼리
$sql = "SELECT *
            " . $sql_common . $sql_where . $sql_order . "
            LIMIT $from_record, $rows
";
//echo $sql.'<br>';
$result = sql_query($sql,1);


$g5['title'] = '담당자 검색 ('.number_format($total_count).')';
include_once('./_head.sub.php');


$qstr1 = 'frm='.$frm.'&tar1='.$tar1.'&tar2='.$tar2.'&tar3='.$tar3.'&file_name='.$file_name.'&mb_where='.stripslashes($mb_where).'&sch_word='.urlencode($sch_word);
?>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <div class="local_desc01 local_desc">
        <p>
            검색할 회원의 이름(또는 아이디)을 입력하세요.
        </p>
    </div>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="tar1" value="<?php echo $_GET['tar1']; ?>">
    <input type="hidden" name="tar2" value="<?php echo $_GET['tar2']; ?>">
    <input type="hidden" name="tar3" value="<?php echo $_GET['tar3']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_GET['file_name']; ?>"><!-- 파일명 -->
    <input type="hidden" name="mb_where" value="<?php echo stripslashes($mb_where); ?>"><!-- 추가조건 -->
    <input type="hidden" name="sit_idx" value="<?php echo $_GET['sit_idx']; ?>"><!-- 제작관리변수 -->

    <div id="scp_list_find">
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required size="20">
        <input type="submit" value="검색" class="btn_frmline">
    </div>
    
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">회원명</th>
            <th scope="col">아이디</th>
            <th scope="col">휴대폰</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            
            // 추가 정보 추출 (업체정보, 최근1개만(limit 1) )
            $com = get_table_meta('company','mb_id',$row['mb_id']);
            // 업체인 경우
            if($com['com_idx']) {
                // 최근업체정보 - 업체메일, 업체측담당자명, 업체측담당자휴대폰, 대표자, 업체전화, 우편번호, 주소1, 주소2, 주소3, 지번
                $mb_values = ",'".$com['com_email']."','".trim($com['com_manager'])."','".$com['com_manager_hp']."','".$com['com_president']."','".$com['com_tel']."','".$com['com_zip1']."','".$com['com_zip2']."','".$com['com_addr1']."','".$com['com_addr2']."','".$com['com_addr3']."','".$com['com_addr_jibeon']."'";

                // 업체명(최근 포함 여러업체가 있는 경우는 다 리스팅)
                $sql2 = " SELECT * FROM {$g5['company_table']} WHERE mb_id = '".$row['mb_id']."' ";
                $rs2 = sql_query($sql2,1);
                for($j=0; $row2=sql_fetch_array($rs2); $j++) {
                    $row['com_names'] .= $row2['com_name'].'<br>';
                }
            }
            // 일반회원인 경우
            else {
                // 메일, 업체측담당자명, 업체측담당자휴대폰, 대표자, 업체전화, 우편번호, 주소1, 주소2, 주소3, 지번
                $mb_values = ",'".$row['mb_email']."','".trim($row['mb_name'])."','".$row['mb_hp']."','".$row['mb_name']."','".$row['mb_hp']."','".$row['mb_zip1']."','".$row['mb_zip2']."','".$row['mb_addr1']."','".$row['mb_addr2']."','".$row['mb_addr3']."','".$row['mb_addr_jibeon']."'";
            }

            // 추천인(담당자)이 있는 경우
            if($row['mb_recommend']) {
                $row['mb_recommends'] = explode(",",preg_replace("/\s+/", "", $row['mb_recommend']));
                $sql2 = " SELECT * FROM {$g5['member_table']} WHERE mb_id IN ('".implode("','",$row['mb_recommends'])."') ";
                $rs2 = sql_query($sql2,1);
                for($j=0; $row2=sql_fetch_array($rs2); $j++) {
                    $row['mb_sales_names'] .= $row2['mb_name'].',';
                }
                $row['mb_sales_names'] = substr($row['mb_sales_names'],0,-1);
            }
        ?>
        <tr>
            <td class="td_left"><?php echo $row['mb_name']; ?></td>
            <td class="scp_target_code"><?php echo $row['mb_id']; ?></td>
            <td class="scp_target_code">****-<?php echo substr($row['mb_hp'],-4); ?></td><!-- 휴대폰 -->
            <td class="td_mng td_mng_s"><button type="button" class="btn btn_03" onclick="put_value('<?php echo trim($row['mb_id']); ?>'
                ,'<?php echo trim($row['mb_name']);?>'
                ,'<?php echo $row['mb_2']; ?>','<?php echo trim($row['mb_nick']);?>'<?php echo $mb_values;?>);">선택</button>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="5" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : 5, $page, $total_page, '?'.$qstr1.'&amp;page='); ?>

    <div class="btn_confirm01 btn_confirm win_btn">
        <button type="button" onclick="window.close();" class="btn btn-secondary">닫기</button>
    </div>
</div>

<script>
function put_value(val1,val2,val3,val4,val_email,val_manager,val_hp,val_president,val_tel,val_zip1,val_zip2,val_addr1,val_addr2,val_addr3,val_addr_jibeon) {
    <?php
    // 고객정보 추가(수정)페이지(file_name=company_form)인 경우는 회원을 추가한다.
    if($file_name=='company_form') {
        ?>
        $('.td_sales', opener.document).append('<div class="div_salesman" style="margin-bottom:5px;">'
                            + '<input readonly type="hidden" name="cms_idx[]" />'
                            + '<input readonly type="hidden" name="cms_status[]" value="ok" />'
                            + '<input readonly type="text" placeholder="영업자ID" name="mb_id_saler[]" value="'+val1+'"'
                                + 'required class="frm_input required" readonly size="15" minlength="3" maxlength="30" style="background-color:#dadada !important;">'
                            + '<input readonly type="text" placeholder="영업자명" name="mb_name_saler[]" value="'+val2+'"'
                                + ' class="frm_input" style="background-color:#dadada;margin-left:4px;margin-right:4px;" size="15" minlength="3" maxlength="30">'
                            + '[<span class="span_saler_delete" style="cursor:pointer;">삭제</span>]'
                            + '</div>'
        );
        <?php
    }
    // 사원 추가인 경우
    else if($file_name=='employee_form') {
        ?>
            window.opener.document.getElementById('reg_mb_id').value = val1;
            window.opener.document.getElementById('wr_6').value = val1;
            window.opener.document.getElementById('mb_name').value = val2;
            window.opener.document.getElementById('wr_5').value = val2;
            window.opener.document.getElementById('reg_mb_nick').value = val4;
			window.opener.document.getElementById('mb_tel').value = val_tel;
			window.opener.document.getElementById('reg_mb_email').value = val_email;
			window.opener.document.getElementById('reg_mb_hp').value = val_hp;
			window.opener.document.getElementById('mb_zip').value = val_zip1+val_zip2;
			window.opener.document.getElementById('mb_addr1').value = val_addr1;
			window.opener.document.getElementById('mb_addr2').value = val_addr2;
			window.opener.document.getElementById('mb_addr3').value = val_addr3;
        <?php
    }
    // 제작관리인 경우
    else if($file_name=='site_list') {
        // 원고접수자, 기획자, 디자이너, 개발자
        if($tar1=='mb_name_wongo'||$tar1=='mb_name_plan'||$tar1=='mb_name_designer'||$tar1=='mb_name_developer') {
        ?>
            var tar1 = $('input[name=tar1]').val();
            var sit_idx = $('input[name=sit_idx]').val();
            // 원고접수자 실시간 변경
            //-- 디버깅 Ajax --//
            $.ajax({
            	url:g5_user_admin_ajax_url+'/member.json.php',
            	data:{"aj":"s1","aj_mb_id":val1,"sit_idx":sit_idx,"tar1":tar1},
            	async:false,dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
            //$.getJSON(g5_user_admin_ajax_url+'/member.json.php',{"aj":"s1","aj_mb_id":val1,"sit_idx":sit_idx,"tar1":tar1},function(res) {
                console.log(res);
                var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                
                if(res.result == true) {
                    alert(res.msg);
                }
                else {
                    alert(res.msg);
                }
                }, error:this_ajax_error	//<-- 디버깅 Ajax --//
            });
            
            <?php if($tar1=='mb_name_wongo') { ?>
                $( "#mb_name_wongo_<?=$_REQUEST['sit_idx']?>", opener.document ).text( val2 );
            <?php } else if($tar1=='mb_name_plan') { ?>
                $( "#mb_name_plan_<?=$_REQUEST['sit_idx']?>", opener.document ).text( val2 );
            <?php } else if($tar1=='mb_name_designer') { ?>
                $( "#mb_name_designer_<?=$_REQUEST['sit_idx']?>", opener.document ).text( val2 );
            <?php } else if($tar1=='mb_name_developer') { ?>
                $( "#mb_name_developer_<?=$_REQUEST['sit_idx']?>", opener.document ).text( val2 );
            <?php } ?>
            
        <?php
        }
    }
    // 디폴트
    else {
        // 타켓폼 변수가 존재하면..
        if($frm) {
        ?>
            // 폼이 존재하면
            if(window.opener.document.<?=$frm?> != undefined) {
                var f = window.opener.document.<?=$frm?>;
                f.<?=$tar1?>.value = val1;
                // 두번째 변수는 input 타입이 아닐 수도 있음
                <?php if($tar2) { ?>
                if( window.opener.document.getElementById('<?=$tar2?>').tagName == 'INPUT' || window.opener.document.getElementById('<?=$tar2?>').tagName == 'SELECT' )
                    window.opener.document.getElementById('<?=$tar2?>').value = val2;
                else
                    window.opener.document.getElementById('<?=$tar2?>').innerHTML = val2;
                <?php } ?>
                <?php if($tar3) { ?>f.<?=$tar3?>.value = val3; <?php } ?>
            }
        <?php
        }
        // 타켓폼 변수가 없으면 아이디로 검색
        else {
        ?>
            if( $( '#<?=$tar1?>', opener.document ).length ) {
                $( '#<?=$tar1?>', opener.document ).val( val1 );
            }
            // 두번째 변수는 input 타입이 아닐 수도 있음
            <?php if($tar2) { ?>
            if( window.opener.document.getElementById('<?=$tar2?>').tagName == 'INPUT' || window.opener.document.getElementById('<?=$tar2?>').tagName == 'SELECT' )
                window.opener.document.getElementById('<?=$tar2?>').value = val2;
            else
                window.opener.document.getElementById('<?=$tar2?>').innerHTML = val2;
            <?php } ?>
            // 세번째 변수도 input 타입이 아닐 수도 있음
            <?php if($tar3) { ?>
            if( window.opener.document.getElementById('<?=$tar3?>').tagName == 'INPUT' || window.opener.document.getElementById('<?=$tar3?>').tagName == 'SELECT' )
                window.opener.document.getElementById('<?=$tar3?>').value = val2;
            else
                window.opener.document.getElementById('<?=$tar3?>').innerHTML = val2;
            <?php } ?>
        <?php
        }
    }
    ?>

    window.close();
}
</script>

<?php
include_once('./_tail.sub.php');
?>