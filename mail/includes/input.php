<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$textl = $lng['mail'];
require_once('../incfiles/head.php');
echo '<div class="phdr"><b>' . $lng_mail['input_messages'] . '</b></div>';

$total = mysql_result(mysql_query("
	SELECT COUNT(DISTINCT `cms_mail`.`user_id`)
	FROM `cms_mail`
	LEFT JOIN `cms_contact`
	ON `cms_mail`.`user_id`=`cms_contact`.`from_id`
	AND `cms_contact`.`user_id`='$user_id'
	WHERE `cms_mail`.`from_id`='$user_id'
	AND `cms_mail`.`sys`='0' AND `cms_mail`.`delete`!='$user_id'
	AND `cms_contact`.`ban`!='1' AND `spam`='0'"), 0);

if ($total) {
    $req = mysql_query("SELECT `users`.*, MAX(`cms_mail`.`time`) AS `time`
		FROM `cms_mail`
		LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
		LEFT JOIN `cms_contact` ON `cms_mail`.`user_id`=`cms_contact`.`from_id` AND `cms_contact`.`user_id`='$user_id'
		WHERE `cms_mail`.`from_id`='$user_id'
		AND `cms_mail`.`delete`!='$user_id'
		AND `cms_mail`.`sys`='0'
		AND `cms_contact`.`ban`!='1'
		GROUP BY `cms_mail`.`user_id`
		ORDER BY MAX(`cms_mail`.`time`) DESC
		LIMIT " . $start . "," . $kmess);

    for ($i = 0; $row = mysql_fetch_assoc($req); ++$i) {
        $count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail`
            WHERE `user_id`='{$row['id']}'
            AND `from_id`='$user_id'
            AND `delete`!='$user_id'
            AND `sys`!='1'
        "), 0);

        $last_msg = mysql_fetch_assoc(mysql_query("SELECT *
            FROM `cms_mail`
            WHERE `from_id`='$user_id'
            AND `user_id` = '{$row['id']}'
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
            'body'   => '<div style="font-size: small" class="text">' . $text . '</div>',
            'sub'    => '<a href="index.php?act=write&amp;id=' . $row['id'] . '"><b>' . $lng_mail['correspondence'] . '</b></a> (' . $count_message . ') | <a href="index.php?act=ignor&id=' . $row['id'] . '&add">Chặn</a> | <a href="index.php?act=deluser&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a>',
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
} else {
    echo '<div class="menu">' . $lng['list_empty'] . '</div>';
}

echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
if ($total > $kmess) {
    echo '<div class="topmenu">' . functions::display_pagination('index.php?act=input&page=', $start, $total, $kmess) . '</div>' .
        '<div class="menu"><form action="index.php" method="get">
                <input type="hidden" name="act" value="input"/>
                <input type="text" name="page" size="2"/>
                <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></div>';
}

echo '<div class="menu"><a href="' . SITE_URL . '/users/profile.php?act=office">' . $lng['personal'] . '</a></div>';