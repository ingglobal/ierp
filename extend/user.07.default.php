<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// perhaps mb_level 4 mamber are into adm page, make him to go v10/index page.
if($member['mb_level']<7 && $g5['dir_name'] == 'adm' && $g5['file_name']=='index') {
    // print_r3($g5['dir_name']);
    // print_r3('you are in prohibited page.');
    goto_url(G5_USER_ADMIN_URL);
}

/*
// 분류(카테고리)
$set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_taxonomies']));
foreach ($set_values as $set_value) {
	list($key, $value) = explode('=', trim($set_value));
    if($key&&$value) {
        $g5['set_taxonomies'][$key] = $value.' ('.$key.')';
        $g5['set_taxonomies_value'][$key] = $value;
        $g5['set_taxonomies_value_key'][$value] = $key;
        $g5['set_taxonomies_radios'] .= '<label for="set_taxonomies_'.$key.'" class="set_taxonomies"><input type="radio" id="set_taxonomies_'.$key.'" name="set_taxonomies" value="'.$key.'">'.$value.'</label>';
        $g5['set_taxonomies_checkboxs'] .= '<label for="set_taxonomies_'.$key.'" class="set_taxonomies"><input type="hidden" name="set_taxonomies_'.$key.'" value=""><input type="checkbox" id="set_taxonomies_'.$key.'" value="'.$key.'">'.$value.'</label>';
        $g5['set_taxonomies_options'] .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
        $g5['set_taxonomies_options_value'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    }
}
unset($set_values);unset($set_value);
*/

