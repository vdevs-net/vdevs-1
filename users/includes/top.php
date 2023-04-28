<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$headmod = 'userstop';
$textl = $lng['users_top'];
require('../incfiles/head.php');

/*
-----------------------------------------------------------------
Функция отображения списков
-----------------------------------------------------------------
*/
function get_top($order = 'postforum') {
    $req = mysql_query("SELECT * FROM `users` WHERE `$order` > 0 ORDER BY `$order` DESC LIMIT 9");

    if (mysql_num_rows($req)) {
        $out = '';
		$i = 0;
        while ($res = mysql_fetch_assoc($req)) {
            $out .= $i % 2 ? '<div class="list2">' : '<div class="list1">';
            $out .= functions::display_user($res, array ('header' => ('<br/>'.($order == 'coin' ? '<img src="' . SITE_URL . '/images/coin.png"/> ':'').'<b>' . $res[$order]) . '</b>')) . '</div>';
            ++$i;
        }
        return $out;
    } else {
		global $lng;
        return '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
    }
}

/*
-----------------------------------------------------------------
Меню выбора
-----------------------------------------------------------------
*/
$menu = array (
    (!$mod ? '<b>' . $lng['forum'] . '</b>' : '<a href="index.php?act=top">' . $lng['forum'] . '</a>'),
    ($mod == 'comm' ? '<b>' . $lng['comments'] . '</b>' : '<a href="index.php?act=top&mod=comm">' . $lng['comments'] . '</a>'),
    ($mod == 'coin' ? '<b>Xu</b>' : '<a href="index.php?act=top&mod=coin">Xu</a>')
);
switch ($mod) {
	case 'coin':
        // Top comment
        echo '<div class="phdr"><a href="index.php"><b>' . $lng['community'] . '</b></a> | Top đại gia</div>';
        echo '<div class="topmenu">' . functions::display_menu($menu) . '</div>';
        echo get_top('coin');
        echo '<div class="phdr"><a href="' . SITE_URL . '/index.php">' . $lng['homepage'] . '</a></div>';
        break;
    case 'comm':
        // Top comment
        echo '<div class="phdr"><a href="index.php"><b>' . $lng['community'] . '</b></a> | ' . $lng['top_comm'] . '</div>';
        echo '<div class="topmenu">' . functions::display_menu($menu) . '</div>';
        echo get_top('komm');
        echo '<div class="phdr"><a href="' . SITE_URL . '/index.php">' . $lng['homepage'] . '</a></div>';
        break;

    default:
        /*
        -----------------------------------------------------------------
        Топ Форума
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="index.php"><b>' . $lng['community'] . '</b></a> | ' . $lng['top_forum'] . '</div>';
        echo '<div class="topmenu">' . functions::display_menu($menu) . '</div>';
        echo get_top('postforum');
        echo '<div class="phdr"><a href="' . SITE_URL . '/forum/index.php">' . $lng['forum'] . '</a></div>';
}
echo '<div class="gmenu"><a href="index.php">' . $lng['back'] . '</a></div>';