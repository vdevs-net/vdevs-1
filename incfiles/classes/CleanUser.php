<?php
class CleanUser
{
    public function removeUser($clean_id)
    {
        // Удаляем историю нарушений
        mysql_query("DELETE FROM `cms_ban_users` WHERE `user_id` = '" . $clean_id . "'");
        // Удаляем историю IP
        mysql_query("DELETE FROM `cms_users_iphistory` WHERE `user_id` = '" . $clean_id . "'");
        // Удаляем пользователя
        mysql_query("DELETE FROM `users` WHERE `id` = '" . $clean_id . "'");
    }

    /**
     * Удаляем пользовательские альбомы
     *
     * @param $clean_id
     */
    public function removeAlbum($clean_id)
    {
        // Удаляем папку с файлами картинок
        $dir = ROOTPATH . 'files/users/album/' . $clean_id;
        if (is_dir($dir)) {
            $this->removeDir($dir);
        }

        // Чистим таблицы
        $req = mysql_query("SELECT `id` FROM `cms_album_files` WHERE `user_id` = '" . $clean_id . "'");
        if (mysql_num_rows($req)) {
            while ($res = mysql_fetch_assoc($req)) {
                mysql_query("DELETE FROM `cms_album_comments` WHERE `sub_id` = '" . $res['id'] . "'");
                mysql_query("DELETE FROM `cms_album_downloads` WHERE `file_id` = '" . $res['id'] . "'");
                mysql_query("DELETE FROM `cms_album_views` WHERE `file_id` = '" . $res['id'] . "'");
                mysql_query("DELETE FROM `cms_album_votes` WHERE `file_id` = '" . $res['id'] . "'");
            }
        }

        mysql_query("DELETE FROM `cms_album_cat` WHERE `user_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_album_files` WHERE `user_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_album_downloads` WHERE `user_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_album_views` WHERE `user_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_album_votes` WHERE `user_id` = '" . $clean_id . "'");
    }

    /**
     * Удаляем почту и контакты
     *
     * @param $clean_id
     */
    public function removeMail($clean_id)
    {
        // The user deletes a file from your mail
        $req = mysql_query("SELECT * FROM `cms_mail` WHERE (`user_id` = '" . $clean_id . "' OR `from_id` = '" . $clean_id . "') AND `file_name` != ''");

        if (mysql_num_rows($req)) {
            while ($res = mysql_fetch_assoc($req)) {
                // Remove mail files
                if (is_file(ROOTPATH . 'files/mail/' . $res['file_name'])) {
                    @unlink('../files/mail/' . $res['file_name']);
                }
            }
        }

        mysql_query("DELETE FROM `cms_mail` WHERE `user_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_mail` WHERE `from_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_contact` WHERE `user_id` = '" . $clean_id . "'");
        mysql_query("DELETE FROM `cms_contact` WHERE `from_id` = '" . $clean_id . "'");
    }

    public function cleanForum($clean_id)
    {
        // Скрываем темы на форуме
        mysql_query("UPDATE `forum` SET `close` = '1', `close_who` = 'SYSTEM' WHERE `type` = 't' AND `user_id` = '" . $clean_id . "'");
        // Скрываем посты на форуме
        mysql_query("UPDATE `forum` SET `close` = '1', `close_who` = 'SYSTEM' WHERE `type` = 'm' AND `user_id` = '" . $clean_id . "'");
        // Удаляем метки прочтения на Форуме
        mysql_query("DELETE FROM `cms_forum_rdm` WHERE `user_id` = '" . $clean_id . "'");
    }

    /**
     * Удаляем личную гостевую
     *
     * @param $clean_id
     */
    public function removeGuestbook($clean_id)
    {
        mysql_query("DELETE FROM `cms_users_guestbook` WHERE `sub_id` = '" . $clean_id . "'");
    }

    /**
     * Удаляем все комментарии пользователя
     *
     * @param $clean_id
     */
    public function cleanComments($clean_id)
    {
        $req = mysql_query("SELECT `account` FROM `users` WHERE `id` = " . $clean_id);
        if (mysql_num_rows($req)) {
            $res = mysql_fetch_assoc($req);
            // Удаляем из Библиотеки
            mysql_query("DELETE FROM `cms_library_comments` WHERE `user_id` = '" . $clean_id . "'");
            // Удаляем комментарии из личных гостевых
            mysql_query("DELETE FROM `cms_users_guestbook` WHERE `user_id` = '" . $clean_id . "'");
            // Удаляем комментарии из личных фотоальбомов
            mysql_query("DELETE FROM `cms_album_comments` WHERE `user_id` = '" . $clean_id . "'");
        }
    }

    /**
     * The recursive delete function directory files
     *
     * @param $dir
     */
    private function removeDir($dir)
    {
        if ($objs = glob($dir . "/*")) {
            foreach ($objs as $obj) {
                is_dir($obj) ? $this->removeDir($obj) : unlink($obj);
            }
        }
        rmdir($dir);
    }
}