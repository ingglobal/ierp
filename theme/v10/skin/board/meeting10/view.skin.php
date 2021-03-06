<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/view.php');

//goto_url(G5_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$wr_id.'&amp;'.$qstr);

// 수주 관련 정보 추출
$sql = " SELECT * FROM {$g5['contract_table']} WHERE wr_id = '".$wr_id."' AND ctr_status NOT IN ('trash','delete') ";
$ctr = sql_fetch($sql,1);
//print_r2($ctr);

// 영업자(작성자)
$mb1 = get_saler($view['mb_id']);
//print_r2($mb1);

//관련파일 추출
$sql = " SELECT * FROM {$g5['file_table']} 
WHERE fle_db_table = 'g5_write_{$bo_table}' AND fle_type = '{$bo_table}' AND fle_db_id = '".$wr_id."' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$row['file_'.$bo_table] = array();
$row['file_'.$bo_table.'_fidxs'] = array(); //여러파일번호(fle_idx) 목록이 담긴 배열
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$row2['fle_reg_dt']:''.PHP_EOL;
    @array_push($row['file_'.$row2['fle_type']],array('file'=>$file_down_del));
    @array_push($row['file_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

<!-- 게시물 읽기 시작 { -->
<style>
.multi_area ul{margin-top:10px;}
.multi_area ul > li{margin-top:10px;}
</style>
<article id="bo_v" style="width:<?php echo $width; ?>">
    <div id="pdf_area">
        <header>
            <h2 id="bo_v_title">
                <?php if ($category_name) { ?>
                <span class="bo_v_cate"><?php echo $view['ca_name']; // 분류 출력 끝 ?></span> 
                <?php } ?>
                <span class="bo_v_tit">
                <?php
                echo cut_str(get_text($view['wr_subject']), 70); // 글제목 출력
                ?></span>
            </h2>
        </header>
        
        <section id="bo_v_info">
            <h2>페이지 정보</h2>
            <span class="sound_only">작성자</span> <strong><?php echo $view['name'] ?><?php //if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></strong>
            <strong class="if_date"><span class="sound_only">작성일</span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo date("y-m-d H:i", strtotime($view['wr_datetime'])) ?></strong>

        </section>
        
        <section id="bo_control">
            <table>
                <tbody><tr>
                        <th>업체명</th>
                        <td><?=$view['wr_1']?></td>
                        <th>업체담당자</th>
                        <td><?=$view['wr_4']?></td>
                    </tr>
                    <tr>
                        <th>회의분류</th>
                        <td><?=$view['ca_name']?></td>
                        <th>회의날짜</th>
                        <td><?=$view['wr_homepage']?></td>
                    </tr>
                </tbody>
            </table>
        </section>
        

        <?php
        // 직원인 경우만 보임 {---------------
        if($member['mb_level']>=6) {
            // 업체정보
            if($view['wr_2']) {
                $com1 = get_table_meta('company','com_idx',$view['wr_2']);
                //print_r3($com1);
            }
            // 고객 정보
            if($view['wr_3']) {
                // 고객
                $mb1 = get_saler($view['wr_3']);
                $cmm1 = get_company_member($view['wr_3'],$view['wr_2']);
                //print_r3($mb1);
                $view['mb_name_customer'] = $cmm1['cmm_name_rank'];
            }
        ?>
        <!--section id="bo_item">
            <span><b>담당자:</b> <?php echo $view['mb_name_customer']?></span>
            <a href="<?=G5_USER_ADMIN_URL?>/member_form.php?w=u&mb_id=<?=$view['wr_3']?>"><i class="fa fa-edit"></i></a>
        </section-->
        <!-- 통합인트라 업체정보 -->
        <!--section id="bo_company">
            <span><b>업체명:</b> <?php echo $com1['com_name']?> <a href="<?=G5_USER_ADMIN_URL?>/company_form.php?w=u&com_idx=<?=$view['wr_2']?>"><i class="fa fa-edit"></i></a> <span class="com_idx"><?php echo $view['wr_2']?></span></span>
            <span><b>대표자:</b> <?php echo $com1['com_president']?></span>
            <span><b>대표전화:</b> <?php echo $com1['com_tel']?></span>
            <span><b>이메일:</b> <?php echo $com1['com_email']?></span>
        </section-->
        <?php
        }
        // } 직원인 경우만 보임 ---------------
        ?>
        
        <section id="bo_v_atc">
            <h2 id="bo_v_atc_title1">회의내용</h2>
            <!-- 본문 내용 시작 { -->
            <div id="bo_v_con" style="white-space:pre-line;"><?php echo get_view_thumbnail($view['content']); ?></div>
            <h2 id="bo_v_atc_title1">참석자</h2>
            <div id="bo_v_con2" style="white-space:pre-line;"><?php echo get_view_thumbnail($view['wr_link1']); ?></div>
            <?php //echo $view['rich_content']; // {이미지:0} 과 같은 코드를 사용할 경우 ?>
            <!-- } 본문 내용 끝 -->

            <?php if ($is_signature) { ?><p><?php echo $signature ?></p><?php } ?>


            <!--  추천 비추천 시작 { -->
            <?php if ( $good_href || $nogood_href) { ?>
            <div id="bo_v_act">
                <?php if ($good_href) { ?>
                <span class="bo_v_act_gng">
                    <a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good"><span class="sound_only">추천</span><strong><?php echo number_format($view['wr_good']) ?></strong></a>
                    <b id="bo_v_act_good"></b>
                </span>
                <?php } ?>
                <?php if ($nogood_href) { ?>
                <span class="bo_v_act_gng">
                    <a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood"><span class="sound_only">비추천</span><strong><?php echo number_format($view['wr_nogood']) ?></strong></a>
                    <b id="bo_v_act_nogood"></b>
                </span>
                <?php } ?>
            </div>
            <?php } else {
                if($board['bo_use_good'] || $board['bo_use_nogood']) {
            ?>
            <div id="bo_v_act">
                <?php if($board['bo_use_good']) { ?><span class="bo_v_good"><span class="sound_only">추천</span><strong><?php echo number_format($view['wr_good']) ?></strong></span><?php } ?>
                <?php if($board['bo_use_nogood']) { ?><span class="bo_v_nogood"><span class="sound_only">비추천</span><strong><?php echo number_format($view['wr_nogood']) ?></strong></span><?php } ?>
            </div>
            <?php
                }
            }
            ?>
            <!-- }  추천 비추천 끝 -->
        </section>
        <div class="multi_area">
        <?php
        if(@count($row['file_'.$bo_table])){
            echo '<ul>'.PHP_EOL;
            for($i=0;$i<count($row['file_'.$bo_table]);$i++) {
                echo "<li>[".($i+1).']'.$row['file_'.$bo_table][$i]['file']."</li>".PHP_EOL;
            }
            echo '</ul>'.PHP_EOL;
        }
        ?>
        </div>
    </div><!--//#pdf_area-->
    <div id="bo_v_share" style="display:none;">
        <?php if ($scrap_href) { ?><a href="<?php echo $scrap_href;  ?>" target="_blank" class="btn btn_b03" onclick="win_scrap(this.href); return false;"><i class="fa fa-thumb-tack" aria-hidden="true"></i> 스크랩</a><?php } ?>

        <?php
        include_once(G5_SNS_PATH."/view.sns.skin.php");
        ?>
    </div>

    <?php
    $cnt = 0;
    if ($view['file']['count']) {
        for ($i=0; $i<count($view['file']); $i++) {
            //if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'])
                $cnt++;
        }
    }
    //print_r3($view['file']);
    ?>

    <?php if($cnt) { ?>
    <!-- 첨부파일 시작 { -->
    <section id="bo_v_file">
        <h2 style="margin-top:0;">첨부파일</h2>
        <style>
        .preview_file{position:relative;}   
        .prepop{position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:4000;display:none;padding:50px 100px;}
        .prepop.focus{display:block;}
        .prepop .pop_close{position:absolute;display:inline-block;top:20px;right:60px;color:#efefef;font-size:3em;z-index:10;}
        .prepop .pop_conbox{position:relative;z-index:5;width:100%;height:100%;background-color:#333;background-repeat:no-repeat;background-position:center center;background-size:contain;}
        .prepop .pop_bg{position:absolute;z-index:0;position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);}
        </style>
        <ul>
        <?php
        // 가변 파일
        for ($i=0; $i<count($view['file']); $i++) {
            //if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source']) {
         ?>
            <li>
                <i class="fa fa-download" aria-hidden="true"></i>
                <a href="<?php echo $view['file'][$i]['href'];  ?>" class="view_file_download">
                    <strong><?php echo $view['file'][$i]['source'] ?></strong>
                </a>
                <?php if( ($view['file'][$i]['image_type'] >0 && $view['file'][$i]['image_type']<4) || $view['file'][$i]['image_type']==6 ){ //gif jpg png bmp 인지 확인?>
                <a href="javascript:" class="preview_file" wd="<?=$view['file'][$i]['image_width']?>" ht="<?=$view['file'][$i]['image_height']?>" url="<?=$view['file'][$i]['path'].'/'.$view['file'][$i]['file']?>">[미리보기]</a>
                <?php } ?>
                <?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
                <span class="bo_v_file_cnt"><?php echo $view['file'][$i]['download'] ?>회 다운로드 | DATE : <?php echo $view['file'][$i]['datetime'] ?></span>
            </li>
        <?php
            }
        }
         ?>
        </ul>
        <div class="prepop">
            <a href="javascript:" class="pop_close"><i class="fa fa-times" aria-hidden="true"></i></a>
            <div class="pop_conbox"></div>
            <div class="pop_bg"></div>
        </div>
        <script>
           if($('.preview_file').length > 0){
                $('.preview_file').on('click',function(){
                    $('.prepop').appendTo('body').addClass('focus');
                    $('.pop_conbox').css('background-image','url('+$(this).attr('url')+')')
                });
                $('.pop_close,.pop_bg').on('click',function(){
                    $(this).parent().removeClass('focus');
                });
           } 
        </script>
    </section>
    <!-- } 첨부파일 끝 -->
    <?php } ?>

    <?php //if(isset($view['link'][1]) && $view['link'][1]) { ?>
    <?php if(false) { ?>
    <!-- 관련링크 시작 { -->
    <section id="bo_v_link">
        <h2>관련링크</h2>
        <ul>
        <?php
        // 링크
        $cnt = 0;
        for ($i=1; $i<=count($view['link']); $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
            ?>
            <li>
                <i class="fa fa-link" aria-hidden="true"></i> <a href="<?php echo $view['link_href'][$i] ?>" target="_blank">
                    
                    <strong><?php echo $link ?></strong>
                </a>
                <span class="bo_v_link_cnt"><?php echo $view['link_hit'][$i] ?>회 연결</span>
            </li>
            <?php
            }
        }
        ?>
        </ul>
    </section>
    <!-- } 관련링크 끝 -->
    <?php } ?>

    <!-- 게시물 상단 버튼 시작 { -->
    <div id="bo_v_top">
        <?php
        ob_start();
        ?>

        <ul class="bo_v_left">
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01 btn"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="btn_b01 btn" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?>
            <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="btn_admin btn" onclick="board_move('<?php echo $board_skin_url;;?>/move.php?sw=copy&bo_table=<?php echo $bo_table;?>&wr_id=<?php echo $wr_id;?>'); return false;"><i class="fa fa-files-o" aria-hidden="true"></i> 복사</a></li><?php } ?>
            <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="btn_admin btn" onclick="board_move('<?php echo $board_skin_url;?>/move.php?sw=move&bo_table=<?php echo $bo_table;?>&wr_id=<?php echo $wr_id;?>'); return false;"><i class="fa fa-arrows" aria-hidden="true"></i> 이동</a></li><?php } ?>
            <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="btn_b01 btn"><i class="fa fa-search" aria-hidden="true"></i> 검색</a></li><?php } ?>
        </ul>

        <ul class="bo_v_com">
           <li><a href="<?php echo $list_href ?>" class="btn_b01 btn"><i class="fa fa-list" aria-hidden="true"></i> 목록</a></li>
            <?php if (false){ //($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn"><i class="fa fa-reply" aria-hidden="true"></i> 답변</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn"><i class="fa fa-pencil" aria-hidden="true"></i> 글쓰기</a></li><?php } ?>
        </ul>

        <?php if ($prev_href || $next_href) { ?>
        <ul class="bo_v_nb" style="display:none;">
            <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit"><i class="fa fa-caret-up" aria-hidden="true"></i> 이전글</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a> <span class="nb_date"><?php echo str_replace('-', '.', substr($prev_wr_date, '2', '8')); ?></span></li><?php } ?>
            <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit"><i class="fa fa-caret-down" aria-hidden="true"></i> 다음글</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a>  <span class="nb_date"><?php echo str_replace('-', '.', substr($next_wr_date, '2', '8')); ?></span></li><?php } ?>
        </ul>
        <?php } ?>
        <?php
        $link_buttons = ob_get_contents();
        ob_end_flush();
         ?>
    </div>
    <!-- } 게시물 상단 버튼 끝 -->

    <?php
    // 코멘트 입출력
    //include_once(G5_BBS_PATH.'/view_comment.php');
     ?>

    <div class="btn_fixed_top">
        <a href="javascript:" class="btn btn_02 pdf_btn">PDF다운로드</a>
    </div>
