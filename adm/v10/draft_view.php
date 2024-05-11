<?php
$sub_menu = '960270';
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

$drfsql = " SELECT drf.*, mb.mb_name, prj_name, mb.mb_2, mb.mb_3, mb2.mb_name AS mb_name_approval FROM {$g5['draft_table']} drf
                LEFT JOIN {$g5['project_table']} prj ON drf.prj_idx = prj.prj_idx
                LEFT JOIN {$g5['member_table']} mb ON drf.mb_id = mb.mb_id
                LEFT JOIN {$g5['member_table']} mb2 ON drf.mb_id_approval = mb2.mb_id
            WHERE drf_idx = '{$drf_idx}' ";
// echo $drfsql;exit;
$drf = sql_fetch($drfsql,1);


//관련파일 추출
$sql = "SELECT * FROM {$g5['file_table']}
    WHERE fle_db_table = 'drf' AND fle_type = 'drf' AND fle_db_id = '".$drf['drf_idx']."' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$drf['drf_f_arr'] = array();
$drf['drf_fidxs'] = array();//회의 파일번호(fle_idx) 목록이 담긴 배열
$drf['drf_lst_idx'] = 0;//회의 파일중에 가장 최신버전의 파일번호
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt']:''.PHP_EOL;
    @array_push($drf['drf_f_arr'],array('file'=>$file_down_del));
    @array_push($drf['drf_fidxs'],$row2['fle_idx']);
}

//회의관련 파일 idx배열에 요소가 1개이상 존재하면 그중에 첫번째 요소(fle_idx)를 변수에 담는다.
if(@count($drf['drf_fidxs'])) $drf['drf_lst_idx'] = $drf['drf_fidxs'][0];


$g5['title'] = (($drf['drf_type'] == 'in')?'내부':'외부').'회의내용';
include_once('./_head.php');
// html_purifier(stripslashes($drf['drf_content']));
// html_purifier(stripslashes($drf['drf_result']));
// print_r2($drf);exit;
?>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<article id="mt_v">
    <div id="pdf_v">
        <p class="drf_code">문서번호 : <?=$drf['drf_code']?></p>
        <div class="ttl_box">
            <h1><span>기</span><span>안</span><span>서</span></h1>
            <ul class="sign_box">
                <li><h3>담당자</h3><p><?=$drf['mb_name']?></p></li>
                <li><h3>부서장</h3><p><?=$drf['mb_name_approval']?></p></li>
                <li><h3>대표이사</h3><p>이병구</p></li>
            </ul>
        </div>
        <div class="tbl_box">
            <table class="tbl_info">
            <colgroup>
                <col class="grid_4" style="width:10%;">
                <col style="width:15%;">
                <col class="grid_4" style="width:10%;">
                <col style="width:15%;">
                <col class="grid_4" style="width:10%;">
                <col style="width:15%;">
                <col class="grid_4" style="width:10%;">
                <col style="width:15%;">
            </colgroup>
            <tbody>
                <tr>
                    <th>작성자소속</th>
                    <td><?=(($g5['department_name'][$drf['drf_part']])?$g5['department_name'][$drf['drf_part']]:'소속없음')?></td>
                    <th>작성자직급</th>
                    <td><?=(($g5['set_mb_ranks_value'][$drf['drf_rank']])?$g5['set_mb_ranks_value'][$drf['drf_rank']]:'직급없음')?></td>
                    <th>작성자명</th>
                    <td><?=$drf['mb_name']?></td>
                    <th>기안날짜</th>
                    <td><?=$drf['drf_date']?></td>
                </tr>
                <tr>
                    <th>프로젝트명</th>
                    <td colspan="7" class="td_center td_title"><?=(($drf['prj_name'])?$drf['prj_name']:'기타기안')?></td>
                </tr>
                <tr>
                    <th>제목</th>
                    <td colspan="7" class="td_center td_title"><?=$drf['drf_subject']?></td>
                </tr>
                <tr>
                    <td colspan="8" class="td_cont">
                        <h2>1. 요청내용</h2>
                        <div class="mt_box mt_content">
                            <?php
                            echo html_purifier(stripslashes($drf['drf_content']));
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" class="td_cont">
                        <h2>2. 세부내용</h2>
                        <div class="mt_box mt_detail">
                            <?php
                            echo html_purifier(stripslashes($drf['drf_detail']));
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" class="td_cont">
                        <h2>3. 금액관련 내용</h2>
                        <div class="mt_box mt_money">
                            <?php
                            echo html_purifier(stripslashes($drf['drf_money']));
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" class="td_cont">
                        <h2>4. 기타내용</h2>
                        <div class="mt_box mt_etc">
                            <?php
                            echo html_purifier(stripslashes($drf['drf_etc']));
                            ?>
                        </div>
                    </td>
                </tr>
                <?php if($drf['drf_response']){ ?>
                <tr>
                    <td colspan="8" class="td_cont">
                        <h2>5. 부서장/대표이사 답변</h2>
                        <div class="mt_box mt_response">
                            <?php
                            echo html_purifier(stripslashes($drf['drf_response']));
                            ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
            </table>
        </div>
    </div><!--//#pdf_v-->
    <?php if(false){ //($drf['drf_response']){ ?>
    <div class="mt_out_box mt_etc_box">
        <h2>5. 승인자 답변</h2>
        <div class="mt_box mt_response">
            <?php
            echo html_purifier(stripslashes($drf['drf_response']));
            ?>
        </div>
    </div>
    <?php } ?>
    <?php if(@count($drf['drf_f_arr'])){ ?>
    <div id="mt_fle_box">
        <div class="mt_file">
            <h2>첨부파일</h2>
            <div class="mt_file_list">
                <?php
                echo '<ul>'.PHP_EOL;
                for($i=0;$i<count($drf['drf_f_arr']);$i++) {
                    echo "<li>[".($i+1).']'.$drf['drf_f_arr'][$i]['file']."</li>".PHP_EOL;
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
    <a href="./draft_list.php?<?php echo $qstr ?>" class="btn btn_04">목록</a>
    <a href="./draft_form.php?<?=$qstr?>&amp;w=u&amp;drf_idx=<?=$drf_idx?>" class="btn btn_02">수정</a>
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
        doc.save('<?php echo get_text($drf['drf_subject']) ?>.pdf'); //pdf저장
    });
});
</script>

<?php
include_once ('./_tail.php');