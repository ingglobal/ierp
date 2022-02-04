<?php
include_once('./_common.php');

$g5['title'] = $board['bo_subject'].' 환경설정';
include_once('./_head.php');

if(!$is_admin)
    alert('관리자만 접속할 수 있습니다.');

// bo_7=>longtext 환경설정 확장변수로 사용하기 위함
$q = sql_query( 'DESCRIBE '.$g5['board_table'] );
while($row = sql_fetch_array($q)) {
    if($row['Field']=='bo_7' && $row['Type']=='varchar(255)') {
        //echo $row['Field'].' - '.$row['Type'].'<br>';
        sql_query(" ALTER TABLE `{$g5['board_table']}` CHANGE `bo_7` `bo_7` longtext ", true);
    }
}


// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>

<section id="bo_config">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

	<div class="config_caution">
		<ol>
			<li>환경 설정 페이지입니다. 설정 항목들을 확인하시고 필요한 설정을 해 주시기 바랍니다.</li>
            <li>기타 게시판 관련 상세 설정은 게시판관리 페이지에서 설정해 주세요.</li>
		</ol>
	</div>
    
    <form name="form01" class="config_form" action="./config_form_update.php" onsubmit="return form_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    
    <div class="tbl_frm01 tbl_wrap">
        <h2>기본 환경설정</h2>
        <ul>
            <li>
                <label class="frm_label">상태값</label>
                <input type="text" name="bo_9" id="bo_9" value="<?php echo $board['bo_9'] ?>" style="width:80%;" required class="frm_input required" placeholder="예약상태값">
                <span class="frm_info">
                    pending=대기,working=진행중,reject=반려(거부),holding=홀딩,ok=수주완료,trash=삭제
                </span>                
            </li>
            <li>
                <label class="frm_label">등록 초기상태값</label>
                <input type="text" name="set_default_status" id="set_default_status" value="<?php echo $board['set_default_status'] ?>" style="width:100px;" class="frm_input" placeholder="등록초기상태값">
                <span class="frm_info">
                    상태값 항목값을 참고하셔서 값을 정확히 입력해 주세요. pending(대기)상태로 설정하면 신청 확인한 후 상태값을 바꿔 주어야 합니다.
                </span>                
            </li>
            <li>
                <label class="frm_label">관심등급</label>
                <input type="text" name="bo_8" id="bo_8" value="<?php echo $board['bo_8'] ?>" style="width:80%;" class="frm_input" placeholder="작업등급">
                <span class="frm_info">
                    1=최고관심,2=관심,3=기회가오면,4=보수적(기존협력업제있음),5=관심없음
                </span>                
            </li>
            <li style="display:<?=($is_admin=='super')?:'none'?>">
                <label class="frm_label">sub_menu 코드</label>
                <input type="text" name="bo_1" id="bo_1" value="<?php echo $board['bo_1'] ?>" style="width:10%;" required class="frm_input required" placeholder="sub_menu코드">
                <span class="frm_info">
                    게시판이 관리자단에 위치할 경우 sub_menu 코드가 있어야 합니다.
                </span>                
            </li>
        </ul>
        
        <div class="btn_confirm write_div">
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?=$bo_table?>" class="btn_cancel btn">취소</a>
            <input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit btn">
        </div>
        
    </div>
    </form>

</section>

<script>
function form_submit(f)
{
	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}
</script>

<?php
include_once('./_tail.php');
?>