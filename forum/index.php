<?php
define('_MRKEN_CMS', 1);

require('../incfiles/core.php');
$lng_forum = core::load_lng('forum');
if (isset($_SESSION['ref'])) unset($_SESSION['ref']);

// The list of file extensions allowed for unloading
// Archive
$ext_arch = array('zip','rar','7z','tar','gz','apk');
// Audio
$ext_audio = array('mp3','amr');
// Text
$ext_doc = array('txt','pdf','doc','docx','rtf','djvu','xls','xlsx');
// Java
$ext_java = array('sis','sisx','apk');
// image
$ext_pic = array('jpg','jpeg','png','bmp');
// SIS
$ext_sis = array('sis','sisx');
// video
$ext_video = array('3gp','avi','flv','mpeg','mp4');
// soft Windows
$ext_win = array('exe','msi');
// other
$ext_other = array('wmf');

// check access rights
$error = '';
if (!$set['mod_forum'] && $rights < 7)
    $error = $lng_forum['forum_closed'];
elseif ($set['mod_forum'] == 1 && !$user_id)
    $error = $lng['access_guest_forbidden'];
if ($error) {
    require('../incfiles/head.php');
    echo '<div class="rmenu"><p>' . $error . '</p></div>';
    require('../incfiles/end.php');
    exit;
}

$headmod = $id ? 'forum,' . $id : 'forum';

