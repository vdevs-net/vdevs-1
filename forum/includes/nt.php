<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

// Check rights
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
                    $name = mb_substr($name, 0, 63) . '...';

                return '[url=' . $m[3] . ']' . $name . '[/url]';
            } else {
                return $m[3];
            }
        } else
            return $m[3];
    }
}

$req_r = mysql_query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 'r' LIMIT 1");
if (!mysql_num_rows($req_r)) {
    require('../incfiles/head.php');
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
$res_r = mysql_fetch_assoc($req_r);

// Check for flood
$flood = functions::antiflood();
if ($flood) {
    require('../incfiles/head.php');
    echo functions::display_error($lng['error_flood'] . ' ' . $flood . $lng['sec'] . ', <a href="'.functions::bodau($res_r['text']).'.' . $id . '.html?start=' . $start . '">' . $lng['back'] . '</a>');
    require('../incfiles/end.php');
    exit;
}

$th = isset($_POST['th']) ? functions::checkin(mb_substr(trim($_POST['th']), 0, 255)) : '';
$msg = isset($_POST['msg']) ? functions::checkin($_POST['msg']) : '';
$tags = isset($_POST['tags']) ? functions::checkin($_POST['tags']) : '';
$tags2 = isset($_POST['tags']) ? functions::forum_tags($_POST['tags']) : '';
$msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&_=/%-:#]*)~', 'forum_link', $msg);
if (isset($_POST['submit'])
    && isset($_POST['token'])
    && isset($_SESSION['token'])
    && $_POST['token'] == $_SESSION['token']
) {
    $error = array();
    if (empty($th))
        $error[] = $lng_forum['error_topic_name'];
    if (mb_strlen($th) < 2)
        $error[] = $lng_forum['error_topic_name_lenght'];
    if (empty($msg))
        $error[] = $lng['error_empty_message'];
    if (mb_strlen($msg) < 4)
        $error[] = $lng['error_message_short'];
	
    if (!$error) {
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&_=/%-:#]*)~', 'forum_link', $msg);
        // Прверяем, есть ли уже такая тема в текущем разделе?
        if (mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `refid` = '$id' AND `text` = '$th'"), 0) > 0)
            $error[] = $lng_forum['error_topic_exists'];
        // Проверяем, не повторяется ли сообщение?
        $req = mysql_query("SELECT * FROM `forum` WHERE `user_id` = '$user_id' AND `type` = 'm' ORDER BY `time` DESC");
        if (mysql_num_rows($req) > 0) {
            $res = mysql_fetch_array($req);
            if ($msg == $res['text'])
                $error[] = $lng['error_message_exists'];
        }
    }
    if (!$error) {
        unset($_SESSION['token']);

        // Если задано в настройках, то назначаем топикстартера куратором
        $curator = $res_r['edit'] == 1 ? serialize(array($user_id => $login)) : '';

        // Добавляем тему
        mysql_query("INSERT INTO `forum` SET
            `refid` = '$id',
            `type` = 't',
            `time` = '" . time() . "',
            `user_id` = '$user_id',
            `from` = '$login',
            `text` = '". mysql_real_escape_string($th). "',
            `soft` = '". mysql_real_escape_string($tags2). "',
            `edit` = '',
            `curators` = '$curator'
        ");
        $rid = mysql_insert_id();

        // Добавляем текст поста
        mysql_query("INSERT INTO `forum` SET
            `refid` = '$rid',
            `type` = 'm',
            `time` = '" . time() . "',
            `user_id` = '$user_id',
            `from` = '$login',
            `ip` = '" . core::$ip . "',
            `ip_via_proxy` = '" . core::$ip_via_proxy . "',
            `soft` = '" . mysql_real_escape_string($agn) . "',
            `text` = '" . mysql_real_escape_string($msg) . "',
            `edit` = '',
            `curators` = ''
        ");

        $postid = mysql_insert_id();

        // Записываем счетчик постов юзера
        $fpst = $datauser['postforum'] + 1;
        mysql_query("UPDATE `users` SET
            `postforum` = '$fpst',
            `lastpost` = '" . time() . "'
            WHERE `id` = '$user_id'
        ");

        // Ставим метку о прочтении
        mysql_query("INSERT INTO `cms_forum_rdm` SET
            `topic_id`='$rid',
            `user_id`='$user_id',
            `time`='" . time() . "'
        ");

        if (isset($_POST['addfiles'])) {
            header('Location: index.php?id='.$postid.'&act=addfile');
        } else {
            header('Location: '.functions::bodau($th).'.'.$rid.'.html');
        }
        exit;
    } else {
        // Выводим сообщение об ошибке
        require('../incfiles/head.php');
        echo functions::display_error($error, '<a href="index.php?act=nt&id=' . $id . '">' . $lng['repeat'] . '</a>');
        require('../incfiles/end.php');
        exit;
    }
} else {
    $req_c = mysql_query("SELECT * FROM `forum` WHERE `id` = '" . $res_r['refid'] . "'");
    $res_c = mysql_fetch_assoc($req_c);
    require('../incfiles/head.php');
    if ($datauser['postforum'] == 0) {
        if (!isset($_GET['yes'])) {
            $lng_faq = core::load_lng('faq');
            echo '<div class="gmenu">' . $lng_faq['forum_rules_text'] . '</div>';
            echo '<div class="phdr"><a href="index.php?act=nt&id=' . $id . '&yes">' . $lng_forum['agree'] . '</a> | <a href="'.functions::bodau($res_r['text']).'.' . $id . '.html">' . $lng_forum['not_agree'] . '</a></div>';
            require('../incfiles/end.php');
            exit;
        }
    }
    $msg_pre = functions::checkout($msg, 1, 1, 1);
    echo '<div class="phdr"><a href="'.functions::bodau($res_r['text']).'.' . $id . '.html"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['new_topic'] . '</div>';
    if ($msg && $th && !isset($_POST['submit']))
        echo '<div class="list1">' . functions::image('op.gif') . '<span style="font-weight: bold">' . functions::checkout($th) . '</span></div>' .
            '<div class="list2">' . functions::display_user($datauser, array('iphide' => 1, 'header' => '<span class="gray">(' . functions::display_date(time()) . ')</span>', 'body' => $msg_pre)) . '</div>';
    echo '<form name="form" action="index.php?act=nt&id=' . $id . '" method="post">' .
        '<div class="gmenu">' .
        '<p><h3>' . $lng['section'] . '</h3>' .
        '<a href="'.functions::bodau($res_c['text']).'.' . $res_c['id'] . '.html">' . functions::checkout($res_c['text']) . '</a> | <a href="'.functions::bodau($res_r['text']).'.' . $id . '.html">' . functions::checkout($res_r['text']) . '</a></p>' .
        '<p><h3>' . $lng_forum['new_topic_name'] . '</h3>' .
        '<input type="text" size="20" maxlength="255" name="th" value="' . functions::checkout($th). '"/></p>' .
        '<p><h3>' . $lng_forum['post'] . '</h3>';
    echo '</p><p>' . bbcode::auto_bb('form', 'msg');
    echo '<textarea rows="' . $set_user['field_h'] . '" name="msg">' . functions::checkout($msg) .'</textarea></p>' .
	'<div><h3>Tags (phân cách bằng dấu ","):</h3><input type="text" name="tags" autocomplete="off" value="'. functions::checkout($tags) .'"/></div>'.
        '<p><input type="checkbox" name="addfiles" value="1"/> ' . $lng_forum['add_file'];
    $token = mt_rand(1000, 100000);
    $_SESSION['token'] = $token;
    echo '</p><p><input type="submit" name="submit" value="' . $lng['save'] . '" style="width: 107px; cursor: pointer;"/> ' .
        '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' .
        '<input type="hidden" name="token" value="' . $token . '"/>' .
        '</p></div></form>' .
        '<div class="phdr"><a href="' . SITE_URL . '/faq.php?act=smileys">' . $lng['smileys'] . '</a></div>' .
        '<div class="menu"><a href="'.functions::bodau($res_r['text']).'.' . $id . '.html">' . $lng['back'] . '</a></div>';
}