<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$headmod = 'forumfiles';
require('../incfiles/head.php');

$types = array(
    1 => $lng_forum['files_type_win'],
    2 => $lng_forum['files_type_java'],
    3 => $lng_forum['files_type_sis'],
    4 => $lng_forum['files_type_txt'],
    5 => $lng_forum['files_type_pic'],
    6 => $lng_forum['files_type_arc'],
    7 => $lng_forum['files_type_video'],
    8 => $lng_forum['files_type_audio'],
    9 => $lng_forum['files_type_other']
);
$new = time() - 86400; // How much time to consider the new files?

// Get the ID section and prepare request
$c = isset($_GET['c']) ? abs(intval($_GET['c'])) : false; // ID section
$s = isset($_GET['s']) ? abs(intval($_GET['s'])) : false; // ID subsection
$t = isset($_GET['t']) ? abs(intval($_GET['t'])) : false; // ID topic
$do = isset($_GET['do']) && intval($_GET['do']) > 0 && intval($_GET['do']) < 10 ? intval($_GET['do']) : 0;
if ($c) {
    $id = $c;
    $lnk = '&c=' . $c;
    $sql = " AND `cat` = '" . $c . "'";
    $caption = '<b>' . $lng_forum['files_category'] . '</b>: ';
    $input = '<input type="hidden" name="c" value="' . $c . '"/>';
} elseif ($s) {
    $id = $s;
    $lnk = '&s=' . $s;
    $sql = " AND `subcat` = '" . $s . "'";
    $caption = '<b>' . $lng_forum['files_section'] . '</b>: ';
    $input = '<input type="hidden" name="s" value="' . $s . '"/>';
} elseif ($t) {
    $id = $t;
    $lnk = '&t=' . $t;
    $sql = " AND `topic` = '" . $t . "'";
    $caption = '<b>' . $lng_forum['files_topic'] . '</b>: ';
    $input = '<input type="hidden" name="t" value="' . $t . '"/>';
} else {
    $id = false;
    $sql = '';
    $lnk = '';
    $caption = '<b>' . $lng_forum['files_forum'] . '</b>';
    $input = '';
}
if ($c || $s || $t) {
    // Get the name of the desired category Forum
    $req = mysql_query("SELECT `text` FROM `forum` WHERE `id` = '$id'");
    if (mysql_num_rows($req) > 0) {
        $res = mysql_fetch_array($req);
        $caption .= htmlspecialchars($res['text']);
		$name = $res['text'];
    } else {
        echo functions::display_error($lng['error_wrong_data'], '<a href="index.php">' . $lng['to_forum'] . '</a>');
        require('../incfiles/end.php');
        exit;
    }
}
if ($do || isset($_GET['new'])) {
    // Displays a list of files desired section
    $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE " . (isset($_GET['new'])
                                              ? " `time` > '$new'" : " `filetype` = '$do'") . $sql), 0);
    if ($total > 0) {
        // section title
        echo '<div class="phdr">' . $caption . (isset($_GET['new']) ? '<br />' . $lng['new_files']
                : '') . '</div>' . ($do ? '<div class="bmenu">' . $types[$do] . '</div>' : '');
        $req = mysql_query("SELECT `cms_forum_files`.*, `forum`.`user_id`, `forum`.`text`, `topicname`.`text` AS `topicname`
            FROM `cms_forum_files`
            LEFT JOIN `forum` ON `cms_forum_files`.`post` = `forum`.`id`
            LEFT JOIN `forum` AS `topicname` ON `cms_forum_files`.`topic` = `topicname`.`id`
            WHERE " . (isset($_GET['new']) ? " `cms_forum_files`.`time` > '$new'" : " `filetype` = '$do'") . ($rights >= 7 ? '' : " AND `del` != '1'") . $sql .
            "ORDER BY `time` DESC LIMIT $start,$kmess");
        for($i = 0; $res = mysql_fetch_assoc($req); ++$i){
            $req_u = mysql_query("SELECT `id`, `account`, `sex`, `rights`, `lastdate`, `status`, `datereg`, `ip`, `browser` FROM `users` WHERE `id` = '" . $res['user_id'] . "'");
            $res_u = mysql_fetch_assoc($req_u);
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            // Text output post
            $text = mb_substr($res['text'], 0, 500);
            $text = functions::checkout($text, 1, 0);
            $text = preg_replace('#\[quote[^\]]*\](.*?)\[/quote\]#si', '', $text);
            $page = ceil(mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['topic'] . "' AND `id`<='" . $res['post'] . "'"), 0) / $kmess);
            $text = '<b><a href="'.functions::bodau($res['topicname']).'.' . $res['topic'] . '.html?page=' . $page . '">' . htmlspecialchars($res['topicname']) . '</a></b><br />' . $text;
            if (mb_strlen($res['text']) > 500)
                $text .= '<br /><a href="index.php?act=post&amp;id=' . $res['post'] . '">' . $lng_forum['read_all'] . ' &gt;&gt;</a>';
            // Form a link to a file
            $fls = @filesize('../files/forum/attach/' . $res['filename']);
            $fls = round($fls / 1024, 0);
            $att_ext = strtolower(functions::format('./files/forum/attach/' . $res['filename']));
            $pic_ext = array(
                'jpg',
                'jpeg',
                'png'
            );
            if (in_array($att_ext, $pic_ext)) {
                // If the picture is, the output preview
                $file = '<div><a href="index.php?act=file&amp;id=' . $res['id'] . '">';
                $file .= '<img src="thumbinal.php?file=' . (urlencode($res['filename'])) . '" alt="' . $lng_forum['click_to_view'] . '" /></a></div>';
            } else {
                // If a regular file, and displays an icon link
                $file = ($res['del'] ? '<img src="' . SITE_URL . '/images/del.png" width="16" height="16" />'
                        : '') . '<img src="' . SITE_URL . '/images/system/' . $res['filetype'] . '.png" width="16" height="16" />&#160;';
            }
            $file .= '<a href="index.php?act=file&amp;id=' . $res['id'] . '">' . htmlspecialchars($res['filename']) . '</a><br />';
            $file .= '<small><span class="gray">' . $lng_forum['size'] . ': ' . $fls . ' kb.<br />' . $lng_forum['downloaded'] . ': ' . $res['dlcount'] . ' ' . $lng_forum['time'] . '</span></small>';
            $arg = array(
                'iphide' => 1,
                'sub' => $file,
                'body' => $text
            );
            echo functions::display_user($res_u, $arg);
            echo '</div>';
        }
        echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        if ($total > $kmess) {
            // pagination
            echo '<p>' . functions::display_pagination('index.php?act=files&' . (isset($_GET['new']) ? 'new'
                                       : 'do=' . $do) . $lnk . '&page=', $start, $total, $kmess) . '</p>' .
                 '<p><form action="index.php" method="get">' .
                 '<input type="hidden" name="act" value="files"/>' .
                 '<input type="hidden" name="do" value="' . $do . '"/>' . $input . '<input type="text" name="page" size="2"/>' .
                 '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
        }
    } else {
        echo '<div class="list1">' . $lng['list_empty'] . '</div>';
    }
} else {
    // Displays a list of topics that have files
    $countnew = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `time` > '$new'" . ($rights >= 7
                                                 ? '' : " AND `del` != '1'") . $sql), 0);
    echo '<div class="phdr">' . $caption . '</div>';
	if($countnew > 0){
		echo '<div class="topmenu"><a href="index.php?act=files&new' . $lnk . '">' . $lng['new_files'] . ' (' . $countnew . ')</a></div>';
	}else{
		echo '<div class="rmenu">'.$lng_forum['new_files_empty'].'</div>';
	}
    $link = array();
    $total = 0;
    for ($i = 1; $i < 10; $i++) {
        $count = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `filetype` = '$i'" . ($rights >= 7
                                                  ? '' : " AND `del` != '1'") . $sql), 0);
        if ($count > 0) {
            $link[] = '<img src="' . SITE_URL . '/images/system/' . $i . '.png" width="16" height="16" class="left" />&#160;<a href="index.php?act=files&amp;do=' . $i . $lnk . '">' . $types[$i] . '</a>&#160;(' . $count . ')';
            $total = $total + $count;
        }
    }
    foreach ($link as $var) {
        echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') . $var . '</div>';
        ++$i;
    }
    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
}
echo '' . (($do || isset($_GET['new'])) ? '<div class="menu"><a href="index.php?act=files' . $lnk . '">' . $lng_forum['section_list'] . '</a></div>' : '') . '<div class="menu"><a href="' . ($id ? functions::bodau($name) .  '.' . $id .'.html' : 'index.php') . '">' . $lng['forum'] . '</a></div>';
?>