<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 7) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}

echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['users_clean'] . '</div>';

switch ($mod) {
    case 1:
        // Получаем список ID "мертвых" профилей
        $req = mysql_query("SELECT `id`
            FROM `users`
            WHERE `datereg` < '" . (time() - 2592000 * 6) . "'
            AND `lastdate` < '" . (time() - 2592000 * 5) . "'
            AND `postforum` = '0'
            AND `komm` < '10'
        ");

        if (mysql_num_rows($req)) {
            $del = new CleanUser;

            // Удаляем всю информацию
            while ($res = mysql_fetch_assoc($req)) {
                $del->removeAlbum($res['id']);      // Удаляем личные Фотоальбомы
                $del->removeGuestbook($res['id']);  // Удаляем личную Гостевую
                $del->removeMail($res['id']);       // Удаляем почту
                $del->cleanComments($res['id']);    // Удаляем комментарии
                $del->removeUser($res['id']);       // Удаляем пользователя
                mysql_query("DELETE FROM `cms_forum_rdm` WHERE `user_id` = '" . $res['id'] . "'");
            }

            mysql_query("
                OPTIMIZE TABLE
                `users`,
                `cms_album_cat,
                `cms_album_files`,
                `cms_album_comments`,
                `cms_album_downloads`,
                `cms_album_views`,
                `cms_album_votes`,
                `cms_mail`,
                `cms_contact`,
                `cms_forum_rdm`
            ");
        }

        echo '<div class="rmenu"><p>' . $lng['dead_profiles_deleted'] . '</p><p><a href="index.php">' . $lng['continue'] . '</a></p></div>';
        break;

    default:
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `users`
            WHERE `datereg` < '" . (time() - 2592000 * 6) . "'
            AND `lastdate` < '" . (time() - 2592000 * 5) . "'
            AND `postforum` = '0'
            AND `komm` < '10'"), 0);
        echo '<div class="menu">' .
            '<form action="index.php?act=usr_clean&amp;mod=1" method="post">' .
            '<p><h3>' . $lng['dead_profiles'] . '</h3>' . $lng['dead_profiles_desc'] . '</p>' .
            '<p>' . $lng['total'] . ': <b>' . $total . '</b></p>' .
            '<p><input type="submit" name="submit" value="' . $lng['delete'] . '"/></p></form></div>' .
            '<div class="phdr"><a href="index.php">' . $lng['back'] . '</a></div>';
}
