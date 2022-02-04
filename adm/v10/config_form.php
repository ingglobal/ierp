<?php
$sub_menu = "950900";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

if(!$config['cf_faq_skin']) $config['cf_faq_skin'] = "basic";
if(!$config['cf_mobile_faq_skin']) $config['cf_mobile_faq_skin'] = "basic";

$g5['title'] = '솔루션설정';
include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
    <li><a href="#anc_cf_message">메시지설정</a></li>
    <li><a href="#anc_cf_secure">관리설정</a></li>
</ul>';

if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
    $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
}
?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">

<section id="anc_cf_default">
	<h2 class="h2_frm">기본설정</h2>
	<?php echo $pg_anchor ?>
	
	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>기본설정</caption>
		<colgroup>
			<col class="grid_4">
			<col>
			<col class="grid_4">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row">디폴트상태값</th>
			<td colspan="3">
				<?php echo help('pending=대기,auto-draft=자동저장,ok=정상,hide=숨김,trash=삭제') ?>
				<input type="text" name="set_status" value="<?php echo $g5['setting']['set_status'] ?>" id="set_status" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">분류(카테고리) terms</th>
			<td colspan="3">
				<?php echo help('') ?>
				<input type="text" name="set_taxonomies" value="<?php echo $g5['setting']['set_taxonomies'] ?>" id="set_taxonomies" required class="required frm_input" style="width:80%;">
			</td>
		</tr>
		<tr>
			<th scope="row">회원레벨명 mb_level</th>
			<td colspan="3">
				<input type="text" name="set_mb_levels" value="<?php echo $g5['setting']['set_mb_levels'] ?>" id="set_mb_levels" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">직책(권한) mb_1</th>
			<td colspan="3">
				<?php echo help('1=지원팀,4=팀원,6=팀장,8=센터장,10=부서장,20=운영관리') ?>
				<input type="text" name="set_mb_positions" value="<?php echo $g5['setting']['set_mb_positions'] ?>" id="set_mb_positions" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">직급(직위) mb_3</th>
			<td colspan="3">
				<?php echo help('2=파트타임............50=팀장,60=과장,70=차장,80=부장,90=센터장,100=본부장,110=실장,120=이사,130=부사장,140=대표') ?>
				<input type="text" name="set_mb_ranks" value="<?php echo $g5['setting']['set_mb_ranks'] ?>" id="set_mb_ranks" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
        <tr>
			<th scope="row">정보(등급/권한) mb_6</th>
			<td colspan="3">
				<?php echo help('6=6등급,5=5등급,4=4등급,3=3등급,2=2등급,1=1등급') ?>
				<input type="text" name="set_mb_grade" value="<?php echo $g5['setting']['set_mb_grade'] ?>" id="set_mb_grade" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">업종분류</th>
			<td colspan="3">
				<?php echo help('electricity=전기,electronic=전자,facility=설비,food=식품,parts=자재') ?>
				<input type="text" name="set_com_type" value="<?php echo $g5['setting']['set_com_type'] ?>" id="set_com_type" required class="required frm_input" style="width:90%;">
			</td>
		</tr>
		<tr>
			<th scope="row">업체분류</th>
			<td colspan="3">
				<?php echo help('normal=일반,buyer=매입업체,project=프로젝트진행업체,goverment=관공서') ?>
				<input type="text" name="set_com_class" value="<?php echo $g5['setting']['set_com_class'] ?>" id="set_com_class" required class="required frm_input" style="width:90%;">
			</td>
		</tr>
		<tr>
			<th scope="row">업체상태값 설정</th>
			<td colspan="3">
				<?php echo help('ok=정상,pending=대기,trash=휴지통,delete=삭제,hide=숨김,prohibit=영업금지업체') ?>
				<input type="text" name="set_com_status" value="<?php echo $g5['setting']['set_com_status']; ?>" class="frm_input" style="width:60%;">
			</td>
		</tr>
        <tr>
            <th scope="row">업체-영업자 상태값 설정</th>
            <td colspan="3">
                <input type="text" name="set_cms_status" value="<?php echo $g5['setting']['set_cms_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
        <tr>
            <th scope="row">업체-회원 상태 설정</th>
            <td colspan="3">
                <input type="text" name="set_cmm_status" value="<?php echo $g5['setting']['set_cmm_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">금액타입</th>
            <td colspan="3">
                <input type="text" name="set_price_type" value="<?php echo $g5['setting']['set_price_type']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
		<tr>
            <th scope="row">수입 금액타입</th>
            <td colspan="3">
                <?php echo help('금액타입 중에서 수입에 표시할 상태값(영문)만 입력하세요.') ?>
                <input type="text" name="set_price_type2" value="<?php echo $g5['setting']['set_price_type2']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
        <tr>
            <th scope="row">지출 금액타입</th>
            <td colspan="3">
                <?php echo help('금액타입 중에서 지출에 표시할 타입을 입력하세요.') ?>
                <input type="text" name="set_exprice_type" value="<?php echo $g5['setting']['set_exprice_type']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
        <tr>
            <th scope="row">지출 금액상태</th>
            <td colspan="3">
                <?php echo help('금액유형 중에서 지출에 표시할 상태를 입력하세요.') ?>
                <input type="text" name="set_exprice_status" value="<?php echo $g5['setting']['set_exprice_status']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
		<tr>
            <th scope="row">매입 금액타입</th>
            <td colspan="3">
                <?php echo help('금액타입 중에서 매입타입의 값(영문)만 입력하세요. 매입은 매출과 구분됩니다.') ?>
                <input type="text" name="set_purchase_type" value="<?php echo $g5['setting']['set_purchase_type']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
		<tr>
            <th scope="row">유무선택</th>
            <td colspan="3">
                <input type="text" name="set_yes_no" value="<?php echo $g5['setting']['set_yes_no']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">간트차트구분</th>
            <td colspan="3">
                <input type="text" name="set_prs_type" value="<?php echo $g5['setting']['set_prs_type']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
        <tr>
            <th scope="row">부서명</th>
            <td colspan="3">
                <input type="text" name="set_department_name" value="<?php echo $g5['setting']['set_department_name']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
			<th scope="row">차트별색상값</th>
			<td colspan="3">
				<?php echo help('간트챠트 색상값입니다.'); ?>
                <?php
                $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_prs_type']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', trim($set_value));
                    echo ' <input type="text" name="set_gantt_color_'.$key.'" value="'.$g5['setting']['set_gantt_color_'.$key].'" class="frm_input" style="width:150px;margin-bottom:5px;"> ('.$value.' <span class="color_gray">'.$key.'</span>)<br>'.PHP_EOL;
                }
                unset($set_values);unset($set_value);
                ?>
			</td>
		</tr>
		<tr>
			<th scope="row">차트별두께값</th>
			<td colspan="3">
				<?php echo help('간트챠트 두께입니다.'); ?>
                <?php
                $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_prs_type']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', trim($set_value));
                    echo ' <input type="text" name="set_gantt_thickness_'.$key.'" value="'.$g5['setting']['set_gantt_thickness_'.$key].'" class="frm_input" style="width:150px;margin-bottom:5px;"> ('.$value.' <span class="color_gray">'.$key.'</span>)<br>'.PHP_EOL;
                }
                unset($set_values);unset($set_value);
                ?>
			</td>
		</tr>
		<tr>
            <th scope="row">간트챠트진행율표시</th>
            <td colspan="3">
                <?php echo help('간트챠트에서 진행율을 표시할 상태값을 입력하세요. (영문자)'); ?>
                <input type="text" name="set_prs_rate_display" value="<?php echo $g5['setting']['set_prs_rate_display']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">담당자 역할</th>
            <td colspan="3">
                <input type="text" name="set_worker_type" value="<?php echo $g5['setting']['set_worker_type']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">결제차수</th>
            <td colspan="3">
                <input type="text" name="set_pay_no" value="<?php echo $g5['setting']['set_pay_no']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">신청상태</th>
            <td colspan="3">
                <input type="text" name="set_apply_status" value="<?php echo $g5['setting']['set_apply_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
        <tr>
            <th scope="row">승인상태</th>
            <td colspan="3">
                <input type="text" name="set_approve_status" value="<?php echo $g5['setting']['set_approve_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
        <tr>
            <th scope="row">잔업유형</th>
            <td colspan="3">
                <?php echo help('etw=연장근무,ntw=야간근무,anw=철야근무,hdw=휴일근무,hew=휴일연장근무,hnw=휴일야간근무,haw=휴일철야근무'); ?>
                <input type="text" name="set_pro_type" value="<?php echo $g5['setting']['set_pro_type']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">잔업수당비율</th>
            <td colspan="3">
                <input type="text" name="set_pro_extrapay_rate" value="<?php echo $g5['setting']['set_pro_extrapay_rate']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">AS접수경로</th>
            <td colspan="3">
                <input type="text" name="set_as_receiptpath" value="<?php echo $g5['setting']['set_as_receiptpath']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">AS상태</th>
            <td colspan="3">
                <input type="text" name="set_as_status" value="<?php echo $g5['setting']['set_as_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">발행예정일예고일수</th>
            <td colspan="3">
                <input type="text" name="set_plan_alarmdaycnt" value="<?php echo $g5['setting']['set_plan_alarmdaycnt']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">세율</th>
            <td colspan="3">
                <input type="text" name="set_tariff" value="<?php echo $g5['setting']['set_tariff']; ?>" class="frm_input" style="width:60px;text-align:right;padding:0 10px;">&nbsp;%
            </td>
        </tr>
		<tr>
            <th scope="row">견적상태</th>
            <td colspan="3">
                <input type="text" name="set_prj_status" value="<?php echo $g5['setting']['set_prj_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">비운영자<br>견적차단항목</th>
            <td colspan="3">
                <input type="text" name="set_prj_blocklist" value="<?php echo $g5['setting']['set_prj_blocklist']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">수입지출상태</th>
            <td colspan="3">
                <input type="text" name="set_prp_status" value="<?php echo $g5['setting']['set_prp_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">수입지출날짜명칭</th>
            <td colspan="3">
                <input type="text" name="set_prp_data_name" value="<?php echo $g5['setting']['set_prp_data_name']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">프로젝트타입</th>
            <td colspan="3">
                <input type="text" name="set_prj_type" value="<?php echo $g5['setting']['set_prj_type']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
        <tr>
            <th scope="row">프로젝트타입(견적)</th>
            <td colspan="3">
                <input type="text" name="set_prj_type2" value="<?php echo $g5['setting']['set_prj_type2']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">프로젝트일정상태</th>
            <td colspan="3">
                <input type="text" name="set_prs_status" value="<?php echo $g5['setting']['set_prs_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">자사,타사구분</th>
            <td colspan="3">
                <input type="text" name="set_prj_belongto" value="<?php echo $g5['setting']['set_prj_belongto']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">영업팀조직코드</th>
            <td colspan="3">
                <input type="text" name="set_team_sales" value="<?php echo $g5['setting']['set_team_sales']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">시스템팀조직코드</th>
            <td colspan="3">
                <input type="text" name="set_team_system" value="<?php echo $g5['setting']['set_team_system']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">회계팀조직코드</th>
            <td colspan="3">
                <input type="text" name="set_team_account" value="<?php echo $g5['setting']['set_team_account']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">ONESIGNAL APP ID</th>
            <td colspan="3">
                <input type="text" name="set_onesignal_id" value="<?php echo $g5['setting']['set_onesignal_id']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">ONESIGNAL REST API KEY</th>
            <td colspan="3">
                <?php echo help('OneSignal > Settings > Keys & IDs : REST API KEY'); ?>
                <input type="text" name="set_onesignal_key" value="<?php echo $g5['setting']['set_onesignal_key']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">재고기준수량</th>
            <td colspan="3">
                <?php echo help('기준수량 이하로 내려가면 문자통지를 하게 됩니다.'); ?>
                <input type="text" name="set_it_stock_min" value="<?php echo $g5['setting']['set_it_stock_min']; ?>" class="frm_input" style="width:60px;text-align:right;padding:0 10px;"> 개
            </td>
        </tr>
		<tr>
            <th scope="row">재고문자 휴대폰번호</th>
            <td colspan="3">
                <?php echo help('재고관련 문자통지 휴대폰 번호입니다.'); ?>
                <input type="text" name="set_it_stock_hp" value="<?php echo $g5['setting']['set_it_stock_hp']; ?>" class="frm_input" style="width:100px;">
            </td>
        </tr>
		<tr>
            <th scope="row">안건상태</th>
            <td colspan="3">
                <input type="text" name="set_agenda_status" value="<?php echo $g5['setting']['set_agenda_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		</tbody>
		</table>
	</div>
