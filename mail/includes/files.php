<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$headmod = 'mail';
$textl = $lng['mail'] . ' | ' . $lng['files'];
require_once('../incfiles/head.php');

echo '<div class="phdr"><b>' . $lng['files'] . '</b></div>';
//Отображаем список файлов
$total = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE (`user_id`='$user_id' OR `from_id`='$user_id') AND `delete`!='$user_id' AND `file_name`!=''"), 0);
if ($total) {
    if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?act=files&page=', $start, $total, $kmess) . '</div>';
    $req = mysql_query("SELECT `cms_mail`.*, `users`.`account`
        FROM `cms_mail`
        LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
	    WHERE (`cms_mail`.`user_id`='$user_id' OR `cms_mail`.`from_id`='$user_id')
	    AND `cms_mail`.`delete`!='$user_id'
	    AND `cms_mail`.`file_name`!=''
	    ORDER BY `cms_mail`.`time` DESC
	    LIMIT " . $start . "," . $kmess);
    for ($i = 0; ($row = mysql_fetch_assoc($req)) !== FALSE; ++$i) {
        echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
        echo '<a href="' . SITE_URL . '/users/profile.php?user=' . $row['user_id'] . '"><b>' . $row['account'] . '</b></a>:: <a href="index.php?act=load&amp;id=' . $row['id'] . '">' . $row['file_name'] . '</a> (' . formatsize($row['size']) . ') (' . $row['count'] . ')';
        echo '</div>';
    }
} else {
    echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
}

echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
if ($total > $kmess) {
    echo '<div class="topmenu">' . functions::display_pagination('index.php?act=files&page=', $start, $total, $kmess) . '</div>';
    echo '<div class="menu"><form action="index.php" method="get">
		<input type="hidden" name="act" value="files"/>
		<input type="text" name="page" size="2"/>
		<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></div>';
}

echo '<div class="menu"><a href="' . SITE_URL . '/users/profile.php?act=office">' . $lng['personal'] . '</a></div>';