<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

// Check right
if ($user['id'] != $user_id && ($rights < 7 || $user['rights'] > $rights)) {
    echo functions::display_error($lng['access_forbidden']);
    require('../incfiles/end.php');
    exit;
}
$lng_pass = core::load_lng('pass');
$textl = $user['account'].' : ' . $lng_pass['change_password'];
require('../incfiles/head.php');

switch ($mod) {
    case 'change':
        // change your password
        $error = array();
        $oldpass = isset($_POST['oldpass']) ? trim($_POST['oldpass']) : '';
        $newpass = isset($_POST['newpass']) ? trim($_POST['newpass']) : '';
        $newconf = isset($_POST['newconf']) ? trim($_POST['newconf']) : '';
        if ($user['id'] != $user_id) {
            if (!$newpass || !$newconf)
                $error[] = $lng_pass['error_fields'];
        } else {
            if (!$oldpass || !$newpass || !$newconf)
                $error[] = $lng_pass['error_fields'];
        }
        if (!$error && $user['id'] == $user_id && md5(md5($oldpass)) !== $user['password'])
            $error[] = $lng_pass['error_old_password'];
        if ($newpass != $newconf)
            $error[] = $lng_pass['error_new_password'];
        if (preg_match('/[^\dA-Za-z\!\@\#\$\%\^\&\*\(\)\_\+\-\=]/', $newpass) && !$error)
            $error[] = $lng['error_wrong_symbols'];

        if (!$error && (strlen($newpass) < 6 || strlen($newpass) > 32))
            $error[] = $lng_pass['error_lenght'];
        if (!$error) {
            // Write to the database
            mysql_query('UPDATE `users` SET `password` = "' . mysql_real_escape_string(md5(md5($newpass))) . '" WHERE `id` = "' . $user['id'] . '";');
            // observe and record COOKIES
            if (isset($_COOKIE['cuid']) && isset($_COOKIE['cups']))
                setcookie('cups', md5(md5($newpass)), time() + 3600 * 24 * 365);
            echo '<div class="gmenu"><p><b>' . $lng_pass['password_changed'] . '</b><br />' .
                '<a href="' . ($user_id == $user['id'] ? '/login.php' : 'profile.php?user=' . $user['id']) . '">' . $lng['continue'] . '</a></p>';
            echo '</div>';
        } else {
            echo functions::display_error($error, '<a href="profile.php?act=password&user=' . $user['id'] . '">' . $lng['repeat'] . '</a>');
        }
        break;

    default:
        // Lost Password Recovery Form
        echo '<div class="phdr"><b>' . $lng_pass['change_password'] . ':</b> ' . $user['account'] . '</div>';
        echo '<form action="profile.php?act=password&mod=change&user=' . $user['id'] . '" method="post">';
        if ($user['id'] == $user_id)
            echo '<div class="menu"><p>' . $lng_pass['input_old_password'] . ':<br /><input type="password" name="oldpass" /></p></div>';
        echo '<div class="gmenu"><p>' . $lng_pass['input_new_password'] . ':<br />' .
            '<input type="password" name="newpass" /><br />' . $lng_pass['repeat_password'] . ':<br />' .
            '<input type="password" name="newconf" /></p>' .
            '<p><input type="submit" value="' . $lng['save'] . '" name="submit" />' .
            '</p></div></form>' .
            '<div class="phdr"><small>' . $lng_pass['password_change_help'] . '</small></div>' .
            '<p><a href="profile.php?user=' . $user['id'] . '">' . $lng['profile'] . '</a></p>';
}