<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// 수정, 삭제 링크
$update_href = $delete_href = '';
// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] === $write['mb_id'])) || $is_admin || $member['mb_id'] == $write['wr_2']) {
    $update_href = short_url_clean(G5_USER_ADMIN_URL.'/bbs_write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr);
    set_session('ss_delete_token', $token = uniqid(time()));
    if(($member['mb_id'] && ($member['mb_id'] === $write['mb_id'])) || $is_admin){
        $delete_href = G5_USER_ADMIN_URL.'/bbs_delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token.'&amp;page='.$page.'&amp;'.urldecode($qstr);
    }
}
else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
    $update_href = G5_USER_ADMIN_URL.'/bbs_password.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr;
    $delete_href = G5_USER_ADMIN_URL.'/bbs_password.php?w=d&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr;
}


// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);

?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v" style="width:<?php echo $width; ?>">
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
        <div class="profile_info">
            <?php $mb_pic = get_table_meta('member','mb_id',$write['wr_2']); ?>
        	<div class="pf_img"><?php echo get_member_profile_img($view['mb_id']) ?></div>
        	<div class="profile_info_ct">
        		<span>작성자: </span> <strong><?php echo $view['name'] ?></strong>
                <span style="margin-left:5px;">책임자: </span> <strong style="font-weight:700;"><?php echo $mb_pic['mb_name'] ?></strong>
                <span style="margin-left:5px;">부서명: </span> <strong style="font-weight:700;"><?php echo $g5['set_department_name_value'][$view['wr_1']]; ?></strong>
                <span style="margin-left:5px;">상태: </span> <strong style="font-weight:700;"><?php echo $g5['set_agenda_status_value'][$view['wr_4']]; ?></strong>
                <br>
       		 	<span class="sound_only">댓글</span><strong><a href="#bo_vc"> <i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo number_format($view['wr_comment']) ?>건</a></strong>
        		<span class="sound_only">조회</span><strong><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($view['wr_hit']) ?>회</strong>
        		<strong class="if_date"><span>등록일시: </span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo date("y-m-d H:i", strtotime($view['wr_datetime'])) ?></strong>
                <strong class="if_date" style="margin-left:10px;"><span>확인일시: </span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo ($view['wr_6'])?date("y-m-d H:i", strtotime($view['wr_6'])):'00-00-00 00:00'; ?></strong>
    		</div>
    	</div>
        
    	<!-- 게시물 상단 버튼 시작 { -->
	    <div id="bo_v_top">
	        <?php ob_start(); ?>

	        <ul class="btn_bo_user bo_v_com">
				<li><a href="<?php echo $list_href ?>" class="btn_b01 btn" title="목록"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a></li>
	            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
	        	<?php if($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
	        	<li>
	        		<button type="button" class="btn_more_opt is_view_btn btn_b01 btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
		        	<ul class="more_opt is_view_btn"> 
			            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>">답변/수정<i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></li><?php } ?>
			            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">삭제<i class="fa fa-trash-o" aria-hidden="true"></i></a></li><?php } ?>
			            <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>">검색<i class="fa fa-search" aria-hidden="true"></i></a></li><?php } ?>
			        </ul> 
	        	</li>
	        	<?php } ?>
	        </ul>
	        <script>

            jQuery(function($){
                // 게시판 보기 버튼 옵션
				$(".btn_more_opt.is_view_btn").on("click", function(e) {
                    e.stopPropagation();
				    $(".more_opt.is_view_btn").toggle();
				})
;
                $(document).on("click", function (e) {
                    if(!$(e.target).closest('.is_view_btn').length) {
                        $(".more_opt.is_view_btn").hide();
                    }
                });
            });
            </script>
	        <?php
	        $link_buttons = ob_get_contents();
	        ob_end_flush();
			?>
	    </div>
	    <!-- } 게시물 상단 버튼 끝 -->
    </section>

    <section id="bo_v_atc">
        <h2 id="bo_v_atc_title">본문</h2>
        <?php
        // 파일 출력
        $v_img_count = count($view['file']);
        if($v_img_count) {
            echo "<div id=\"bo_v_img\">\n";

            for ($i=0; $i<=count($view['file']); $i++) {
                echo get_file_thumbnail($view['file'][$i]);
            }

            echo "</div>\n";
        }
         ?>

        <!-- 본문 내용 시작 { -->
        <div id="bo_v_con" class="bo_con">
            <h3>안건내용</h3>
            <div class="bo_in_con"><?php echo get_view_thumbnail($view['content']); ?></div>
        </div>
        <?php //echo $view['rich_content']; // {이미지:0} 과 같은 코드를 사용할 경우 ?>
        <!-- } 본문 내용 끝 -->
        
        <?php if($view['wr_5']){ ?>
         <div id="bo_r_con" class="bo_con">
            <h3>계획내용</h3>
            <div class="bo_in_con" style="margin-top:-20px;"><?php echo get_view_thumbnail($view['wr_5']); ?></div>
        </div>
        <?php }else{ ?>
        <div style="text-align:left !important;">
            <p>안건요청에 대해서 "답변/수정"페이지로 들어가 계획내용의 답변을 작성하고 안건상태를 "<span style="font-weight:bold;color:#000;">완료</span>"로 해 주세요.</p>
            <p>계획내용의 답변을 작성할 수 없는 상황이면 안건상태를 "<span style="font-weight:bold;color:blue;">확인중</span>"으로 해 주세요.</p>
        </div>
        <?php } ?>

        <?php
        if(@count($row['bo_f_ref'])){
            echo '<div class="multifile">'.PHP_EOL;
            echo '<h4>참고자료파일</h4>'.PHP_EOL;
            echo '<ul>'.PHP_EOL;
            for($i=0;$i<count($row['bo_f_ref']);$i++) {
                echo "<li>[".($i+1).']'.$row['bo_f_ref'][$i]['file']."</li>".PHP_EOL;
            }
            echo '</ul>'.PHP_EOL;
            echo '</div>'.PHP_EOL;
        }
        ?>
    </section>

    <?php
    // 코멘트 입출력
    //if(defined('G5_IS_ADMIN')) include_once(G5_USER_ADMIN_PATH.'/bbs_view_comment.php');
    //else include_once(G5_BBS_PATH.'/view_comment.php');
    
	?>
</article>
<!-- } 게시판 읽기 끝 -->

<script>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script>
$(function() {

});

</script>
<!-- } 게시글 읽기 끝 -->