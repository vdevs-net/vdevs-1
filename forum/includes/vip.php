<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

if (($rights != 3 && $rights < 6) || !$id) {
    header('Location: index.php');
    exit;
}
$req = mysql_query("SELECT `text`, `vip` FROM `forum` WHERE `id` = '" . $id . "' AND `type` = 't' LIMIT 1");
if (mysql_num_rows($req)) {
    $res = mysql_fetch_assoc($req);
    mysql_query("UPDATE `forum` SET `vip` = '" . ($res['vip'] ? '0' : '1') . "' WHERE `id` = '$id'");
    header('Location: '.functions::bodau($res['text']).'.' . $id.'.html');
	exit;
} else {
    require('../incfiles/head.php');
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}