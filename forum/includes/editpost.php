<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = 'Chỉnh sửa bài đăng';
require('../incfiles/head.php');
if (!$user_id || !$id) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
$req = mysql_query("SELECT `forum`.*,`t2`.`text` AS `ref_text` FROM `forum` LEFT JOIN `forum` as `t2` ON `t2`.`id`=`forum`.`refid` WHERE `forum`.`id` = '$id' AND `forum`.`type` = 'm' " . ($rights >= 7 ? "" : " AND `forum`.`close` != '1'"));
if (mysql_num_rows($req)) {
    // Preliminary checks
    $res = mysql_fetch_assoc($req);

    $topic = mysql_fetch_assoc(mysql_query("SELECT `refid`, `curators` FROM `forum` WHERE `id` = " . $res['refid']));
    $curators = !empty($topic['curators']) ? unserialize($topic['curators']) : array();

    if (array_key_exists($user_id, $curators)) $rights = 3;
    $page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id`<='$id'" . ($rights < 7 ? " AND `close` != '1'" : '')), 0) / $kmess);
    $posts = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `close` != '1'"), 0);
    $link = functions::bodau($res['ref_text']).'.' . $res['refid'] . '.html?page=' . $page;
    $error = FALSE;
    if ($rights == 3 || $rights >= 6) {
        // Check for Administration
        if ($res['user_id'] != $user_id) {
            $req_u = mysql_query("SELECT `rights` FROM `users` WHERE `id` = '" . $res['user_id'] . "'");
            if (mysql_num_rows($req_u)) {
                $res_u = mysql_fetch_assoc($req_u);
                if ($res_u['rights'] > $datauser['rights'])
                    $error = $lng['error_edit_rights'] . '<br /><a href="' . $link . '">' . $lng['back'] . '</a>';
            }
        }
    } else {
        // Check for normal users
        if ($res['user_id'] != $user_id)
            $error = $lng_forum['error_edit_another'] . '<br /><a href="' . $link . '">' . $lng['back'] . '</a>';
        if (!$error) {
            $section = mysql_fetch_assoc(mysql_query("SELECT * FROM `forum` WHERE `id` = " . $topic['refid']));
            $allow = !empty($section['edit']) ? intval($section['edit']) : 0;

            $check = TRUE;
            if ($allow == 2) {
                $first = mysql_fetch_assoc(mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['refid'] . "' ORDER BY `id` ASC LIMIT 1"));
                if ($first['user_id'] == $user_id && $first['id'] == $id) {
                    $check = FALSE;
                }
            }

            if ($check) {
                $req_m = mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['refid'] . "' ORDER BY `id` DESC LIMIT 1");
                $res_m = mysql_fetch_assoc($req_m);
                if ($res_m['user_id'] != $user_id) {
                    $error = $lng_forum['error_edit_last'] . '<br /><a href="' . $link . '">' . $lng['back'] . '</a>';
                } elseif ($res['time'] < time() - 300) {
                    $error = $lng_forum['error_edit_timeout'] . '<br /><a href="' . $link . '">' . $lng['back'] . '</a>';
                }
            }
        }
    }
} else {
    $error = $lng_forum['error_post_deleted'] . '<br /><a href="index.php">' . $lng['forum'] . '</a>';
}
if (!$error) {
    switch ($do) {
        case 'restore':
            // Undelete post
            $req_u = mysql_query("SELECT `postforum` FROM `users` WHERE `id` = '" . $res['user_id'] . "'");
            if (mysql_num_rows($req_u)) {
                // add one point to the meter user posts
                $res_u = mysql_fetch_assoc($req_u);
                mysql_query("UPDATE `users` SET `postforum` = '" . ($res_u['postforum'] + 1) . "' WHERE `id` = '" . $res['user_id'] . "'");
            }
            mysql_query("UPDATE `forum` SET `close` = '0', `close_who` = '$login' WHERE `id` = '$id'");
            $req_f = mysql_query("SELECT * FROM `cms_forum_files` WHERE `post` = '$id' LIMIT 1");
            if (mysql_num_rows($req_f)) {
                mysql_query("UPDATE `cms_forum_files` SET `del` = '0' WHERE `post` = '$id' LIMIT 1");
            }
            header('Location: ' . $link);
            exit;
            break;

        case 'delete':
            // Removing the post and attached file
            if ($res['close'] != 1) {
                $req_u = mysql_query("SELECT `postforum` FROM `users` WHERE `id` = '" . $res['user_id'] . "'");
                if (mysql_num_rows($req_u)) {
                    // Subtract one point from the meter user posts
                    $res_u = mysql_fetch_assoc($req_u);
                    $postforum = $res_u['postforum'] > 0 ? $res_u['postforum'] - 1 : 0;
                    mysql_query("UPDATE `users` SET `postforum` = '" . $postforum . "' WHERE `id` = '" . $res['user_id'] . "'");
                }
            }
            if ($rights == 9 && !isset($_GET['hide'])) {
                // Deleting a post (Supervisor)
                $req_f = mysql_query("SELECT * FROM `cms_forum_files` WHERE `post` = '$id' LIMIT 1");
                if (mysql_num_rows($req_f)) {
                    // If there is an attachment, delete it
                    $res_f = mysql_fetch_assoc($req_f);
                    unlink('../files/forum/attach/' . $res_f['filename']);
                    mysql_query("DELETE FROM `cms_forum_files` WHERE `post` = '$id' LIMIT 1");
                }
                // Forming a link to the page theme
                $page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id`<'$id'"), 0) / $kmess);
                mysql_query("DELETE FROM `forum` WHERE `id` = '$id'");
				$count_l = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_likes` WHERE `type`="1" AND `sub_id`="'. $id .'"'), 0);
				if($count_l){
					mysql_query('DELETE FROM `cms_likes` WHERE `type`="1" AND `sub_id` = "'. $id .'"');
				}
                if ($posts < 2) {
                    // Transfer to delete the entire thread
                    header('Location: index.php?act=deltema&id=' . $res['refid']);
                } else {
                    header('Location: '.functions::bodau($res['ref_text']).'.' . $res['refid'] . '.html?page=' . $page);
                }
                exit;
            } else {
                // Closing post
                $req_f = mysql_query("SELECT `id` FROM `cms_forum_files` WHERE `post` = '$id' LIMIT 1");
                if (mysql_num_rows($req_f)) {
                    // If there is an attached file, hide it
                    mysql_query("UPDATE `cms_forum_files` SET `del` = '1' WHERE `post` = '$id' LIMIT 1");
                }
                if ($posts == 1) {
                    // If this was the last post topics, then hide itself subject
                    $res_l = mysql_fetch_assoc(mysql_query("SELECT `forum`.`refid`,`section`.`text` FROM `forum` LEFT JOIN `forum` AS `section` ON `section`.`id` = `forum`.`refid` WHERE `forum`.`id` = '" . $res['refid'] . "'")) or die(mysql_error());
                    mysql_query("UPDATE `forum` SET `close` = '1', `close_who` = '$login' WHERE `id` = '" . $res['refid'] . "' AND `type` = 't'");
                    header('Location: '.functions::bodau($res_l['text']).'.' . $res_l['refid'] .'.html');
                    exit;
                } else {
                    mysql_query("UPDATE `forum` SET `close` = '1', `close_who` = '$login' WHERE `id` = '$id'");
                    header('Location: '.functions::bodau($res['ref_text']).'.' . $res['refid'] . '.html?page=' . $page);
                    exit;
                }
            }
            break;

        case 'del':
            // Deleting a post prior reminder
            echo '<div class="phdr"><a href="' . $link . '"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['delete_post'] . '</div>' .
                '<div class="rmenu"><p>';
            if ($posts == 1)
                echo $lng_forum['delete_last_post_warning'] . '<br />';
            echo $lng['delete_confirmation'] . '</p>' .
                '<p><a href="' . $link . '">' . $lng['cancel'] . '</a> | <a href="index.php?act=editpost&do=delete&hide&id=' . $id . '">' . $lng['hide'] . '</a>';
            if ($rights == 9)
                echo ' | <a href="index.php?act=editpost&do=delete&id=' . $id . '">' . $lng['delete'] . '</a>';
            echo '</p></div>';
            echo '<div class="notif">' . $lng_forum['delete_post_help'] . '</div>';
            break;

        default:
            // Edit post
            $msg = isset($_POST['msg']) ? functions::checkin(trim($_POST['msg'])) : '';
            if (isset($_POST['submit'])) {
                if (empty($_POST['msg'])) {
                    echo functions::display_error($lng['error_empty_message'], '<a href="index.php?act=editpost&amp;id=' . $id . '">' . $lng['repeat'] . '</a>');
                    require('../incfiles/end.php');
                    exit;
                }
                mysql_query("UPDATE `forum` SET
                    `tedit` = '" . time() . "',
                    `edit` = '$login',
                    `text` = '" . mysql_real_escape_string($msg) . "'
                    WHERE `id` = '$id'
                ");
                header('Location: '.functions::bodau($res['ref_text']).'.' . $res['refid'] . '.html?page=' . $page);
                exit;
            } else {
                $msg_pre = functions::checkout($msg, 1, 1, 1);
                echo '<div class="phdr"><a href="' . $link . '"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['edit_message'] . '</div>';
                if ($msg && !isset($_POST['submit'])) {
                    $user = mysql_fetch_assoc(mysql_query("SELECT * FROM `users` WHERE `id` = '" . $res['user_id'] . "' LIMIT 1"));
                    echo '<div class="list1">' . functions::display_user($user, array('iphide' => 1, 'header' => '<span class="gray">(' . functions::display_date($res['time']) . ')</span>', 'body' => $msg_pre)) . '</div>';
                }
                echo '<div class="rmenu"><form name="form" action="?act=editpost&amp;id=' . $id . '&amp;start=' . $start . '" method="post"><p>';
                echo bbcode::auto_bb('form', 'msg');
                echo '<textarea rows="' . $set_user['field_h'] . '" name="msg">' . (empty($_POST['msg']) ? htmlspecialchars($res['text']) : functions::checkout($_POST['msg'])) . '</textarea><br/>';
                echo '</p><p><input type="submit" name="submit" value="' . $lng['save'] . '" style="width: 107px; cursor: pointer;"/> ' .
                    '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' .
                    '</p></form></div>' .
                    '<div class="phdr"><a href="' . SITE_URL . '/faq.php?act=smileys">' . $lng['smileys'] . '</a></div>' .
                    '<div class="menu"><a href="' . $link . '">' . $lng['back'] . '</a></div>';
            }
    }
} else {
    // Displays an error message
    echo functions::display_error($error);
}