<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$file_count = (int)$board['bo_upload_count'];

if ($w == '') {
    $write['ct_info'] = '선택된 상품이 존재하지 않습니다. 상품을 선택하세요.';

}
else if ($w == 'u') {

    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];
    
    // For a simple view, link and file are showing only if existed.
    $link_display = (!$write['wr_link1']&&!$write['wr_link2']) ? "none":"block";
    $file_display = (!$file['count']) ? "none":"block";
    
    // 고객(업체) 정보 추출
    $mb1 = get_saler($write['wr_3']);
    $com = get_table_meta('company','com_idx',$write['wr_2']);
    $cmm = get_company_member($write['wr_3'],$write['wr_2']);
//    print_r3($com);
    if($com['com_idx']) {
        $write['customer_info'] = '<b>담당자:</b> '.$cmm['cmm_name_rank'];
        $write['customer_info'] .= ' <span class="div_com_president">(<b>업체명:</b> '.$com['com_name'];
        $write['customer_info'] .= ', <b>대표자:</b> '.$com['com_president'].')</span>';
    }
//    else
//        $write['customer_info'] = '선택된 고객이 없습니다. 고객을 선택하세요.';

    
} else if ($w == 'r') {


}

?>