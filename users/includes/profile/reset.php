<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

if ($rights >= 7 && $rights > $user['rights']) {
    // Reset User
    $textl = $user['account'] . ': ' . $lng_profile['profile_edit'];
    require('../incfiles/head.php');
    mysql_query("UPDATE `users` SET `set_user` = '' WHERE `id` = '" . $user['id'] . "'");
    echo '<div class="gmenu"><p>' . $lng_profile['reset1'] . ' <b>' . $user['account'] . '</b> ' . $lng_profile['reset2'] . '<br />' .
    '<a href="profile.php?user=' . $user['id'] . '">' . $lng['profile'] . '</a></p></div>';
    require_once ('../incfiles/end.php');
    exit;
}else{
	header('Location: ' . SITE_URL . '/?err');
    exit;
}