<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = $lng['birthday_men'];
$headmod = 'birth';
require('../incfiles/head.php');

/*
-----------------------------------------------------------------
Выводим список именинников
-----------------------------------------------------------------
*/
echo '<div class="phdr"><a href="index.php"><b>' . $lng['community'] . '</b></a> | ' . $lng['birthday_men'] . '</div>';
$total = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1'"), 0);
if ($total) {
    $req = mysql_query("SELECT * FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1' LIMIT $start, $kmess");
    while ($res = mysql_fetch_assoc($req)) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        echo functions::display_user($res) . '</div>';
        ++$i;
    }
    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
    if ($total > $kmess) {
        echo '<p>' . functions::display_pagination('index.php?act=birth&page=', $start, $total, $kmess) . '</p>';
        echo '<p><form action="index.php?act=birth" method="post">' .
             '<input type="text" name="page" size="2"/>' .
             '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
             '</form></p>';
    }
} else {
    echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
}
echo '<p><a href="index.php">' . $lng['back'] . '</a></p>';
?>