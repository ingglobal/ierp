<?php
foreach($_POST as $pk => $pv){
    ${$pk} = $pv;
}

//exit;