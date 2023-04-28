<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
require('../incfiles/head.php');

if ($rights >= 7) {
    $req = mysql_query("SELECT `text` FROM `forum` WHERE `id` = '$id' AND `type` = 't'");
    if (!mysql_num_rows($req) || $rights < 7) {
        echo functions::display_error($lng_forum['error_topic_deleted']);
        require('../incfiles/end.php');
        exit;
    }
    $topic = mysql_fetch_assoc($req);
    $req = mysql_query("SELECT `forum`.*, `users`.`id`
        FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
        WHERE `forum`.`refid`='$id' AND `users`.`rights` < 6 AND `users`.`rights` != 3 GROUP BY `forum`.`from` ORDER BY `forum`.`from`");
    $total = mysql_num_rows($req);
    echo '<div class="phdr"><a href="'.functions::bodau($topic['text']).'.' . $id . '.html?start=' . $start . '"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['curators'] . '</div>' .
         '<div class="bmenu">' . htmlspecialchars($topic['text']) . '</div>';
    $curators = array();
    $users = !empty($topic['curators']) ? unserialize($topic['curators']) : array();
    if (isset($_POST['submit'])) {
        $users = (isset($_POST['users']) && is_array($_POST['users'])) ? $_POST['users'] : array();
    }
    if ($total > 0) {
        echo '<form action="index.php?act=curators&id=' . $id . '&start=' . $start . '" method="post">';
        $i = 0;
        while ($res = mysql_fetch_array($req)) {
            $checked = array_key_exists($res['user_id'], $users) ? true : false;
            if ($checked) $curators[$res['user_id']] = $res['from'];
            echo ($i++ % 2 ? '<div class="list2">' : '<div class="list1">') .
                 '<input type="checkbox" name="users[' . $res['user_id'] . ']" value="' . $res['from'] . '"' . ($checked ? ' checked="checked"' : '') . '/>&#160;' .
                 '<a href="' . SITE_URL . '/users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a></div>';
        }
        echo '<div class="gmenu"><input type="submit" value="' . $lng_forum['assign'] . '" name="submit" /></div></form>';
        if (isset($_POST['submit']))
			mysql_query("UPDATE `forum` SET `curators`='" . mysql_real_escape_string(serialize($curators)) . "' WHERE `id` = '$id'");

    } else{
        echo functions::display_error($lng['list_empty']);
	}
    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>' .
         '<div class="gmenu"><a href="'.functions::bodau($topic['text']).'.' . $id . '.html?start=' . $start . '">' . $lng['back'] . '</a></div>';
}