<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = $lng['mail'];
require_once('../incfiles/head.php');
echo '<div class="phdr"><b>' . $lng_mail['new_messages'] . '</b></div>';
$total = mysql_result(mysql_query("SELECT COUNT(*) FROM (SELECT DISTINCT `user_id` FROM `cms_mail` WHERE `from_id`='$user_id' AND `read`='0' AND `spam`='0') a;"), 0);
if($total == 1) {
    // When all the new messages from the same total as Chela show immediately correspondence
    $max = mysql_result(mysql_query("SELECT `user_id`, count(*) FROM `cms_mail` WHERE `from_id`='$user_id' AND `read`='0' AND `spam`='0' GROUP BY `user_id`"), 0);
    Header('Location: index.php?act=write&id='.$max);
    exit();
}
if($total) {
	if($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?act=new&page=', $start, $total, $kmess) . '</div>';
	// Grupiruem for contacts
	$query = mysql_query("SELECT `users`.* FROM `cms_mail`
		LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
		LEFT JOIN `cms_contact` ON `cms_mail`.`user_id`=`cms_contact`.`user_id`
		WHERE `cms_mail`.`from_id`='" . $user_id . "' AND `cms_mail`.`read`='0' AND `cms_mail`.`spam`='0' GROUP BY `cms_mail`.`user_id` ORDER BY `cms_contact`.`time` DESC LIMIT " . $start . "," . $kmess
    );
	for ($i = 0; ($row = mysql_fetch_assoc($query)) !== false; ++$i) {
		echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
		$subtext = '<a href="index.php?act=write&amp;id=' . $row['id'] . '">' . $lng_mail['correspondence'] . '</a> | <a href="index.php?act=deluser&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a> | <a href="index.php?act=ignor&amp;id=' . $row['id'] . '&amp;add">' . $lng_mail['ban_contact'] . '</a>';
		$count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='{$row['id']}' AND `from_id`='$user_id') OR (`user_id`='$user_id' AND `from_id`='{$row['id']}')) AND `delete`!='$user_id' AND `spam`='0';"), 0);
		$new_count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='{$row['id']}' AND `cms_mail`.`from_id`='$user_id' AND `read`='0' AND `delete`!='$user_id' AND `spam`='0';"), 0);
		$arg = array(
		'header' => '('.$count_message. ($new_count_message ? '/<span class="red">+' . $new_count_message . '</span>' : '') . ')',
		'sub' => $subtext
		);
		echo functions::display_user($row, $arg);
		echo '</div>';
	}
} else {
	echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
}
echo '<div class="phdr">' . $lng['total'] . ': ' . $new_mail . '</div>';
if ($total > $kmess) {
    echo '<div class="topmenu">' . functions::display_pagination('index.php?act=new&page=', $start, $total, $kmess) . '</div>';
    echo '<p><form action="index.php" method="get">
		<input type="hidden" name="act" value="new"/>
		<input type="text" name="page" size="2"/>
		<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
}
echo '<p><a href="' . SITE_URL . '/users/profile.php?act=office">' . $lng['personal'] . '</a></p>';