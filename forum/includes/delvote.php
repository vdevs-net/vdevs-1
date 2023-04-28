<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
if ($rights == 3 || $rights >= 6) {
    $topic_vote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='1' AND `topic` = '$id'"), 0);
    require('../incfiles/head.php');
    if ($topic_vote == 0) {
        echo functions::display_error($lng['error_wrong_data']);
        require('../incfiles/end.php');
        exit;
    }
    if (isset($_GET['yes'])) {
        mysql_query("DELETE FROM `cms_forum_vote` WHERE `topic` = '$id'");
        mysql_query("DELETE FROM `cms_forum_vote_users` WHERE `topic` = '$id'");
        mysql_query("UPDATE `forum` SET  `realid` = '0'  WHERE `id` = '$id'");
		mysql_query("OPTIMIZE TABLE `cms_forum_vote`,`cms_forum_vote_users`");
        echo '<div class="gmenu">'.$lng_forum['voting_deleted'] . '<br /><a href="' . $_SESSION['prd'] . '">' . $lng['continue'] . '</a></div>';
    } else {
        echo '<div class="rmenu">' . $lng_forum['voting_delete_warning'] . '<br/>';
        echo '<a href="?act=delvote&id=' . $id . '&yes">' . $lng['delete'] . '</a><br />';
        echo '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . $lng['cancel'] . '</a></div>';
        $_SESSION['prd'] = htmlspecialchars(getenv("HTTP_REFERER"));
    }
} else {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}