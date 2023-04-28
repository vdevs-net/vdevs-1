<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 9) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}
echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | Cài đặt cửa hàng</div>';
if (isset($_POST['submit'])) {
    // Save the system settings
    mysql_query("UPDATE `cms_settings` SET `val`='" . abs(intval($_POST['offer'])) . "' WHERE `key` = 'offer'");
    $req = mysql_query("SELECT * FROM `cms_settings`");
    $set = array ();
    while ($res = mysql_fetch_row($req)) $set[$res[0]] = $res[1];
    echo '<div class="rmenu">' . $lng['settings_saved'] . '</div>';
}
// Settings Shop
echo '<form action="index.php?act=shop" method="post"><div class="menu">';
// Offers
echo '<div>' .
    '<h3>Khuyến mãi thẻ nạp</h3>' .
    '<input type="text" name="offer" value="' . $set['offer'] . '" style="width:50px"/> %' .
    '</div>';
// Выбор темы оформления
echo '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></div></form>' .
    '<div class="phdr"><a href="index.php">' . $lng['admin_panel'] . '</a></div>';