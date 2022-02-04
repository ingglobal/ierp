<div style="margin:30px auto;width:660px;border:">
	<h2 style="font-size:20px;text-align:center;">견적서 첨부파일 이메일</h2>
	<p style="margin:0;font-size:12px;padding-bottom:5px;">
	No.<?=$orl_no?>
    </p>
    <p>
        <?=$m['short_memo']?> 안녕하세요.<br><br>
        "<?=$m['subject']?>"를 전송합니다<br>
        아래 파일명[다운로드] 버튼을 클릭해서 다운로드 받으세요.
        <br>
        <br>
        <?=$file_down?>
        <div style="margin-top:100px;padding-top:20px;border-top:1px solid #ddd;">
            <p><?=$default['de_admin_company_name']?></p>
            <p><strong><?=$default['de_admin_business_name']?></strong></p>
            <p>영업담당자 : <?=$m['from_name']?></p>
            <p>이메일 : <?=$m['from_email']?></p>
        </div>
    </p>
</div>