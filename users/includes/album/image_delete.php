<?php



defined('_MRKEN_CMS') or die('Error: restricted access');

require('../incfiles/head.php');

/*
-----------------------------------------------------------------
Удалить картинку
-----------------------------------------------------------------
*/
if ($img && $user['id'] == $user_id || $rights >= 6) {
    $req = mysql_query("SELECT * FROM `cms_album_files` WHERE `id` = '$img' AND `user_id` = '" . $user['id'] . "' LIMIT 1");
    if (mysql_num_rows($req)) {
        $res = mysql_fetch_assoc($req);
        $album = $res['album_id'];
        echo '<div class="phdr"><a href="album.php?act=show&amp;al=' . $album . '&amp;user=' . $user['id'] . '"><b>' . $lng['photo_album'] . '</b></a> | ' . $lng_profile['image_delete'] . '</div>';
        //TODO: Сделать проверку, чтоб администрация не могла удалять фотки старших по должности
        if (isset($_POST['submit'])) {
            // Удаляем файлы картинок
            @unlink('../files/users/album/' . $user['id'] . '/' . $res['img_name']);
            @unlink('../files/users/album/' . $user['id'] . '/' . $res['tmb_name']);
            // Удаляем записи из таблиц
            mysql_query("DELETE FROM `cms_album_files` WHERE `id` = '$img'");
            mysql_query("DELETE FROM `cms_album_votes` WHERE `file_id` = '$img'");
            mysql_query("OPTIMIZE TABLE `cms_album_votes`");
            mysql_query("DELETE FROM `cms_album_comments` WHERE `sub_id` = '$img'");
            mysql_query("OPTIMIZE TABLE `cms_album_comments`");
            header('Location: album.php?act=show&al=' . $album . '&user=' . $user['id']);
            exit;
        } else {
            echo '<div class="rmenu"><form action="album.php?act=image_delete&amp;img=' . $img . '&amp;user=' . $user['id'] . '" method="post">' .
                '<p>' . $lng_profile['image_delete_warning'] . '</p>' .
                '<p><input type="submit" name="submit" value="' . $lng['delete'] . '"/></p>' .
                '</form></div>' .
                '<div class="phdr"><a href="album.php?act=show&amp;al=' . $album . 'user=' . $user['id'] . '">' . $lng['cancel'] . '</a></div>';
        }
    } else {
        echo functions::display_error($lng['error_wrong_data']);
    }
}