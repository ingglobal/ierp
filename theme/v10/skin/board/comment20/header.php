<?php
include_once('./_common.php');

if(!$ser_prj_idx)
    return;

$prj = get_table_meta('project','prj_idx',$ser_prj_idx);
$com = get_table_meta('company','com_idx',$prj['com_idx']);
// print_r2($prj);
// print_r2($com);
?>
<div class="title_com_prj_name">
    <b>프로젝트명</b>: <?php echo $prj['prj_name']?> (<?php echo $com['com_name']?>)
</div>
