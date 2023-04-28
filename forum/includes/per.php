<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
if ($rights == 3 || $rights >= 6) {
    if ($id) {
        if (mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `id` = "' . $id . '" AND `type` = "t"'), 0)) {
            $ms = mysql_fetch_assoc(mysql_query('SELECT `refid`, `text` FROM `forum` WHERE `id` = "' . $id . '" AND `type` = "t"'));
            if (isset($_POST['submit'])) {
                $razd = isset($_POST['razd']) ? abs(intval($_POST['razd'])) : false;
                if (!$razd) {
                    require('../incfiles/head.php');
                    echo functions::display_error($lng['error_wrong_data']);
                    require('../incfiles/end.php');
                    exit;
                }
                $typ1 = mysql_query('SELECT `refid` FROM `forum` WHERE `id` = "' . $razd . '" AND `type` = "r" LIMIT 1');
                if (!mysql_num_rows($typ1)) {
                    require('../incfiles/head.php');
                    echo functions::display_error($lng['error_wrong_data']);
                    require('../incfiles/end.php');
                    exit;
                }
                mysql_query('UPDATE `forum` SET
                    `refid` = "' . $razd . '"
                    WHERE `id` = "' . $id . '"
                ');
                if (mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_forum_files` WHERE `topic` = "' . $id . '"'), 0)) {
                    mysql_query('UPDATE `cms_forum_files` SET `cat` = "' . mysql_result($typ1, 0) . '", `subcat` = "' . $razd . '" WHERE `topic` = "' . $id . '"');
                }
                header('Location: ' . functions::bodau($ms['text']) . '.' . $id . '.html'); exit;
            } else {
                // Moving threads
                require('../incfiles/head.php');
                if (empty($_GET['other'])) {
                    $other = mysql_result(mysql_query('SELECT `refid` FROM `forum` WHERE `id` = "' . $ms['refid'] . '" AND `type` = "r" LIMIT 1'), 0);
                } else {
                    $other = abs(intval($_GET['other']));
                }
                $fr = mysql_result(mysql_query('SELECT `text` FROM `forum` WHERE `id` = "' . $other . '" AND `type` = "f" LIMIT 1'), 0);
                echo '<div class="phdr"><a href="' . functions::bodau($ms['text']) . '.' . $id . '.html"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['topic_move'] . '</div>' .
                    '<form action="index.php?act=per&id=' . $id . '" method="post">' .
                    '<div class="gmenu"><p>' .
                    '<h3>' . $lng['category'] . '</h3>' . htmlspecialchars($fr) . '</p>' .
                    '<p><h3>' . $lng['section'] . '</h3>' .
                    '<select name="razd">';
                $raz = mysql_query('SELECT `id`, `text` FROM `forum` WHERE `refid` = "' . $other . '" AND `type` = "r" AND `id` != "' . $ms['refid'] . '" ORDER BY `realid` ASC');
                while ($raz1 = mysql_fetch_assoc($raz)) {
                    echo '<option value="' . $raz1['id'] . '">' . htmlspecialchars($raz1['text']) . '</option>';
                }
                echo '</select></p>' .
                    '<p><input type="submit" name="submit" value="' . $lng['move'] . '"/></p>' .
                    '</div></form>' .
                    '<div class="phdr">' . $lng_forum['other_categories'] . '</div>';
                $frm = mysql_query('SELECT `id`, `text` FROM `forum` WHERE `type` = "f" AND `id` != "' . $other . '" ORDER BY `realid` ASC');
                $i=0;
                while ($frm1 = mysql_fetch_assoc($frm)) {
                    echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    echo '<a href="index.php?act=per&id=' . $id . '&other=' . $frm1['id'] . '">' . htmlspecialchars($frm1['text']) . '</a></div>';
                    ++$i;
                }
            }
        } else {
            require('../incfiles/head.php');
            echo functions::display_error($lng['error_wrong_data']);
        }
    } else {
		require('../incfiles/head.php');
		echo functions::display_error($lng['error_wrong_data']);
    }
}