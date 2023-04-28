<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 7) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}

$set_af = isset($set['antiflood']) ? unserialize($set['antiflood']) : array ();
echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['antiflood_settings'] . '</div>';
if (isset($_POST['submit']) || isset($_POST['save'])) {
    // receive data from the form
    $set_af['mode'] = isset($_POST['mode']) && $_POST['mode'] > 0 && $_POST['mode'] < 5 ? intval($_POST['mode']) : 2;
    $set_af['day'] = isset($_POST['day']) ? intval($_POST['day']) : 5;
    $set_af['night'] = isset($_POST['night']) ? intval($_POST['night']) : 15;
    $set_af['dayfrom'] = isset($_POST['dayfrom']) ? intval($_POST['dayfrom']) : 10;
    $set_af['dayto'] = isset($_POST['dayto']) ? intval($_POST['dayto']) : 22;
    // Check the correctness of the data entry
    if ($set_af['day'] < 4)
        $set_af['day'] = 4;
    if ($set_af['day'] > 300)
        $set_af['day'] = 300;
    if ($set_af['night'] < 4)
        $set_af['night'] = 4;
    if ($set_af['night'] > 300)
        $set_af['night'] = 300;
    if ($set_af['dayfrom'] < 6)
        $set_af['dayfrom'] = 6;
    if ($set_af['dayfrom'] > 12)
        $set_af['dayfrom'] = 12;
    if ($set_af['dayto'] < 17)
        $set_af['dayto'] = 17;
    if ($set_af['dayto'] > 23)
        $set_af['dayto'] = 23;
    mysql_query("UPDATE `cms_settings` SET `val` = '" . serialize($set_af) . "' WHERE `key` = 'antiflood' LIMIT 1");
    echo '<div class="rmenu">' . $lng['settings_saved'] . '</div>';
} elseif (empty($set_af) || isset($_GET['reset'])) {
    // Set the default settings (if not specified in the system)
    echo '<div class="rmenu">' . $lng['settings_default'] . '</div>';
    $set_af['mode'] = 2;
    $set_af['day'] = 5;
    $set_af['night'] = 15;
    $set_af['dayfrom'] = 10;
    $set_af['dayto'] = 22;
    mysql_query("UPDATE `cms_settings` SET `val` = '" . serialize($set_af) . "' WHERE `key` = 'antiflood' LIMIT 1");
}

// Form input parameters in flood
echo '<form action="index.php?act=antiflood" method="post">' .
    '<div class="gmenu"><p><h3>' . $lng['operation_mode'] . '</h3><table cellspacing="2">' .
    '<tr><td valign="top"><input type="radio" name="mode" value="3" ' . ($set_af['mode'] == 3 ? 'checked="checked"' : '') . '/></td><td><b>' . $lng['day'] . '</b></td></tr>' .
    '<tr><td valign="top"><input type="radio" name="mode" value="4" ' . ($set_af['mode'] == 4 ? 'checked="checked"' : '') . '/></td><td><b>' . $lng['night'] . '</b></td></tr>' .
    '<tr><td valign="top"><input type="radio" name="mode" value="2" ' . ($set_af['mode'] == 2 ? 'checked="checked"' : '') . '/></td><td><b>' . $lng['day'] . ' / ' . $lng['night'] . '</b><br /><small>' . $lng['antiflood_dn_help']
    . '</small></td></tr>' .
    '<tr><td valign="top"><input type="radio" name="mode" value="1" ' . ($set_af['mode'] == 1 ? 'checked="checked"' : '') . '/></td><td><b>' . $lng['adaptive'] . '</b><br /><small>' . $lng['antiflood_ad_help'] . '</small></td></tr>' .
    '</table></p></div>' .
    '<div class="menu"><p><h3>' . $lng['time_limit'] . '</h3>' .
    '<input name="day" size="3" value="' . $set_af['day'] . '" maxlength="3" />&#160;' . $lng['day'] . '<br />' .
    '<input name="night" size="3" value="' . $set_af['night'] . '" maxlength="3" />&#160;' . $lng['night'] .
    '<br /><small>' . $lng['antiflood_tl_help'] . '</small></p>' .
    '<p><h3>' . $lng['day_mode'] . '</h3>' .
    '<input name="dayfrom" size="2" value="' . $set_af['dayfrom'] . '" maxlength="2" style="text-align:right"/>:00&#160;' . $lng['day_begin'] . ' <span class="gray">(6-12)</span><br />' .
    '<input name="dayto" size="2" value="' . $set_af['dayto'] . '" maxlength="2" style="text-align:right"/>:00&#160;' . $lng['day_end'] . ' <span class="gray">(17-23)</span>' .
    '</p><p><br /><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></div></form>' .
    '<div class="phdr"><a href="index.php?act=antiflood&amp;reset">' . $lng['reset_settings'] . '</a></div>' .
    '<p><a href="index.php">' . $lng['admin_panel'] . '</a></p>';
?>