</article>
<!-- } 게시판 읽기 끝 -->

<script>
// 상단 제목 수정
$('#container_title').text('<?php echo $board['bo_subject']?> 보기');

$("#wr_6").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

// 작업자 검색
$("#mb_name_worker").click(function() {
    var href = $(this).attr("href");
    memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
    memberwin.focus();
    return false;
});

// 수주정보 입력
$("#btn_order").click(function() {
    var href = $(this).attr("href");
    winOrder = window.open(href, "winOrder", "left=100,top=100,width=520,height=600,scrollbars=1");
    winOrder.focus();
    return false;
});

// 설정변경 바로 적용
$(document).on('click','#btn_set',function(e){
    e.preventDefault();
    var target_form = $(this).closest('form');

    //폼 validation
    if(target_form.find('input[name=wr_10]').val() == '') {
        alert("상태값을 선택해 주세요.");
        return false;
    }
    else {
        // form 설정값 serialize 
        //alert( target_form.serialize() );
        data_serialized = target_form.serialize();
        
        //-- 디버깅 Ajax --//
        //$.ajax({
        //	url:'<?php echo $board_skin_url?>/write_update.ajax.php',
        //	data:{"type":"set","data_serialized":data_serialized},
        //	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
        $.getJSON('<?php echo $board_skin_url?>/write_update.ajax.php',{"type":"set","data_serialized":data_serialized},function(res) {
            //alert(res.sql);
            //console.log(res);
            var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
            
            if(res.result == true) {
                //alert(res.msg);
                alert('설정값을 변경하였습니다.');
                // 수주완료인 경우 수주정보입력
                if(res.status=='ok') {
                    $('#btn_order').show();
                }
                else {
                    $('#btn_order').hide();
                }
            }
            else {
                alert(res.msg);
            }
            //}, error:this_ajax_error	//<-- 디버깅 Ajax --//
        });
    }
});

