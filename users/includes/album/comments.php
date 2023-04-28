<?php

// Проверяем наличие комментируемого объекта
$req_obj = mysql_query("SELECT * FROM `cms_album_files` WHERE `id` = '$img'");
if (mysql_num_rows($req_obj)) {
    $res_obj = mysql_fetch_assoc($req_obj);

    /*
    -----------------------------------------------------------------
    Получаем данные владельца Альбома
    -----------------------------------------------------------------
    */
    $owner = functions::get_user($res_obj['user_id']);
    if (!$owner) {
        require('../incfiles/head.php');
        echo functions::display_error($lng['user_does_not_exist']);
        require('../incfiles/end.php');
        exit;
    }

    /*
    -----------------------------------------------------------------
    Показываем выбранную картинку
    -----------------------------------------------------------------
    */
    unset($_SESSION['ref']);
    $req_a = mysql_query("SELECT * FROM `cms_album_cat` WHERE `id` = '" . $res_obj['album_id'] . "'");
    $res_a = mysql_fetch_assoc($req_a);
    if (($res_a['access'] == 1 && $owner['id'] != $user_id && $rights < 7) || ($res_a['access'] == 2 && $rights < 7 && (!isset($_SESSION['ap']) || $_SESSION['ap'] != $res_a['password']) && $owner['id'] != $user_id)) {
        // Если доступ закрыт
        require('../incfiles/head.php');
        echo functions::display_error($lng['access_forbidden']) .
            '<div class="phdr"><a href="album.php?act=list&amp;user=' . $owner['id'] . '">' . $lng_profile['album_list'] . '</a></div>';
        require('../incfiles/end.php');
        exit;
    }
    $context_top = '<div class="phdr"><a href="album.php"><b>' . $lng['photo_albums'] . '</b></a> | ' .
        '<a href="album.php?act=list&amp;user=' . $owner['id'] . '">' . $lng['personal_2'] . '</a></div>' .
        '<div class="menu"><a href="album.php?act=show&amp;al=' . $res_obj['album_id'] . '&amp;img=' . $img . '&amp;user=' . $owner['id'] . '&amp;view"><img src="' . SITE_URL . '/files/users/album/' . $owner['id'] . '/' . $res_obj['tmb_name'] . '" /></a>';
    if (!empty($res_obj['description']))
        $context_top .= '<div class="gray">' . functions::checkout($res_obj['description'], 1, 0, 2) . '</div>';
    $context_top .= '<div class="sub">' .
        '<a href="profile.php?user=' . $owner['id'] . '"><b>' . $owner['account'] . '</b></a> | ' .
        '<a href="album.php?act=show&al=' . $res_a['id'] . '&user=' . $owner['id'] . '">' . functions::checkout($res_a['name']) . '</a>';
    if ($res_obj['access'] == 4 || $rights >= 7) {
        $context_top .= vote_photo($res_obj) .
            '<div class="gray">' . $lng['count_views'] . ': ' . $res_obj['views'] . ', ' . $lng['count_downloads'] . ': ' . $res_obj['downloads'] . '</div>' .
            '<a href="album.php?act=image_download&amp;img=' . $res_obj['id'] . '">' . $lng['download'] . '</a>';
    }
    $context_top .= '</div></div>';

    /*
    -----------------------------------------------------------------
    Параметры комментариев
    -----------------------------------------------------------------
    */
    $arg = array (
        'comments_table' => 'cms_album_comments',                              // Таблица с комментариями
        'object_table' => 'cms_album_files',                                   // Таблица комментируемых объектов
        'script' => 'album.php?act=comments',                                  // Имя скрипта (с параметрами вызова)
        'sub_id_name' => 'img',                                                // Имя идентификатора комментируемого объекта
        'sub_id' => $img,                                                      // Идентификатор комментируемого объекта
        'owner' => $owner['id'],                                               // Владелец объекта
        'owner_delete' => true,                                                // Возможность владельцу удалять комментарий
        'owner_reply' => true,                                                 // Возможность владельцу отвечать на комментарий
        'owner_edit' => false,                                                 // Возможность владельцу редактировать комментарий
        'title' => $lng['comments'],                                           // Название раздела
        'context_top' => $context_top,                                         // Выводится вверху списка
        'context_bottom' => ''                                                 // Выводится внизу списка
    );

    /*
    -----------------------------------------------------------------
    Ставим метку прочтения
    -----------------------------------------------------------------
    */
    if(core::$user_id == $user['id'] && $res_obj['unread_comments'])
        mysql_query("UPDATE `cms_album_files` SET `unread_comments` = '0' WHERE `id` = '$img' LIMIT 1");

    /*
    -----------------------------------------------------------------
    Показываем комментарии
    -----------------------------------------------------------------
    */
    require('../incfiles/head.php');
    $comm = new comments($arg);

    /*
    -----------------------------------------------------------------
    Обрабатываем метки непрочитанных комментариев
    -----------------------------------------------------------------
    */
    if($comm->added && core::$user_id != $owner['id'])
        mysql_query("UPDATE `cms_album_files` SET `unread_comments` = '1' WHERE `id` = '$img' LIMIT 1");
} else {
    echo functions::display_error($lng['error_wrong_data']);
}