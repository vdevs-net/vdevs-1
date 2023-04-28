<?php
define('_MRKEN_CMS', 1);

require('incfiles/core.php');
$textl = $lng['registration'];
$headmod = 'registration';
require('incfiles/head.php');
$lng_reg = core::load_lng('registration');

// If the registration is closed, a warning is displayed
if (core::$deny_registration || !$set['mod_reg'] || core::$user_id) {
    echo '<p>' . $lng_reg['registration_closed'] . '</p>';
    require('incfiles/end.php');
    exit;
}

$captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : NULL;
$account = isset($_POST['account']) ? functions::checkin($_POST['account']) : '';
$reg_pass = isset($_POST['password']) ? trim($_POST['password']) : '';
$reg_name = isset($_POST['imname']) ? functions::checkin(mb_substr($_POST['imname'], 0, 30)) : '';
$reg_about = isset($_POST['about']) ? functions::checkin(mb_substr($_POST['about'], 0, 500)) : '';
$reg_sex = isset($_POST['sex']) ? functions::checkin(mb_substr(trim($_POST['sex']), 0, 1)) : '';

echo '<div class="phdr"><b>' . $lng['registration'] . '</b></div>';
if (isset($_POST['submit'])) {
    $error = array();

    // Check account
    if (empty($account)) {
        $error['login'][] = $lng_reg['error_nick_empty'];
    } elseif (mb_strlen($account) < 5 || mb_strlen($account) > 30) {
        $error['login'][] = $lng_reg['error_nick_lenght'];
    }

    if (preg_match('/[^\da-z.]|^[\d\.]|\.$|\.\.+/i', $account)) {
        $error['login'][] = $lng['error_wrong_symbols'];
    }

    // check password
    if (empty($reg_pass)) {
        $error['password'][] = $lng['error_empty_password'];
    } elseif (mb_strlen($reg_pass) < 6 || mb_strlen($reg_pass) > 32) {
        $error['password'][] = $lng['error_wrong_lenght'];
    }

    if (preg_match('/[^\dA-Za-z\!\@\#\$\%\^\&\*\(\)\_\+\-\=]/', $reg_pass)) {
        $error['password'][] = $lng['error_wrong_symbols'];
    }

    // Check gender
    if ($reg_sex != 'm' && $reg_sex != 'f') {
        $error['sex'] = $lng_reg['error_sex'];
    }

    if (preg_match('/[^a-z\s]/', functions::bodau($reg_name, 1))) {
        $error['name'] = 'Họ và tên không hợp lệ';
    }

    // Check CAPTCHA
    if (!$captcha
        || !isset($_SESSION['code'])
        || mb_strlen($captcha) < 4
        || $captcha != $_SESSION['code']
    ) {
        $error['captcha'] = $lng['error_wrong_captcha'];
    }
    unset($_SESSION['code']);

    // Checking variables
    if (empty($error)) {
        // Check nick name is used?
        if (mysql_result(mysql_query('SELECT COUNT(*) FROM `users` WHERE REPLACE(`account`, ".", "") = "' . mysql_real_escape_string(str_replace('.', '', $account)) . '"'), 0)) {
            $error['login'][] = $lng_reg['error_nick_occupied'];
        }
    }
    if (empty($error)) {
        $preg = $set['mod_reg'] > 1 ? 1 : 0;
        $pass = md5(md5($reg_pass));
        mysql_query('INSERT INTO `users` SET
            `account` = "' . mysql_real_escape_string($account) . '",
            `password` = "' . mysql_real_escape_string($pass) . '",
            `imname` = "' . $reg_name . '",
            `about` = "' . mysql_real_escape_string($reg_about) . '",
            `sex` = "' . $reg_sex . '",
            `rights` = "0",
            `ip` = "' . core::$ip . '",
            `ip_via_proxy` = "' . core::$ip_via_proxy . '",
            `browser` = "' . mysql_real_escape_string($agn) . '",
            `datereg` = "' . time() . '",
            `lastdate` = "' . time() . '",
            `sestime` = "' . time() . '",
            `preg` = "' . $preg . '",
            `set_user` = "",
            `set_mail` = ""
        ') or exit(__LINE__ . ': ' . mysql_error());
        $usid = mysql_insert_id();

        echo '<div class="menu"><p><h3>' . $lng_reg['you_registered'] . '</h3>' . $lng_reg['your_id'] . ': <b>' . $usid . '</b><br/>' . $lng_reg['your_login'] . ': <b>' . $account . '</b><br/>' . $lng_reg['your_password'] . ': <b>' . $reg_pass . '</b></p>';

        if ($set['mod_reg'] == 1) {
            echo '<p><span class="red"><b>Tài khoản của bạn sẽ sử dụng được sau khi Quản trị viên kích hoạt!</b></span></p>';
        } else {
            $_SESSION['uid'] = $usid;
            $_SESSION['ups'] = md5(md5($reg_pass));
            echo '<p><a href="' . SITE_URL . '">' . $lng_reg['enter'] . '</a></p>';
        }

        echo '</div>';
        require('incfiles/end.php');
        exit;
    }
}

// registration form
if ($set['mod_reg'] == 1) echo '<div class="rmenu"><p>' . $lng_reg['moderation_warning'] . '</p></div>';
echo '<form action="registration.php" method="post"><div class="gmenu">' .
    '<p><h3>' . $lng_reg['login'] . '</h3>' .
    (isset($error['login']) ? '<span class="red"><small>' . implode('<br />', $error['login']) . '</small></span><br />' : '') .
    '<input type="text" name="account" maxlength="15" value="' . htmlspecialchars($account) . '"' . (isset($error['login']) ? ' style="background-color: #FFCCCC"' : '') . ' autocomplete="off"/><br />' .
    '<small>' . $lng_reg['login_help'] . '</small></p>' .
    '<p><h3>' . $lng_reg['password'] . '</h3>' .
    (isset($error['password']) ? '<span class="red"><small>' . implode('<br />', $error['password']) . '</small></span><br />' : '') .
    '<input type="text" name="password" maxlength="20" value="' . htmlspecialchars($reg_pass) . '"' . (isset($error['password']) ? ' style="background-color: #FFCCCC"' : '') . '/><br/>' .
    '<small>' . $lng_reg['password_help'] . '</small></p>' .
    '<p><h3>' . $lng_reg['sex'] . '</h3>' .
    (isset($error['sex']) ? '<span class="red"><small>' . $error['sex'] . '</small></span><br />' : '') .
    '<select name="sex"' . (isset($error['sex']) ? ' style="background-color: #FFCCCC"' : '') . '>' .
    '<option value="?">-?-</option>' .
    '<option value="m"' . ($reg_sex == 'm' ? ' selected="selected"' : '') . '>' . $lng_reg['sex_m'] . '</option>' .
    '<option value="f"' . ($reg_sex == 'f' ? ' selected="selected"' : '') . '>' . $lng_reg['sex_w'] . '</option>' .
    '</select></p></div>' .
    '<div class="menu">' .
    '<p><h3>' . $lng_reg['name'] . '</h3>' .
    (isset($error['name']) ? '<span class="red"><small>' . $error['name'] . '</small></span><br />' : '') .
    '<input type="text" name="imname" maxlength="30" value="' . htmlspecialchars($reg_name) . '"' . (isset($error['name']) ? ' style="background-color: #FFCCCC"' : '') . ' /><br />' .
    '<small>' . $lng_reg['name_help'] . '</small></p>' .
    '<p><h3>' . $lng_reg['about'] . '</h3>' .
    '<textarea rows="3" name="about">' . htmlspecialchars($reg_about) . '</textarea><br />' .
    '<small>' . $lng_reg['about_help'] . '</small></p></div>' .
    '<div class="gmenu"><p>' .
    '<h3>' . $lng_reg['captcha'] . '</h3>' .
    '<img src="' . SITE_URL . '/assets/captcha.php?r=' . rand(1000, 9999) . '" alt="' . $lng_reg['captcha'] . '" border="1"/><br />' .
    (isset($error['captcha']) ? '<span class="red"><small>' . $error['captcha'] . '</small></span><br />' : '') .
    '<input type="text" size="5" maxlength="5"  name="captcha" ' . (isset($error['captcha']) ? ' style="background-color: #FFCCCC"' : '') . '/><br />' .
    '<small>' . $lng_reg['captcha_help'] . '</small></p>' .
    '<p><input type="submit" name="submit" value="' . $lng_reg['registration'] . '"/></p></div></form>' .
    '<div class="notif"><small>' . $lng_reg['registration_terms'] . '</small></div>';

require('incfiles/end.php');