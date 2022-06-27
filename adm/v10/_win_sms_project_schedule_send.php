<?php
$sub_menu = '960230';
include_once('./_common.php');

$g5['title'] = '프로젝트 일정 문자 통지';

include_once(G5_PATH.'/head.sub.php');


?>
<style>
/* html,body{overflow:hidden;} */
#com_sch_list{padding:20px;position:relative;}
.btn_close{position:absolute;right:20px;top:13px;}
</style>
<div class="new_win">
	<?php if(G5_IS_MOBILE){ ?>
	<a href="javascript:" class="btn btn_close" onclick="window.close()"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></a>
	<?php }else{ ?>
	<a href="javascript:" class="btn btn_submit btn_close" onclick="window.close()">닫기</a>
	<?php } ?>

	<h1><?php echo $g5['title']; ?></h1>
    <div class="local_desc01 local_desc" style="display:no ne;">
        <p>해당 날짜 이후 프로젝트 일정을 완료하지 못한 담당자들에게 문자를 발송합니다.</p>
    </div>
	<div id="com_sch_list" class="new_win" style="word-break:break-all;">

        <?php
        // 문자 발송
        if ($config['cf_sms_use'] == 'icode' && $prs_date != '0000-00-00')
        {

            $send_number = preg_replace("/[^0-9]/", "", $sms5['cf_phone']);// 발신자번호
            $sms_contents = $g5['setting']['set_project_schedule_sms_content'];

            $sql = "SELECT prs.prj_idx, prs_end_date, prs_status
                        , prj_name, prj_percent, prj_status
                        , mb_id, mb_name, mb_hp
                    FROM {$g5['project_schedule_table']} AS prs
                        LEFT JOIN {$g5['project_table']} AS prj ON prj.prj_idx = prs.prj_idx
                        LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = prs.mb_id_worker
                    WHERE prs_status NOT IN ('cancel','trash')
                        AND prj_status NOT IN ('trash','delete')
                        AND prs_end_date < '".$prs_date."'
                        AND prj_percent < 100
            ";
            // echo $sql.'<br>';

            if($config['cf_sms_type'] == 'LMS') {
                include_once(G5_LIB_PATH.'/icode.lms.lib.php');

                $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

                // SMS 모듈 클래스 생성
                if($port_setting !== false) {
                    $SMS = new LMS;
                    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                    // $strDest     = array();
                    $rs1 = sql_query($sql,1);
                    for($j=0;$row=sql_fetch_array($rs1);$j++) {

                        $sms_content[$j] = preg_replace("/{프로젝트명}/", cut_str($row['prj_name'],15), $sms_contents);
                        $sms_content[$j] = preg_replace("/{종료일}/", $row['prs_end_date'], $sms_content[$j]);
                        $sms_content[$j] = preg_replace("/{이름}/", $row['mb_name'], $sms_content[$j]);
                        $sms_content[$j] = preg_replace("/{회원아이디}/", $row['mb_id'], $sms_content[$j]);
                        $sms_content[$j] = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $sms_content[$j]);
                        $sms_content[$j] = preg_replace("/{DATA_AGREE_URL}/", '<a href="'.G5_USER_URL.'/e1.php?'.$com_idx.'" style="color:white;">자료사용동의하기</a>', $sms_content[$j]);
            
                        $sms_hp[$j] = preg_replace("/[^0-9]/", "", $row['mb_hp']);
                        $strData[] = iconv_euckr(stripslashes($sms_content[$j]));
                        $strDest[]   =  $sms_hp[$j];
                        echo cut_str($row['prj_name'],15).' / '.$row['mb_name'].'님께 문자 발송<br>';
                    }
                    // $strDest[]   = $receive_number;
                    $strCallBack = $send_number;
                    $strCaller   = iconv_euckr(trim($config['cf_title']));
                    $strSubject  = '';
                    $strURL      = '';
                    // $strData     = iconv_euckr($sms_contents);
                    $strDate     = '';
                    $nCount      = count($strDest);

                    $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                    $SMS->Send();
                    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                }
            }
            else {
                include_once(G5_LIB_PATH.'/icode.sms.lib.php');

                $SMS = new SMS; // SMS 연결
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
                // $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
                $rs2 = sql_query($sql,1);
                for($j=0;$row=sql_fetch_array($rs2);$j++) {

                    $sms_content[$j] = preg_replace("/{프로젝트명}/", cut_str($row['prj_name'],15), $sms_contents);
                    $sms_content[$j] = preg_replace("/{종료일}/", $row['prs_end_date'], $sms_content[$j]);
                    $sms_content[$j] = preg_replace("/{이름}/", $row['mb_name'], $sms_content[$j]);
                    $sms_content[$j] = preg_replace("/{회원아이디}/", $row['mb_id'], $sms_content[$j]);
                    $sms_content[$j] = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $sms_content[$j]);
                    $sms_content[$j] = preg_replace("/{DATA_AGREE_URL}/", '<a href="'.G5_USER_URL.'/e1.php?'.$com_idx.'" style="color:white;">자료사용동의하기</a>', $sms_content[$j]);
        
                    $sms_hp[$j] = preg_replace("/[^0-9]/", "", $row['mb_hp']);
                    // $sms_hp[$j] = preg_replace("/[^0-9]/", "", '010-5581-3430');
                    $sms_msg[$j] = iconv_euckr(stripslashes($sms_content[$j]));
                    $SMS->Add($sms_hp[$j], $send_number, $config['cf_icode_id'], $sms_msg[$j], "");
                    echo cut_str($row['prj_name'],15).' / '.$row['mb_name'].'님께 문자 발송<br>';
                }
                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }

        }
        if($j<=0) {
            echo '관련 일정이 존재하지 않습니다.';
        }



		?>

	</div><!--#com_sch_list-->
</div><!--.new_win-->
<script>
$('body').attr({'onresize':'parent.resizeTo(450,640)','onload':'parent.resizeTo(450,640)'});

</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>