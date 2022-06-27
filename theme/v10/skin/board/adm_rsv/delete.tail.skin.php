<?php
delete_cache_latest($bo_table);

if($calendar)
    goto_url(short_url_clean(G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;calendar='.$calendar.'&amp;page='.$page.'&amp;'.$qstr));
else
    goto_url(short_url_clean(G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;page='.$page.'&amp;'.$qstr));
exit;