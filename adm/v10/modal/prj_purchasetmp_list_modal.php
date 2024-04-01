<div id="prj_purchasetmp_list_modal" class="modal mdl_hide">
    <div class="mdl_bg"></div>
    <div class="mdl_box">
        <i class="fa fa-times mdl_close" aria-hidden="true"></i>
        <div class="mdl_head">
            <h1 class="mdl_title">그룹발주등록</h1>
        </div>
        <form id="pptppcform01" name="pptppcform01" action="./prj_purchasetmp_list_modal_update.php" onsubmit="return fpptppcform01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="ppt_idxs" id="ppt_idxs" value="">
            <input type="hidden" name="com_idx" id="com_idx" value="">
            <input type="hidden" name="prj_idx" id="prj_idx" value="">
            <input type="hidden" name="token" value="">
            <?=$form_input?>
            <div class="mdl_cont">
                <table class="mdl_tbl">
                <colgroup>
                    <col class="grid_4" style="width:30%;">
                    <col style="width:70%;">
                </colgroup>
                <tbody>
                    <tr>
                        <th scope="row">그룹발주날짜</th>
                        <td>
                            <input type="text" name="ppc_date" id="ppc_date" value="" class="frm_input" style="width:130px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">그룹발주금액</th>
                        <td>
                            <input type="text" name="ppc_price" id="ppc_price" value="" class="frm_input" style="width:130px;text-align:right;">&nbsp;원
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">제목(중요품목)</th>
                        <td>
                            <input type="text" name="ppc_subject" id="ppc_subject" value="" class="frm_input" style="width:100%;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">내용</th>
                        <td>
                            <textarea name="ppc_content" id="ppc_content" rows="3"><?=$ppt['ppt_content']?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="multi_file_ppt">그룹발주 관련파일</label></th>
                        <td>
                            <?php echo help("그룹발주관련 파일들을 등록하고 관리해 주시면 됩니다."); ?>
                            <input type="file" id="multi_file_ppc" name="ppc_datas[]" multiple class="">
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
            <div class="mdl_tail">
                <input type="submit" name="mdl_button" value="그룹발주등록" onclick="document.pressed=this.value" class="btn btn_04">
            </div>
        </form>
    </div>
</div>
<script>
$(function(){
    //날짜입력
    $("#prj_purchasetmp_list_modal #ppc_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    //개별발주서 멀티파일
	$('#multi_file_ppc').MultiFile();
});

function fpptppcform01_submit(f){
    if(!f.ppt_idxs.value){
        alert('개별발주정보가 제대로 넘어오지 않았습니다.');
        return false;
    }
    if(!f.com_idx.value){
        alert('공급업체정보가 제대로 넘어오지 않았습니다.');
        return false;
    }
    if(!f.prj_idx.value){
        alert('프로젝트정보가 제대로 넘어오지 않았습니다.');
        return false;
    }
    if(!f.ppc_date.value){
        alert('그룹발주날짜를 입력해 주세요.');
        f.ppc_date.focus();
        return false;
    }
    if(!f.ppc_price.value){
        alert('그룹발주금액을 입력해 주세요.');
        f.ppc_price.focus();
        return false;
    }
    if(!f.ppc_subject.value){
        alert('제목(중요품목)을 입력해 주세요.');
        f.ppc_subject.focus();
        return false;
    }

    return true;
}
</script>