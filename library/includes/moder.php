<?php

 
defined('_MRKEN_CMS') or die('Error: restricted access');

$lng_gal = core::load_lng('gallery');

  $obj = new Hashtags($id);  
  $type = isset($_GET['type']) && in_array($_GET['type'], array('dir', 'article')) ? $_GET['type'] : redir404();
  
  $author = ($type == 'article' && mysql_result(mysql_query("SELECT `uploader_id` FROM `library_texts` WHERE `id` = " . $id), 0) == $user_id) ? 1 : 0;
  $adm || $author ?: redir404();
  
  if (isset($_POST['submit'])) {
    switch ($type) {
    case 'dir':
      $sql = "UPDATE `library_cats` SET `name`='" . mysql_real_escape_string($_POST['name']) . "', `description`='" . mysql_real_escape_string($_POST['description']) . "' " . (isset($_POST['move']) && mysql_result(mysql_query("SELECT count(*) FROM `library_cats`") , 0) > 1 ? ', `parent`=' . intval($_POST['move']) : '') . (isset($_POST['dir']) ? ', `dir`=' . intval($_POST['dir']) : '') . (isset($_POST['user_add']) ? ' , `user_add`=' . intval($_POST['user_add']) : '') . " WHERE `id`=" . $id;
      break;

    case 'article':
      $obj->del_tags();
      if (isset($_POST['tags'])) {
        $obj->del_cache();  
        $tags = array_map('trim', explode(',', $_POST['tags']));
        if (sizeof($tags > 0)) {
            $obj->add_tags($tags);
        }
      }
    
      $image = isset($_FILES['image']['tmp_name']) ? $_FILES['image'] : '';
      require ('../incfiles/lib/class.upload.php');

      $handle = new upload($image);
      if ($handle->uploaded) {
        // Обрабатываем фото
        $handle->file_new_name_body = $id;
        $handle->allowed = array(
          'image/jpeg',
          'image/png'
        );
        $handle->file_max_size = 1024 * $set['flsz'];
        $handle->file_overwrite = true;
        $handle->image_x = $handle->image_src_x;
        $handle->image_y = $handle->image_src_y;
        $handle->image_convert = 'png';
        $handle->process('../files/library/orig/');
        $err_image = $handle->error;
        $handle->file_new_name_body = $id;
        $handle->file_overwrite = true;
        if ($handle->image_src_y > 240) {
          $handle->image_resize = true;
          $handle->image_x = 240;
          $handle->image_y = $handle->image_src_y * (240 / $handle->image_src_x);
        }
        else {
          $handle->image_x = $handle->image_src_x;
          $handle->image_y = $handle->image_src_y;
        }
        $handle->image_convert = 'png';
        $handle->process('../files/library/big/');
        $err_image = $handle->error;
        $handle->file_new_name_body = $id;
        $handle->file_overwrite = true;
        $handle->image_resize = true;
        $handle->image_x = 32;
        $handle->image_y = 32;
        $handle->image_convert = 'png';
        $handle->process('../files/library/small/');
        if ($err_image) {
          echo functions::display_error($lng_gal['error_uploading_photo']);
        }
        $handle->clean();
      }
      $sql = "UPDATE `library_texts` SET `name`='" . mysql_real_escape_string($_POST['name']) . "', " . ($_POST['text'] != 'do_not_change' ? " `text`='" . mysql_real_escape_string($_POST['text']) . "', " : '') . " " . (isset($_POST['move']) ? '`cat_id`=' . intval($_POST['move']) . ', ' : '') . " `announce`='" . mysql_real_escape_string(mb_substr(trim($_POST['announce']), 0, 500)) . "' " . ($adm ? ", `count_views`=" . intval($_POST['count_views']) . ", `premod`=" . intval($_POST['premod']) . ", `comments`=" . (isset($_POST['comments']) ? intval($_POST['comments']) : 0) : '') . " WHERE `id`=" . $id;
      break;
    }
    if (mysql_query($sql)) {
      echo '<div>' . $lng_lib['changed'] . '</div><div><a href="?do=' . ($type == 'dir' ? 'dir' : 'text') . '&amp;id=' . $id . '">' . $lng['back'] . '</a></div>' . PHP_EOL;
    }
  }
  else {
    $child_dir = new Tree($id);
    $childrens = $child_dir->get_childs_dir()->result();
    $sqlsel = mysql_query("SELECT " . ($type == 'dir' ? '`id`, `parent`' : '`id`') . ", `name` FROM `library_cats` WHERE `dir`=" . ($type == 'dir' ? 1 : 0) . ' ' . ($type == 'dir' && sizeof($childrens) ? 'AND `id` NOT IN(' . implode(', ', $childrens) . ')' : ''));
    $row = mysql_fetch_assoc(mysql_query("SELECT * FROM `" . ($type == 'article' ? 'library_texts' : 'library_cats') . "` WHERE `id`=" . $id));
    $empty = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_cats` WHERE `parent`=" . $id) , 0) > 0 || mysql_result(mysql_query("SELECT COUNT(*) FROM `library_texts` WHERE `cat_id`=" . $id) , 0) > 0 ? 0 : 1;
    
    if (!$row) {
      redir404();
    }
    
    echo '<div class="phdr"><strong><a href="?">' . $lng['library'] . '</a></strong> | '
    . ($type == 'dir' ? $lng_lib['edit_category'] : $lng_lib['edit_article'])
    . '</div>'
    . '<form name="form" enctype="multipart/form-data" action="?act=moder&amp;type=' . $type . '&amp;id=' . $id . '" method="post">'
    . '<div class="menu">'
    . ($type == 'article' ? (file_exists('../files/library/big/' . $id . '.png') 
    ? '<div><img src="' . SITE_URL . '/files/library/big/' . $id . '.png" alt="screen" />' . '</div>'
    . '<div class="alarm"><a href="?act=del&amp;type=image&amp;id=' . $id . '">Удалить обложку</a></div>'
    : '')
    . '<h3>' . $lng_gal['upload_photo'] . '</h3>'
    . '<div><input name="image" type="file" /></div>'
    . '<h3>' . $lng['title'] . '</h3>' : '')
    . '<div><input type="text" name="name" value="' . functions::checkout($row['name']) . '" /></div>'
    . ($type == 'dir' ? '<h3>' . $lng_lib['add_dir_descriptions'] . '</h3>'
    . '<div><textarea name="description" rows="4" cols="20">' . functions::checkout($row['description']) . '</textarea></div>' : '')
    . ($type == 'article'
    ? '<h3>' . $lng_lib['announce'] . '</h3><div><textarea rows="2" cols="20" name="announce">' . functions::checkout($row['announce'])
    . '</textarea></div>'
    : '')
    . ($type == 'article' && mb_strlen($row['text']) < 500000
    ? '<h3>' . $lng['text'] . '</h3><div>' . bbcode::auto_bb('form', 'text') . '<textarea rows="5" cols="20" name="text">' . functions::checkout($row['text'])
    . '</textarea></div>'
    : ($type == 'article' && mb_strlen($row['text']) > 500000
    ? '<div class="alarm">' . $lng_lib['large_text'] . '</div><input type="hidden" name="text" value="do_not_change" /></div>'
    : ''))
    . ($type == 'article' 
    ? '<h3>' . $lng_lib['tags'] . '</h3><div><input name="tags" type="text" value="' . functions::checkout($obj->get_all_stat_tags()) . '" /></div>'
    : '');
    if ($adm) {
    if (mysql_num_rows($sqlsel) > 1) { 
        echo '<h3>' . $lng_lib['move_dir'] . '</h3>'
        . '<div><select name="move">'
        . ($type == 'dir' 
        ? '<option ' . ($type == 'dir' && $row['parent'] == 0 
        ? 'selected="selected"'
        : '')
        . ' value="0">' . $lng_lib['root'] . '</option>'
        : '');
        while ($res = mysql_fetch_assoc($sqlsel)) {
            if ($row['name'] != $res['name']) {
                echo '<option '
                . (($type == 'dir' && $row['parent'] == $res['id']) || ($type == 'article' && $row['cat_id'] == $res['id'])
                ? 'selected="selected" '
                : '')
                . 'value="' . $res['id'] . '">' . functions::checkout($res['name']) . '</option>';
            }
        }
    echo '</select></div>';
    }
    echo (($type == 'dir' && $empty) 
    ? '<h3>' . $lng_lib['category_type'] . '</h3><div><input type="radio" name="dir" value="1" '
    . ($row['dir'] == 1 
    ? 'checked="checked"' 
    : '') . ' />' . $lng_lib['categories'] . '</div>'
    . '<div><input type="radio" name="dir" value="0" ' . ($row['dir'] == 0 ? 'checked="checked"' : '') . ' />' . $lng_lib['articles'] . '</div>' : '')
    . ($type == 'dir' && $row['dir'] == 0
    ? '<div>' . $lng_lib['allow_to_add'] . '</div><div><input type="radio" name="user_add" value="1" '
    . ($row['user_add'] == 1 ? 'checked="checked"' : '') . ' /> ' . $lng_lib['_yes'] . '</div><div><input type="radio" name="user_add" value="0" '
    . ($row['user_add'] == 0 ? 'checked="checked"' : '') . ' /> ' . $lng_lib['_no'] . '</div>' : '')
    . ($type == 'article' ? '<div class="' . ($row['premod'] > 0 ? 'green' : 'red') . '"><input type="checkbox" name="premod" value="1" ' . ($row['premod'] > 0 
    ? 'checked="checked"' : '') . '/> ' . $lng_lib['verified'] . '</div>'
    . '<div class="' . ($row['comments'] > 0 ? 'green' : 'red') . '"><input type="checkbox" name="comments" value="1" '
    . ($row['comments'] > 0 ? 'checked="checked"' : '') . ' /> ' . $lng_lib['comment_article'] . '</div>'
    . '<div class="rmenu">' 
#    . '<h3>' . $lng['author'] . '</h3>'
#    . '<div><input type="text" name="author" value="' . functions::checkout($row['uploader']) . '" /></div>' . PHP_EOL 
    . '<h3>' . $lng_lib['reads'] 
    . '</h3><div><input type="text" name="count_views" value="' . intval($row['count_views']) . '" /></div></div>' . PHP_EOL : '');
    }
    echo '<div class="bmenu"><input type="submit" name="submit" value="' . $lng['save'] . '" />' 
    . '</div></div></form>' . PHP_EOL 
    . '<p><a href="?do=' . ($type == 'dir' ? 'dir' : 'text') . '&amp;id=' . $id . '">' . $lng['back'] . '</a></p>' . PHP_EOL;
  }