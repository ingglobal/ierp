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

<!-- 게시판 이름 표시 <div id="bo_v_table"><?php echo ($board['bo_mobile_subject'] ? $board['bo_mobile_subject'] : $board['bo_subject']); ?></div> -->
<ul class="btn_top top btn_bo_user"> 
	<li><a href="#bo_vc" class="btn_b03 btn" title="댓글"><i class="fa fa-commenting" aria-hidden="true"></i><span class="sound_only">댓글</span></a></li>
    <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b03 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</a></li><?php } ?>
	
	<li>
		<button type="button" class="btn_more_opt btn_b03 btn is_view_btn" title="게시판 리스트 옵션"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
    	<?php ob_start(); ?>
	    <ul class="more_opt is_view_btn">
			<?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
	    	<?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?>
	    	<?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>">검색</a></li><?php } ?>
	    	<li><a href="<?php echo $list_href ?>" class="btn_list"><i class="fa fa-list" aria-hidden="true"></i> 목록</a></li>
		</ul>
		<?php $link_buttons = ob_get_contents(); ob_end_flush(); ?>
	</li>
</ul>
<script>
jQuery(function($){
    // 게시판 보기 버튼 옵션
    $(".btn_more_opt.is_view_btn").on("click", function(e) {
        e.stopPropagation();
        $(".more_opt.is_view_btn").toggle();
    });
    // 게시글 공유
    $(".btn_share_opt").on("click", function(e) {
        e.stopPropagation();
        $("#bo_v_share").toggle();
    });
    $(document).on("click", function (e) {
        if(!$(e.target).closest('.is_view_btn').length) {
            $(".more_opt.is_view_btn").hide();
            $("#bo_v_share").hide();
        }
    });
});
</script>
<article id="bo_v" style="width:<?php echo $width; ?>">
    <header>
        <h2 id="bo_v_title">
            <?php if ($category_name) { ?>
            <span class="bo_v_cate"><?php echo $view['ca_name']; // 분류 출력 끝 ?></span> 
            <?php } ?>
            <span class="bo_v_tit"><?php echo cut_str(get_text($view['wr_subject']), 70); // 글제목 출력 ?></span>
        </h2>
        <div id="bo_v_info">
	        <h2>페이지 정보</h2>
            <?php $mb_pic = get_table_meta('member','mb_id',$write['wr_2']); ?>
	        <span>작성자: </span><strong style="font-weight:700;"><?php echo $view['name'] ?></strong>
            <span style="margin-left:5px;">책임자: </span><strong style="font-weight:700;"><?php echo $mb_pic['mb_name'] ?></strong>
            <span style="margin-left:5px;">상태: </span><strong style="font-weight:700;"><?php echo $g5['set_agenda_status_value'][$view['wr_4']] ?></strong><br>
	        <span>등록일: </span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo date("y-m-d H:i", strtotime($view['wr_datetime'])) ?><br>
            <span>접수일: </span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo ($view['wr_6'])?date("y-m-d H:i", strtotime($view['wr_6'])):'00-00-00 00:00'; ?><br>
	        <span>조회: </span><strong><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($view['wr_hit']) ?></strong>
	        <span>댓글: </span><strong><i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo number_format($view['wr_comment']) ?></strong>
	    </div>
    </header>

    <section id="bo_v_atc">
        <h2 id="bo_v_atc_title">본문</h2>

        <div id="bo_v_con" class="bo_con">
            <h3>안건내용</h3>
            <div class="bo_in_con"><?php echo get_view_thumbnail($view['content']); ?></div>
        </div>


        <?php if($view['wr_5']){ ?>
         <div id="bo_r_con" class="bo_con">
            <h3>계획내용</h3>
            <div class="bo_in_con"><?php echo get_view_thumbnail($view['wr_5']); ?></div>
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

<script>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<!-- 게시글 보기 끝 -->

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