<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

if ($id) {
    if (mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `id` = "' . $id . '" AND `type` = "m"' . ($rights >= 7 ? '' : ' AND `forum`.`close` != "1"') . ';'), 0)) {
        $refid = mysql_result(mysql_query('SELECT `refid` FROM `forum` WHERE `id` = "' . $id . '"'), 0);
        $them = mysql_result(mysql_query('SELECT `text` FROM `forum` WHERE `id` = "' . $refid . '"'), 0);
        $page = ceil(mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `refid` = "' . $refid . '" AND `id` <= "' . $id . '"'), 0) / $kmess);
        header('Location: ' . SITE_URL . '/forum/' . functions::bodau($them) . '.' . $refid . '.html?page=' . $page . '#post' . $id . '');
        exit;
    } else {
        require('../incfiles/head.php');
        echo functions::display_error($lng_forum['error_post_deleted']);
    }
} else {
    require('../incfiles/head.php');
    echo functions::display_error($lng['error_wrong_data']);
}