<?php
define('_MRKEN_CMS', 1);

$headmod = 'login';
require('incfiles/core.php');

if ($user_id) {
    header('Location: ' . SITE_URL); exit;
} else {
    if (empty($_SESSION['ref'])) {
        $_SESSION['ref'] = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : SITE_URL . '/?_redir=login';
    }
    require('incfiles/head.php');
    echo '<div class="phdr"><b>' . $lng['login'] . '</b></div>';
        $error = array();
        $captcha = FALSE;
        $show_captcha = FALSE;
        $display_form = 1;
        $user_login = isset($_POST['account']) ? functions::checkin($_POST['account']) : NULL;
        $user_pass = isset($_POST['password']) ? functions::checkin($_POST['password']) : NULL;
        $user_code = isset($_POST['code']) ? trim($_POST['code']) : NULL;
        if ($user_pass && !$user_login)
            $error[] = $lng['error_login_empty'];
        if ($user_login && !$user_pass)
            $error[] = $lng['error_empty_password'];
        if ($user_login && (mb_strlen($user_login) < 3 || mb_strlen($user_login) > 30))
            $error[] = $lng['nick'] . ': ' . $lng['error_wrong_lenght'];
        if ($user_pass && (mb_strlen($user_pass) < 6 || mb_strlen($user_pass) > 32))
            $error[] = $lng['password'] . ': ' . $lng['error_wrong_lenght'];
        if (!$error && $user_pass && $user_login) {
            // Check Database
            $req = mysql_query('SELECT * FROM `users` WHERE REPLACE(`account`, ".", "") = "' . mysql_real_escape_string(str_replace('.', '', $user_login)) . '" LIMIT 1');
            if (mysql_num_rows($req)) {
                $user = mysql_fetch_assoc($req);
                if ($user['failed_login'] > 2) {
                    if ($user_code) {
                        if (mb_strlen($user_code) > 3 && $user_code == $_SESSION['code']) {
                            // if captcha is match
                            unset($_SESSION['code']);
                            $captcha = TRUE;
                        } else {
                            // if not
                            unset($_SESSION['code']);
                            $error[] = $lng['error_wrong_captcha'];
                            $show_captcha = TRUE;
                        }
                    } else {
                        // Show CAPTCHA
                        $show_captcha = TRUE;
                        $error[] = 'Tài khoản của bạn đã đăng nhập sai quá nhiều lần. Vui lòng nhập mã bảo vệ!';
                    }
                }
                if ($user['failed_login'] < 3 || $captcha) {
                    if (md5(md5($user_pass)) == $user['password']) {
                        // If a successful login
                        $display_form = 0;
                        if (!$user['preg']) {
                            // If the registration is not confirmed
                            echo '<div class="rmenu">Tài khoản của bạn chưa được kích hoạt!</div>';
                            mysql_query('UPDATE `users` SET `failed_login` = "0" WHERE `id` = "' . $user['id'] . '"');
                        } else {
                            // If all checks are successful, we prepare the entrance to the site
                            if (isset($_POST['mem'])) {
                                // setting COOKIE
                                $cuid = base64_encode($user['id']);
                                $cups = md5(md5($user_pass));
                                setcookie('cuid', $cuid, time() + 3600 * 24 * 365);
                                setcookie('cups', $cups, time() + 3600 * 24 * 365);
                            }
                            // 	Setting the session data
                            $_SESSION['uid'] = $user['id'];
                            $_SESSION['ups'] = md5(md5($user_pass));
                            mysql_query('UPDATE `users` SET `failed_login` = "0", `sestime` = "' . time() . '" WHERE `id` = "' . $user['id'] . '"');
                            $next = $_SESSION['ref'];
                            unset($_SESSION['ref']);
                            header('Location: ' . $next.''); exit;
                        }
                    } else {
                        // If the login failed
                        if ($user['failed_login'] < 3) {
                            // Added to the counter of failed logins
                            mysql_query("UPDATE `users` SET `failed_login` = '" . ($user['failed_login'] + 1) . "' WHERE `id` = '" . $user['id'] . "'");
                        }
                        if($user['failed_login'] >= 2) $show_captcha = TRUE;
                        $error[] = $lng['authorisation_not_passed'];
                    }
                }
            } else {
                $error[] = $lng['authorisation_not_passed'];
            }
        }
        if ($display_form) {
            if ($error)
                echo functions::display_error($error);

            if ($set['site_access'] == 0 || $set['site_access'] == 1) {
                if ($set['site_access'] == 0) {
                    echo '<div class="rmenu">' . $lng['info_only_sv'] . '</div>';
                } elseif ($set['site_access'] == 1) {
                    echo '<div class="rmenu">' . $lng['info_only_adm'] . '</div>';
                }
            }

            echo '<div class="menu"><form action="login.php" method="post"><p>' . $lng['login_name'] . ':<br/>' .
                '<input type="text" name="account" value="' . htmlspecialchars($user_login) . '" maxlength="20"/>' .
                '<br/>' . $lng['password'] . ':<br/>' .
                '<input type="password" name="password" maxlength="20"/></p>' .
                ($show_captcha ? '<p>' . $lng['verifying_code'] . '<br/><img src="' . SITE_URL . '/assets/captcha.php?r=' . rand(1000, 9999) . '" alt="' . $lng['verifying_code'] . '"/><br/><input type="text" size="5" maxlength="5"  name="code"/></p>' : '') .
                '<p><input type="checkbox" name="mem" value="1" checked="checked"/>' . $lng['remember'] . '</p>' .
                '<p><input type="submit" value="' . $lng['login'] . '"/></p>' .
                '</form></div>';
        }
}

require('incfiles/end.php');