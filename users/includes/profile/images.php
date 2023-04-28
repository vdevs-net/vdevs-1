<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = $lng_profile['profile_edit'];
require('../incfiles/head.php');
require('../incfiles/lib/class.upload.php');
// Check right
if (($user_id != $user['id'] && $rights < 7) || $user['rights'] > $datauser['rights']) {
    echo display_error($lng_profile['error_rights']);
    require('../incfiles/end.php');
    exit;
}
switch ($mod) {
    case 'avatar':
        // Change avatar
        echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '"><b>' . $lng['profile'] . '</b></a> | ' . $lng_profile['upload_avatar'] . '</div>';
        if (isset($_POST['submit'])) {
            $handle = new upload($_FILES['imagefile']);
            if ($handle->uploaded) {
                // Обрабатываем фото
                $handle->file_new_name_body = $user['id'];
                $handle->allowed = array(
                    'image/jpeg',
                    'image/png'
                );
                $handle->file_max_size = 1024 * $set['flsz'];
                $handle->file_overwrite = true;
                $handle->image_resize = true;
                $handle->image_ratio_crop = true;
                $handle->image_x = 32;
                $handle->image_y = 32;
                $handle->image_convert = 'png';
                $handle->process('../files/users/avatar/');
                if ($handle->processed) {
                    echo '<div class="gmenu"><p>' . $lng_profile['avatar_uploaded'] . '<br />' .
                        '<a href="profile.php?act=edit&amp;user=' . $user['id'] . '">' . $lng['continue'] . '</a></p></div>' .
                        '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '">' . $lng['profile'] . '</a></div>';
                } else {
                    echo functions::display_error($handle->error);
                }
                $handle->clean();
            }
        } else {
            echo'<form enctype="multipart/form-data" method="post" action="profile.php?act=images&amp;mod=avatar&amp;user=' . $user['id'] . '">' .
                '<div class="menu"><p>' . $lng_profile['select_image'] . ':<br />' .
                '<input type="file" name="imagefile" value="" />' .
                '<input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $set['flsz']) . '" /></p>' .
                '<p><input type="submit" name="submit" value="' . $lng_profile['upload'] . '" />' .
                '</p></div></form>' .
                '<div class="notif"><small>' . $lng_profile['select_image_help'] . ' ' . $set['flsz'] . ' kb.<br />' . $lng_profile['select_image_help_2'] . '<br />' . $lng_profile['select_image_help_3'] .'</small></div>';
        }
        break;

    case 'up_photo':
        echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '"><b>' . $lng['profile'] . '</b></a> | ' . $lng_profile['upload_photo'] . '</div>';
        if (isset($_POST['submit'])) {
            $handle = new upload($_FILES['imagefile']);
            if ($handle->uploaded) {
                // Обрабатываем фото
                $handle->file_new_name_body = $user['id'];
                $handle->allowed = array(
                    'image/jpeg',
                    'image/png'
                );
                $handle->file_max_size = 1024 * $set['flsz'];
                $handle->file_overwrite = true;
                $handle->image_resize = true;
                $handle->image_ratio_crop = true;
                $handle->image_x = 320;
                $handle->image_y = 240;
                $handle->image_convert = 'jpg';
                $handle->process('../files/users/photo/');
                if ($handle->processed) {
                    // Create thumbnail
                    $handle->file_new_name_body = $user['id'] . '_small';
                    $handle->file_overwrite = true;
                    $handle->image_resize = true;
                    $handle->image_x = 100;
                    $handle->image_ratio_y = true;
                    $handle->image_convert = 'jpg';
                    $handle->process('../files/users/photo/');
                    if ($handle->processed) {
                        echo '<div class="gmenu"><p>' . $lng_profile['photo_uploaded'] . '<br /><a href="profile.php?act=edit&amp;user=' . $user['id'] . '">' . $lng['continue'] . '</a></p></div>';
                        echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '">' . $lng['profile'] . '</a></div>';
                    } else {
                        echo functions::display_error($handle->error);
                    }
                } else {
                    echo functions::display_error($handle->error);
                }
                $handle->clean();
            }
        } else {
            echo '<form enctype="multipart/form-data" method="post" action="profile.php?act=images&amp;mod=up_photo&amp;user=' . $user['id'] . '"><div class="menu"><p>' . $lng_profile['select_image'] . ':<br />' .
                '<input type="file" name="imagefile" value="" />' .
                '<input type="hidden" name="MAX_FILE_SIZE" value="' . (1024 * $set['flsz']) . '" /></p>' .
                '<p><input type="submit" name="submit" value="' . $lng_profile['upload'] . '" /></p>' .
                '</div></form>' .
                '<div class="phdr"><small>' . $lng_profile['select_image_help'] . ' ' . $set['flsz'] . 'kb.<br />' . $lng_profile['select_image_help_5'] . '<br />' . $lng_profile['select_image_help_3'] . '</small></div>';
        }
        break;
}
?>