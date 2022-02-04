<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
if($calendar) include_once($board_skin_path.'/list_calendar.skin.php');
else include_once($board_skin_path.'/list_default.skin.php');
