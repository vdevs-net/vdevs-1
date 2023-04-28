<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
require('../incfiles/head.php');
$topic_vote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id'"), 0);
if ($topic_vote == 0 || $rights < 7) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
} else {
    $topic_vote = mysql_fetch_array(mysql_query("SELECT `cms_forum_vote`.`name`, `cms_forum_vote`.`time`, `cms_forum_vote`.`count`,`forum`.`text` FROM `cms_forum_vote` LEFT JOIN `forum` ON `forum`.`id`=`cms_forum_vote`.`topic` WHERE `cms_forum_vote`.`type` = '1' AND `cms_forum_vote`.`topic` = '$id' LIMIT 1"));
    echo '<div  class="phdr">' . $lng_forum['voting_users'] . ' &laquo;<b>' . htmlspecialchars($topic_vote['name']) . '</b>&raquo;</div>';
    $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `topic`='$id'"), 0);
    $req = mysql_query("SELECT `cms_forum_vote_users`.*, `users`.`rights`, `users`.`lastdate`, `users`.`account`, `users`.`sex`, `users`.`status`, `users`.`datereg`, `users`.`id`
    FROM `cms_forum_vote_users` LEFT JOIN `users` ON `cms_forum_vote_users`.`user` = `users`.`id`
    WHERE `cms_forum_vote_users`.`topic`='$id' LIMIT $start,$kmess");
    $i = 0;
    while ($res = mysql_fetch_array($req)) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        echo functions::display_user($res, array ('iphide' => 1));
        echo '</div>';
        ++$i;
    }
    if ($total == 0)
        echo '<div class="menu">' . $lng_forum['voting_users_empty'] . '</div>';
    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
    if ($total > $kmess) {
        echo '<p>' . functions::display_pagination('index.php?act=users&id=' . $id . '&page=', $start, $total, $kmess) . '</p>' .
            '<p><form action="index.php?act=users&id=' . $id . '" method="post">' .
            '<input type="text" name="page" size="2"/>' .
            '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
    }
    echo '<div class="gmenu"><a href="'.functions::bodau($topic_vote['text']).'.' . $id . '.html">' . $lng_forum['to_topic'] . '</a></div>';
}

require('../incfiles/end.php');
?>