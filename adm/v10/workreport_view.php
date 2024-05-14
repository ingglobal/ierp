<?php
$sub_menu = "960300";
include_once('./_common.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

auth_check($auth[$sub_menu], "r");

if(!$wrp_idx) alert('보고서 고유번호가 넘어오지 않았습니다.');
if(!$type) alert('보고서 타입정보가 넘어오지 않았습니다.');
if(!$yy) alert('연도정보가 넘어오지 않았습니다.');
if(!$mm) alert('월정보가 넘어오지 않았습니다.');

foreach($_REQUEST as $key => $value ) {
    if(substr($key,0,4)=='ser_') {
        if(is_array($value)) {
            foreach($value as $k2 => $v2 ) {
                $qstr .= '&'.$key.'[]='.$v2;
            }
        }
        else {
            $qstr .= '&'.$key.'='.(($key == 'ser_stx')?urlencode(cut_str($value, 40, '')):$value);
        }
    }
}

$wrpsql = " SELECT wrp.*, mb_name, prj_name, mb_2, mb_3 FROM {$g5['workreport_table']} wrp
                    LEFT JOIN {$g5['member_table']} mb ON wrp.mb_id = mb.mb_id
                    LEFT JOIN {$g5['project_table']} prj ON wrp.prj_idx = prj.prj_idx
                WHERE wrp_idx = '{$wrp_idx}' ";
$wrp = sql_fetch($wrpsql,1);

//관련파일 추출
$sql = "SELECT * FROM {$g5['file_table']}
    WHERE fle_db_table = 'wrp' AND fle_type = 'wrp' AND fle_db_id = '".$wrp['wrp_idx']."' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$wrp['wrp_f_arr'] = array();
$wrp['wrp_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$wrp['wrp_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt']:''.PHP_EOL;
    @array_push($wrp['wrp_f_arr'],array('file'=>$file_down_del));
    @array_push($wrp['wrp_fidxs'],$row2['fle_idx']);
}

//회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
if(@count($wrp['wrp_fidxs'])) $wrp['wrp_lst_idx'] = $wrp['wrp_fidxs'][0];

$g5['title'] = $g5['set_wrp_type_value'][$type].' 내용';
include_once('./_head.php');
?>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<article id="wr_v">
    <div id="pdf_v">
        <p class="wrp_code">문서번호 : <?=$wrp['wrp_code']?></p>
        <div class="ttl_box">
            <h1><?=$g5['set_wrp_type_value'][$type]?>서</h1>
        </div>
        <div class="tbl_box">
            <table class="tbl_info">
            <colgroup>
                <col class="grid_4" style="width:12%;">
                <col style="width:21%;">
                <col class="grid_4" style="width:12%;">
                <col style="width:22%;">
                <col class="grid_4" style="width:12%;">
                <col style="width:21%;">
            </colgroup>
            <tbody>
                <tr>
                    <th>작성자소속</th>
                    <td><?=(($g5['department_name'][$wrp['wrp_part']])?$g5['department_name'][$wrp['wrp_part']]:'소속없음')?></td>
                    <th>작성자직급</th>
                    <td><?=(($g5['set_mb_ranks_value'][$wrp['wrp_rank']])?$g5['set_mb_ranks_value'][$wrp['wrp_rank']]:'직급없음')?></td>
                    <th>작성자명</th>
                    <td><?=$wrp['mb_name']?></td>
                </tr>
                <tr>
                    <th>보고날짜</th>
                    <td><?=$wrp['wrp_date']?></td>
                    <th>주차</th>
                    <td><?=$wrp['wrp_week']?> 주차</td>
                    <th>프로젝트명</th>
                    <td class="td_center td_title" style="text-align:left !important;"><?=(($wrp['prj_name'])?$wrp['prj_name']:'기타업무보고')?></td>
                </tr>
                <tr>
                    <th>제목</th>
                    <td colspan="5" class="td_center td_title"><?=$wrp['wrp_subject']?></td>
                </tr>
            </tbody>
            </table>
        </div>
        <div class="mt_out_box mt_content_box">
            <h2>내용</h2>
            <div class="mt_box mt_content">
                <?php
                echo html_purifier(stripslashes($wrp['wrp_content']));
                ?>
            </div>
        </div>
    </div><!--//#pdf_v-->
    <?php if(@count($wrp['wrp_f_arr'])){ ?>
    <div id="mt_fle_box">
        <div class="mt_file">
            <h2>첨부파일</h2>
            <div class="mt_file_list">
                <?php
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($wrp['wrp_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$wrp['wrp_f_arr'][$i]['file']."</li>".PHP_EOL;
                }
                echo '</ul>'.PHP_EOL;
                ?>
            </div>
        </div>
    </div>
    <?php } ?>
</article>
<div class="btn_fixed_top">
    <a href="javascript:" class="btn btn_03 pdf_btn">PDF다운로드</a>
    <?php if($list){ ?>
        <a href="./workreport_list.php?ser_wrp_type=<?=$ser_wrp_type?>&amp;ser_mb_id=<?=$ser_mb_id?>&amp;ser_from_date=<?=$ser_from_date?>&amp;ser_to_date=<?=$ser_to_date?>" class="btn btn_04">목록</a>
        <a href="./workreport_form.php?wrp_idx=<?=$wrp_idx?>&amp;w=u&amp;type=<?=$type?>&amp;yy=<?=$yy?>&amp;mm=<?=$mm?>&amp;ser_wrp_type=<?=$ser_wrp_type?>&amp;ser_mb_id=<?=$ser_mb_id?>&amp;ser_from_date=<?=$ser_from_date?>&amp;ser_to_date=<?=$ser_to_date?>&amp;list=<?=$list?>" class="btn btn_02">수정</a>
    <?php } else { ?>
    <a href="./workreport_calendar.php?type=<?=$type?>&amp;yy=<?=$yy?>&amp;mm=<?=$mm?>" class="btn btn_04">달력</a>
    <a href="./workreport_form.php?wrp_idx=<?=$wrp_idx?>&amp;w=u&amp;type=<?=$type?>&amp;yy=<?=$yy?>&amp;mm=<?=$mm?>" class="btn btn_02">수정</a>
    <?php } ?>
</div>

<script>
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
        doc.save('<?php echo get_text($wrp['wrp_subject']) ?>.pdf'); //pdf저장
    });
});
</script>

<?php
include_once ('./_tail.php');