// 모든 분류 추출, 로딩속도 개선을 위해 캐시 처리, 기본적으로 12시간 (자주 안 바뀜)
$term_cache_time = 12;
if( is_array($g5['set_taxonomies_value']) ) {
    foreach ($g5['set_taxonomies_value'] as $key=>$value) {
        // print_r3($key.'/'.$value);
        // 캐시 파일이 없거나 캐시 시간을 초과했으면 (재)생성
        $term_cache_file = G5_DATA_PATH.'/cache/term-'.$key.'.php';
        @$term_cache_filetime = filemtime($term_cache_file);
        if( !file_exists($term_cache_file) || $term_cache_filetime < (G5_SERVER_TIME - 3600*$term_cache_time) ) {
            @unlink($term_cache_file);
            
            $g5[$key] = array();
            // 조직구조 추출
            $sql = "SELECT 
                        trm_idx term_idx
                        , GROUP_CONCAT(name) term_name
                        , trm_name2 trm_name2
                        , trm_content trm_content
                        , trm_more trm_more
                        , trm_status trm_status
                        , GROUP_CONCAT(cast(depth as char)) depth
                        , GROUP_CONCAT(up_idxs) up_idxs
                        , SUBSTRING_INDEX(SUBSTRING_INDEX(up_idxs, ',', GROUP_CONCAT(cast(depth as char))),',',-1) up1st_idx
                        , SUBSTRING_INDEX(up_idxs, ',', 1) uptop_idx
                        , GROUP_CONCAT(up_names) up_names
                        , GROUP_CONCAT(down_idxs) down_idxs
                        , GROUP_CONCAT(down_names) down_names
                        , REPLACE(GROUP_CONCAT(down_idxs), CONCAT(SUBSTRING_INDEX(GROUP_CONCAT(down_idxs), ',', 1),','), '') down_idxs2
                        , REPLACE(GROUP_CONCAT(down_names), CONCAT(SUBSTRING_INDEX(GROUP_CONCAT(down_names), ',', 1),','), '') down_names2
                        , leaf_node_yn leaf_node_yn
                        , SUM(table_row_count) table_row_count
                    FROM (	(
                            SELECT term.trm_idx
                                , CONCAT( REPEAT('   ', COUNT(parent.trm_idx) - 1), term.trm_name) AS name
                                , term.trm_name2
                                , term.trm_content
                                , term.trm_more
                                , term.trm_status
                                , (COUNT(parent.trm_idx) - 1) AS depth
                                , GROUP_CONCAT(cast(parent.trm_idx as char) ORDER BY parent.trm_left) up_idxs
                                , GROUP_CONCAT(parent.trm_name ORDER BY parent.trm_left SEPARATOR ' > ') up_names
                                , NULL down_idxs
                                , NULL down_names
                                , (CASE WHEN term.trm_right - term.trm_left = 1 THEN 1 ELSE 0 END ) leaf_node_yn
                                , 0 table_row_count
                                , term.trm_left
                                , 1 sw
                            FROM {$g5['term_table']} AS term,
                                    {$g5['term_table']} AS parent
                            WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
                                AND term.trm_taxonomy = '".$key."'
                                AND parent.trm_taxonomy = '".$key."'
                                AND term.trm_status in ('ok','hide') AND parent.trm_status in ('ok','hide')
                                
                                GROUP BY term.trm_idx
                            ORDER BY term.trm_left
                            )
                        UNION ALL
                            (
                            SELECT parent.trm_idx
                                , NULL name
                                , term.trm_name2
                                , term.trm_content
                                , term.trm_more
                                , term.trm_status
                                , NULL depth
                                , NULL up_idxs
                                , NULL up_names
                                , GROUP_CONCAT(cast(term.trm_idx as char) ORDER BY term.trm_left) AS down_idxs
                                , GROUP_CONCAT(term.trm_name ORDER BY term.trm_left SEPARATOR ',') AS down_names
                                , (CASE WHEN parent.trm_right - parent.trm_left = 1 THEN 1 ELSE 0 END ) leaf_node_yn
                                , SUM(term.trm_count) table_row_count
                                , parent.trm_left
                                , 2 sw
                            FROM {$g5['term_table']} AS term
                                    , {$g5['term_table']} AS parent
                            WHERE term.trm_left BETWEEN parent.trm_left AND parent.trm_right
                                AND term.trm_taxonomy = '".$key."'
                                AND parent.trm_taxonomy = '".$key."'
                                AND term.trm_status in ('ok','hide') AND parent.trm_status in ('ok','hide')
                                
                            GROUP BY parent.trm_idx
                            ORDER BY parent.trm_left
                            ) 
                        ) db_table
                    GROUP BY trm_idx
                    ORDER BY trm_left
            ";
            $result = sql_query($sql,1);
            //echo $sql;
            for($i=0; $row=sql_fetch_array($result); $i++) {
                $g5[$key][$i] = $row;
				//-- 지역 키값
                $g5[$key.'_key'][$row['term_idx']] = $row;
                //-- 하위 카테고리 전체
                $g5[$key.'_down_idxs'][$row['term_idx']] = $row['down_idxs'];
                //-- 하위 카테고리 전체 (자기 빼고 하위만)
                $g5[$key.'_down_idxs2'][$row['term_idx']] = $row['down_idxs2'];
                //-- 하위 카테고리 이름 전체
                $g5[$key.'_down_names2'][$row['term_idx']] = $row['down_names2'];
                //-- 상위 카테고리 전체
                $g5[$key.'_up_idxs'][$row['term_idx']] = $row['up_idxs'];
                //-- 상위 카테고리 이름 전체
                $g5[$key.'_up_names'][$row['term_idx']] = $row['up_names'];
                //-- 부서 이름
                $g5[$key.'_name'][$row['term_idx']] = trim($row['term_name']);
                //-- 조직코드 정렬 우선순위
                $g5[$key.'_sort'][$row['term_idx']] = $i;
                //-- 바로 상위 카테고리 idx
                $g5[$key.'_up1_idx'][$row['term_idx']] = $row['up1st_idx'];
                //-- 최 상위 카테고리 idx
                $g5[$key.'_uptop_idx'][$row['term_idx']] = $row['uptop_idx'];
                //-- 카테고리 lefa_node 여부
                $g5[$key.'_lefa_yn'][$row['term_idx']] = $row['leaf_node_yn'];
                
                // 추가 부분 unserialize
                $unser = unserialize(stripslashes($row['trm_more']));
                if( is_array($unser) ) {
                    foreach ($unser as $key1=>$value1) {
                        $row[$key1] = htmlspecialchars($value1, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
                    }    
                }
                // 삭제조직코드 (공백 제거)
                if($row['trash_idxs'])
                    $g5[$key.'_trash_idxs'][$row['term_idx']] = ','.preg_replace("/\s+/", "", $row['trash_idxs']);
                
            }
			
            // 캐시파일 생성 (다음 접속을 위해서 생성해 둔다.)
            $handle = @fopen($term_cache_file, 'w');
            $term_content = "<?php\n";
            $term_content .= "if (!defined('_GNUBOARD_')) exit;\n";
            $term_content .= "\$g5['".$key."']=".var_export($g5[$key], true).";\n";
            $term_content .= "\$g5['".$key."_key']=".var_export($g5[$key.'_key'], true).";\n";
            $term_content .= "\$g5['".$key."_down_idxs']=".var_export($g5[$key.'_down_idxs'], true).";\n";
            $term_content .= "\$g5['".$key."_down_names']=".var_export($g5[$key.'_down_names'], true).";\n";
            $term_content .= "\$g5['".$key."_down_idxs2']=".var_export($g5[$key.'_down_idxs2'], true).";\n";
            $term_content .= "\$g5['".$key."_down_names2']=".var_export($g5[$key.'_down_names2'], true).";\n";
            $term_content .= "\$g5['".$key."_up_idxs']=".var_export($g5[$key.'_up_idxs'], true).";\n";
            $term_content .= "\$g5['".$key."_up_names']=".var_export($g5[$key.'_up_names'], true).";\n";
            $term_content .= "\$g5['".$key."_name']=".var_export($g5[$key.'_name'], true).";\n";
            $term_content .= "\$g5['".$key."_sort']=".var_export($g5[$key.'_sort'], true).";\n";
            $term_content .= "\$g5['".$key."_up1_idx']=".var_export($g5[$key.'_up1_idx'], true).";\n";
            $term_content .= "\$g5['".$key."_uptop_idx']=".var_export($g5[$key.'_uptop_idx'], true).";\n";
            $term_content .= "\$g5['".$key."_lefa_yn']=".var_export($g5[$key.'_lefa_yn'], true).";\n";
            $term_content .= "\$g5['".$key."_trash_idxs']=".var_export($g5[$key.'_trash_idxs'], true).";\n";
            $term_content .= "?>";
            @fwrite($handle, $term_content);
            @fclose($handle);
        }
        // 캐시 파일 존재한다면..
        else {
            // 캐시 파일 내부에 배열로 department 변수 설정되어 있음
            include($term_cache_file);
        }

        // 분류 카테고리 옵션 생성 (다운idxs 포함해서 변수 넘길 때)
        for($i=0; $i<sizeof($g5[$key]); $i++) {
            ${$key.'_select_options'} .= '<option value="'.$g5[$key][$i]['down_idxs'].'">'.$g5[$key][$i]['up_names'].'</option>';	// value 모든 하위값 다 가지고 있어야 함
            ${$key.'_form_options'} .= '<option value="'.$g5[$key][$i]['term_idx'].'">'.$g5[$key][$i]['up_names'].'</option>';		// 수정(등록) 시는 특정값 설정되어야 함
            ${$key.'_form_depth0_options'} .= ($g5[$key][$i]['depth']==0) ? '<option value="'.$g5[$key][$i]['term_idx'].'">'.$g5[$key][$i]['up_names'].'</option>' : '';	// 최상위 단계만
            ${$key.'_radio_options'} .= '<label for="set_'.$key.'_idx_'.$g5[$key][$i]['term_idx'].'" class="set_'.$key.'_idx"><input type="radio" id="set_'.$key.'_idx_'.$g5[$key][$i]['term_idx'].'" name="set_'.$key.'_idx" value="'.$g5[$key][$i]['term_idx'].'">'.$g5[$key][$i]['term_name'].'</label>';
            ${$key.'_checkbox_options'} .= '<label for="set_'.$key.'_idx_'.$g5[$key][$i]['term_idx'].'" class="set_'.$key.'_idx"><input type="checkbox" id="set_'.$key.'_idx_'.$g5[$key][$i]['term_idx'].'" name="set_'.$key.'_idx[]" value="'.$g5[$key][$i]['term_idx'].'">'.$g5[$key][$i]['term_name'].'</label>';
        }
        
        
    }    
}


// 게시판 설정들
// $g5['board'] 배열 변수를 생성합니다.
$g5['setting_bo_tables'] = array();
for ($j=0;$j<sizeof($g5['setting_bo_tables']);$j++) {
    $key = $g5['setting_bo_tables'][$j];
    //echo $key.'<br>';
    // 캐시 파일이 없거나 캐시 시간을 초과했으면 (재)생성
    $bsetting_cache_file = G5_DATA_PATH.'/cache/board-setting-'.$key.'.php';
    @$bsetting_cache_filetime = filemtime($bsetting_cache_file);
    if( !file_exists($bsetting_cache_file) || $bsetting_cache_filetime < (G5_SERVER_TIME - 3600*$term_cache_time) ) {
        @unlink($bsetting_cache_file);
        
        $g5['board'][$key] = array();
        $board = get_board_db($key, true);
        $bo_subject = get_text($board['bo_subject']);
        $tmp_write_table = $g5['write_prefix'] . $key; // 게시판 테이블 전체이름
        $sql = " SELECT * FROM {$tmp_write_table} WHERE wr_is_comment = 0 ORDER BY wr_num ";
        $result = sql_query($sql,1);
        for ($i=0; $row = sql_fetch_array($result); $i++) {
            $row['mta'] = get_meta('board/'.$key,$row['wr_id']);
            if(is_array($row['mta'])) {$row=array_merge($row,$row['mta']);}
            try {
                unset($row['wr_password']);     //패스워드 저장 안함( 아예 삭제 )
            } catch (Exception $e) {}

            $g5['board'][$key][$i] = get_list($row, $board, 'basic', 250);
            $g5['board'][$key][$i]['bo_table'] = $key;
            $g5['board'][$key.'_name'][$row['wr_id']] = trim($row['wr_subject']);  // 사업체명
            $g5['board'][$key.'_email'][$row['wr_id']] = trim($row['wr_email']);  // 이메일
            $g5['board'][$key.'_banks'][$row['wr_id']] = trim($row['wr_banks']);  // 계좌번호
            $g5['board'][$key.'_sried'] = get_serialized($row['wr_9']);
            if( is_array($g5['board'][$key.'_sried']) ) {
                foreach($g5['board'][$key.'_sried'] as $key2=>$value2) {
                    //echo $key.' | '.$value.'<br>';
                    $g5['board'][$key.'_'.$key2][$row['wr_id']] = $value2;
                }
            }
            
        }
		
        // 캐시파일 생성 (다음 접속을 위해서 생성해 둔다.)
        $handle = fopen($bsetting_cache_file, 'w');
        $bset_content = "<?php\n";
        $bset_content .= "if (!defined('_GNUBOARD_')) exit;\n";
        $bset_content .= "\$g5['board']['".$key."']=".var_export($g5['board'][$key], true).";\n";
        $bset_content .= "\$g5['board']['".$key."_name']=".var_export($g5['board'][$key.'_name'], true).";\n";
        $bset_content .= "\$g5['board']['".$key."_email']=".var_export($g5['board'][$key.'_email'], true).";\n";
        $bset_content .= "\$g5['board']['".$key."_banks']=".var_export($g5['board'][$key.'_banks'], true).";\n";
        if( is_array($g5['board'][$key.'_sried']) ) {
            foreach($g5['board'][$key.'_sried'] as $key2=>$value2) {
                $bset_content .= "\$g5['board']['".$key."_".$key2."']=".var_export($g5['board'][$key.'_'.$key2], true).";\n";
            }
        }
        $bset_content .= "?>";
        fwrite($handle, $bset_content);
        fclose($handle);
    }
    // 캐시 파일 존재한다면..
    else {
        // 캐시 파일 내부에 배열로 department 변수 설정되어 있음
        include($bsetting_cache_file);
    }
    
    // 분류 카테고리 옵션 생성 (다운idxs 포함해서 변수 넘길 때)
    for($i=0; $i<sizeof($g5['board'][$key]); $i++) {
        $g5['board'][$key.'_form_options'] .= '<option value="'.$g5['board'][$key][$i]['wr_id'].'">'.$g5['board'][$key][$i]['wr_subject'].'</option>';
        $g5['board'][$key.'_radio_options'] .= '<label for="set_'.$key.'_idx_'.$g5['board'][$key][$i]['wr_id'].'" class="set_'.$key.'_idx"><input type="radio" id="set_'.$key.'_idx_'.$g5['board'][$key][$i]['wr_id'].'" name="set_'.$key.'_idx" value="'.$g5['board'][$key][$i]['wr_id'].'">'.$g5['board'][$key][$i]['wr_subject'].'</label>';
        $g5['board'][$key.'_checkbox_options'] .= '<label for="set_'.$key.'_idx_'.$g5['board'][$key][$i]['wr_id'].'" class="set_'.$key.'_idx"><input type="checkbox" id="set_'.$key.'_idx_'.$g5['board'][$key][$i]['wr_id'].'" name="set_'.$key.'_idx[]" value="'.$g5['board'][$key][$i]['wr_id'].'">'.$g5['board'][$key][$i]['wr_subject'].'</label>';
    }   
    
}
//print_r2($g5['board']);
//exit;   

$mms_code_file = G5_DATA_PATH.'/cache/mms-code.php';
if( file_exists($mms_code_file) ) {
    include($mms_code_file);
}
$mms_setting_file = G5_DATA_PATH.'/cache/mms-setting.php';
if( file_exists($mms_setting_file) ) {
    include($mms_setting_file);
}



// 조직별 접근가능 상품분류 카테고리들 (나의 제일 상위 조직에 대한 접근가능 상품분류 코드들입니다.)
$sql = "	SELECT trm_idx, tmr_status
				, GROUP_CONCAT(tmr_db_id ORDER BY tmr_db_id) AS ca_ids
			FROM {$g5['term_relation_table']}
			WHERE tmr_db_table = 'department' AND tmr_db_key = 'shop_category'
				AND tmr_status = 'ok'
			GROUP BY trm_idx
";
//print_r3($sql).'<br>';
$result = sql_query($sql,1);
for($i=0; $row=sql_fetch_array($result); $i++) {
	$department_shop_categorys[$row['trm_idx']] = $row['ca_ids'];
	//echo $row['trm_idx'].'. '.$g5['department_name'][$row['trm_idx']].' - '.$row['ca_ids'].'<br>';
}
//print_r2($department_shop_categorys);

// 수퍼관리자인 경우의 추가 설정
if($member['mb_level']>=9) {
    //운영권한, 정산권한 확보
    $member['mb_manager_yn'] = 1;
    $member['mb_account_yn'] = 1;
    $member['mb_firm_yn'] = 1;
}
if($member['mb_manager_yn']&&$member['mb_account_yn'])
    $member['mb_manager_account_yn'] = $member['mb_manager_and_account'] = 1;
if($member['mb_manager_yn']||$member['mb_account_yn'])
    $member['mb_manager_or_account'] = 1;
if($member['mb_manager_yn']&&$member['mb_account_yn']&&$member['mb_firm_yn'])
    $member['mb_allauth_yn'] = 1;
// 운영권한 없는 사람들의 dom display
if(!$member['mb_manager_yn']&&!$member['mb_account_yn']) {
    $member['mb_manager_account_display'] = 'display:none;';
}

$super_adm_arr = explode(',',$g5['setting']['set_mng_accounting']);
$super_mng_arr = explode(',',$g5['setting']['set_mng_operate']);
$super_sales_arr = explode(',',$g5['setting']['set_auth_sales']);

$super_admin = ($member['mb_6'] == '1' || $member['mb_id'] == 'super' || in_array($member['mb_id'],$super_adm_arr)) ? true : false;
$super_ceo_admin = ($member['mb_level'] >= 8) ? true : false;
$super_mng_admin = ($super_admin || in_array($member['mb_id'],$super_mng_arr)) ? true : false;
$super_sales_admin = ($super_admin || in_array($member['mb_id'],$super_sales_arr)) ? true : false;

// 기본 업체 할당
// 디폴트 업체 (운영권한이 있으면 디폴트 com_idx=1, 일반업체담당자는 자기 업체 com_idx)
if($member['mb_manager_yn']) {
    $member['com_idx'] = $g5['setting']['set_com_idx'];
}
else {
    $sql = "SELECT com_idx
            FROM {$g5['company_member_table']}
            WHERE mb_id = '".$member['mb_id']."' AND cmm_status = 'ok'
            ORDER BY cmm_idx DESC
            LIMIT 1
            ";
    $row = sql_fetch($sql,1);
    $member['com_idx'] = $row['com_idx'];
}

// 데이타그룹, 데이터그룹별 그래프 초기값도 추출
$set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_data_group']));
foreach ($set_values as $set_value) {
	list($key, $value) = explode('=', trim($set_value));
	$g5['set_data_group'][$key] = $value.' ('.$key.')';
	$g5['set_data_group_value'][$key] = $value;
	$g5['set_data_group_radios'] .= '<label for="set_data_group_'.$key.'" class="set_data_group"><input type="radio" id="set_data_group_'.$key.'" name="set_data_group" value="'.$key.'">'.$value.'</label>';
	$g5['set_data_group_options'] .= '<option value="'.trim($key).'">'.trim($value).' ('.trim($key).')</option>';
	$g5['set_data_group_value_options'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    
    // 데이타 그룹별 그래프 디폴트값 추출, $g5['set_graph_run']['default1'], $g5['set_graph_err']['default4'] 등과 같은 배열값으로 디폴트값 추출됨
    $set_values1 = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_graph_'.$key]));
    for($i=0;$i<sizeof($set_values1);$i++) {
        $g5['set_graph_'.$key]['default'.$i] = $set_values1[$i];
    }
    unset($set_values1);unset($set_value1);
}
unset($set_values);unset($set_value);

// 단위별(분,시,일,주,월,년) 초변환수
// 첫번째 변수 = 단위별 초단위 전환값
// 두번째 변수 = 종료일(or시작일)계산시 선택단위, 0이면 기존 선택된 단위값, 아니면 해당숫자 
$seconds = array(
    "daily"=>array(86400,1)
    ,"weekly"=>array(604800,1)
    ,"monthly"=>array(2592000,1)
    ,"yearly"=>array(31536000,1)
    ,"minute"=>array(60,0)
    ,"second"=>array(1,0)
);
$seconds_text = array(
    "86400"=>'일간'
    ,"604800"=>'주간'
    ,"2592000"=>'월간'
    ,"31536000"=>'년간'
    ,"60"=>'분단위'
    ,"1"=>'초단위'
);

// default data_url
$g5['set_data_url'] = 'icmms.co.kr';

// 간트챠트진행율표시 상태값들
$g5['setting']['set_prs_rate_display_array'] = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_prs_rate_display']));

// 팀구분 배열 (영업팀, 시스템팀, 회계팀)
$g5['set_team_sales_array'] = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_team_sales']));
$g5['set_team_system_array'] = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_team_system']));
$g5['set_team_account_array'] = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_team_account']));

// 수입지출 금액타입
$g5['setting']['set_price_type2_array'] = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_price_type2']));
foreach ($g5['set_price_type_value'] as $key => $value) {
    if( in_array($key,$g5['setting']['set_price_type2_array']) ) {
        // echo $key.'='.$value.PHP_EOL;
        $g5['set_price_type2'][$key] = $value.' ('.$key.')';
        $g5['set_price_type2_value'][$key] = $value;
        $g5['set_price_type2_options'] .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
        $g5['set_price_type2_options_value'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
    }
}


// 매입 금액타입
//$g5['setting']['set_purchase_type_array'] = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_purchase_type']));
//print_r3($g5['setting']['set_purchase_type_array']);

// 매입업체 추출
$sql = "	SELECT com_idx, com_name
			FROM {$g5['company_table']}
			WHERE com_class = 'buyer'
				AND com_status NOT IN ('trash','delete')
			ORDER BY com_idx
";
$result = sql_query($sql,1);
for($i=0; $row=sql_fetch_array($result); $i++) {
	$g5['set_buyer_value_options'] .= '<option value="'.$row['com_idx'].'">'.$row['com_name'].'</option>';;
}
//print_r2($department_shop_categorys);



// A=B 구조는 더 이상 정의하지 않아도 됩니다.
// user.03.default.php 쪽에 모두 한꺼번에 정의했습니다.
?>