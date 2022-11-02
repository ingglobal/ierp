<?php
include_once('./_common.php');

// echo $prj_idx.' > '.$prj_percent;

$sql = " UPDATE {$g5['project_table']} SET
            prj_percent = '{$prj_percent}'
        WHERE prj_idx = '{$prj_idx}'
";
sql_query($sql);

echo $prj_percent;