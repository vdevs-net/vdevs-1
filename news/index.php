<?php
define('_MRKEN_CMS', 1);

$headmod = 'news';
require('../incfiles/core.php');
$lng_news = core::load_lng('news');
$textl = $lng['news'];
require('../incfiles/head.php');
switch ($do) {
    case 'add':
        // Add news
        if ($rights >= 6) {
            $display_form = true;
            $error = array();
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['news'] . '</b></a> | ' . $lng['add'] . '</div>';
            $name = isset($_POST['name']) ? functions::checkin($_POST['name']) : '';
            $text = isset($_POST['text']) ? functions::checkin($_POST['text']) : '';
            if (isset($_POST['submit'])) {
                if (empty($name))
                    $error[] = $lng_news['error_title'];
                if (empty($text))
                    $error[] = $lng_news['error_text'];
                $flood = functions::antiflood();
                if ($flood)
                    $error[] = $lng['error_flood'] . ' ' . $flood . ' ' . $lng['seconds'];
                if (empty($error)) {
                    $rid = 0;
                    if (!empty($_POST['pf']) && ($_POST['pf'] != '0')) {
                        $pf = intval($_POST['pf']);
                        $rz = $_POST['rz'];
                        $pr = mysql_query('SELECT * FROM `forum` WHERE `refid` = "' . $pf . '" AND `type` = "r"');
                        while ($pr1 = mysql_fetch_array($pr)) {
                            $arr[] = $pr1['id'];
                        }
                        foreach ($rz as $v) {
                            if (in_array($v, $arr)) {
                                mysql_query('INSERT INTO `forum` SET
                                    `refid` = "' . $v . '",
                                    `type` = "t",
                                    `time` = "' . time() . '",
                                    `user_id` = "' . $user_id . '",
                                    `from` = "' . $login . '",
                                    `text` = "' . mysql_real_escape_string($name) . '",
									`curators` = ""
                                ') or die(mysql_error());
                                $rid = mysql_insert_id();
                                mysql_query('INSERT INTO `forum` SET
                                    `refid` = "' . $rid . '",
                                    `type` = "m",
                                    `time` = "' . time() . '",
                                    `user_id` = "' . $user_id . '",
                                    `from` = "' . $login . '",
                                    `ip` = "' . $ip . '",
                                    `soft` = "' . mysql_real_escape_string($agn) . '",
                                    `text` = "' . mysql_real_escape_string($text) . '",
									`curators` = ""
                                ') or die(mysql_error());
                            }
                        }
                    }
                    mysql_query('INSERT INTO `news` SET
                        `time` = "' . time() . '",
                        `avt` = "' . $login . '",
                        `name` = "' . mysql_real_escape_string($name) . '",
                        `text` = "' . mysql_real_escape_string($text) . '",
                        `kom` = "' . $rid . '"
                    ');
                    mysql_query('UPDATE `users` SET
                        `lastpost` = "' . time() . '"
                        WHERE `id` = "' . $user_id . '"
                    ');
                    echo '<p>' . $lng_news['article_added'] . '<br /><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
                    $display_form = false;
                } else {
                    echo functions::display_error($error);
                }
            }
            if ($display_form) {
                echo '<form action="index.php?do=add" method="post"><div class="menu">' .
                     '<p><h3>' . $lng_news['article_title'] . '</h3>' .
                     '<input type="text" name="name" value="' . htmlspecialchars($name) . '" autocomplete="off" /></p>' .
                     '<p><h3>' . $lng['text'] . '</h3>' .
                     '<textarea rows="' . $set_user['field_h'] . '" name="text">' . htmlspecialchars($text) . '</textarea></p>' .
                     '<p><h3>' . $lng_news['discuss'] . '</h3>';
                $fr = mysql_query('SELECT * FROM `forum` WHERE `type` = "f"');
                echo '<input type="radio" name="pf" value="0" checked="checked" />' . $lng_news['discuss_off'] . '<br />';
                while ($fr1 = mysql_fetch_array($fr)) {
                    echo '<input type="radio" name="pf" value="' . $fr1['id'] . '"/>' . htmlspecialchars($fr1['text']) . '<select name="rz[]">';
                    $pr = mysql_query('SELECT * FROM `forum` WHERE `type` = "r" AND `refid` = "' . $fr1['id'] . '"');
                    while ($pr1 = mysql_fetch_array($pr)) {
                        echo '<option value="' . $pr1['id'] . '">' . htmlspecialchars($pr1['text']) . '</option>';
                    }
                    echo '</select><br/>';
                }
                echo '</p></div><div class="bmenu">' .
                     '<input type="submit" name="submit" value="' . $lng['save'] . '"/>' .
                     '</div></form>' .
                     '<p><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
            }
        } else {
            header("location: index.php"); exit;
        }
        break;

    case 'edit':
        // Editing news
        if ($rights >= 6) {
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['news'] . '</b></a> | ' . $lng['edit'] . '</div>';
            if ($id) {
                if (mysql_result(mysql_query('SELECT COUNT(*) FROM `news` WHERE `id` = "' . $id . '"'), 0)) {
                    $display_form = true;
                    $error = array();
                    $name = isset($_POST['name']) ? functions::checkin($_POST['name']) : '';
                    $text = isset($_POST['text']) ? functions::checkin($_POST['text']) : '';
                    if (isset($_POST['submit'])) {
                        if (empty($name))
                            $error[] = $lng_news['error_title'];
                        if (empty($text))
                            $error[] = $lng_news['error_text'];
                        if (empty($error)) {
                            mysql_query('UPDATE `news` SET
                                `name` = "' . mysql_real_escape_string($name) . '",
                                `text` = "' . mysql_real_escape_string($text) . '"
                                WHERE `id` = "' . $id . '"
                            ');
                            echo '<p>' . $lng_news['article_changed'] . '</p>';
                            $display_form = false;
                        } else {
                            echo functions::display_error($error);
                        }
                    }
                    if ($display_form) {
                        $res = mysql_fetch_assoc(mysql_query('SELECT * FROM `news` WHERE `id` = "' . $id . '" LIMIT 1'));
                        echo '<div class="menu"><form action="index.php?do=edit&amp;id=' . $id . '" method="post">' .
                            '<p><h3>' . $lng_news['article_title'] . '</h3>' .
                            '<input type="text" name="name" value="' . htmlspecialchars($res['name']) . '"/></p>' .
                            '<p><h3>' . $lng['text'] . '</h3>' .
                            '<textarea rows="' . $set_user['field_h'] . '" name="text">' . htmlspecialchars($res['text']) . '</textarea></p>' .
                            '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p>' .
                            '</form></div>' .
                            '<div class="phdr"><a href="index.php">' . $lng_news['to_news'] . '</a></div>';
                    }
                } else {
                    echo functions::display_error($lng['error_wrong_data']);
                }
            } else {
                echo functions::display_error($lng['error_wrong_data']);
            }
        } else {
            header('location: index.php');
            exit;
        }
        break;

    case 'clean':
        // Cleaning news
        if ($rights >= 7) {
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['site_news'] . '</b></a> | ' . $lng['clear'] . '</div>';
            if (isset($_POST['submit'])) {
                $cl = isset($_POST['cl']) ? intval($_POST['cl']) : '';
                switch ($cl) {
                    case '1':
                        // Clean the news older than 1 week
                        mysql_query('DELETE FROM `news` WHERE `time` <= "' . (time() - 604800) . '"');
                        mysql_query('OPTIMIZE TABLE `news`');
                        echo '<p>' . $lng_news['clear_week_confirmation'] . '</p><p><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
                        break;

                    case '2':
                        // Clean all news
                        mysql_query('TRUNCATE TABLE `news`');
                        echo '<p>' . $lng_news['clear_all_confirmation'] . '</p><p><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
                        break;
                    default :
                        // Clean the news older than 1 month
                        mysql_query('DELETE FROM `news` WHERE `time` <= "' . (time() - 2592000) . '"');
                        mysql_query('OPTIMIZE TABLE `news`;');
                        echo '<p>' . $lng_news['clear_month_confirmation'] . '</p><p><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
                }
            } else {
                echo '<div class="menu"><form id="clean" method="post" action="index.php?do=clean">' .
                     '<p><h3>' . $lng['clear_param'] . '</h3>' .
                     '<input type="radio" name="cl" value="0" checked="checked" />' . $lng_news['clear_month'] . '<br />' .
                     '<input type="radio" name="cl" value="1" />' . $lng_news['clear_week'] . '<br />' .
                     '<input type="radio" name="cl" value="2" />' . $lng['clear_all'] . '</p>' .
                     '<p><input type="submit" name="submit" value="' . $lng['clear'] . '" /></p>' .
                     '</form></div>' .
                     '<div class="phdr"><a href="index.php">' . $lng['cancel'] . '</a></div>';
            }
        } else {
            header("location: index.php"); exit;
        }
        break;

    case 'del':
        // Removing news
        if ($rights >= 6) {
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['site_news'] . '</b></a> | ' . $lng['delete'] . '</div>';
            if (isset($_GET['yes'])) {
                mysql_query('DELETE FROM `news` WHERE `id` = "' . $id . '"');
                echo '<p>' . $lng_news['article_deleted'] . '<br/><a href="index.php">' . $lng_news['to_news'] . '</a></p>';
            } else {
                echo '<p>' . $lng['delete_confirmation'] . '<br/>' .
                     '<a href="index.php?do=del&id=' . $id . '&yes">' . $lng['delete'] . '</a> | <a href="index.php">' . $lng['cancel'] . '</a></p>';
            }
        } else {
            header("location: index.php"); exit;
        }
        break;

    default:
        // Displays a list of news
        echo '<div class="phdr"><b>' . $lng['site_news'] . '</b></div>';
        if ($rights >= 6)
            echo '<div class="topmenu"><a href="index.php?do=add">' . $lng['add'] . '</a> | <a href="index.php?do=clean">' . $lng['clear'] . '</a></div>';
        $req = mysql_query("SELECT COUNT(*) FROM `news`");
        $total = mysql_result($req, 0);
        $req = mysql_query("SELECT `news`.*,`forum`.`text` as `tname` FROM `news` LEFT JOIN `forum` ON `forum`.`id`=`news`.`kom` ORDER BY `time` DESC LIMIT $start, $kmess");
        $i = 0;
        while ($res = mysql_fetch_array($req)) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            $text = functions::checkout($res['text'], 1, 1, 1);
            echo '<h3>' . htmlspecialchars($res['name']) . '</h3>' .
                 '<div class="gray"><small>' . $lng['author'] . ': ' . $res['avt'] . ' (' . functions::display_date($res['time']) . ')</small></div>' .
                 '<div class="text">' . $text . '</div><div class="sub">';
            if ($res['kom'] != 0 && $res['kom'] != "") {
                $mes = mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '" . $res['kom'] . "'");
                $komm = mysql_result($mes, 0) - 1;
                if ($komm >= 0)
                    echo '<a href="' . SITE_URL . '/forum/'.functions::bodau($res['tname']).'.' . $res['kom'] . '.html">' . $lng_news['discuss_on_forum'] . ' (' . $komm . ')</a><br/>';
            }
            if ($rights >= 6) {
                echo '<a href="index.php?do=edit&id=' . $res['id'] . '">' . $lng['edit'] . '</a> | ' .
                     '<a href="index.php?do=del&id=' . $res['id'] . '">' . $lng['delete'] . '</a>';
            }
            echo '</div></div>';
            ++$i;
        }
        echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        if ($total > $kmess) {
            echo '<div class="topmenu">' . functions::display_pagination('index.php?page=', $start, $total, $kmess) . '</div>' .
                 '<p><form action="index.php" method="post">' .
                 '<input type="text" name="page" size="2"/>' .
                 '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
        }
}

require('../incfiles/end.php');