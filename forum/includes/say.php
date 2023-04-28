<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

// Deny access to specific situations
if (!$id || !$user_id || isset($ban['1']) || isset($ban['11']) || (!$rights && $set['mod_forum'] == 3)) {
    require('../incfiles/head.php');
    echo functions::display_error($lng['access_forbidden']);
    require('../incfiles/end.php');
    exit;
}

// The auxiliary processing function links Forum
function forum_link($m)
{
    global $set;
    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);
        if (('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == SITE_URL . '/forum/index.php?id=') || ('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == SITE_URL . '/forum/?id=') || ('http://' . $p['host'] == SITE_URL && isset($p['path']) && preg_match('#/forum/([^\.]+?)\.([\d]+?)\.html#', $p['path']))) {
            if(preg_match('#/forum/([^\.]+?)\.([\d]+?)\.html#', $p['path'])){
                $thid = abs(intval(preg_replace('/^([^\.]+?)\./si', '', $p['path'])));
            }else{
                $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
            }
            $req = mysql_query("SELECT `text` FROM `forum` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");
            if (mysql_num_rows($req) > 0) {
                $res = mysql_fetch_array($req);
                $name = strtr($res['text'], array(
                    '['      => '',
                    ']'      => ''
                ));
                if (mb_strlen($name) > 40)
                    $name = mb_substr($name, 0, 40) . '...';

                return '[url=' . $m[3] . ']' . $name . '[/url]';
            } else {
                return $m[3];
            }
        } else
            return $m[3];
    }
}
$headmod = 'forum,' . $id . ',1';
$agn1 = strtok($agn, ' ');
$type = mysql_query("SELECT `forum`.* FROM `forum` WHERE `forum`.`id` = '$id'");
$type1 = mysql_fetch_assoc($type);
// Check for flood
$flood = functions::antiflood();
if ($flood) {
    require('../incfiles/head.php');
    echo functions::display_error($lng['error_flood'] . ' ' . $flood . $lng['sec'], '<a href="'.functions::bodau($type1['text']).'.'.$id.'.html?start=' . $start . '">' . $lng['back'] . '</a>');
    require('../incfiles/end.php');
    exit;
}
switch ($type1['type']) {
    case 't':
        // Adding a simple message
        if (($type1['edit'] == 1 || $type1['close'] == 1) && $rights < 7) {
            // Проверка, закрыта ли тема
            require('../incfiles/head.php');
            echo functions::display_error($lng_forum['error_topic_closed'], '<a href="'.functions::bodau($type1['text']).'.'.$id.'.html">' . $lng['back'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        $msg = isset($_POST['msg']) ? functions::checkin(trim($_POST['msg'])) : '';
        // Handle links
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&_=/%-:#]*)~', 'forum_link', $msg);
        if (isset($_POST['submit'])
            && !empty($_POST['msg'])
            && isset($_POST['token'])
            && isset($_SESSION['token'])
            && $_POST['token'] == $_SESSION['token']
        ) {
            // Check the minimum length
            if (mb_strlen($msg) < 4) {
                require('../incfiles/head.php');
                echo functions::display_error($lng['error_message_short'], '<a href="'.functions::bodau($type1['text']).'.'.$id.'.html">' . $lng['back'] . '</a>');
                require('../incfiles/end.php');
                exit;
            }
            // Check, if the message is not repeated?
            $req = mysql_query("SELECT * FROM `forum` WHERE `user_id` = '$user_id' AND `type` = 'm' ORDER BY `time` DESC LIMIT 1");
            if (mysql_num_rows($req) > 0) {
                $res = mysql_fetch_array($req);
                if ($msg == $res['text']) {
                    require('../incfiles/head.php');
                    echo functions::display_error($lng['error_message_exists'], '<a href="'.functions::bodau($type1['text']).'.'.$id.'.html?start=' . $start . '">' . $lng['back'] . '</a>');
                    require('../incfiles/end.php');
                    exit;
                }
            }
            // Remove the filter, if it was
            if (isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id) {
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
            }

            unset($_SESSION['token']);

            // Add a message on the base
            mysql_query("INSERT INTO `forum` SET
                `refid` = '$id',
                `type` = 'm' ,
                `time` = '" . time() . "',
                `user_id` = '$user_id',
                `from` = '$login',
                `ip` = '" . core::$ip . "',
                `ip_via_proxy` = '" . core::$ip_via_proxy . "',
                `soft` = '" . mysql_real_escape_string($agn1) . "',
                `text` = '" . mysql_real_escape_string($msg) . "',
                `edit` = '',
                `curators` = ''
            ");
            $fadd = mysql_insert_id();
            mysql_query("UPDATE `forum` SET
                `time` = '" . time() . "'
                WHERE `id` = '$id'
            ");
            // Update user statistics
            mysql_query("UPDATE `users` SET
                `postforum`='" . ($datauser['postforum'] + 1) . "',
                `lastpost` = '" . time() . "'
                WHERE `id` = '$user_id'
            ");
            // Compute, which page gets added post
            $page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '$id'" . ($rights >= 7 ? '' : " AND `close` != '1'")), 0) / $kmess);
            if (isset($_POST['addfiles'])) {
                header('Location: index.php?id='.$fadd.'&act=addfile');
            } else {
                header('Location: '.functions::bodau($type1['text']).'.'.$id.'.html?page='.$page.'');
            }
            exit;
        } else {
            require('../incfiles/head.php');
            if ($datauser['postforum'] == 0) {
                if (!isset($_GET['yes'])) {
                    $lng_faq = core::load_lng('faq');
                    echo '<p>' . $lng_faq['forum_rules_text'] . '</p>' .
                        '<p><a href="index.php?act=say&id=' . $id . '&yes">' . $lng_forum['agree'] . '</a> | ' .
                        '<a href="'.functions::bodau($type1['text']).'.' . $id . '.html">' . $lng_forum['not_agree'] . '</a></p>';
                    require('../incfiles/end.php');
                    exit;
                }
            }
            $msg_pre = functions::checkout($msg, 1, 1, 1);
            echo '<div class="phdr"><b>' . $lng_forum['topic'] . ':</b> ' . htmlspecialchars($type1['text']) . '</div>';
            if ($msg && !isset($_POST['submit'])) {
                echo '<div class="list1">' . functions::display_user($datauser, array('iphide' => 1, 'header' => '<span class="gray">(' . functions::display_date(time()) . ')</span>', 'body' => $msg_pre)) . '</div>';
            }
            echo '<form name="form" action="index.php?act=say&id=' . $id . '&amp;start=' . $start . '" method="post"><div class="gmenu">' .
                '<p><h3>' . $lng_forum['post'] . '</h3>';
            echo '</p><p>' . bbcode::auto_bb('form', 'msg');
            echo '<textarea rows="' . $set_user['field_h'] . '" name="msg">' . (empty($_POST['msg']) ? '' : functions::checkout($msg)) . '</textarea></p>' .
                '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($_POST['addfiles']) ? 'checked="checked" ' : '') . '/> ' . $lng_forum['add_file'];
            $token = mt_rand(1000, 100000);
            $_SESSION['token'] = $token;
            echo '</p><p>' .
                '<input type="submit" name="submit" value="' . $lng['sent'] . '" style="width: 107px; cursor: pointer"/> ' .
                '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer"/>' .
                '<input type="hidden" name="token" value="' . $token . '"/>' .
                '</p></div></form>';
        }

        echo '<div class="phdr">' .
            '<a href="' . SITE_URL . '/faq.php?act=smileys">' . $lng['smileys'] . '</a></div>' .
            '<div class="gmenu"><a href="'.functions::bodau($type1['text']).'.' . $id . '.html?start=' . $start . '">' . $lng['back'] . '</a></div>';
        break;

    case 'm':
        // Add a message Quote post
        $th = $type1['refid'];
        $th2 = mysql_query("SELECT `id`,`edit`,`close`,`text` FROM `forum` WHERE `id` = '$th'");
        $th1 = mysql_fetch_array($th2);
        if (($th1['edit'] == 1 || $th1['close'] == 1) && $rights < 7) {
            require('../incfiles/head.php');
            echo functions::display_error($lng_forum['error_topic_closed'], '<a href="'.functions::bodau($th1['text']).'.' . $th1['id'] . '.html">' . $lng['back'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        if ($type1['user_id'] == $user_id) {
            require('../incfiles/head.php');
            echo functions::display_error('Bạn không thể trích dẫn bài viết của chính mình!', '<a href="'.functions::bodau($th1['text']).'.' . $th1['id'] . '.html">' . $lng['back'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        $msg = isset($_POST['msg']) ? functions::checkin($_POST['msg']) : '';
        if (!empty($_POST['citata'])) {
            // If you have a quote, format it, and treat
            $citata = isset($_POST['citata']) ? trim($_POST['citata']) : '';
            $citata = preg_replace('#\[quote\](.*?)\[/quote\]#si', '', $citata);
            $citata = preg_replace('#\[quote=([^\]]+)\](.*?)\[/quote\]#si', '', $citata);
            $msg = '[quote=' . $type1['id'] . ',' . $type1['user_id'] . ',' . $type1['from'] . ']' . $citata . '[/quote]' . "\n" . $msg;
        }
        // Handle links
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&_=/%-:#]*)~', 'forum_link', $msg);
        if (isset($_POST['submit'])
            && isset($_POST['token'])
            && isset($_SESSION['token'])
            && $_POST['token'] == $_SESSION['token']
        ) {
            if (empty($_POST['msg'])) {
                require('../incfiles/head.php');
                echo functions::display_error($lng['error_empty_message'], '<a href="index.php?act=say&id=' . $id . '">' . $lng['repeat'] . '</a>');
                require('../incfiles/end.php');
                exit;
            }
            // Check the minimum length
            if (mb_strlen($msg) < 4) {
                require('../incfiles/head.php');
                echo functions::display_error($lng['error_message_short'], '<a href="index.php?act=say&id=' . $id . '">' . $lng['back'] . '</a>');
                require('../incfiles/end.php');
                exit;
            }
            // Check, if the message is not repeated?
            $req = mysql_query("SELECT * FROM `forum` WHERE `user_id` = '$user_id' AND `type` = 'm' ORDER BY `time` DESC LIMIT 1");
            if (mysql_num_rows($req) > 0) {
                $res = mysql_fetch_array($req);
                if ($msg == $res['text']) {
                    require('../incfiles/head.php');
                    echo functions::display_error($lng['error_message_exists'], '<a href="'.functions::bodau($th1['text']).'.' . $th1['id'] . '.html?start=' . $start . '">' . $lng['back'] . '</a>');
                    require('../incfiles/end.php');
                    exit;
                }
            }
            // Remove the filter, if it was
            if (isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $th) {
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
            }

            unset($_SESSION['token']);

            // Add a message on the base
            mysql_query("INSERT INTO `forum` SET
                `refid` = '$th',
                `type` = 'm',
                `time` = '" . time() . "',
                `user_id` = '$user_id',
                `from` = '$login',
                `ip` = '" . core::$ip . "',
                `ip_via_proxy` = '" . core::$ip_via_proxy . "',
                `soft` = '" . mysql_real_escape_string($agn1) . "',
                `text` = '" . mysql_real_escape_string($msg) . "',
                `edit` = '',
                `curators` = ''
            ");
            $fadd = mysql_insert_id();
            // Обновляем время топика
            mysql_query("UPDATE `forum`
                SET `time` = '" . time() . "'
                WHERE `id` = '$th'
            ");
            // Update user statistics
            mysql_query("UPDATE `users` SET
                `postforum`='" . ($datauser['postforum'] + 1) . "',
                `lastpost` = '" . time() . "'
                WHERE `id` = '$user_id'
            ");
            // Compute, which page gets added post
            $page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '$th'" . ($rights >= 7 ? '' : " AND `close` != '1'")), 0) / $kmess);
            if (isset($_POST['addfiles'])) {
                header('Location: index.php?id='.$fadd.'&act=addfile');
            } else {
                header('Location: '.functions::bodau($th1['text']).'.'.$th.'.html?page='.$page.'');
            }
            exit;
        } else {
            $textl = $lng['forum'];
            require('../incfiles/head.php');
            $qt = $type1['text'];
            if ($datauser['postforum'] == 0) {
                if (!isset($_GET['yes'])) {
                    $lng_faq = core::load_lng('faq');
                    echo '<p>' . $lng_faq['forum_rules_text'] . '</p>';
                    echo '<p><a href="index.php?act=say&id=' . $id . '&yes">' . $lng_forum['agree'] . '</a> | <a href="'.functions::bodau($th1['text']).'.' . $th1['id'] . '.html">' . $lng_forum['not_agree'] . '</a></p>';
                    require('../incfiles/end.php');
                    exit;
                }
            }
            $msg_pre = functions::checkout($msg, 1, 1, 1);
            echo '<div class="phdr"><b>' . $lng_forum['topic'] . ':</b> ' . htmlspecialchars($th1['text']) . '</div>';
            $qt = preg_replace('#\[quote\](.*?)\[/quote\]#si', '', $qt);
            $qt = preg_replace('#\[quote=([^\]]+)\](.*?)\[/quote\]#si', '', $qt);
            $qt = functions::checkout($qt);
            if (!empty($_POST['msg']) && !isset($_POST['submit'])) {
                echo '<div class="list1">' . functions::display_user($datauser, array('iphide' => 1, 'header' => '<span class="gray">(' . functions::display_date(time()) . ')</span>', 'body' => $msg_pre)) . '</div>';
            }
            echo '<form name="form" action="index.php?act=say&id=' . $id . '&start=' . $start . '" method="post"><div class="gmenu">';
            echo '<p><b>' . $type1['from'] . '</b></p>' .
                    '<p><h3>' . $lng_forum['cytate'] . '</h3>' .
                    '<textarea rows="' . $set_user['field_h'] . '" name="citata">' . (empty($_POST['citata']) ? $qt : functions::checkout($_POST['citata'])) . '</textarea>' .
                    '<br /><small>' . $lng_forum['cytate_help'] . '</small></p>';
            echo '<p><h3>' . $lng_forum['post'] . '</h3>';
            echo '</p><p>' . bbcode::auto_bb('form', 'msg');
            echo '<textarea rows="' . $set_user['field_h'] . '" name="msg">' . (empty($_POST['msg']) ? '' : functions::checkout($_POST['msg'])) . '</textarea></p>' .
                '<p><input type="checkbox" name="addfiles" value="1" ' . (isset($_POST['addfiles']) ? 'checked="checked" ' : '') . '/> ' . $lng_forum['add_file'];
            $token = mt_rand(1000, 100000);
            $_SESSION['token'] = $token;
            echo '</p><p><input type="submit" name="submit" value="' . $lng['sent'] . '" style="width: 107px; cursor: pointer;"/> ' .
                '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' .
                '<input type="hidden" name="token" value="' . $token . '"/>' .
                '</p></div></form>';
        }
        echo '<div class="phdr">' .
            '<a href="' . SITE_URL . '/faq.php?act=smileys">' . $lng['smileys'] . '</a></div>' .
            '<div class="menu"><a href="'.functions::bodau($th1['text']).'.' . $type1['refid'] . '.html?start=' . $start . '">' . $lng['back'] . '</div>';
        break;

    default:
        require('../incfiles/head.php');
        echo functions::display_error($lng_forum['error_topic_deleted'], '<a href="index.php">' . $lng['to_forum'] . '</a>');
        require('../incfiles/end.php');
}