// get title
if (empty($id) || $act) {
    $textl = $lng['forum'];
} else {
    $req = mysql_query('SELECT `text`, `type`, `id`, `soft` FROM `forum` WHERE `id`= "' . $id . '"');
    $res = mysql_fetch_assoc($req);
    $textl = $res['text'];
	if($res['type'] == 't'){
		if(!empty($res['soft'])) $keyword = functions::show_tags($res['soft']);
		$add = '<link rel="canonical" href="' . SITE_URL . '/forum/' . functions::bodau($res['text']) . '.' . $id . '.html' . ($page != 1 ? '?page=' . $page . '' : '') . '" />'.
            '<base href="' . SITE_URL . '/forum/" />';
	} else {
        $add = '<base href="' . SITE_URL . '/forum/" />';
    }
    $add .= '<script type="text/javascript">
<!--
    var b = document.getElementsByTagName("base")[0], _b = "' . SITE_URL . '/forum/";
    if (typeof b!=\'undefined\' && b!=null && b.href != _b) b.href = _b;
-->
</script>';
}

// Switch modes
$mods = array(
    'addfile',
    'addvote',
    'close',
    'deltema',
    'delvote',
    'editpost',
    'editvote',
    'file',
    'files',
    'filter',
    'loadtem',
    'like',
    'new',
    'nt',
    'per',
    'post',
    'ren',
    'restore',
    'say',
    'tema',
    'users',
    'vip',
    'vote',
    'who',
    'curators'
);
if ($act && ($key = array_search($act, $mods)) !== false && file_exists('includes/' . $mods[$key] . '.php')) {
    require('includes/' . $mods[$key] . '.php');
} else {
	if (isset($_SESSION['prd'])) unset($_SESSION['prd']);
    require('../incfiles/head.php');

    // If the forum is closed, for admins derive reminder
    if (!$set['mod_forum']) echo '<div class="alarm">' . $lng_forum['forum_closed'] . '</div>';
    elseif ($set['mod_forum'] == 3) echo '<div class="rmenu">' . $lng['read_only'] . '</div>';
    if ($id) {
        // Determine the type of request (catalog, or topic)
        $type = mysql_query("SELECT * FROM `forum` WHERE `id`= '$id'");
        if (!mysql_num_rows($type)) {
            // If the topic does not exist, show an error
            echo functions::display_error($lng_forum['error_topic_deleted'], '<a href="index.php">' . $lng['to_forum'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        $type1 = mysql_fetch_assoc($type);

        // Fixing the fact reading Topic
        if ($user_id && $type1['type'] == 't') {
            $req_r = mysql_query("SELECT * FROM `cms_forum_rdm` WHERE `topic_id` = '$id' AND `user_id` = '$user_id' LIMIT 1");
            if (mysql_num_rows($req_r)) {
                $res_r = mysql_fetch_assoc($req_r);
                if ($type1['time'] > $res_r['time'])
                    mysql_query("UPDATE `cms_forum_rdm` SET `time` = '" . time() . "' WHERE `topic_id` = '$id' AND `user_id` = '$user_id' LIMIT 1");
            } else {
                mysql_query("INSERT INTO `cms_forum_rdm` SET `topic_id` = '$id', `user_id` = '$user_id', `time` = '" . time() . "'");
            }
        }

        // The resulting structure Forum
        $res = true;
        $allow = 0;
        $parent = $type1['refid'];
        while ($parent != '0' && $res != false) {
            $req = mysql_query("SELECT `type`,`edit`,`refid`,`text` FROM `forum` WHERE `id` = '$parent' LIMIT 1");
            $res = mysql_fetch_assoc($req);
            if ($res['type'] == 'f' || $res['type'] == 'r') {
                $tree[] = $type1['type'] == 't' ? '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="itemscope"><a itemprop="url" href="'.functions::bodau($res['text']).'.' . $parent . '/"><span itemprop="title">' . htmlspecialchars($res['text']) . '</span></a></span>' : '<a href="'.functions::bodau($res['text']).'.' . $parent . '/">' . htmlspecialchars($res['text']) . '</a>';
                if ($res['type'] == 'r' && !empty($res['edit'])) {
                    $allow = intval($res['edit']);
                }
            }
            $parent = $res['refid'];
        }
        $tree[] = $type1['type'] == 't' ? '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="itemscope"><a itemprop="url" href="index.php"><span itemprop="title">' . $lng['forum'] . '</a></span>' : '<a href="index.php">' . $lng['forum'] . '</a>';
        krsort($tree);
        if ($type1['type'] != 't' && $type1['type'] != 'm')
            $tree[] = '<b>' . htmlspecialchars($type1['text']) . '</b>';

        // Counter files and link to them
        $sql = ($rights == 9) ? "" : " AND `del` != '1'";
        if ($type1['type'] == 'f') {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `cat` = '$id'" . $sql), 0);
            if ($count > 0)
                $filelink = '<a href="index.php?act=files&c=' . $id . '">' . $lng_forum['files_category'] . '</a>';
        } elseif ($type1['type'] == 'r') {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `subcat` = '$id'" . $sql), 0);
            if ($count > 0)
                $filelink = '<a href="index.php?act=files&s=' . $id . '">' . $lng_forum['files_section'] . '</a>';
        } elseif ($type1['type'] == 't') {
            $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `topic` = '$id'" . $sql), 0);
            if ($count > 0)
                $filelink = '<a href="index.php?act=files&t=' . $id . '">' . $lng_forum['files_topic'] . '</a>';
        }
        $filelink = isset($filelink) ? $filelink . ' <span class="red">(' . $count . ')</span>' : false;

        // Counter "Who's the topic?"
        $wholink = false;
        if ($user_id && $type1['type'] == 't') {
            $online_u = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 300) . " AND `place` = 'forum,$id'"), 0);
            $online_g = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 300) . " AND `place` = 'forum,$id'"), 0);
            $wholink = '<a href="index.php?act=who&id=' . $id . '">' . $lng_forum['who_here'] . '?</a> <span class="red">(' . $online_u . '&#160;/&#160;' . $online_g . ')</span>';
        }

        // Output the top navigation bar
        echo '<div class="phdr">' . functions::display_menu($tree, ' » ') . '</div>' .
            '<div class="topmenu"><a href="search.php">' . $lng['search'] . '</a>' . ($filelink ? ' | ' . $filelink : '') . ($wholink ? ' | ' . $wholink : '') . ' | ' . counters::forum_new(1) . '</div>';

        switch ($type1['type']) {
            case 'f':
                ////////////////////////////////////////////////////////////
                // List of sections Forum                                 //
                ////////////////////////////////////////////////////////////

                $req = mysql_query("SELECT `id`, `text`, `soft`, `edit` FROM `forum` WHERE `type`='r' AND `refid`='$id' ORDER BY `realid`");
                $total = mysql_num_rows($req);
                if ($total) {
                    $i = 0;
                    while (($res = mysql_fetch_assoc($req)) !== false) {
                        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                        $coltem = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `refid` = '" . $res['id'] . "'"), 0);
                        echo '<a href="' . SITE_URL . '/forum/' . functions::bodau($res['text']).'.' . $res['id'] . '/">' . htmlspecialchars($res['text']) . '</a>';
                        if ($coltem)
                            echo " [$coltem]";
                        if (!empty($res['soft']))
                            echo '<div class="sub"><span class="gray">' . htmlspecialchars($res['soft']) . '</span></div>';
                        echo '</div>';
                        ++$i;
                    }
                    unset($_SESSION['fsort_id']);
                    unset($_SESSION['fsort_users']);
                } else {
                    echo '<div class="menu"><p>' . $lng_forum['section_list_empty'] . '</p></div>';
                }
                echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
                break;

            case 'r':
                ////////////////////////////////////////////////////////////
                // List of topics                                         //
                ////////////////////////////////////////////////////////////
                $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `refid`='$id'" . ($rights >= 7 ? '' : " AND `close`!='1'")), 0);
                if (($user_id && !isset($ban['1']) && !isset($ban['11']) && $set['mod_forum'] != 4) || $rights) {
                    // Button to create a new theme
                    echo '<div class="gmenu"><form action="index.php?act=nt&amp;id=' . $id . '" method="post"><input type="submit" value="' . $lng_forum['new_topic'] . '" /></form></div>';
                }
                if ($total) {
                    $req = mysql_query("SELECT * FROM `forum` WHERE `type`='t'" . ($rights >= 7 ? '' : " AND `close`!='1'") . " AND `refid`='$id' ORDER BY `vip` DESC, `time` DESC LIMIT $start, $kmess");
                    $i = 0;
                    while (($res = mysql_fetch_assoc($req)) !== false) {
                        if ($res['close'])
                            echo '<div class="rmenu">';
                        else
                            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                        $nikuser = mysql_query("SELECT `from` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "' ORDER BY `time` DESC LIMIT 1");
                        $nam = mysql_fetch_assoc($nikuser);
                        $colmes = mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='m' AND `refid`='" . $res['id'] . "'" . ($rights >= 7 ? '' : " AND `close` != '1'"));
                        $colmes1 = mysql_result($colmes, 0);
                        $cpg = ceil($colmes1 / $kmess);
                        $np = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_rdm` WHERE `time` >= '" . $res['time'] . "' AND `topic_id` = '" . $res['id'] . "' AND `user_id`='$user_id'"), 0);
                        // badges
                        $icons = array(
                            ($np ? (!$res['vip'] ? functions::image('op.gif') : '') : functions::image('np.gif')),
                            ($res['vip'] ? functions::image('pt.gif') : ''),
                            ($res['realid'] ? functions::image('rate.gif') : ''),
                            ($res['edit'] ? functions::image('tz.gif') : '')
                        );
                        echo functions::display_menu($icons, '');
                        echo ($res['prefix'] ? '<span class="label label-'.$res['prefix'].'">'.$prefixs[$res['prefix']].'</span>':'') .'<a href="'.functions::bodau($res['text']).'.' . $res['id'] . '.html">' . htmlspecialchars($res['text']) . '</a> [' . $colmes1 . ']';
                        if ($cpg > 1) {
                            echo '<a href="'.functions::bodau($res['text']).'.' . $res['id'] . '.html?page=' . $cpg . '">&#160;&gt;&gt;</a>';
                        }
                        echo '<div class="sub">';
                        echo $res['from'];
                        if (!empty($nam['from'])) {
                            echo '&#160;/&#160;' . $nam['from'];
                        }
                        echo ' <span class="gray">(' . functions::display_date($res['time']) . ')</span></div></div>';
                        ++$i;
                    }
                    unset($_SESSION['fsort_id']);
                    unset($_SESSION['fsort_users']);
                } else {
                    echo '<div class="menu"><p>' . $lng_forum['topic_list_empty'] . '</p></div>';
                }
                echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
                if ($total > $kmess) {
                    echo '<div class="topmenu">' . functions::display_pagination(''.functions::bodau($type1['text']).'.' . $id . '.html?page=', $start, $total, $kmess) . '</div>' .
                        '<p><form action="'.functions::bodau($type1['text']).'.' . $id . '.html" method="get">' .
                        '<input type="text" name="page" size="2"/>' .
                        '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
                        '</form></p>';
                }
                break;

            case 't':
                ////////////////////////////////////////////////////////////
                // show theme with posts                              //
                ////////////////////////////////////////////////////////////
                $filter = isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id ? 1 : 0;
                $sql = '';
                if ($filter && !empty($_SESSION['fsort_users'])) {
                    // prepare a request for filtering users
                    $sw = 0;
                    $sql = ' AND (';
                    $fsort_users = unserialize($_SESSION['fsort_users']);
                    foreach ($fsort_users as $val) {
                        if ($sw)
                            $sql .= ' OR ';
                        $sortid = intval($val);
                        $sql .= "`forum`.`user_id` = '$sortid'";
                        $sw = 1;
                    }
                    $sql .= ')';
                }

                // If the topic is marked for deletion, allow access only to the administration
                if ($rights < 6 && $type1['close'] == 1) {
					$type2 = mysql_fetch_assoc(mysql_query('SELECT `text` FROM `forum` WHERE `type`="r" AND `id`="'.$type1['refid'].'" LIMIT 1'));
                    echo '<div class="rmenu"><p>' . $lng_forum['topic_deleted'] . '<br/><a href="'.functions::bodau($type2['text']).'.' . $type1['refid'] . '.html">' . $lng_forum['to_section'] . '</a></p></div>';
                    require('../incfiles/end.php');
                    exit;
                }

                // 	Counter post topics
                $colmes = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='m'$sql AND `refid`='$id'" . ($rights >= 7 ? '' : " AND `close` != '1'")), 0);
                if ($start >= $colmes) {
                    // Fixing a request for a non-existent page
                    $start = max(0, $colmes - (($colmes % $kmess) == 0 ? $kmess : ($colmes % $kmess)));
                }
                // Print the name of the topic
                echo '<div class="menu"><h1 class="topic-name">' . ($type1['vip'] ? functions::image('pt.gif') : '') .($type1['prefix'] ? '<span class="label label-'.$type1['prefix'].'">'.$prefixs[$type1['prefix']].'</span>':''). htmlspecialchars($type1['text']) . '</h1></div>';
				$url = functions::bodau($type1['text']);
                if ($colmes > $kmess) {
                    echo '<div class="menu">' . functions::display_pagination(''.$url.'.' . $id . '.html?page=', $start, $colmes, $kmess) . '</div>';
                }

                // Tag removal threads
                if ($type1['close']) {
                    echo '<div class="rmenu">' . $lng_forum['topic_delete_who'] . ': <b>' . $type1['close_who'] . '</b></div>';
                } elseif (!empty($type1['close_who']) && $rights >= 7) {
                    echo '<div class="gmenu"><small>' . $lng_forum['topic_delete_whocancel'] . ': <b>' . $type1['close_who'] . '</b></small></div>';
                }

                // Tag closing theme
                if ($type1['edit']) {
                    echo '<div class="rmenu">' . $lng_forum['topic_closed'] . '</div>';
                }

                // Polls
                if ($type1['realid']) {
                    $vote_user = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `user`='$user_id' AND `topic`='$id'"), 0);
                    $topic_vote = mysql_fetch_assoc(mysql_query("SELECT `name`, `time`, `count` FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id' LIMIT 1"));
                    echo '<div  class="gmenu"><b>' . functions::checkout($topic_vote['name']) . '</b><br />';
                    $vote_result = mysql_query("SELECT `id`, `name`, `count` FROM `cms_forum_vote` WHERE `type`='2' AND `topic`='" . $id . "' ORDER BY `id` ASC");
                    if (!$type1['edit'] && !isset($_GET['vote_result']) && $user_id && $vote_user == 0) {
                        // print the form with polls
                        echo '<form action="index.php?act=vote&id=' . $id . '" method="post">';
                        while (($vote = mysql_fetch_assoc($vote_result)) !== false) {
                            echo '<input type="radio" value="' . $vote['id'] . '" name="vote"/> ' . functions::checkout($vote['name'], 0, 1) . '<br />';
                        }
                        echo '<p><input type="submit" name="submit" value="' . $lng['vote'] . '"/><br /><a href="'.$url.'.' . $id . '.html?start=' . $start . '&vote_result' . '">' . $lng_forum['results'] . '</a></p></form></div>';
                    } else {
                        // Conclusions The results of the voting
                        echo '<small>';
                        while (($vote = mysql_fetch_assoc($vote_result)) !== false) {
                            $count_vote = $topic_vote['count'] ? round(100 / $topic_vote['count'] * $vote['count']) : 0;
                            echo functions::checkout($vote['name'], 0, 1) . ' [' . $vote['count'] . ']<br />';
                            echo '<img src="vote_img.php?img=' . $count_vote . '" alt="' . $lng_forum['rating'] . ': ' . $count_vote . '%" /><br />';
                        }
                        echo '</small></div><div class="bmenu">' . $lng_forum['total_votes'] . ': ';
                        if ($rights > 6)
                            echo '<a href="index.php?act=users&id=' . $id . '">' . $topic_vote['count'] . '</a>';
                        else
                            echo $topic_vote['count'];
                        echo '</div>';
                        if ($user_id && $vote_user == 0)
                            echo '<div class="bmenu"><a href="'.$url.'.' . $id . '.html?start=' . $start . '">' . $lng['vote'] . '</a></div>';
                    }
                }

                // obtain data on the Curators
                $curators = !empty($type1['curators']) ? unserialize($type1['curators']) : array();
                $curator = false;
                if ($rights < 6 && $rights != 3 && $user_id) {
                    if (array_key_exists($user_id, $curators)) $curator = true;
                }

                // Reminder that the filter is enabled
                if ($filter) {
                    echo '<div class="rmenu">' . $lng_forum['filter_on'] . '</div>';
                }

                ////////////////////////////////////////////////////////////
                // The main query to the database, get a list of topics posts    //
                ////////////////////////////////////////////////////////////
                $req = mysql_query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`, `users`.`postforum`
                  FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
                  WHERE `forum`.`type` = 'm' AND `forum`.`refid` = '$id'"
                    . ($rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "$sql
                  ORDER BY `forum`.`id` ASC LIMIT $start, $kmess");

                $i = 1;

                ////////////////////////////////////////////////////////////
                // 	The main list of posts                                 //
                ////////////////////////////////////////////////////////////
                while (($res = mysql_fetch_assoc($req)) !== false) {
                    echo '<div class="forum_post">';
					// head
					echo '<div class="title" id="post'. $res['id'] .'"><table cellpadding="0" cellspacing="0" width="100%"><tr><td>'. functions::display_date($res['time']) .'</td><td align="right"><a href="#post' . $res['id'] . '" title="Link to post">#<b>'.($start + $i).'</b></a></td></tr></table></div>';
                    // author info
                    echo '<div class="topmenu" itemscope="itemscope" itemtype="http://data-vocabulary.org/Person"><table cellpadding="0" cellspacing="0" width="100%"><tr valign="top"><td width="38"><img src="' . functions::get_avatar($res['user_id']) . '" width="32" height="32" alt="' . $res['from'] . '" /></td><td>' .
                    '<div><a href="' . SITE_URL . '/users/profile.php?user=' . $res['user_id'] . '"><b itemprop="name">' . functions::nick_color($res['from'], $res['rights']) . '</b></a> ';

                    // Tag online / offline
                    echo '<img src="' . SITE_URL . '/images/o' . (time() > $res['lastdate'] + 300 ? 'ff' : 'n') . '.gif" alt="*" />';
                    echo '</div><div>Bài đăng: ' . $res['postforum'] . '</div>';

					echo '</td><td align="right">';
					// Tag office
                    if(isset($user_rights[$res['rights']])) echo '<div><b itemprop="title">' . $user_rights[$res['rights']] . '</b></div>';
					if (!empty($res['status'])) {
                        echo '<div class="status">' . htmlspecialchars($res['status']) . '</div>';
                    }
                    // Close the table with Text
                    echo '</td></tr></table></div>';

                    ////////////////////////////////////////////////////////////
                    // Text output post                                     //
                    ////////////////////////////////////////////////////////////
                    $text = $res['text'];
                    $text = functions::checkout($text, 1, 1, 1);
                    echo '<div class="text' . ($res['close'] ? ' bg-error' : '') . '">' . $text . '</div>';

                    // If the post was edited, show who and when
                    if ($res['tedit']) {
                        echo '<div class="info gray p4"><small>' . $lng_forum['edited'] . ' <b>' . $res['edit'] . '</b> (' . functions::display_date($res['tedit']) . ')</small></div>';
                    }

					$menu = array();
                    // If there is an attached file, print it Description
                    $freq = mysql_query("SELECT `id`,`filename`,`dlcount`,`time` FROM `cms_forum_files` WHERE `post` = '" . $res['id'] . "'");
                    if (mysql_num_rows($freq) > 0) {
                        $fres = mysql_fetch_assoc($freq);
                        $fls = round(@filesize('../files/forum/attach/' . $fres['filename']) / 1024, 2);
                        echo '<div class="gray attach"><div class="attach_file">' . $lng_forum['attached_file'] . ':';
                        echo '<br /><a href="index.php?act=file&id=' . $fres['id'] . '">' . $fres['filename'] . '</a>';
                        echo ' (' . $fls . ' KB)<br/>';
                        echo $lng_forum['downloads'] . ': ' . $fres['dlcount'] . ' ' . $lng_forum['time'] . '</div></div>';
                    } elseif($res['user_id'] == $user_id && $rights == 9) {
						$menu[] = '<a href="index.php?act=addfile&id='.$res['id'].'">'. $lng_forum['add_file'] .'</a>';
					}

                    // Links to edit / delete posts
                    $sub_info = '';
                    if (
                        (($rights == 3 || $rights >= 6 || $curator) && $rights >= $res['rights'])
                        || ($res['user_id'] == $user_id && ($start + $i) == $colmes && $res['time'] > time() - 300)
                        || ($i == 1 && $allow == 2 && $res['user_id'] == $user_id)
                    ) {
                        // Service menu post
                        $menu[] = '<a href="index.php?act=editpost&do=del&id=' . $res['id'] . '">&#160;' . $lng['delete'] . '&#160;</a>';
                        $menu[] = '<a href="index.php?act=editpost&id=' . $res['id'] . '">&#160;' . $lng['edit'] . '&#160;</a>';
                        $menu[] = ($rights >= 7 && $res['close'] == 1 ? '<a href="index.php?act=editpost&do=restore&id=' . $res['id'] . '">&#160;' . $lng_forum['restore'] . '&#160;</a>' : '');

                        $sub_info .= '<div class="sub p4">';
                        // Shows who deleted the post
                        if ($res['close']) {
                            $sub_info .= '<div class="red">' . $lng_forum['who_delete_post'] . ': <b>' . $res['close_who'] . '</b></div>';
                        } elseif (!empty($res['close_who'])) {
                            $sub_info .= '<div class="green">' . $lng_forum['who_restore_post'] . ': <b>' . $res['close_who'] . '</b></div>';
                        }

                        // Shows IP and Useragent
                        if ($rights == 3 || $rights >= 6) {
                            if ($res['ip_via_proxy']) {
                                $sub_info .= '<div class="gray"><b class="red"><a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=search_ip&ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a></b> - ' .
                                    '<a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=search_ip&ip=' . long2ip($res['ip_via_proxy']) . '">' . long2ip($res['ip_via_proxy']) . '</a>' .
                                    ' - ' . htmlspecialchars($res['soft']) . '</div>';
                            } else {
                                $sub_info .= '<div class="gray"><a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=search_ip&ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a> - ' . htmlspecialchars($res['soft']) . '</div>';
                            }
                        }
                        $sub_info .='</div>';
                    }
					$chkl = false;
					if($user_id && $user_id != $res['user_id']){
						$menu[] = '<a href="index.php?act=say&id=' . $res['id'] . '&start=' . $start . '">&#160;Quote&#160;</a>';
						$chkl = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_likes` WHERE `type`="1" AND `user_id`="'. $user_id .'" AND `sub_id`="'.$res['id'].'"'), 0);
						$menu[] = '<a href="index.php?act=like&id=' . $res['id'] . '&start=' . $start . '">&#160;'. ($chkl ? 'Unlike' : 'Like') .'&#160;</a>';
					}
                    if(!empty($menu)) echo '<div class="tools right">'. functions::display_menu($menu, ' ') .'</div>';
					/* Show list users like post */
					$clike = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_likes` WHERE `type`="1" AND `sub_id`="'.$res['id'].'"'), 0);
					if($clike){
						$likes = '';
						$alike = array();
						/* You */
						if($chkl) $likes .= 'Bạn';
						if(($clike == 1 && !$chkl) || $clike > 1){
							/* Other User */
							$lreq = mysql_query('SELECT `cms_likes`.`user_id`,`users`.`account` FROM `cms_likes` LEFT JOIN `users` ON `users`.`id` = `cms_likes`.`user_id` WHERE `cms_likes`.`type`="1" AND `cms_likes`.`sub_id`="'.$res['id'].'" AND `cms_likes`.`user_id` != "'. $user_id .'" ORDER BY `cms_likes`.`id` DESC LIMIT 2');
							while($lres = mysql_fetch_assoc($lreq)){
								$alike[] = '<a href="' . SITE_URL . '/users/profile.php?user='.$lres['user_id'].'">'. htmlspecialchars($lres['account']) .'</a>';
							}
							if($clike == 1 || ($clike == 2 && $chkl)){
								$likes .= ($chkl ? ' và ' : '') . implode('', $alike);
							}elseif($clike == 2 || ($clike == 3 && $chkl)){
								$likes .= ($chkl ? ', ' :'') . implode(' và ', $alike);
							}else{
								$likes .= ($chkl ? ', ' : '') .implode(', ', $alike) . ' và <a href="index.php?act=like&id='. $res['id'] .'&likes">' . ($clike - 2 - ($chkl ? 1 : 0)) . ' người khác</a>';
							}
						}
						if(!empty($likes)){
							echo '<div class="likes">'. $likes .' thích điều này</div>';
						}
					}
                    if(!empty($sub_info)) echo $sub_info;
                    echo '</div>';
                    ++$i;
                }

                // The field "Write"
                if (($user_id && !$type1['edit'] && $set['mod_forum'] != 3 && $allow != 4) || ($rights >= 7)) {
                    echo '<div class="gmenu"><form name="form2" action="index.php?act=say&id=' . $id . '" method="post">';
                        $token = mt_rand(1000, 100000);
                        $_SESSION['token'] = $token;
                        echo '<p>';
                        echo bbcode::auto_bb('form2', 'msg');
                        echo '<textarea rows="' . $set_user['field_h'] . '" name="msg"></textarea><br/></p>' .
                            '<p><input type="checkbox" name="addfiles" value="1" /> ' . $lng_forum['add_file'];
                        echo '</p><p><input type="submit" name="submit" value="' . $lng['write'] . '" style="width: 107px; cursor: pointer;"/> ' .
                            '<input type="submit" value="' . $lng['preview'] . '" style="width: 107px; cursor: pointer;"/>' .
                            '<input type="hidden" name="token" value="' . $token . '"/>' .
                            '</p></form></div>';
                }

                echo '<div class="phdr">' . $lng['total'] . ': ' . $colmes . '</div>';
				if(!empty($type1['soft'])){
					echo '<div class="list1">Tags: '. functions::show_tags($type1['soft'], 1).'</div>';
				}

                // pagination
                if ($colmes > $kmess) {
                    echo '<div class="topmenu">' . functions::display_pagination(''.$url.'.' . $id . '.html?page=', $start, $colmes, $kmess) . '</div>' .
                        '<div class="menu"><form action="'.$url.'.' . $id . '.html" method="get">' .
                        '<input type="text" name="page" size="2"/>' .
                        '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
                        '</form></div>';
                }

                // List of curators
                if ($curators) {
                    $array = array();
                    foreach ($curators as $key => $value) {
                        $array[] = '<a href="' . SITE_URL . '/users/profile.php?user=' . $key . '">' . $value . '</a>';
                    }
                    echo '<div class="func">' . $lng_forum['curators'] . ': ' . implode(', ', $array) . '</div>';
                }

                // 	Links to leading management theme
                if ($rights == 3 || $rights >= 6) {
                    echo '<div class="phdr">Công cụ</div><form class="menu" action="' . SITE_URL . '/forum/" method="get"><select name="act">';
                    if ($rights >= 7) echo '<option value="curators">' . $lng_forum['curators_of_the_topic'] . '</option>';
                    echo isset($topic_vote) && $topic_vote > 0 ? '<option value="editvote">' . $lng_forum['edit_vote'] . '</option><option value="delvote">' . $lng_forum['delete_vote'] . '</option>' : '<option value="addvote">' . $lng_forum['add_vote'] . '</option>';
                    echo '<option value="ren">' . $lng_forum['topic_rename'] . '</a><br/>';
                    // Close - open topic
                    echo '<option value="close">' . ($type1['edit'] == 1 ? $lng_forum['topic_open'] : $lng_forum['topic_close']) . '</option>';
                    // Delete - Restore topic
                    if ($type1['close'] == 1)
                        echo '<option value="restore">' . $lng_forum['topic_restore'] . '</a><br/>';
                    echo '<option value="deltema">' . $lng_forum['topic_delete'] . '</option>';
                    echo '<option value="vip">' . ($type1['vip'] == 1 ? $lng_forum['topic_unfix'] : $lng_forum['topic_fix']) . '</a></option>';
                    echo '<option value="per">' . $lng_forum['topic_move'] . '</option></select><input type="hidden" name="id" value="'.$id.'"/><input type="hidden" name="start" value="'.$start.'"/><input type="submit" value="Thực hiện"/></form>';
                }

                // Link to filter posts
                if ($filter) {
                    echo '<div class="menu"><a href="index.php?act=filter&id=' . $id . '&do=unset">' . $lng_forum['filter_cancel'] . '</a></div>';
                } else {
                    echo '<div class="menu"><a href="index.php?act=filter&id=' . $id . '&start=' . $start . '">' . $lng_forum['filter_on_author'] . '</a></div>';
                }

                // Link to jump topics
                echo '<div class="menu"><a href="index.php?act=tema&id=' . $id . '">' . $lng_forum['download_topic'] . '</a></div>';
				// similar topic
				$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `forum`
					WHERE MATCH (`text`) AGAINST ("'. mysql_real_escape_string($type1['text']) .'" IN BOOLEAN MODE)
					AND `type` = "t" AND `id`!= "'.$id.'"' . ($rights >= 7 ? '' : ' AND `close` != "1"').''), 0);
				if($total){
					$req = mysql_query('SELECT `id`,`text`, MATCH (`text`) AGAINST ("' . mysql_real_escape_string($type1['text']) . '" IN BOOLEAN MODE) as `rel`
						FROM `forum`
						WHERE MATCH (`text`) AGAINST ("'. mysql_real_escape_string($type1['text']) .'" IN BOOLEAN MODE)
						AND `type` = "t" AND `id`!= "'.$id.'"
						ORDER BY `rel` DESC
						LIMIT 5
					');
					echo '<div class="phdr">Chủ đề tương tự</div>';
					while(($res = mysql_fetch_assoc($req)) !== false){
						echo '<div class="list'.($i%2 + 1).'"><a href="' . functions::bodau($res['text']) . '.' . $res['id'] . '.html">' . htmlspecialchars($res['text']) . '</a></div>';
						$i++;
					}
					
				}
                break;

            default:
                // If incorrect data show an error
                header('Location: index.php');
				exit;
                break;
        }
    } else {
        ////////////////////////////////////////////////////////////
        // List Categories Forums                                //
        ////////////////////////////////////////////////////////////
        $count = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_forum_files`' . ($rights >= 7 ? '' : ' WHERE `del` != "1"')), 0);
        echo '<div class="box"><div class="phdr"><b>' . $lng['forum'] . '</b></div>' .
            '<div class="topmenu"><a href="search.php">' . $lng['search'] . '</a> | <a href="index.php?act=files">' . $lng_forum['files_forum'] . '</a> <span class="red">(' . $count . ')</span> | ' . counters::forum_new(1) . '</div></div>';
        $total = mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type` = "f"'), 0);
        if ($total) {
            $req = mysql_query('SELECT `id`, `text`, `soft` FROM `forum` WHERE `type`="f" ORDER BY `realid`');
            while ($res = mysql_fetch_array($req)) {
                $count = mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type`="r" and `refid`="' . $res['id'] . '"'), 0);
                echo '<div class="box"><div class="phdr">';
                echo '<h3 class="cat-name"><a href="' . SITE_URL . '/forum/' . functions::bodau($res['text']) . '.' . $res['id'] . '/">' . htmlspecialchars($res['text']) . '</a></h3>' . (empty($res['soft']) ? '' : '<div class="sub">' . htmlspecialchars($res['soft']) . '</div>') . '</div>';
                if ($count) {
                    $req2 = mysql_query('SELECT `id`, `text`, `soft` FROM `forum` WHERE `type`="r" AND `refid` = "' . $res['id'] . '" ORDER BY `realid`');
                    while ($res2 = mysql_fetch_assoc($req2)) {
                        echo '<div class="menu"><a href="' . SITE_URL . '/forum/' . functions::bodau($res2['text']).'.' . $res2['id'] . '/">' . htmlspecialchars($res2['text']) . '</a>' . (empty($res2['soft']) ? '' : '<div class="sub">' . htmlspecialchars($res2['soft']) . '</div>') . '</div>';
                    }
                } else {
                    echo '<div class="rmenu">Chưa có diễn đàn nào trong chuyên mục này!</div>';
                }
                echo '</div>';
            }
        } else {
            echo '<div class="rmenu">Diễn đàn chưa có chuyên mục nào! Nếu bạn là quản trị viên, vui lòng tạo chuyên mục diễn đàn trước.</div>';
        }
        $online_u = mysql_result(mysql_query('SELECT COUNT(*) FROM `users` WHERE `lastdate` > ' . (time() - 300) . ' AND `place` LIKE "forum%"'), 0);
        $online_g = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > ' . (time() - 300) . ' AND `place` LIKE "forum%"'), 0);
        echo '<div class="phdr">' . ($user_id ? '<a href="index.php?act=who">' . $lng_forum['who_in_forum'] . '</a>' : $lng_forum['who_in_forum']) . ' (' . $online_u . ' / ' . $online_g . ')</div>';
        unset($_SESSION['fsort_id']);
        unset($_SESSION['fsort_users']);
    }
}

require_once('../incfiles/end.php');