</section>


    
<section id="anc_cf_message">
    <h2 class="h2_frm">메시지설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>메시지설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">재고통보 문자내용</th>
            <td colspan="3">
                <?php echo help('80byte(약 40자)를 넘을 수 없습니다. 치환 변수: {부품명} {재고량}'); ?>
                <input type="text" name="set_it_stock_sms_content" value="<?php echo $g5['setting']['set_it_stock_sms_content']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
        <tr>
            <th scope="row">프로젝트일정 문자내용</th>
            <td colspan="3">
                <?php echo help('80byte 넘을 수 없음. 치환 변수: {프로젝트명} {종료일} {이름} {회원아이디} {HOME_URL}'); ?>
                <input type="text" name="set_project_schedule_sms_content" value="<?php echo $g5['setting']['set_project_schedule_sms_content']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
        <tr style="display:none;">
            <th scope="row">알람/예지 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {제목} {업체명} {이름} {설비명} {코드} {만료일} {년월일} {남은기간} {HOME_URL}'); ?>
                <input type="text" name="set_error_subject" value="<?php echo $g5['setting']['set_error_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_error_content", get_text($g5['setting']['set_error_content'], 0)); ?>
            </td>
        </tr>
        <tr style="display:none;">
            <th scope="row">계획정비 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {제목} {업체명} {이름} {설비명} {만료일} {년월일} {남은기간} {HOME_URL}'); ?>
                <input type="text" name="set_maintain_plan_subject" value="<?php echo $g5['setting']['set_maintain_plan_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_maintain_plan_content", get_text($g5['setting']['set_maintain_plan_content'], 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">게시판 new 아이콘</th>
            <td>
                <input type="text" name="set_new_icon_hour" value="<?php echo $g5['setting']['set_new_icon_hour']; ?>" class="frm_input" style="width:20px;"> 시간동안 new 아이콘 표시
            </td>
            <th scope="row">new 아이콘 주말포함</th>
            <td>
                <div style="visibility:hidden;">
                <label for="set_new_icon_holiday_yn_1">
                    <input type="radio" name="set_new_icon_holiday_yn" value="1" id="set_new_icon_holiday_yn_1" <?php echo ($g5['setting']['set_new_icon_holiday_yn']) ? 'checked':'' ?>> 영업일만 포함
                </label> &nbsp;&nbsp;
                <label for="set_new_icon_holiday_yn_0">
                    <input type="radio" name="set_new_icon_holiday_yn" value="0" id="set_new_icon_holiday_yn_0" <?php echo ($g5['setting']['set_new_icon_holiday_yn']) ? '':'checked' ?>> 주말까지 포함
                </label>
                </div>
            </td>
        </tr>
        <tr style="display:none;">
            <th scope="row">만료공지 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {법인명} {업체명} {담당자} {년월일} {승인명} {남은기간} {HOME_URL} {연락처} {이메일}'); ?>
                <input type="text" name="set_expire_email_subject" value="<?php echo $g5['setting']['set_expire_email_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_expire_email_content", get_text($g5['setting']['set_expire_email_content'], 0)); ?>
            </td>
        </tr>
		</tbody>
		</table>
	</div>
</section>

<section id="anc_cf_secure">
    <h2 class="h2_frm">관리설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>관리자 설정입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>관리설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">관리자메모</th>
            <td>
                <?php echo help('관리자 메모입니다.') ?>
                <textarea name="set_memo_super" id="set_memo_super"><?php echo get_text($g5['setting']['set_memo_super']); ?></textarea>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){

});

function fconfigform_submit(f) {

    <?php echo get_editor_js("set_expire_email_content"); ?>
    <?php echo chk_editor_js("set_expire_email_content"); ?>
    <?php echo get_editor_js("set_maintain_plan_content"); ?>
    <?php echo chk_editor_js("set_maintain_plan_content"); ?>
    <?php echo get_editor_js("set_error_content"); ?>
    <?php echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_form_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
