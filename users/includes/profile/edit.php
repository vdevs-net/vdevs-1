<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$textl = $user['account'] . ': ' . $lng_profile['profile_edit'];
require('../incfiles/head.php');

// Check right
if ($user['id'] != $user_id && ($rights < 7 || $user['rights'] > $rights)) {
    echo functions::display_error($lng_profile['error_rights']);
    require('../incfiles/end.php');
    exit;
}

echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '"><b>' . ($user['id'] != $user_id ? $lng['profile'] : $lng_profile['my_profile']) . '</b></a> | ' . $lng['edit'] . '</div>';
if (isset($_GET['delavatar'])) {
    // remove avatar
    @unlink('../files/users/avatar/' . $user['id'] . '.png');
    echo '<div class="rmenu">' . $lng_profile['avatar_deleted'] . '</div>';
} elseif (isset($_GET['delphoto'])) {
    // remove photo
    @unlink('../files/users/photo/' . $user['id'] . '.jpg');
    @unlink('../files/users/photo/' . $user['id'] . '_small.jpg');
    echo '<div class="rmenu">' . $lng_profile['photo_deleted'] . '</div>';
} elseif (isset($_POST['submit'])) {
    // accept data from the form, check and write to the database
    $error = array ();
    $user['live'] = isset($_POST['live']) ? functions::checkin(mb_substr($_POST['live'], 0, 100)) : '';
    $user['dayb'] = isset($_POST['dayb']) ? intval($_POST['dayb']) : 0;
    $user['monthb'] = isset($_POST['monthb']) ? intval($_POST['monthb']) : 0;
    $user['yearb'] = isset($_POST['yearb']) ? intval($_POST['yearb']) : 0;
    $user['about'] = isset($_POST['about']) ? functions::checkin(mb_substr($_POST['about'], 0, 500)) : '';
    $user['status'] = isset($_POST['status']) ? functions::checkin(mb_substr($_POST['status'], 0, 50)) : '';
    $user['mail'] = isset($_POST['mail']) ? functions::checkin(mb_substr($_POST['mail'], 0, 40)) : '';
    $user['mailvis'] = isset($_POST['mailvis']) ? 1 : 0;
    $user['facebook'] = isset($_POST['facebook']) ? functions::checkin(mb_substr($_POST['facebook'], 0, 40)) : '';
    // User data (for administrators)
    $user['sex'] = isset($_POST['sex']) && $_POST['sex'] == 'm' ? 'm' : 'f';
    $user['rights'] = isset($_POST['rights']) ? abs(intval($_POST['rights'])) : $user['rights'];
    // carry out the necessary checks
    if($user['rights'] > $rights || $user['rights'] > 9 || $user['rights'] < 0)
        $user['rights'] = 0;
    if ($user['dayb'] || $user['monthb'] || $user['yearb']) {
        if ($user['dayb'] < 1 || $user['dayb'] > 31 || $user['monthb'] < 1 || $user['monthb'] > 12)
            $error[] = $lng_profile['error_birth'];
    }
    if (filter_var($user['mail'], FILTER_VALIDATE_EMAIL) === false) {
        $error = 'Định dạng email không hợp lệ!';
    }
    if (!$error) {
        mysql_query("UPDATE `users` SET
            `live` = '" . mysql_real_escape_string($user['live']) . "',
            `dayb` = '" . $user['dayb'] . "',
            `monthb` = '" . $user['monthb'] . "',
            `yearb` = '" . $user['yearb'] . "',
            `about` = '" . mysql_real_escape_string($user['about']) . "',
            `status` = '" . mysql_real_escape_string($user['status']) . "',
            `mail` = '" . mysql_real_escape_string($user['mail']) . "',
            `mailvis` = '" . $user['mailvis'] . "',
            `facebook` = '" . mysql_real_escape_string($user['facebook']) . "'
            WHERE `id` = '" . $user['id'] . "'
        ");
        if ($rights >= 7) {
            mysql_query("UPDATE `users` SET
                `sex` = '" . $user['sex'] . "',
                `rights` = '" . $user['rights'] . "'
                WHERE `id` = '" . $user['id'] . "'
            ");
        }
        echo '<div class="gmenu">' . $lng_profile['data_saved'] . '</div>';
    } else {
        echo functions::display_error($error);
    }
}

// Form editing user profiles
echo '<form action="profile.php?act=edit&user=' . $user['id'] . '" method="post">' .
    '<div class="gmenu"><p>' .
    $lng['login_name'] . ': <b>' . $user['account'] . '</b><br />';
    echo $lng['status'] . ': (' . $lng_profile['status_lenght'] . ')<br /><input type="text" value="' . htmlspecialchars($user['status']) . '" name="status" /><br />';
echo '</p><p>' . $lng['avatar'] . ':<br />';
$link = '';
if (file_exists(('../files/users/avatar/' . $user['id'] . '.png'))) {
    echo '<img src="' . SITE_URL . '/files/users/avatar/' . $user['id'] . '.png" width="32" height="32" alt="' . $user['account'] . '" /><br />';
    $link = ' | <a href="profile.php?act=edit&user=' . $user['id'] . '&delavatar">' . $lng['delete'] . '</a>';
}
echo '<small><a href="profile.php?act=images&mod=avatar&user=' . $user['id'] . '">' . $lng_profile['upload'] . '</a>';
echo $link . '</small></p>';
echo '<p>' . $lng_profile['photo'] . ':<br />';
$link = '';
if (file_exists('../files/users/photo/' . $user['id'] . '_small.jpg')) {
    echo '<a href="' . SITE_URL . '/files/users/photo/' . $user['id'] . '.jpg"><img src="' . SITE_URL . '/files/users/photo/' . $user['id'] . '_small.jpg" alt="' . $user['account'] . '" border="0" /></a><br />';
    $link = ' | <a href="profile.php?act=edit&user=' . $user['id'] . '&delphoto">' . $lng['delete'] . '</a>';
}
echo '<small><a href="profile.php?act=images&mod=up_photo&user=' . $user['id'] . '">' . $lng_profile['upload'] . '</a>' . $link . '</small><br />' .
    '</p></div>' .
    '<div class="menu">' .
    '<p><h3><img src="' . SITE_URL . '/images/contacts.png" width="16" height="16" class="left" /> ' . $lng_profile['personal_data'] . '</h3>' .
    $lng_profile['name'] . ':<br /><input type="text" value="' . htmlspecialchars($user['imname']) . '" disabled="disabled" /></p>' .
    '<p>' . $lng_profile['birth_date'] . '<br />' .
    '<input type="text" value="' . htmlspecialchars($user['dayb']) . '" size="2" maxlength="2" name="dayb" />.' .
    '<input type="text" value="' . htmlspecialchars($user['monthb']) . '" size="2" maxlength="2" name="monthb" />.' .
    '<input type="text" value="' . htmlspecialchars($user['yearb']) . '" size="4" maxlength="4" name="yearb" /></p>' .
    '<p>' . $lng_profile['city'] . ':<br /><input type="text" value="' . htmlspecialchars($user['live']) . '" name="live" /></p>' .
    '<p>' . $lng_profile['about'] . ':<br /><textarea rows="' . $set_user['field_h'] . '" name="about">' . htmlspecialchars($user['about']) . '</textarea></p>' .
    '<p><h3><img src="' . SITE_URL . '/images/mail.png" width="16" height="16" class="left" /> ' . $lng_profile['communication'] . '</h3>' .
    $lng_profile['phone_number'] . ':<br />' . (empty($user['mobile']) ? '<small>Sử dụng số điện thoại của bạn, soạn <b class="red">ON GT '.$user['account'].'</b> gửi 8085 để cập nhật</small><br/>':'') . '<input type="text" value="' . (empty($user['mobile']) ? '': '0' . htmlspecialchars($user['mobile'])) . '" disabled="disabled" /><br />' .
    '</p><p>E-mail:<br /><small>' . $lng_profile['email_warning'] . '</small><br />' .
    '<input type="text" value="' . htmlspecialchars($user['mail']) . '" name="mail" /><br />' .
    '<input name="mailvis" type="checkbox" value="1" ' . ($user['mailvis'] ? 'checked="checked"' : '') . ' /> ' . $lng_profile['show_in_profile'] . '</p>' .
    '<p>Facebook:<br /><input type="text" value="' . htmlspecialchars($user['facebook']) . '" name="facebook" /></p>' .
    '</div>';
// administrative functions
if ($rights >= 7) {
    echo '<div class="rmenu"><p><h3><img src="' . SITE_URL . '/images/settings.png" width="16" height="16" class="left" /> ' . $lng['settings'] . '</h3><ul>';
    echo '<li><a href="profile.php?act=password&user=' . $user['id'] . '">' . $lng['change_password'] . '</a></li>';
    if($rights > $user['rights'])
        echo '<li><a href="profile.php?act=reset&user=' . $user['id'] . '">' . $lng['reset_settings'] . '</a></li>';
    echo '<li>' . $lng_profile['specify_sex'] . ':<br />' .
        '<input type="radio" value="m" name="sex" ' . ($user['sex'] == 'm' ? 'checked="checked"' : '') . '/> ' . $lng_profile['sex_m'] . '<br />' .
        '<input type="radio" value="f" name="sex" ' . ($user['sex'] == 'f' ? 'checked="checked"' : '') . '/> ' . $lng_profile['sex_w'] . '</li>' .
        '</ul></p>';
    if ($user['id'] != $user_id) {
        echo '<p><h3><img src="' . SITE_URL . '/images/forbidden.png" width="16" height="16" class="left" /> ' . $lng_profile['rank'] . '</h3><ul>' .
            '<input type="radio" value="0" name="rights" ' . (!$user['rights'] ? 'checked="checked"' : '') . '/> <b>' . $lng_profile['rank_0'] . '</b><br />' .
            '<input type="radio" value="3" name="rights" ' . ($user['rights'] == 3 ? 'checked="checked"' : '') . '/> ' . $lng_profile['rank_3'] . '<br />' .
            '<input type="radio" value="5" name="rights" ' . ($user['rights'] == 5 ? 'checked="checked"' : '') . '/> ' . $lng_profile['rank_5'] . '<br />' .
            '<input type="radio" value="6" name="rights" ' . ($user['rights'] == 6 ? 'checked="checked"' : '') . '/> ' . $lng_profile['rank_6'] . '<br />';
        if ($rights == 9) {
            echo '<input type="radio" value="7" name="rights" ' . ($user['rights'] == 7 ? 'checked="checked"' : '') . '/> ' . $lng_profile['rank_7'] . '<br />' .
                '<input type="radio" value="9" name="rights" ' . ($user['rights'] == 9 ? 'checked="checked"' : '') . '/> <span class="red"><b>' . $lng_profile['rank_9'] . '</b></span><br />';
        }
        echo '</ul></p>';
    }
    echo '</div>';
}
echo '<div class="gmenu"><input type="submit" value="' . $lng['save'] . '" name="submit" /></div>' .
    '</form>';