<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 7) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}

echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['access_rights'] . '</div>';
if (isset($_POST['submit'])) {
    // Write in the database
    mysql_query("REPLACE `cms_settings` SET `val`='" . (isset($_POST['reg']) ? intval($_POST['reg']) : 0) . "', `key`='mod_reg'");
    mysql_query("REPLACE `cms_settings` SET `val`='" . (isset($_POST['forum']) ? intval($_POST['forum']) : 0) . "', `key`='mod_forum'");
    mysql_query("REPLACE `cms_settings` SET `val`='" . (isset($_POST['lib']) ? intval($_POST['lib']) : 0) . "', `key`='mod_lib'");
    mysql_query("REPLACE `cms_settings` SET `val`='" . isset($_POST['libcomm']) . "', `key`='mod_lib_comm'");
    mysql_query("REPLACE `cms_settings` SET `val`='" . (isset($_POST['active']) ? intval($_POST['active']) : 0) . "', `key`='active'");
    mysql_query("REPLACE `cms_settings` SET `val`='" . (isset($_POST['access']) ? intval($_POST['access']) : 0) . "', `key`='site_access'");
    $req = mysql_query("SELECT * FROM `cms_settings`");
    $set = array();
    while ($res = mysql_fetch_row($req)) $set[$res[0]] = $res[1];
    mysql_free_result($req);
    echo '<div class="rmenu">' . $lng['settings_saved'] . '</div>';
}

$color = array('red', 'yelow', 'green', 'gray');
echo '<form method="post" action="index.php?act=access">';

// Managing Access to Forum
echo '<div class="menu"><p>' .
    '<h3><img src="' . SITE_URL . '/images/' . $color[$set['mod_forum']] . '.gif" width="16" height="16" class="left"/>&#160;' . $lng['forum'] . '</h3>' .
    '<div style="font-size: x-small">' .
    '<input type="radio" value="2" name="forum" ' . ($set['mod_forum'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_enabled'] . '<br />' .
    '<input type="radio" value="1" name="forum" ' . ($set['mod_forum'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_authorised'] . '<br />' .
    '<input type="radio" value="3" name="forum" ' . ($set['mod_forum'] == 3 ? 'checked="checked"' : '') . '/>&#160;' . $lng['read_only'] . '<br />' .
    '<input type="radio" value="0" name="forum" ' . (!$set['mod_forum'] ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_disabled'] .
    '</div></p>';

// Managing Access to Library
echo '<p><h3><img src="' . SITE_URL . '/images/' . $color[$set['mod_lib']] . '.gif" width="16" height="16" class="left"/>&#160;' . $lng['library'] . '</h3>' .
    '<div style="font-size: x-small">' .
    '<input type="radio" value="2" name="lib" ' . ($set['mod_lib'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_enabled'] . '<br />' .
    '<input type="radio" value="1" name="lib" ' . ($set['mod_lib'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_authorised'] . '<br />' .
    '<input type="radio" value="0" name="lib" ' . (!$set['mod_lib'] ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_disabled'] . '<br />' .
    '<input name="libcomm" type="checkbox" value="1" ' . ($set['mod_lib_comm'] ? 'checked="checked"' : '') . ' />&#160;' . $lng['comments'] .
    '</div></p>';

// Controlling access to the assets of the site (lists of users, etc.)
echo '<p><h3><img src="' . SITE_URL . '/images/' . $color[$set['active'] + 1] . '.gif" width="16" height="16" class="left"/>&#160;' . $lng['community'] . '</h3>' .
    '<div style="font-size: x-small">' .
    '<input type="radio" value="1" name="active" ' . ($set['active'] ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_enabled'] . '<br />' .
    '<input type="radio" value="0" name="active" ' . (!$set['active'] ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_authorised'] . '<br />' .
    '</div></p></div>';

// Control Access Registry
echo '<div class="gmenu"><h3><img src="' . SITE_URL . '/images/' . $color[$set['mod_reg']] . '.gif" width="16" height="16" class="left"/>&#160;' . $lng['registration'] . '</h3>' .
    '<div style="font-size: x-small">' .
    '<input type="radio" value="2" name="reg" ' . ($set['mod_reg'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_enabled'] . '<br />' .
    '<input type="radio" value="1" name="reg" ' . ($set['mod_reg'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_with_moderation'] . '<br />' .
    '<input type="radio" value="0" name="reg" ' . (!$set['mod_reg'] ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_disabled'] .
    '</div></div>';

// Controlling access to the Site (closing the site)

echo '<div class="rmenu">' .
    '<h3><img src="' . SITE_URL . '/images/' . $color[$set['site_access']] . '.gif" width="16" height="16" class="left"/>&#160;' . $lng['site_access'] . '</h3>' .
    '<div style="font-size: x-small">' .
    '<input class="btn btn-large" type="radio" value="2" name="access" ' . ($set['site_access'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . $lng['access_enabled'] . '<br />' .
    '<input class="btn btn-large" type="radio" value="1" name="access" ' . ($set['site_access'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng['site_closed_except_adm'] . '<br />' .
    '<input class="btn btn-large" type="radio" value="0" name="access" ' . (!$set['site_access'] ? 'checked="checked"' : '') . '/>&#160;' . $lng['site_closed_except_sv'] . '<br />' .
    '</div></div>';

echo '<div class="phdr"><small>' . $lng['access_help'] . '</small></div>' .
    '<p><input type="submit" name="submit" id="button" value="' . $lng['save'] . '" /></p>' .
    '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p></form>';