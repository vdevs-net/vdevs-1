<?php
ini_set("max_execution_time", "600");
define('_MRKEN_CMS', 1);
define('_IS_MRKEN', 1);

require('../incfiles/core.php');
$lng = array_merge($lng, core::load_lng('admin'));

// Check rights
if ($rights < 1) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}

$headmod = 'admin';
$textl = $lng['admin_panel'];
require('../incfiles/head.php');
$array = array(
    'forum',
    'news',
    'ads',
    'ip_whois',
    'languages',
    'settings',
    'smileys',
    'access',
    'antispy',
    'httpaf',
    'ipban',
    'antiflood',
    'ban_panel',
    'reg',
    'mail',
    'search_ip',
	'shop',
    'usr',
    'usr_adm',
    'usr_clean',
    'usr_del'
);
if ($act && ($key = array_search($act, $array)) !== false && file_exists('includes/' . $array[$key] . '.php')) {
    require('includes/' . $array[$key] . '.php');
} else {
    $regtotal = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `preg`='0'"), 0);
    $bantotal = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `ban_time` > '" . time() . "'"), 0);
    echo '<div class="phdr"><b>' . $lng['admin_panel'] . '</b></div>';

    // Manage users
    echo '<div class="user"><p><h3>' . $lng['users'] . '</h3><ul>';
    if ($regtotal && $rights >= 9) echo '<li><span class="red"><b><a href="index.php?act=reg">' . $lng['users_reg'] . '</a>&#160;(' . $regtotal . ')</b></span></li>';
    echo '<li><a href="index.php?act=usr">' . $lng['users'] . '</a>&#160;(' . counters::users() . ')</li>' .
        '<li><a href="index.php?act=usr_adm">' . $lng['users_administration'] . '</a>&#160;(' . mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `rights` >= '1'"), 0) . ')</li>' .
        ($rights >= 7 ? '<li><a href="index.php?act=usr_clean">' . $lng['users_clean'] . '</a></li>' : '') .
        '<li><a href="index.php?act=ban_panel">' . $lng['ban_panel'] . '</a>&#160;(' . $bantotal . ')</li>' .
        ($rights >= 7 ? '<li><a href="index.php?act=antiflood">' . $lng['antiflood'] . '</a></li>' : '') .
        '<br />' .
        '<li><a href="' . SITE_URL . '/users/search.php">' . $lng['search_nick'] . '</a></li>' .
        '<li><a href="index.php?act=search_ip">' . $lng['ip_search'] . '</a></li>' .
        '</ul></p></div>';
    if ($rights >= 7) {

        // Manage modules
        $spam = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `spam`='1';"), 0);
        echo '<div class="gmenu"><p>';
        echo '<h3>' . $lng['modules'] . '</h3><ul>' .
            '<li><a href="index.php?act=forum">' . $lng['forum'] . '</a></li>' .
            '<li><a href="index.php?act=news">' . $lng['news'] . '</a></li>' .
            '<li><a href="index.php?act=ads">' . $lng['advertisement'] . '</a></li>';
        if ($rights == 9) {
            echo '<br/><li><a href="index.php?act=shop">Shop</a></li>' .
                '<li><a href="index.php?act=mail">' . $lng['mail'] . '</a></li>';
        }
        echo '</ul></p></div>';

        // system settings
        echo '<div class="menu"><p>' .
            '<h3>' . $lng['system'] . '</h3>' .
            '<ul>' .
            ($rights == 9 ? '<li><a href="index.php?act=settings"><b>' . $lng['site_settings'] . '</b></a></li>' : '') .
            '<li><a href="index.php?act=smileys">' . $lng['refresh_smileys'] . '</a></li>' .
            ($rights == 9 ? '<li><a href="index.php?act=languages">' . $lng['language_settings'] . '</a></li>' : '') .
            '<li><a href="index.php?act=access">' . $lng['access_rights'] . '</a></li>' .
            '</ul>' .
            '</p></div>';

        // security settings
        echo '<div class="rmenu"><p>' .
            '<h3>' . $lng['security'] . '</h3>' .
            '<ul>' .
            '<li><a href="index.php?act=antispy">' . $lng['antispy'] . '</a></li>' .
            ($rights == 9 ? '<li><a href="index.php?act=ipban">' . $lng['ip_ban'] . '</a></li>' : '') .
            '</ul>' .
            '</p></div>';
    }
    echo '<div class="phdr" style="font-size: x-small"><b>JohnCMS 6.2.0</b></div>';
}

require('../incfiles/end.php');