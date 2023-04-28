<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
if ($rights == 3 || $rights >= 6) {
	$topic = mysql_query("SELECT `text` FROM `forum` WHERE `type`='t' AND `id`='$id' AND `edit` != '1' LIMIT 1");
    $topic_vote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id'"), 0);
    require('../incfiles/head.php');
    if (!$topic_vote|| !mysql_num_rows($topic)) {
        echo functions::display_error($lng['error_wrong_data']);
        require('../incfiles/end.php');
        exit;
    }
	$res = mysql_fetch_assoc($topic);
	$vote_name = isset($_POST['name_vote']) ? mb_substr(trim($_POST['name_vote']), 0, 255) : '';
	$vote_count = isset($_POST['count_vote']) ? abs(intval($_POST['count_vote'])) : 2;
	
    if (isset($_GET['delvote']) && !empty($_GET['vote'])) {
        $vote = abs(intval($_GET['vote']));
        $totalvote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type` = '2' AND `id` = '$vote' AND `topic` = '$id'"), 0);
        $countvote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type` = '2' AND `topic` = '$id'"), 0);
        if ($countvote <= 2) {
            header('location: ?act=editvote&id=' . $id . '');
            exit;
        }
        if ($totalvote != 0) {
            if (isset($_GET['yes'])) {
                mysql_query("DELETE FROM `cms_forum_vote` WHERE `id` = '$vote'");
                $countus = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `vote` = '$vote' AND `topic` = '$id'"), 0);
                $topic_vote = mysql_fetch_array(mysql_query("SELECT `count` FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id' LIMIT 1"));
                $totalcount = $topic_vote['count'] - $countus;
                mysql_query("UPDATE `cms_forum_vote` SET  `count` = '$totalcount'   WHERE `type` = '1' AND `topic` = '$id'");
                mysql_query("DELETE FROM `cms_forum_vote_users` WHERE `vote` = '$vote'");
                header('location: ?act=editvote&id=' . $id . '');
                exit;
            } else {
                echo '<div class="rmenu"><p>' . $lng_forum['voting_variant_warning'] . '<br />' .
                    '<a href="index.php?act=editvote&id=' . $id . '&vote=' . $vote . '&delvote&yes">' . $lng['delete'] . '</a><br />' .
                    '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . $lng['cancel'] . '</a></p></div>';
            }
        } else {
            header('location: ?act=editvote&id=' . $id . '');
            exit;
        }
    } else if (isset($_POST['submit'])) {
        if (!empty($vote_name))
            mysql_query("UPDATE `cms_forum_vote` SET  `name` = '" . mysql_real_escape_string($vote_name) . "'  WHERE `topic` = '$id' AND `type` = '1'");
        $vote_result = mysql_query("SELECT `id` FROM `cms_forum_vote` WHERE `type`='2' AND `topic`='" . $id . "'");
        while ($vote = mysql_fetch_array($vote_result)) {
            if (!empty($_POST[$vote['id'] . 'vote'])) {
                $text = mb_substr(trim($_POST[$vote['id'] . 'vote']), 0, 100);
                mysql_query("UPDATE `cms_forum_vote` SET  `name` = '" . mysql_real_escape_string($text) . "'  WHERE `id` = '" . $vote['id'] . "'");
            }
        }
        $countvote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='2' AND `topic`='" . $id . "'"), 0);
        for ($vote = $countvote; $vote < 10; $vote++) {
            if (!empty($_POST[$vote])) {
                $text = mb_substr(trim($_POST[$vote]), 0, 100);
                mysql_query("INSERT INTO `cms_forum_vote` SET `name` = '" . mysql_real_escape_string($text) . "',  `type` = '2', `topic` = '$id'");
            }
        }
        echo '<div class="gmenu"><p>' . $lng_forum['voting_changed'] . '<br /><a href="'.functions::bodau($res['text']).'.' . $id . '.html">' . $lng['continue'] . '</a></p></div>';
    } else {
		for($i=0; $i <= $vote_count; $i++){
			if(!isset($_POST[$i])) $_POST[$i] = '';
		}
        // Editing form survey
        $countvote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type` = '2' AND `topic` = '$id'"), 0);
        $topic_vote = mysql_fetch_array(mysql_query("SELECT `name` FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id' LIMIT 1"));
        echo '<div class="phdr"><a href="'.functions::bodau($res['text']).'.' . $id . '.html"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['edit_vote'] . '</div>' .
            '<form action="index.php?act=editvote&id=' . $id . '" method="post">' .
            '<div class="gmenu"><p>' .
            '<b>' . $lng_forum['voting'] . ':</b><br/>' .
            '<input type="text" size="20" maxlength="150" name="name_vote" value="' . htmlspecialchars($topic_vote['name']) . '"/>' .
            '</p></div>' .
            '<div class="menu"><p>';
        $vote_result = mysql_query("SELECT `id`, `name` FROM `cms_forum_vote` WHERE `type` = '2' AND `topic` = '$id'");
		$i=0;
        while ($vote = mysql_fetch_array($vote_result)) {
            echo $lng_forum['answer'] . ' ' . ($i + 1) . ' (max. 100): <br/>' .
                '<input type="text" name="' . $vote['id'] . 'vote" value="' . htmlspecialchars($vote['name']) . '"/>';
            if ($countvote > 2)
                echo '&nbsp;<a href="index.php?act=editvote&id=' . $id . '&vote=' . $vote['id'] . '&delvote">[x]</a>';
            echo '<br/>';
            ++$i;
        }
        if ($countvote < 10) {
            if (isset($_POST['plus']))
                ++$vote_count;
            elseif (isset($_POST['minus']))
                --$vote_count;
            if (empty($_POST['count_vote']))
                $vote_count = $countvote;
            elseif ($vote_count > 10)
                $vote_count = 10;
            for ($vote = $i; $vote < $vote_count; $vote++) {
                echo $lng_forum['answer'] . ' ' . ($vote + 1) . ' (max. 100): <br/><input type="text" name="' . $vote . '" value="' . htmlspecialchars($_POST[$vote]) . '"/><br/>';
            }
            echo '<input type="hidden" name="count_vote" value="' . $vote_count . '"/>' . ($vote_count < 10 ? '<input type="submit" name="plus" value="' . $lng['add'] . '"/>' : '')
                . ($vote_count - $countvote ? '<input type="submit" name="minus" value="' . $lng_forum['delete_last'] . '"/>' : '');
        }
        echo '</p></div><div class="gmenu">' .
            '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p>' .
            '</div></form>' .
            '<div class="phdr"><a href="'.functions::bodau($res['text']).'.' . $id . '.html">' . $lng['cancel'] . '</a></div>';
    }
}else{
	header('Location: ' . SITE_URL . '/?err');
    exit;
}