<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$headmod = 'userban';
$lng_ban = core::load_lng('ban');
require('../incfiles/head.php');
$ban = isset($_GET['ban']) ? intval($_GET['ban']) : 0;
switch ($mod) {
    case 'do':
        // Ban user (Ban add to the database)
        if ($rights < 1 || ($rights < 6 && $user['rights']) || ($rights <= $user['rights'])) {
            echo functions::display_error($lng_ban['ban_rights']);
        } else {
            echo '<div class="phdr"><b><a href="profile.php?user=' . $user['id'] . '">' . $lng['profile'] . '</a></b> | ' . $lng_ban['ban_do'] . '</div>';
            echo '<div class="rmenu"><p>' . functions::display_user($user) . '</p></div>';
            if (isset($_POST['submit'])) {
                $error = false;
                $term = isset($_POST['term']) ? intval($_POST['term']) : false;
                $timeval = isset($_POST['timeval']) ? intval($_POST['timeval']) : false;
                $time = isset($_POST['time']) ? intval($_POST['time']) : false;
                $reason = !empty($_POST['reason']) ? trim($_POST['reason']) : '';
                $banref = isset($_POST['banref']) ? intval($_POST['banref']) : false;
                if (empty($reason) && empty($banref))
                    $reason = $lng_ban['reason_not_specified'];
                if (empty($term) || empty($timeval) || empty($time) || $timeval < 1)
                    $error = $lng_ban['error_data'];
                if ($rights == 1 && $term != 14 || $rights == 2 && $term != 12 || $rights == 3 && $term != 11 || $rights == 4 && $term != 16 || $rights == 5 && $term != 15)
                    $error = $lng_ban['error_rights_section'];
                if (mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "' AND `ban_time` > '" . time() . "' AND `ban_type` = '$term'"), 0))
                    $error = $lng_ban['error_ban_exist'];
                switch ($time) {
                    case 2:
                        // hours
                        if ($timeval > 24)
                            $timeval = 24;
                        $timeval = $timeval * 3600;
                        break;

                    case 3:
                        // days
                        if ($timeval > 30)
                            $timeval = 30;
                        $timeval = $timeval * 86400;
                        break;

                    case 4:
                        // until unban (max 10 yeah)
                        $timeval = 315360000;
                        break;

                    default:
                        // minutes
                        if ($timeval > 60)
                            $timeval = 60;
                        $timeval = $timeval * 60;
                }
                if ($datauser['rights'] < 6 && $timeval > 86400)
                    $timeval = 86400;
                if ($datauser['rights'] < 7 && $timeval > 2592000)
                    $timeval = 2592000;
                if (!$error) {
                    // entered into the database
                    mysql_query("INSERT INTO `cms_ban_users` SET
                        `user_id` = '" . $user['id'] . "',
                        `ban_time` = '" . (time() + $timeval) . "',
                        `ban_while` = '" . time() . "',
                        `ban_type` = '$term',
                        `ban_who` = '$login',
                        `ban_reason` = '" . mysql_real_escape_string($reason) . "'
                    ");
                    echo '<div class="rmenu"><b>' . $lng_ban['user_banned'] . '</b></div>';
                } else {
                    echo functions::display_error($error);
                }
            } else {
                // Form parameters Ban
                echo '<form action="profile.php?act=ban&amp;mod=do&amp;user=' . $user['id'] . '" method="post">' .
                     '<div class="menu"><p><h3>' . $lng_ban['ban_type'] . '</h3>';
                if ($rights >= 6) {
                    // Locking
                    echo '<div><input name="term" type="radio" value="1" checked="checked" />&#160;' . $lng_ban['ban_1'] . '</div>';
                    // Privat
                    echo '<div><input name="term" type="radio" value="3" />&#160;' . $lng_ban['ban_3'] . '</div>';
                    // Comments
                    echo '<div><input name="term" type="radio" value="10" />&#160;' . $lng_ban['ban_10'] . '</div>';
                }
                if ($rights == 3 || $rights >= 6) {
                    // forum
                    echo '<div><input name="term" type="radio" value="11" ' . ($rights == 3 ? 'checked="checked"'
                            : '') . '/>&#160;' . $lng_ban['ban_11'] . '</div>';
                }
                if ($rights == 1 || $rights >= 6) {
                    // gallery
                    echo '<div><input name="term" type="radio" value="14" />&#160;' . $lng_ban['ban_14'] . '</div>';
                }
                if ($rights == 5 || $rights >= 6) {
                    // library
                    echo '<div><input name="term" type="radio" value="15" />&#160;' . $lng_ban['ban_15'] . '</div>';
                }
                if ($rights == 2 || $rights >= 6) {
                    // chat
                    echo '<div><input name="term" type="radio" value="12" />&#160;' . $lng_ban['ban_12'] . '</div>';
                }
                echo '</p><p><h3>' . $lng_ban['ban_time'] . '</h3>' .
                     '&#160;<input type="text" name="timeval" size="2" maxlength="2" value="12"/>&#160;' . $lng['time'] . '<br/>' .
                     '<input name="time" type="radio" value="1" />&#160;' . $lng_ban['ban_time_minutes'] . '<br />' .
                     '<input name="time" type="radio" value="2" checked="checked" />&#160;' . $lng_ban['ban_time_hours'] . '<br />';
                if ($rights >= 6)
                    echo '<input name="time" type="radio" value="3" />&#160;' . $lng_ban['ban_time_days'] . '<br />';
                if ($rights >= 7)
                    echo '<input name="time" type="radio" value="4" />&#160;<span class="red">' . $lng_ban['ban_time_before_cancel'] . '</span>';
                echo '</p><p><h3>' . $lng['reason'] . '</h3>';
                if (isset($_GET['fid'])) {
                    // If the ban from the forum, post fix ID
                    $fid = intval($_GET['fid']);
                    echo '&#160;' . $lng_ban['infringement'] . ' <a href="' . SITE_URL . '/forum/index.php?act=post&id=' . $fid . '">' . $lng_ban['in_forum'] . '</a><br />' .
                         '<input type="hidden" value="' . $fid . '" name="banref" />';
                }
                echo '&#160;<textarea rows="' . $set_user['field_h'] . '" name="reason"></textarea>' .
                     '</p><p><input type="submit" value="' . $lng['ban_do'] . '" name="submit" />' .
                     '</p></div></form>';
            }
        }
        break;

    case 'cancel':
        /*
        -----------------------------------------------------------------
        Разбаниваем пользователя (с сохранением истории)
        -----------------------------------------------------------------
        */
        if (!$ban || $user['id'] == $user_id || $rights < 7)
            echo functions::display_error($lng['error_wrong_data']);
        else {
            $req = mysql_query("SELECT * FROM `cms_ban_users` WHERE `id` = '$ban' AND `user_id` = '" . $user['id'] . "'");
            if (mysql_num_rows($req)) {
                $res = mysql_fetch_assoc($req);
                $error = false;
                if ($res['ban_time'] < time())
                    $error = $lng_ban['error_ban_not_active'];
                if (!$error) {
                    echo '<div class="phdr"><b>' . $lng_ban['ban_cancel'] . '</b></div>';
                    echo '<div class="gmenu"><p>' . functions::display_user($user) . '</p></div>';
                    if (isset($_POST['submit'])) {
                        mysql_query("UPDATE `cms_ban_users` SET `ban_time` = '" . time() . "' WHERE `id` = '$ban'");
                        echo '<div class="gmenu"><p><h3>' . $lng_ban['ban_cancel_confirmation'] . '</h3></p></div>';
                    } else {
                        echo '<form action="profile.php?act=ban&amp;mod=cancel&amp;user=' . $user['id'] . '&amp;ban=' . $ban . '" method="POST">' .
                             '<div class="menu"><p>' . $lng_ban['ban_cancel_help'] . '</p>' .
                             '<p><input type="submit" name="submit" value="' . $lng_ban['ban_cancel_do'] . '" /></p>' .
                             '</div></form>' .
                             '<div class="phdr"><a href="profile.php?act=ban&amp;user=' . $user['id'] . '">' . $lng['back'] . '</a></div>';
                    }
                } else {
                    echo functions::display_error($error);
                }
            } else {
                echo functions::display_error($lng['error_wrong_data']);
            }
        }
        break;

    case 'delete':
        /*
        -----------------------------------------------------------------
        Удаляем бан (с удалением записи из истории)
        -----------------------------------------------------------------
        */
        if (!$ban || $rights < 9)
            echo functions::display_error($lng['error_wrong_data']);
        else {
            $req = mysql_query("SELECT * FROM `cms_ban_users` WHERE `id` = '$ban' AND `user_id` = '" . $user['id'] . "'");
            if (mysql_num_rows($req)) {
                $res = mysql_fetch_assoc($req);
                echo '<div class="phdr"><b>' . $lng_ban['ban_delete'] . '</b></div>' .
                     '<div class="gmenu"><p>' . functions::display_user($user) . '</p></div>';
                if (isset($_POST['submit'])) {
                    mysql_query("DELETE FROM `cms_ban_users` WHERE `id` = '$ban'");
                    echo '<div class="gmenu"><p><h3>' . $lng_ban['ban_deleted'] . '</h3><a href="profile.php?act=ban&amp;user=' . $user['id'] . '">' . $lng['continue'] . '</a></p></div>';
                } else {
                    echo '<form action="profile.php?act=ban&amp;mod=delete&amp;user=' . $user['id'] . '&amp;ban=' . $ban . '" method="POST">' .
                         '<div class="menu"><p>' . $lng_ban['ban_delete_help'] . '</p>' .
                         '<p><input type="submit" name="submit" value="' . $lng['delete'] . '" /></p>' .
                         '</div></form>' .
                         '<div class="phdr"><a href="profile.php?act=ban&amp;user=' . $user['id'] . '">' . $lng['back'] . '</a></div>';
                }
            } else {
                echo functions::display_error($lng['error_wrong_data']);
            }
        }
        break;

    case 'delhist':
        /*
        -----------------------------------------------------------------
        Очищаем историю нарушений юзера
        -----------------------------------------------------------------
        */
        if ($rights == 9) {
            echo '<div class="phdr"><b>' . $lng_ban['infringements_history'] . '</b></div>' .
                 '<div class="gmenu"><p>' . functions::display_user($user) . '</p></div>';
            if (isset($_POST['submit'])) {
                mysql_query("DELETE FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'");
                echo '<div class="gmenu"><h3>' . $lng_ban['history_cleared'] . '</h3></div>';
            } else {
                echo '<form action="profile.php?act=ban&amp;mod=delhist&amp;user=' . $user['id'] . '" method="post">' .
                     '<div class="menu"><p>' . $lng_ban['clear_confirmation'] . '</p>' .
                     '<p><input type="submit" value="' . $lng['clear'] . '" name="submit" />' .
                     '</p></div></form>';
            }
            $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'"), 0);
            echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>' .
                 '<p>' . ($total
                    ? '<a href="profile.php?act=ban&amp;user=' . $user['id'] . '">' . $lng_ban['infringements_history'] . '</a><br />'
                    : '') .
                 '<a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=ban_panel">' . $lng_ban['ban_panel'] . '</a></p>';
        } else {
            echo functions::display_error($lng_ban['error_rights_clear']);
        }
        break;

    default:
        /*
        -----------------------------------------------------------------
        История нарушений
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '"><b>' . $lng['profile'] . '</b></a> | ' . $lng_ban['infringements_history'] . '</div>';
        // Меню
        $menu = array();
        if ($rights >= 6)
            $menu[] = '<a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=ban_panel">' . $lng_ban['ban_panel'] . '</a>';
        if ($rights == 9)
            $menu[] = '<a href="profile.php?act=ban&amp;mod=delhist&amp;user=' . $user['id'] . '">' . $lng_ban['clear_history'] . '</a>';
        if (!empty($menu))
            echo '<div class="topmenu">' . functions::display_menu($menu) . '</div>';
        if ($user['id'] != $user_id)
            echo '<div class="user"><p>' . functions::display_user($user) . '</p></div>';
        else
            echo '<div class="list2"><p>' . $lng_ban['my_infringements'] . '</p></div>';
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'"), 0);
        if ($total) {
            $req = mysql_query("SELECT * FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "' ORDER BY `ban_time` DESC LIMIT $start, $kmess");
            $i = 0;
            while ($res = mysql_fetch_assoc($req)) {
                $remain = $res['ban_time'] - time();
                $period = $res['ban_time'] - $res['ban_while'];
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                echo '<img src="' . SITE_URL . '/images/' . ($remain > 0 ? 'red'
                        : 'green') . '.gif" width="16" height="16" align="left" />&#160;' .
                     '<b>' . $lng_ban['ban_' . $res['ban_type']] . '</b>' .
                     ' <span class="gray">(' . date("d.m.Y / H:i", $res['ban_while']) . ')</span>' .
                     '<br />' . functions::checkout($res['ban_reason']) .
                     '<div class="sub">';
                if ($rights > 0)
                    echo '<span class="gray">' . $lng_ban['ban_who'] . ':</span> ' . $res['ban_who'] . '<br />';
                echo '<span class="gray">' . $lng['term'] . ':</span> ' . ($period < 86400000
                        ? functions::timecount($period) : $lng_ban['ban_time_before_cancel']);
                if ($remain > 0)
                    echo '<br /><span class="gray">' . $lng['remains'] . ':</span> ' . functions::timecount($remain);
                // Меню отдельного бана
                $menu = array();
                if ($rights >= 7 && $remain > 0)
                    $menu[] = '<a href="profile.php?act=ban&amp;mod=cancel&amp;user=' . $user['id'] . '&amp;ban=' . $res['id'] . '">' . $lng_ban['ban_cancel_do'] . '</a>';
                if ($rights == 9)
                    $menu[] = '<a href="profile.php?act=ban&amp;mod=delete&amp;user=' . $user['id'] . '&amp;ban=' . $res['id'] . '">' . $lng_ban['ban_delete_do'] . '</a>';
                if (!empty($menu))
                    echo '<div>' . functions::display_menu($menu) . '</div>';
                echo '</div></div>';
                ++$i;
            }
        } else {
            echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
        }
        echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        if ($total > $kmess) {
            echo '<p>' . functions::display_pagination('profile.php?act=ban&amp;user=' . $user['id'] . '&page=', $start, $total, $kmess) . '</p>' .
                 '<p><form action="profile.php?act=ban&amp;user=' . $user['id'] . '" method="post">' .
                 '<input type="text" name="page" size="2"/>' .
                 '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
        }
}