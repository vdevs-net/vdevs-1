<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 9) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}
echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['site_settings'] . '</div>';
if (isset($_POST['submit'])) {
    // Save the system settings
    $copyright = isset($_POST['copyright']) ? functions::checkin($_POST['copyright']) : '';
    $meta_key = isset($_POST['meta_key']) ? functions::checkin($_POST['meta_key']) : '';
    $meta_desc = isset($_POST['meta_desc']) ? functions::checkin($_POST['meta_desc']) : '';
    $madm = isset($_POST['madm']) ? functions::checkin($_POST['madm']) : '';
    if (filter_var($madm, FILTER_VALIDATE_EMAIL) === false) {
        mysql_query('UPDATE `cms_settings` SET `val` = "' . mysql_real_escape_string($madm) . '" WHERE `key` = "email"');
    }
    if (!empty($copyright)) {
        mysql_query('UPDATE `cms_settings` SET `val` = "' . mysql_real_escape_string($copyright) . '" WHERE `key` = "copyright"');
    }
    mysql_query('UPDATE `cms_settings` SET `val` = "' . abs(intval($_POST['flsz'])) . '" WHERE `key` = "flsz"');
    mysql_query('UPDATE `cms_settings` SET `val` = "' . (isset($_POST['gz']) ? 1 : 0) . '" WHERE `key` = "gzip"');
    if (!empty($meta_key)) {
        mysql_query('UPDATE `cms_settings` SET `val` = "' . mysql_real_escape_string($meta_key) . '" WHERE `key` = "meta_key"');
    }
    if (!empty($meta_desc)) {
        mysql_query('UPDATE `cms_settings` SET `val` = "' . mysql_real_escape_string($meta_desc) . '" WHERE `key` = "meta_desc"');
    }
    $req = mysql_query('SELECT * FROM `cms_settings`');
    $set = array();
    while ($res = mysql_fetch_row($req)) $set[$res[0]] = $res[1];
    echo '<div class="rmenu">' . $lng['settings_saved'] . '</div>';
}
/*
-----------------------------------------------------------------
Форма ввода параметров системы
-----------------------------------------------------------------
*/
echo '<form action="index.php?act=settings" method="post"><div class="menu">';
// Общие настройки
echo '<p>' .
    '<h3>' . $lng['common_settings'] . '</h3>' .
    $lng['site_copyright'] . ':<br/>' . '<input type="text" name="copyright" value="' . htmlspecialchars($set['copyright']) . '"/><br/>' .
    $lng['site_email'] . ':<br/>' . '<input name="madm" maxlength="50" value="' . htmlspecialchars($set['email']) . '"/><br />' .
    $lng['file_maxsize'] . ' (kb):<br />' . '<input type="text" name="flsz" value="' . intval($set['flsz']) . '"/><br />' .
    '<input name="gz" type="checkbox" value="1" ' . ($set['gzip'] ? 'checked="checked"' : '') . ' /> ' . $lng['gzip_compress'] .
    '</p>';
// META тэги
echo '<p>' .
    '<h3>' . $lng['meta_tags'] . '</h3>' .
    '&#160;' . $lng['meta_keywords'] . ':<br />&#160;<textarea rows="' . $set_user['field_h'] . '" name="meta_key">' . htmlspecialchars($set['meta_key']) . '</textarea><br />' .
    '&#160;' . $lng['meta_description'] . ':<br />&#160;<textarea rows="' . $set_user['field_h'] . '" name="meta_desc">' . htmlspecialchars($set['meta_desc']) . '</textarea>' .
    '</p>';
echo '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></div></form>' .
    '<div class="phdr">&#160;</div>' .
    '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';