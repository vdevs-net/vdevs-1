<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 9) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}

$user = false;
$error = false;
if ($id && $id != $user_id) {
    // Получаем данные юзера
    $req = mysql_query("SELECT * FROM `users` WHERE `id` = '$id'");
    if (mysql_num_rows($req)) {
        $user = mysql_fetch_assoc($req);
        if ($user['rights'] > $datauser['rights'])
            $error = $lng['error_usrdel_rights'];
    } else {
        $error = $lng['error_user_not_exist'];
    }
} else {
    $error = $lng['error_wrong_data'];
}
if (!$error) {
    // Считаем комментарии в библиотеке
    $comm_lib = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_library_comments` WHERE `user_id` = '" . $user['id'] . "'"), 0);
    // Считаем посты в личных гостевых
    $comm_gb = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_users_guestbook` WHERE `user_id` = '" . $user['id'] . "'"), 0);
    // Считаем комментарии в личных альбомах
    $comm_al = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_comments` WHERE `user_id` = '" . $user['id'] . "'"), 0);
    $comm_count = $comm_lib + $comm_gb + $comm_al;
    // Считаем созданные темы на Форуме
    $forumt_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `user_id` = '" . $user['id'] . "' AND `type` = 't' AND `close` != '1'"), 0);
    // Считаем посты на Форуме
    $forump_count = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `user_id` = '" . $user['id'] . "' AND `type` = 'm'  AND `close` != '1'"), 0);
    echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['user_del'] . '</div>';
    // Выводим краткие данные
    echo '<div class="user"><p>' . functions::display_user($user, array(
            'lastvisit' => 1,
            'iphist'    => 1
        )) . '</p></div>';

    switch ($mod) {

        case 'del':
            /*
            -----------------------------------------------------------------
            Удаляем личные данные
            -----------------------------------------------------------------
            */
            $del = new CleanUser;
            $del->removeAlbum($user['id']);         // Удаляем личные Фотоальбомы
            $del->removeGuestbook($user['id']);     // Удаляем личную Гостевую
            $del->removeMail($user['id']);          // Удаляем почту

            if (isset($_POST['comments'])) {
                $del->cleanComments($user['id']);   // Удаляем комментарии
            }

            if (isset($_POST['forum'])) {
                $del->cleanForum($user['id']);      // Чистим Форум
            }

            $del->removeUser($user['id']);          // Удаляем пользователя

            // Оптимизируем таблицы
            mysql_query("
                OPTIMIZE TABLE
                `cms_users_iphistory`,
                `cms_ban_users`,
                `cms_album_comments`,
                `cms_users_guestbook`,
                `cms_album_votes`,
                `cms_album_views`,
                `cms_album_downloads`,
                `cms_album_cat`,
                `cms_album_files`,
                `cms_forum_rdm`
            ");

            echo '<div class="rmenu"><p><h3>' . $lng['user_deleted'] . '</h3></p></div>';
            break;

        default:
            ////////////////////////////////////////////////////////////
            // Форма параметров удаления                              //
            ////////////////////////////////////////////////////////////
            echo '<form action="index.php?act=usr_del&amp;mod=del&amp;id=' . $user['id'] . '" method="post"><div class="menu"><p><h3>' . $lng['user_del_activity'] . '</h3>';
            if ($comm_count)
                echo '<div><input type="checkbox" value="1" name="comments" checked="checked" />&#160;' . $lng['comments'] . ' <span class="red">(' . $comm_count . ')</span></div>';
            if ($forumt_count || $forump_count) {
                echo '<div><input type="checkbox" value="1" name="forum" checked="checked" />&#160;' . $lng['forum'] . ' <span class="red">(' . $forumt_count . '&nbsp;/&nbsp;' . $forump_count . ')</span></div>';
                echo '<small><span class="gray">' . $lng['user_del_forumnote'] . '</span></small>';
            }
            echo '</p></div><div class="rmenu"><p>' . $lng['user_del_confirm'];
            echo '</p><p><input type="submit" value="' . $lng['delete'] . '" name="submit" />';
            echo '</p></div></form>';
            echo '<div class="phdr"><a href="' . SITE_URL . '/users/profile.php?user=' . $user['id'] . '">' . $lng['to_form'] . '</a></div>';
    }
} else {
    echo functions::display_error($error);
}
echo '<p><a href="index.php?act=users">' . $lng['users_list'] . '</a><br /><a href="index.php">' . $lng['admin_panel'] . '</a></p>';
?>