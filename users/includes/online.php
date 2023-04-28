<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$headmod = 'online';
$textl = $lng['online'];
require('../incfiles/head.php');

// Displays a list of Online
$menu[] = !$mod ? '<b>' . $lng['users'] . '</b>' : '<a href="index.php?act=online">' . $lng['users'] . '</a>';
$menu[] = $mod == 'history' ? '<b>' . $lng['history'] . '</b>' : '<a href="index.php?act=online&mod=history">' . $lng['history'] . '</a> ';
if ($rights) {
    $menu[] = $mod == 'guest' ? '<b>' . $lng['guests'] . '</b>' : '<a href="index.php?act=online&mod=guest">' . $lng['guests'] . '</a>';
    $menu[] = $mod == 'ip' ? '<b>' . $lng['ip_activity'] . '</b>' : '<a href="index.php?act=online&mod=ip">' . $lng['ip_activity'] . '</a>';
}

echo '<div class="phdr"><b>' . $lng['who_on_site'] . '</b></div>' .
     '<div class="topmenu">' . functions::display_menu($menu) . '</div>';

switch ($mod) {
    case 'ip':
        // Список активных IP, со счетчиком обращений
        $ip_array = array_count_values(core::$ip_count);
        $total = count($ip_array);
        if ($start >= $total) {
            // Исправляем запрос на несуществующую страницу
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        }
        $end = $start + $kmess;
        if ($end > $total) $end = $total;
        arsort($ip_array);
        $i = 0;
        foreach ($ip_array as $key => $val) {
            $ip_list[$i] = array($key => $val);
            ++$i;
        }
        if ($total && $rights) {
            if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?act=online&amp;mod=ip&page=', $start, $total, $kmess) . '</div>';
            for ($i = $start; $i < $end; $i++) {
                $out = each($ip_list[$i]);
                $ip = long2ip($out[0]);
                if ($out[0] == core::$ip) echo '<div class="gmenu">';
                else echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                echo'[' . $out[1] . ']&#160;&#160;<a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=search_ip&amp;ip=' . $ip . '">' . $ip . '</a>' .
                    '&#160;&#160;<small>[<a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=ip_whois&ip=' . $ip . '">?</a>]</small>';
                echo '</div>';
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
            if ($total > $kmess) {
                echo '<div class="topmenu">' . functions::display_pagination('index.php?act=online&amp;mod=ip&page=', $start, $total, $kmess) . '</div>' .
                     '<div class="menu"><form action="index.php?act=online&amp;mod=ip" method="post">' .
                     '<input type="text" name="page" size="2"/>' .
                     '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></div>';
            }
        }
        require_once('../incfiles/end.php');
        exit;
        break;

    case 'guest':
        // Список гостей Онлайн
        $sql_total = "SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300);
        $sql_list = "SELECT * FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300) . " ORDER BY `movings` DESC LIMIT ";
        break;

    case 'history':
        // История посетилелей за последние 2 суток
        $sql_total = "SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 172800 . " AND `lastdate` < " . (time() - 310));
        $sql_list = "SELECT * FROM `users` WHERE `lastdate` > " . (time() - 172800) . " AND `lastdate` < " . (time() - 310) . " ORDER BY `sestime` DESC LIMIT ";
        break;

    default:
        // Список посетителей Онлайн
        $sql_total = "SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 300);
        $sql_list = "SELECT * FROM `users` WHERE `lastdate` > " . (time() - 300) . " ORDER BY `account` ASC LIMIT ";
}

$total = mysql_result(mysql_query($sql_total), 0);
if ($start >= $total) {
    // Исправляем запрос на несуществующую страницу
    $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
}

if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?act=online&' . ($mod ? 'mod=' . $mod . '&' : '').'page=', $start, $total, $kmess) . '</div>';
if ($total) {
    $req = mysql_query($sql_list . "$start, $kmess");
    $i = 0;
    while (($res = mysql_fetch_assoc($req)) !== false) {
		$res['id'] = isset($res['id']) ? $res['id'] : 0;
        if ($res['id'] == core::$user_id) echo '<div class="gmenu">';
        else echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        $arg['stshide'] = 1;
        $arg['header'] = ' <span class="gray">(';
        if ($mod == 'history') $arg['header'] .= functions::display_date($res['sestime']);
        else $arg['header'] .= $res['movings'] . ' - ' . functions::timecount(time() - $res['sestime']);
        $arg['header'] .= ')</span><br /><img src="' . SITE_URL . '/images/info.png" width="16" height="16" align="middle" />&#160;' . functions::display_place($res['id'], $res['place']);
        echo functions::display_user($res, $arg);
        echo '</div>';
        ++$i;
    }
} else {
    echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
}
echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
if ($total > $kmess) {
    echo '<div class="topmenu">' . functions::display_pagination('index.php?act=online&' . ($mod ? 'mod=' . $mod . '&' : '').'page=', $start, $total, $kmess) . '</div>' .
         '<div class="menu"><form action="index.php?act=online' . ($mod ? '&mod=' . $mod : '') . '" method="post">' .
         '<input type="text" name="page" size="2"/>' .
         '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
         '</form></div>';
}