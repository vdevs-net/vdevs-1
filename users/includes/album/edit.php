<?php



defined('_MRKEN_CMS') or die('Error: restricted access');

require('../incfiles/head.php');

/*
-----------------------------------------------------------------
Создать / изменить альбом
-----------------------------------------------------------------
*/
if ($user['id'] == $user_id && empty($ban) || $rights >= 7) {
    if ($al) {
        $req = mysql_query("SELECT * FROM `cms_album_cat` WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'");
        if (mysql_num_rows($req)) {
            echo '<div class="phdr"><b>' . $lng_profile['album_edit'] . '</b></div>';
            $res = mysql_fetch_assoc($req);
            $name = htmlspecialchars($res['name']);
            $description = htmlspecialchars($res['description']);
            $password = htmlspecialchars($res['password']);
            $access = $res['access'];
        } else {
            echo functions::display_error($lng['error_wrong_data']);
            require('../incfiles/end.php');
            exit;
        }
    } else {
        echo '<div class="phdr"><b>' . $lng_profile['album_create'] . '</b></div>';
        $name = '';
        $description = '';
        $password = '';
        $access = 0;
    }
    $error = array ();
    if (isset($_POST['submit'])) {
        // Принимаем данные
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $access = isset($_POST['access']) ? abs(intval($_POST['access'])) : NULL;
        // Проверяем на ошибки
        if (empty($name))
            $error[] = $lng['error_empty_title'];
        elseif (mb_strlen($name) < 2 || mb_strlen($name) > 50)
            $error[] = $lng['title'] . ': ' . $lng['error_wrong_lenght'];
        $description = mb_substr($description, 0, 500);
        if ($access == 2 && empty($password))
            $error[] = $lng['error_empty_password'];
        elseif ($access == 2 && mb_strlen($password) < 3 || mb_strlen($password) > 15)
            $error[] = $lng['password'] . ': ' . $lng['error_wrong_lenght'];
        if ($access < 1 || $access > 4)
            $error[] = $lng['error_wrong_data'];
        // Проверяем, есть ли уже альбом с таким же именем?
        if (!$al && mysql_num_rows(mysql_query("SELECT * FROM `cms_album_cat` WHERE `name` = '" . mysql_real_escape_string($name) . "' AND `user_id` = '" . $user['id'] . "' LIMIT 1")))
            $error[] = $lng_profile['error_album_exists'];
        if (!$error) {
            if ($al) {
                // Изменяем данные в базе
                mysql_query("UPDATE `cms_album_files` SET `access` = '$access' WHERE `album_id` = '$al' AND `user_id` = '" . $user['id'] . "'");
                mysql_query("UPDATE `cms_album_cat` SET
                    `name` = '" . mysql_real_escape_string($name) . "',
                    `description` = '" . mysql_real_escape_string($description) . "',
                    `password` = '" . mysql_real_escape_string($password) . "',
                    `access` = '$access'
                    WHERE `id` = '$al' AND `user_id` = '" . $user['id'] . "'
                ");
            } else {
                // Вычисляем сортировку
                $req = mysql_query("SELECT * FROM `cms_album_cat` WHERE `user_id` = '" . $user['id'] . "' ORDER BY `sort` DESC LIMIT 1");
                if (mysql_num_rows($req)) {
                    $res = mysql_fetch_assoc($req);
                    $sort = $res['sort'] + 1;
                } else {
                    $sort = 1;
                }
                // Заносим данные в базу
                mysql_query("INSERT INTO `cms_album_cat` SET
                    `user_id` = '" . $user['id'] . "',
                    `name` = '" . mysql_real_escape_string($name) . "',
                    `description` = '" . mysql_real_escape_string($description) . "',
                    `password` = '" . mysql_real_escape_string($password) . "',
                    `access` = '$access',
                    `sort` = '$sort'
                ");
            }
            echo '<div class="gmenu"><p>' . ($al ? $lng_profile['album_changed'] : $lng_profile['album_created']) . '<br />' .
                '<a href="album.php?act=list&amp;user=' . $user['id'] . '">' . $lng['continue'] . '</a></p></div>';
            require('../incfiles/end.php');
            exit;
        }
    }
    if ($error)
        echo functions::display_error($error);
    echo '<div class="menu">' .
        '<form action="album.php?act=edit&amp;user=' . $user['id'] . '&amp;al=' . $al . '" method="post">' .
        '<p><h3>' . $lng['title'] . '</h3>' .
        '<input type="text" name="name" value="' . functions::checkout($name) . '" maxlength="30" /><br />' .
        '<small>Min. 2, Max. 30</small></p>' .
        '<p><h3>' . $lng['description'] . '</h3>' .
        '<textarea name="description" rows="' . $set_user['field_h'] . '">' . functions::checkout($description) . '</textarea><br />' .
        '<small>' . $lng['not_mandatory_field'] . '<br />Max. 500</small></p>' .
        '<p><h3>' . $lng['password'] . '</h3>' .
        '<input type="text" name="password" value="' . functions::checkout($password) . '" maxlength="15" /><br />' .
        '<small>' . $lng_profile['access_help'] . '<br />Min. 3, Max. 15</small></p>' .
        '<p><h3>Ai có thể xem?</h3>' .
        '<input type="radio" name="access" value="4" ' . (!$access || $access == 4 ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['access_all'] . '<br />' .
        '<input type="radio" name="access" value="3" ' . ($access == 3 ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['access_friends'] . '<br />' .
        '<input type="radio" name="access" value="2" ' . ($access == 2 ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['access_by_password'] . '<br />' .
        '<input type="radio" name="access" value="1" ' . ($access == 1 ? 'checked="checked"' : '') . '/>&#160;' . $lng_profile['access_closed'] . '</p>' .
        '<p><input type="submit" name="submit" value="' . $lng['save'] . '" /></p>' .
        '</form></div>' .
        '<div class="phdr"><a href="album.php?act=list&amp;user=' . $user['id'] . '">' . $lng['cancel'] . '</a></div>';
}