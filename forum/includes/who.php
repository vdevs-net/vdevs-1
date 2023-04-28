<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$textl = $lng_forum['who_in_forum'];
$headmod = $id ? 'forum,' . $id : 'forumwho';
require_once('../incfiles/head.php');
if (!$user_id) {
    header('Location: index.php');
    exit;
}

if ($id) {
    /// show a general list of those who are chosen topic
    $req = mysql_query("SELECT `text` FROM `forum` WHERE `id` = '$id' AND `type` = 't'");
    if (mysql_num_rows($req)) {
        $res = mysql_fetch_assoc($req);
        echo '<div class="phdr"><b>' . $lng_forum['who_in_topic'] . ':</b> <a href="'.functions::bodau($res['text']).'.' . $id . '.html">' . htmlspecialchars($res['text']) . '</a></div>';
        if ($rights > 0){
            echo'<div class="topmenu">' .
                ($do == 'guest' ? '<a href="index.php?act=who&id=' . $id . '">' . $lng['authorized'] . '</a> | ' . $lng['guests'] : $lng['authorized'] . ' | <a href="index.php?act=who&do=guest&id=' . $id . '">' . $lng['guests'] . '</a>') .
                '</div>';
        }
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `" . ($do == 'guest' ? 'cms_sessions' : 'users') . "` WHERE `lastdate` > " . (time() - 300) . " AND `place` = 'forum,$id'"), 0);
        if ($start >= $total) {
            // Fixing a request for a non-existent page
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        }
        if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?act=who&id=' . $id . '&' . ($do == 'guest' ? 'do=guest&' : '').'page=', $start, $total, $kmess) . '</div>';
        if ($total) {
            $req = mysql_query("SELECT * FROM `" . ($do == 'guest' ? 'cms_sessions' : 'users') . "` WHERE `lastdate` > " . (time() - 300) . " AND `place` = 'forum,$id' ORDER BY " . ($do == 'guest' ? "`movings` DESC" : "`account` ASC") . " LIMIT $start, $kmess");
            for($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
                if($do == 'guest'){$res['id'] = 0;}
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                $set_user['avatar'] = 0;
                echo functions::display_user($res, array());
                echo '</div>';
            }
        } else {
            echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
        }
    } else {
        header('Location: index.php'); exit;
    }
    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
    if ($total > $kmess) {
        echo '<div class="topmenu">' . functions::display_pagination('index.php?act=who&id=' . $id . '&' . ($do == 'guest' ? 'do=guest&' : '').'page=', $start, $total, $kmess) . '</div>';
    }
} else {
    // show a general list of those who are online now
    echo '<div class="phdr"><a href="index.php"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['who_in_forum'] . '</div>';
    if ($rights > 0)
        echo '<div class="topmenu">' . ($do == 'guest' ? '<a href="index.php?act=who">' . $lng['users'] . '</a> | <b>' . $lng['guests'] . '</b>'
                : '<b>' . $lng['users'] . '</b> | <a href="index.php?act=who&do=guest">' . $lng['guests'] . '</a>') . '</div>';
    $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `" . ($do == 'guest' ? "cms_sessions" : "users") . "` WHERE `lastdate` > " . (time() - 300) . " AND `place` LIKE 'forum%'"), 0);
    if ($start >= $total) {
        // Fixing a request for a non-existent page
        $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
    }
    if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?act=who&' . ($do == 'guest' ? 'do=guest&' : '').'page=', $start, $total, $kmess) . '</div>';
    if ($total) {
        $req = mysql_query("SELECT * FROM `" . ($do == 'guest' ? "cms_sessions" : "users") . "` WHERE `lastdate` > " . (time() - 300) . " AND `place` LIKE 'forum%' ORDER BY " . ($do == 'guest' ? "`movings` DESC" : "`account` ASC") . " LIMIT $start, $kmess");
        for($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
			if(empty($res['id'])) $res['id'] = 0;
            if ($res['id'] == $user_id) echo '<div class="gmenu">';
            else echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            // process location
            $place = '';
            switch ($res['place']) {
                case 'forum':
                    $place = '<a href="index.php">' . $lng_forum['place_main'] . '</a>';
                    break;

                case 'forumwho':
                    $place = $lng_forum['place_list'];
                    break;

                case 'forumfiles':
                    $place = '<a href="index.php?act=files">' . $lng_forum['place_files'] . '</a>';
                    break;

                case 'forumnew':
                    $place = '<a href="index.php?act=new">' . $lng_forum['place_new'] . '</a>';
                    break;

                case 'forumsearch':
                    $place = '<a href="search.php">' . $lng_forum['place_search'] . '</a>';
                    break;

                default:
                    $where = explode(",", $res['place']);
                    if ($where[0] == 'forum' && intval($where[1])) {
                        $req_t = mysql_query("SELECT `type`, `refid`, `text` FROM `forum` WHERE `id` = '$where[1]'");
                        if (mysql_num_rows($req_t)) {
                            $res_t = mysql_fetch_assoc($req_t);
                            $link = '<a href="'.functions::bodau($res_t['text']).'.' . $where[1] . '.html">' . htmlspecialchars($res_t['text']) . '</a>';
                            switch ($res_t['type']) {
                                case 'f':
                                    $place = $lng_forum['place_category'] . ' &quot;' . $link . '&quot;';
                                    break;

                                case 'r':
                                    $place = $lng_forum['place_section'] . ' &quot;' . $link . '&quot;';
                                    break;

                                case 't':
                                    $place = (isset($where[2]) ? $lng_forum['place_write'] . ' &quot;' : $lng_forum['place_topic'] . ' &quot;') . $link . '&quot;';
                                    break;

                                case 'm':
                                    $req_m = mysql_query("SELECT `text` FROM `forum` WHERE `id` = '" . $res_t['refid'] . "' AND `type` = 't'");
                                    if (mysql_num_rows($req_m)) {
                                        $res_m = mysql_fetch_assoc($req_m);
                                        $place = (isset($where[2]) ? $lng_forum['place_answer'] : $lng_forum['place_topic']) . ' &quot;<a href="'.functions::bodau($res_m['text']).'.' . $res_t['refid'] . '.html">' . htmlspecialchars($res_m['text']) . '</a>&quot;';
                                    }
                                    break;
                            }
                        }
                    }
            }
            $arg = array(
                'stshide' => 1,
                'header' => ('<br /><img src="' . SITE_URL . '/images/info.png" width="16" height="16" align="middle" />&#160;' . $place)
            );
            echo functions::display_user($res, $arg);
            echo '</div>';
        }
    } else {
        echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
    }
    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
    if ($total > $kmess) {
        echo '<div class="topmenu">' . functions::display_pagination('index.php?act=who&' . ($do == 'guest' ? 'do=guest&' : '').'page=', $start, $total, $kmess) . '</div>' .
             '<p><form action="index.php?act=who' . ($do == 'guest' ? '&amp;do=guest' : '') . '" method="post">' .
             '<input type="text" name="page" size="2"/>' .
             '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
             '</form></p>';
    }
}
?>