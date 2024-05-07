<?php
$sub_menu = '960265';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

auth_check($auth[$sub_menu], "r");

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

$mtgsql = " SELECT mtg.*, mb_name, prj_name, mb_2, mb_3 FROM {$g5['meeting_table']} mtg
                LEFT JOIN {$g5['project_table']} prj ON mtg.prj_idx = prj.prj_idx
                LEFT JOIN {$g5['member_table']} mb ON mtg.mb_id_writer = mb.mb_id
            WHERE mtg_idx = '{$mtg_idx}' ";
$mtg = sql_fetch($mtgsql,1);

$mtpsql = " SELECT (ROW_NUMBER() OVER(ORDER BY mtp_idx)) AS num 
                , mtp_idx
                , mtg_idx
                , mtp_belong
                , mtp_name
                , mtp_rank
                , mtp_phone
                FROM {$g5['meeting_participant_table']}
            WHERE mtg_idx = '{$mtg_idx}' ORDER BY mtp_idx ";
$mtpres = sql_query($mtpsql,1);
$mtprows = $mtpres->num_rows + 1;

//관련파일 추출
$sql = "SELECT * FROM {$g5['file_table']}
    WHERE fle_db_table = 'mtg' AND fle_type = 'mtg' AND fle_db_id = '".$mtg['mtg_idx']."' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$mtg['mtg_f_arr'] = array();
$mtg['mtg_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$mtg['mtg_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt']:''.PHP_EOL;
    @array_push($mtg['mtg_f_arr'],array('file'=>$file_down_del));
    @array_push($mtg['mtg_fidxs'],$row2['fle_idx']);
}

//회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
if(@count($mtg['mtg_fidxs'])) $mtg['mtg_lst_idx'] = $mtg['mtg_fidxs'][0];


$g5['title'] = (($mtg['mtg_type'] == 'in')?'내부':'외부').'회의내용';
include_once('./_head.php');
// html_purifier(stripslashes($mtg['mtg_content']));
// html_purifier(stripslashes($mtg['mtg_result']));
// print_r2($mtg);exit;
?>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<article id="mt_v">
    <div id="pdf_v">
        <p class="mtg_code">문서번호 : <?=$mtg['mtg_code']?></p>
        <div class="ttl_box">
            <h1><span>회</span><span>의</span><span>록</span></h1>
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
                    <td><?=(($g5['department_name'][$mtg['mtg_part']])?$g5['department_name'][$mtg['mtg_part']]:'소속없음')?></td>
                    <th>작성자직급</th>
                    <td><?=(($g5['set_mb_ranks_value'][$mtg['mtg_rank']])?$g5['set_mb_ranks_value'][$mtg['mtg_rank']]:'직급없음')?></td>
                    <th>작성자명</th>
                    <td><?=$mtg['mb_name']?></td>
                </tr>
                <tr>
                    <th>회의날짜</th>
                    <td><?=$mtg['mtg_date']?></td>
                    <th>회의시간</th>
                    <td><?=substr($mtg['mtg_start_time'],0,5)?>&nbsp;&nbsp;&nbsp;~&nbsp;&nbsp;&nbsp;<?=substr($mtg['mtg_end_time'],0,5)?></td>
                    <th>회의장소</th>
                    <td><?=$mtg['mtg_location']?></td>
                </tr>
                <tr>
                    <th>프로젝트명</th>
                    <td colspan="5" class="td_center td_title"><?=(($mtg['prj_name'])?$mtg['prj_name']:'기타회의')?></td>
                </tr>
                <tr>
                    <th>주요안건</th>
                    <td colspan="5" class="td_center td_title"><?=$mtg['mtg_subject']?></td>
                </tr>
            </tbody>
            </table>
        </div>
        <div class="mt_out_box mt_content_box">
            <h2>회의내용</h2>
            <div class="mt_box mt_content">
                <?php
                echo html_purifier(stripslashes($mtg['mtg_content']));
                ?>
            </div>
        </div>
        <div class="mt_out_box mt_result_box">
            <h2>결과</h2>
            <div class="mt_box mt_result">
                <?php
                echo html_purifier(stripslashes($mtg['mtg_result']));
                ?>
            </div>
        </div>
        <div class="mt_participant_box tlb_box">
            <p class="desc">위 결의사항의 명확을 기하기 위하여 연서/날인 합니다.</p>
            <table>
                <tbody>
                    <tr>
                        <th rowspan="<?=$mtprows?>">참석자</th>
                        <th>소속</th>
                        <th>성명</th>
                        <th>직급</th>
                        <th>연락처</th>
                        <th>서명</th>
                    </tr>
                    <?php for($j=0;$row=sql_fetch_array($mtpres);$i++){ ?>
                    <tr>
                        <td class="td_center"><?=$row['mtp_belong']?></td>
                        <td class="td_center"><?=$row['mtp_name']?></td>
                        <td class="td_center"><?=$row['mtp_rank']?></td>
                        <td class="td_center"><?=$row['mtp_phone']?></td>
                        <td></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div><!--//#pdf_v-->
    <?php if(@count($mtg['mtg_f_arr'])){ ?>
    <div id="mt_fle_box">
        <div class="mt_file">
            <h2>첨부파일</h2>
            <div class="mt_file_list">
                <?php
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($mtg['mtg_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$mtg['mtg_f_arr'][$i]['file']."</li>".PHP_EOL;
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
    <a href="./meeting_list.php?<?php echo $qstr ?>" class="btn btn_04">목록</a>
    <a href="./meeting_form.php?<?=$qstr?>&amp;w=u&amp;mtg_idx=<?=$mtg_idx?>" class="btn btn_02">수정</a>
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
        doc.save('<?php echo get_text($mtg['mtg_subject']) ?>.pdf'); //pdf저장
    });
});
</script>

<?php
include_once ('./_tail.php');