<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

if (($rights != 3 && $rights < 6) || !$id) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}
$req = mysql_query("SELECT `type`,`refid`,`text` FROM `forum` WHERE `id` = '$id' AND (`type` = 't' OR `type` = 'm')");
if (mysql_num_rows($req)) {
    $res = mysql_fetch_assoc($req);
    mysql_query("UPDATE `forum` SET `close` = '0', `close_who` = '$login' WHERE `id` = '$id'");
    if ($res['type'] == 't') {
        header('Location: '.functions::bodau($res['text']).'.' . $id.'.html');
        exit;
    } else {
        $page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id`<='" . $id . "'"), 0) / $kmess);
        header('Location: '.functions::bodau($res['text']).'.' . $res['refid'] . '.html?page=' . $page);
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}