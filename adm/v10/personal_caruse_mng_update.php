<?php
include_once('./_common.php');

$sql = " UPDATE {$g5['config_table']} SET 
            cf_perprice_gasoline = '{$cf_perprice_gasoline}'
            ,cf_perprice_diesel = '{$cf_perprice_diesel}'
            ,cf_perkm_gasoline = '{$cf_perkm_gasoline}'
            ,cf_perkm_diesel = '{$cf_perkm_diesel}'
            ,cf_perprice_lpg = '{$cf_perprice_lpg}'
            ,cf_perkm_lpg = '{$cf_perkm_lpg}'
";
sql_query($sql);