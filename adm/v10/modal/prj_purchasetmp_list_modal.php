<div id="prj_purchasetmp_list_modal" class="modal mdl_hide">
    <div class="mdl_bg"></div>
    <div class="mdl_box">
        <i class="fa fa-times mdl_close" aria-hidden="true"></i>
        <div class="mdl_head">
            <h1 class="mdl_title">정식발주등록</h1>
        </div>
        <form id="pptppcform01" name="pptppcform01" action="./ppt_ppc_create_update.php" onsubmit="return form01_submit(this);" method="post">
            <input type="text" name="ppt_idxs" id="ppt_idxs" value="">
            <div class="mdl_cont">
                <ul>
                    <li>
                        <strong>입고예정일</strong>
                        <span><input type="text" name="mto_input_date" class="frm_input"></span>
                    </li>
                </ul>
            </div>
            <div class="mdl_tail">
                <input type="button" value="발주등록" class="btn btn_05">
            </div>
        </form>
    </div>
</div>