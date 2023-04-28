<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

// History of activity
$textl = $user['account'] . ': ' . $lng_profile['activity'];
require('../incfiles/head.php');
echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '"><b>' . $lng['profile'] . '</b></a> | ' . $lng_profile['activity'] . '</div>';
$menu = array(
    (!$mod ? '<b>' . $lng['messages'] . '</b>' : '<a href="profile.php?act=activity&user=' . $user['id'] . '">' . $lng['messages'] . '</a>'),
    ($mod == 'topic' ? '<b>' . $lng['themes'] . '</b>' : '<a href="profile.php?act=activity&mod=topic&user=' . $user['id'] . '">' . $lng['themes'] . '</a>')
);
echo '<div class="topmenu">' . functions::display_menu($menu) . '</div>' .
     '<div class="user"><p>' . functions::display_user($user, array('iphide' => 1,)) . '</p></div>';
switch ($mod) {

    case 'topic':
        /*
        -----------------------------------------------------------------
        Список тем Форума
        -----------------------------------------------------------------
        */
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `user_id` = '" . $user['id'] . "' AND `type` = 't'" . ($rights >= 7 ? '' : " AND `close`!='1'")), 0);
        echo '<div class="phdr"><b>' . $lng['forum'] . '</b>: ' . $lng['themes'] . '</div>';
        if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('profile.php?act=activity&mod=topic&user=' . $user['id'] . '&page=', $start, $total, $kmess) . '</div>';
        $req = mysql_query("SELECT * FROM `forum` WHERE `user_id` = '" . $user['id'] . "' AND `type` = 't'" . ($rights >= 7 ? '' : " AND `close`!='1'") . " ORDER BY `id` DESC LIMIT $start, $kmess");
        if (mysql_num_rows($req)) {
            $i = 0;
            while ($res = mysql_fetch_assoc($req)) {
                $post = mysql_fetch_assoc(mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['id'] . "'" . ($rights >= 7 ? '' : " AND `close`!='1'") . " ORDER BY `id` ASC LIMIT 1"));
                $section = mysql_fetch_assoc(mysql_query("SELECT * FROM `forum` WHERE `id` = '" . $res['refid'] . "'"));
                $category = mysql_fetch_assoc(mysql_query("SELECT * FROM `forum` WHERE `id` = '" . $section['refid'] . "'"));
                $text = mb_substr($post['text'], 0, 300);
                $text = functions::checkout($text, 1, 1);
                echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
                     '<a href="' . SITE_URL . '/forum/' . functions::bodau($res['text']) . '.' . $res['id'] . '.html"><strong>' . htmlspecialchars($res['text']) . '</strong></a>' .
                     '<br />' . $text . '...' .
                     '<div class="sub">' .
                     '<a href="' . SITE_URL . '/forum/index.php?id=' . $category['id'] . '">' . htmlspecialchars($category['text']) . '</a> | ' .
                     '<a href="' . SITE_URL . '/forum/index.php?id=' . $section['id'] . '">' . htmlspecialchars($section['text']) . '</a>' .
                     '<br /><span class="gray">(' . functions::display_date($res['time']) . ')</span>' .
                     '</div></div>';
                ++$i;
            }
        } else {
            echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
        }
        break;

    default:
        /*
        -----------------------------------------------------------------
        Список постов Форума
        -----------------------------------------------------------------
        */
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `user_id` = '" . $user['id'] . "' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close`!='1'")), 0);
        echo '<div class="phdr"><b>' . $lng['forum'] . '</b>: ' . $lng['messages'] . '</div>';
        if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('profile.php?act=activity&user=' . $user['id'] . '&page=', $start, $total, $kmess) . '</div>';
        $req = mysql_query("SELECT * FROM `forum` WHERE `user_id` = '" . $user['id'] . "' AND `type` = 'm' " . ($rights >= 7 ? '' : " AND `close`!='1'") . " ORDER BY `id` DESC LIMIT $start, $kmess");
        if (mysql_num_rows($req)) {
            $i = 0;
            while ($res = mysql_fetch_assoc($req)) {
                $topic = mysql_fetch_assoc(mysql_query('SELECT `text` FROM `forum` WHERE `id` = "' . $res['refid'] . '" LIMIT 1'));
                $text = mb_substr($res['text'], 0, 300);
                $text = functions::checkout($text, 1, 1);
                echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
                     '<a href="' . SITE_URL . '/forum/' . functions::bodau($topic['text']) . '.' . $res['refid'] . '.html"><strong>' . htmlspecialchars($topic['text']) . '</strong></a>' .
                     '<br />' . $text . '...<a href="' . SITE_URL . '/forum/index.php?act=post&id=' . $res['id'] . '"> &gt;&gt;</a>' .
                     '<div class="sub">' .
                     '<span class="gray">(' . functions::display_date($res['time']) . ')</span>' .
                     '</div></div>';
                ++$i;
            }
        } else {
            echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
        }
}
echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
if ($total > $kmess) {
    echo '<div class="topmenu">' . functions::display_pagination('profile.php?act=activity' . ($mod ? '&mod=' . $mod : '') . '&user=' . $user['id'] . '&page=', $start, $total, $kmess) . '</div>' .
         '<div class="menu"><form action="profile.php?act=activity&user=' . $user['id'] . ($mod ? '&mod=' . $mod : '') . '" method="post">' .
         '<input type="text" name="page" size="2"/>' .
         '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
         '</form></div>';
}