<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$lng_set = core::load_lng('settings');
$textl = $lng['settings'];
require('../incfiles/head.php');

// Check right
if ($user['id'] != $user_id) {
    echo functions::display_error($lng['access_forbidden']);
    require('../incfiles/end.php');
    exit;
}

$menu = array(
    (!$mod ? '<b>' . $lng['common_settings'] . '</b>' : '<a href="profile.php?act=settings">' . $lng['common_settings'] . '</a>'),
    ($mod == 'mail' ? '<b>' . $lng['mail'] . '</b>' : '<a href="profile.php?act=settings&mod=mail">' . $lng['mail'] . '</a>')
);

// custom settings
switch ($mod) {
    case 'mail':
        echo '<div class="phdr"><b>' . $lng['settings'] . '</b> | ' . $lng['mail'] . '</div>' .
            '<div class="topmenu">' . functions::display_menu($menu) . '</div>';

        $set_mail_user = unserialize($datauser['set_mail']);
        if (isset($_POST['submit'])) {
            $set_mail_user['access'] = isset($_POST['access']) && $_POST['access'] >= 0 && $_POST['access'] <= 2 ? abs(intval($_POST['access'])) : 0;
            mysql_query("UPDATE `users` SET `set_mail` = '" . mysql_real_escape_string(serialize($set_mail_user)) . "' WHERE `id` = '$user_id'");
        }

        echo '<form method="post" action="profile.php?act=settings&amp;mod=mail">' .
            '<div class="menu">' .
            '<strong>' . $lng_profile['write_messages'] . '</strong><br />' .
            '<input type="radio" value="0" name="access" ' . (!$set_mail_user['access'] ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['write_all'] . '<br />' .
            '<input type="radio" value="1" name="access" ' . ($set_mail_user['access'] == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['write_contacts'] . '<br />' .
            '<input type="radio" value="2" name="access" ' . ($set_mail_user['access'] == 2 ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['write_friends'] .
            '<br/><p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></div></form>' .
            '<div class="phdr">&#160;</div>';
        break;

    default:
        echo '<div class="phdr"><b>' . $lng['settings'] . '</b> | ' . $lng['common_settings'] . '</div>' .
            '<div class="topmenu">' . functions::display_menu($menu) . '</div>';
        if (isset($_POST['submit'])) {
            $set_user['smileys'] = isset($_POST['smileys']);
            $set_user['direct_url'] = isset($_POST['direct_url']);
            $set_user['field_h'] = isset($_POST['field_h']) ? abs(intval($_POST['field_h'])) : 3;
            $set_user['kmess'] = isset($_POST['kmess']) ? abs(intval($_POST['kmess'])) : 10;
            if ($set_user['kmess'] < 5)
                $set_user['kmess'] = 5;
            elseif ($set_user['kmess'] > 99)
                $set_user['kmess'] = 99;
            if ($set_user['field_h'] < 1)
                $set_user['field_h'] = 1;
            elseif ($set_user['field_h'] > 9)
                $set_user['field_h'] = 9;

            // Set the language
            $lng_select = isset($_POST['iso']) ? trim($_POST['iso']) : false;
            if ($lng_select && array_key_exists($lng_select, core::$lng_list)) {
                $set_user['lng'] = $lng_select;
                unset($_SESSION['lng']);
            }

            // Save settings
            mysql_query("UPDATE `users` SET `set_user` = '" . mysql_real_escape_string(serialize($set_user)) . "' WHERE `id` = '$user_id'");
            $_SESSION['set_ok'] = 1;
            header('Location: profile.php?act=settings');
            exit;
        } elseif (isset($_GET['reset']) || empty($set_user)) {
            // set the default settings
            mysql_query("UPDATE `users` SET `set_user` = '' WHERE `id` = '$user_id'");
            $_SESSION['reset_ok'] = 1;
            header('Location: profile.php?act=settings');
            exit;
        }

        // Entry form customization
        if (isset($_SESSION['set_ok'])) {
            echo '<div class="rmenu">' . $lng['settings_saved'] . '</div>';
            unset($_SESSION['set_ok']);
        }
        if (isset($_SESSION['reset_ok'])) {
            echo '<div class="rmenu">' . $lng['settings_default'] . '</div>';
            unset($_SESSION['reset_ok']);
        }
        echo '<form action="profile.php?act=settings" method="post" >' .
            '<div class="menu"><p><h3>' . $lng['system_functions'] . '</h3>' .
            '<input name="direct_url" type="checkbox" value="1" ' . (core::$user_set['direct_url'] ? 'checked="checked"' : '') . ' />&#160;' . $lng['direct_url'] . '<br />' .
            '<input name="smileys" type="checkbox" value="1" ' . (core::$user_set['smileys'] ? 'checked="checked"' : '') . ' />&#160;' . $lng['smileys'] . '<br/>' .
            '</p><p><h3>' . $lng['text_input'] . '</h3>' .
            '<input type="text" name="field_h" size="2" maxlength="1" value="' . core::$user_set['field_h'] . '"/> ' . $lng['field_height'] . ' (1-9)<br />';
        echo '</p><p><h3>' . $lng['apperance'] . '</h3>' .
            '<input type="text" name="kmess" size="2" maxlength="2" value="' . core::$user_set['kmess'] . '"/> ' . $lng['lines_on_page'] . ' (5-99)' .
            '</p>';

        // Выбор языка
        if (count(core::$lng_list) > 1) {
            echo '<p><h3>' . $lng['language_select'] . '</h3>';
            $user_lng = isset(core::$user_set['lng']) ? core::$user_set['lng'] : core::$lng_iso;
            foreach (core::$lng_list as $key => $val) {
                echo '<div><input type="radio" value="' . $key . '" name="iso" ' . ($key == $user_lng ? 'checked="checked"' : '') . '/>&#160;' .
                    (file_exists('../images/flags/' . $key . '.gif') ? '<img src="' . SITE_URL . '/images/flags/' . $key . '.gif" alt=""/>&#160;' : '') .
                    $val .
                    ($key == core::$system_set['lng'] ? ' <small class="red">[' . $lng['default'] . ']</small>' : '') .
                    '</div>';
            }
            echo '</p>';
        }

        echo '<p><input type="submit" name="submit" value="' . $lng['save'] . '"/></p></div></form>' .
            '<div class="phdr"><a href="profile.php?act=settings&amp;reset">' . $lng['reset_settings'] . '</a></div>';
}
