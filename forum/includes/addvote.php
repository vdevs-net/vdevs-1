<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
if ($rights == 3 || $rights >= 6) {
    $topic = mysql_query("SELECT `text` FROM `forum` WHERE `type`='t' AND `id`='$id' AND `edit` != '1' LIMIT 1");
    $topic_vote = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id'"), 0);
    require_once('../incfiles/head.php');
    if ($topic_vote || !mysql_num_rows($topic)) {
        echo functions::display_error($lng['error_wrong_data'], '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . $lng['back'] . '</a>');
        require('../incfiles/end.php');
        exit;
    }
	$res = mysql_fetch_assoc($topic);
    $vote_name = isset($_POST['name_vote']) ? mb_substr(trim($_POST['name_vote']), 0, 255) : '';
	$vote_count = isset($_POST['count_vote']) ? abs(intval($_POST['count_vote'])) : 2;
	for($i=0; $i <= $vote_count; $i++){
		if(!isset($_POST[$i])) $_POST[$i] = '';
	}
    if (isset($_POST['submit'])) {
        if (!empty($vote_name) && !empty($_POST[0]) && !empty($_POST[1]) && !empty($vote_count)) {
            mysql_query("INSERT INTO `cms_forum_vote` SET
                `name`='" . mysql_real_escape_string($vote_name) . "',
                `time`='" . time() . "',
                `type` = '1',
                `topic`='$id'
            ");
            mysql_query("UPDATE `forum` SET  `realid` = '1'  WHERE `id` = '$id'");
            if ($vote_count > 10)
                $vote_count = 10;
            else if ($vote_count < 2)
                $vote_count = 2;
            for ($vote = 0; $vote < $vote_count; $vote++) {
                $text = mb_substr(trim($_POST[$vote]), 0, 100);
                if (empty($text)) {
                    continue;
                }
                mysql_query("INSERT INTO `cms_forum_vote` SET
                    `name`='" . mysql_real_escape_string($text) . "',
                    `type` = '2',
                    `topic`='$id'
                ");
            }
            echo '<div class="gmenu">'.$lng_forum['voting_added'] . '<br /><a href="'.functions::bodau($res['text']).'.' . $id . '.html">' . $lng['continue'] . '</a></div>';
        } else
            echo '<div class="rmenu">'.$lng['error_empty_fields'] . '<br /><a href="?act=addvote&id=' . $id . '">' . $lng['repeat'] . '</a></div>';
    } else {
        echo '<div class="phdr">Tạo bình chọn</div><form action="index.php?act=addvote&id=' . $id . '" method="post" class="gmenu">' .
            '' . $lng_forum['voting'] . ':<br/>' .
            '<input type="text" size="20" maxlength="150" name="name_vote" value="' . htmlspecialchars($vote_name) . '"/><br/>';
        if (isset($_POST['plus']))
            ++$vote_count;
        elseif (isset($_POST['minus']))
            --$vote_count;
        if ($vote_count < 2 || empty($vote_count))
            $vote_count = 2;
        elseif ($vote_count > 10)
            $vote_count = 10;
        for ($vote = 0; $vote < $vote_count; $vote++) {
            echo $lng_forum['answer'] . ' ' . ($vote + 1) . '(max. 100): <br/><input type="text" name="' . $vote . '" value="' . htmlspecialchars($_POST[$vote]) . '"/><br/>';
        }
        echo '<input type="hidden" name="count_vote" value="' . $vote_count . '"/>';
        echo ($vote_count < 10) ? '<br/><input type="submit" name="plus" value="' . $lng_forum['add_answer'] . '"/>' : '';
        echo $vote_count > 2 ? '<input type="submit" name="minus" value="' . $lng_forum['delete_last'] . '"/><br/>' : '<br/>';
        echo '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></form>';
        echo '<div class="phdr"><a href="'.functions::bodau($res['text']).'.' . $id . '.html">' . $lng['back'] . '</a></div>';
    }
} else {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}