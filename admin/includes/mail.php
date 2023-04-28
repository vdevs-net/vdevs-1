<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 9) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}
$lng_mail = core::load_lng('mail');

echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['mail'] . '</div>';
if (isset($_POST['submit'])) {
    /*
    -----------------------------------------------------------------
    Сохраняем настройки системы
    -----------------------------------------------------------------
    */
	$set['cat_friends'] = isset($_POST['cat_friends']) && $_POST['cat_friends'] == 1 ? 1 : 0;
	mysql_query('UPDATE `cms_settings` SET `val` = "' . $set['cat_friends'] . '" WHERE `key` = "cat_friends"');
    echo '<div class="rmenu">' . $lng['settings_saved'] . '</div>';
}

echo '<form action="index.php?act=mail" method="post"><div class="menu">';
// Общие настройки
echo '<h3>' . $lng_mail['system_message_reg'] . '</h3><strong>' . $lng_mail['cat_friends'] . ':</strong><br />' .
	'<input type="radio" value="1" name="cat_friends" ' . ($set['cat_friends'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng['lng_on'] . '<br />' .
    '<input type="radio" value="0" name="cat_friends" ' . ($set['cat_friends'] != 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng['lng_off'] . '<br />' .
	'<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></div></form>' .
    '<div class="phdr"><a href="index.php">' . $lng['admin_panel'] . '</a></div>';