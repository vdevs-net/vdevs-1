<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$textl = $lng['mail'];
require_once('../incfiles/head.php');
echo '<div class="phdr"><b>' . $lng_mail['sent_messages'] . '</b></div>';

$total = mysql_result(mysql_query("
  SELECT COUNT(DISTINCT `cms_mail`.`from_id`)
  FROM `cms_mail`
  LEFT JOIN `cms_contact` ON `cms_mail`.`from_id`=`cms_contact`.`from_id`
  AND `cms_contact`.`user_id`='$user_id'
  WHERE `cms_mail`.`user_id`='$user_id'
  AND `cms_mail`.`delete`!='$user_id'
  AND `cms_mail`.`sys`='0'
  AND `cms_contact`.`ban`!='1'
"), 0);

if ($total) {
    $req = mysql_query("SELECT `users`.*, MAX(`cms_mail`.`time`) AS `time`
        FROM `cms_mail`
	    LEFT JOIN `users` ON `cms_mail`.`from_id`=`users`.`id`
		LEFT JOIN `cms_contact` ON `cms_mail`.`from_id`=`cms_contact`.`from_id` AND `cms_contact`.`user_id`='$user_id'
		WHERE `cms_mail`.`user_id`='" . $user_id . "'
		AND `cms_mail`.`delete`!='$user_id'
		AND `cms_mail`.`sys`='0'
		AND `cms_contact`.`ban`!='1'
		GROUP BY `cms_mail`.`from_id`
		ORDER BY MAX(`cms_mail`.`time`) DESC
		LIMIT " . $start . "," . $kmess
    );

    for ($i = 0; $row = mysql_fetch_assoc($req); ++$i) {
        $count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail`
            WHERE `user_id`='$user_id'
            AND `from_id`='{$row['id']}'
            AND `delete`!='$user_id'
            AND `sys`!='1'
        "), 0);

        $last_msg = mysql_fetch_assoc(mysql_query("SELECT *
            FROM `cms_mail`
            WHERE `from_id`='{$row['id']}'
            AND `user_id` = '$user_id'
            AND `delete` != '$user_id'
            ORDER BY `id` DESC
            LIMIT 1"));
        if (mb_strlen($last_msg['text']) > 500) {
            $text = mb_substr($last_msg['text'], 0, 500);
            $text = functions::checkout($text, 1, 1, 1);
            $text = bbcode::notags($text);
            $text .= '...<a href="index.php?act=write&amp;id=' . $row['id'] . '">' . $lng['continue'] . ' &gt;&gt;</a>';
        } else {
            // Or, process tags and print the entire text
            $text = functions::checkout($last_msg['text'], 1, 1, 1);
        }

        $arg = array(
            'header' => '<span class="gray">(' . functions::display_date($last_msg['time']) . ')</span>',
            'body'   => '<div style="font-size: small">' . $text . '</div>',
            'sub'    => '<div class="mv"><a href="index.php?act=write&amp;id=' . $row['id'] . '"><b>' . $lng_mail['correspondence'] . '</b></a> (' . $count_message . ') | <a href="index.php?act=ignor&amp;id=' . $row['id'] . '&amp;add">Chặn</a> | <a href="index.php?act=deluser&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a></div>',
            'iphide' => 1
        );

        if (!$last_msg['read']) {
            echo '<div class="gmenu">';
        } else {
            echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
        }
        echo functions::display_user($row, $arg);
        echo '</div>';
    }

//    for ($i = 0; $row = mysql_fetch_assoc($req); ++$i) {
//        echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
//        $subtext = '<a href="index.php?act=output&amp;id=' . $row['id'] . '">' . $lng_mail['sent'] . '</a> | <a href="index.php?act=write&amp;id=' . $row['id'] . '">' . $lng_mail['correspondence'] . '</a> | <a href="index.php?act=deluser&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a> | <a href="index.php?act=ignor&amp;id=' . $row['id'] . '&amp;add">' . $lng_mail['ban_contact'] . '</a>';
//        $count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `user_id`='$user_id' AND `from_id`='{$row['id']}' AND `delete`!='$user_id';"), 0);
//        $new_count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='$user_id' AND `cms_mail`.`from_id`='{$row['id']}' AND `read`='0' AND `delete`!='$user_id'"), 0);
//        $arg = array(
//            'header' => '(' . $count_message . ($new_count_message ? '/<span class="red">+' . $new_count_message . '</span>' : '') . ')',
//            'sub'    => $subtext
//        );
//        echo functions::display_user($row, $arg);
//        echo '</div>';
//    }
} else {
    echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
}

echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
if ($total > $kmess) {
    echo '<div class="topmenu">' . functions::display_pagination('index.php?act=output&page=', $start, $total, $kmess) . '</div>' .
        '<p><form action="index.php" method="get">
                <input type="hidden" name="act" value="input"/>
                <input type="text" name="page" size="2"/>
                <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
}

echo '<p><a href="' . SITE_URL . '/users/profile.php?act=office">' . $lng['personal'] . '</a></p>';