//pdf다운로드 버튼을 클릭하면
$('.pdf_btn').on('click',function(){
    //pdf_wrap을 canvas객체로 변환
    html2canvas($('#pdf_area')[0]).then(function(canvas) {
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
        doc.save('<?php echo get_text($view['wr_subject']) ?>.pdf'); //pdf저장
    });
});

// 설정리셋 바로 적용
$(document).on('click','#btn_reset',function(e){
    e.preventDefault();
    var target_form = $(this).closest('form');

    //폼 validation
    if(confirm('설정값을 리셋하시겠습니까?')) {
        // form 설정값 serialize 
        //alert( target_form.serialize() );
        data_serialized = target_form.serialize();
        
        //-- 디버깅 Ajax --//
        //$.ajax({
        //	url:'<?php echo $board_skin_url?>/write_update.ajax.php',
        //	data:{"type":"reset","data_serialized":data_serialized},
        //	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
        $.getJSON('<?php echo $board_skin_url?>/write_update.ajax.php',{"type":"reset","data_serialized":data_serialized},function(res) {
            //alert(res.sql);
            console.log(res);
            var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
            
            if(res.result == true) {
                //alert(res.msg);
                alert('설정값을 변경하였습니다.');
                self.location.reload();
            }
            else {
                alert(res.msg);
            }
            //}, error:this_ajax_error	//<-- 디버깅 Ajax --//
        });
    }
});	

</script>


<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }

        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);

            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    boardmove = window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
    boardmove.focus();
}
</script>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 추천, 비추천
    $("#good_button, #nogood_button").click(function() {
        var $tx;
        if(this.id == "good_button")
            $tx = $("#bo_v_act_good");
        else
            $tx = $("#bo_v_act_nogood");

        excute_good(this.href, $(this), $tx);
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();

    //sns공유
    $(".btn_share").click(function(){
        $("#bo_v_sns").fadeIn();
   
    });

    $(document).mouseup(function (e) {
        var container = $("#bo_v_sns");
        if (!container.is(e.target) && container.has(e.target).length === 0){
        container.css("display","none");
        }	
    });
});

function excute_good(href, $el, $tx)
{
    $.post(
        href,
        { js: "on" },
        function(data) {
            if(data.error) {
                alert(data.error);
                return false;
            }

            if(data.count) {
                $el.find("strong").text(number_format(String(data.count)));
                if($tx.attr("id").search("nogood") > -1) {
                    $tx.text("이 글을 비추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                } else {
                    $tx.text("이 글을 추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                }
            }
        }, "json"
    );
}
</script>
<!-- } 게시글 읽기 끝 -->