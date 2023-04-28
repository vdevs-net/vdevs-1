<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
if ($rights == 3 || $rights >= 6) {
    if (!$id) {
        require('../incfiles/head.php');
        echo functions::display_error($lng['error_wrong_data']);
        require('../incfiles/end.php');
        exit;
    }
    $typ = mysql_query("SELECT `type`,`text`,`refid`,`soft` FROM `forum` WHERE `id` = '$id'");
    $ms = mysql_fetch_assoc($typ);
    if ($ms['type'] != "t") {
        require('../incfiles/head.php');
        echo functions::display_error($lng['error_wrong_data']);
        require('../incfiles/end.php');
        exit;
    }
    if (isset($_POST['submit'])) {
        $nn = isset($_POST['nn']) ? functions::checkin(mb_substr(trim($_POST['nn']), 0, 255)) : '';
        $tags = isset($_POST['tags']) ? functions::forum_tags($_POST['tags']) : '';
        if (empty($nn)) {
            require('../incfiles/head.php');
            echo functions::display_error($lng_forum['error_topic_name'], '<a href="index.php?act=ren&id=' . $id . '">' . $lng['repeat'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        // Check whether there is a theme with the same name?
        $pt = mysql_query("SELECT * FROM `forum` WHERE `type` = 't' AND `refid` = '" . $ms['refid'] . "' and text='" . mysql_real_escape_string($nn) . "' AND `id` != '$id' LIMIT 1");
        if (mysql_num_rows($pt) != 0) {
            require('../incfiles/head.php');
            echo functions::display_error($lng_forum['error_topic_exists'], '<a href="index.php?act=ren&id=' . $id . '">' . $lng['repeat'] . '</a>');
            require('../incfiles/end.php');
            exit;
        }
        mysql_query('UPDATE `forum` SET `text`="'. mysql_real_escape_string($nn) .'",`soft`="'. mysql_real_escape_string($tags) .'" WHERE `id`="' . $id . '"');
        header('Location: ' . functions::bodau($ms['text']) . '.' . $id . '.html'); exit;
        exit;
    } else {
        // Rename topic
		$tag = '';
		$tags = array();
		if(!empty($ms['soft'])) $tags = unserialize($ms['soft']);
		if(count($tags)) $tag = implode(', ', $tags);
        require('../incfiles/head.php');
        echo '<div class="phdr"><a href="'.functions::bodau($ms['text']).'.'.$id.'.html"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['topic_rename'] . '</div>' .
            '<div class="menu"><form action="index.php?act=ren&id=' . $id . '" method="post">' .
            '<p><h3>' . $lng_forum['topic_name'] . '</h3>' .
            '<input type="text" name="nn" value="' . htmlspecialchars($ms['text']) . '" autocomplete="off"/></p>' .
            '<p><h3>Tags</h3>' .
            '<input type="text" name="tags" value="' . htmlspecialchars($tag) . '" autocomplete="off"/></p>' .
            '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p>' .
            '</form></div>' .
            '<div class="phdr"><a href="'.functions::bodau($ms['text']).'.'.$id.'.html">' . $lng['back'] . '</a></div>';
    }
} else {
    require('../incfiles/head.php');
    echo functions::display_error($lng['access_forbidden']);
}
?>
