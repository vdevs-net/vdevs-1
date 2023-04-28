<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

require('../incfiles/head.php');
if (!$id || !$user_id) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
echo '<div class="phdr">Đính kèm tập tin</div>';
// Check whether the user fills in the file and whether to place
$req = mysql_query('SELECT `type`,`user_id`,`refid`,`time` FROM `forum` WHERE `id` = "'. $id .'"');
if(!mysql_num_rows($req)){
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
$res = mysql_fetch_assoc($req);
if ($res['type'] != 'm' || $res['user_id'] != $user_id) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
$req2 = mysql_query('SELECT `refid`,`text` FROM `forum` WHERE `id` = "' . $res['refid'] . '" LIMIT 1');
$res2 = mysql_fetch_assoc($req2);
// Check the time limit allowed for file upload
if ($res['time'] < (time() - 300) && $rights < 9) {
    echo functions::display_error($lng_forum['upload_timeout'], '<a href="'.functions::bodau($res2['text']).'.' . $res['refid'] . '.html?page=' . $page . '">' . $lng['back'] . '</a>');
    require('../incfiles/end.php');
    exit;
}

// Check whether the file was already loaded
$exist = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_forum_files` WHERE `post` = "'. $id .'"'), 0);
if ($exist) {
    echo functions::display_error($lng_forum['error_file_uploaded'], '<a href="'.functions::bodau($res2['text']).'.' . $res['refid'] . '.html?page=' . $page . '">' . $lng['back'] . '</a>');
    require('../incfiles/end.php');
    exit;
}
if (isset($_POST['submit'])) {
    // Check whether the file is loaded with a browser
    $do_file = false;
    $file = '';
    if ($_FILES['fail']['size'] > 0) {
        // Check boot from a standard browser
        $do_file = true;
        $file = mb_strtolower($_FILES['fail']['name']);
        $fsize = $_FILES['fail']['size'];
    }
    // Processing of the file (if any), error checking
    if ($do_file) {
        // The list of valid file extensions.
        $al_ext = array_merge($ext_win, $ext_java, $ext_sis, $ext_doc, $ext_pic, $ext_arch, $ext_video, $ext_audio, $ext_other);
        $ext = explode(".", $file);
        $error = array();
        // Check for file size limit
        if ($fsize > 1024 * $set['flsz'])
            $error[] = $lng_forum['error_file_size'] . ' ' . $set['flsz'] . 'kb.';
        // Checking the file for the presence of only one extension
        if (count($ext) != 2)
            $error[] = $lng_forum['error_file_name'];
        // Validation of file extensions
        if (!in_array($ext[1], $al_ext))
            $error[] = $lng_forum['error_file_ext'] . ':<br />' . implode(', ', $al_ext);

        // Processing file name
        if(mb_strlen($ext[0]) == 0){
            $ext[0] = 'PhoNho_Net---NoName';
        }
        $ext[0] = str_replace(' ', '_', $ext[0]);
        $fname = 'PhoNho_Net---' . mb_substr($ext[0], 0, 32) . '.' . $ext[1];

        // Check for illegal characters
        if (preg_match('/[^\dA-z_\-.]/', $fname))
            $error[] = $lng_forum['error_file_symbols'];
        // Checking a file with the same name
        if (file_exists("../files/forum/attach/$fname")) {
            $fname = 'PhoNho_Net---' . mb_substr($ext[0], 0, 32) . '--' . time() . '.' . $ext[1];
        }

        // finishing
        if (!$error && $do_file) {
            // For a standard browser
            if ((move_uploaded_file($_FILES["fail"]["tmp_name"], "../files/forum/attach/$fname")) == true) {
                @chmod("$fname", 0777);
                @chmod("../files/forum/attach/$fname", 0777);
                echo '<div class="rmenu">' . $lng_forum['file_uploaded'] . '</div>';
            } else {
                $error[] = $lng_forum['error_upload_error'];
            }
        }

        if (!$error) {
            // Determine the type of file
            $ext = strtolower($ext[1]);
            if (in_array($ext, $ext_win)) $type = 1;
            elseif (in_array($ext, $ext_java)) $type = 2; elseif (in_array($ext, $ext_sis)) $type = 3; elseif (in_array($ext, $ext_doc)) $type = 4; elseif (in_array($ext, $ext_pic)) $type = 5; elseif (in_array($ext, $ext_arch)) $type = 6; elseif (in_array($ext, $ext_video)) $type = 7; elseif (in_array($ext, $ext_audio)) $type = 8; else $type = 9;

            // Identify the ID and sub-categories
            $req3 = mysql_query("SELECT `refid` FROM `forum` WHERE `id` = '" . $res2['refid'] . "' LIMIT 1");
            $res3 = mysql_fetch_assoc($req3);

            // Enter data into the database
            mysql_query("INSERT INTO `cms_forum_files` SET
                        `cat` = '" . $res3['refid'] . "',
                        `subcat` = '" . $res2['refid'] . "',
                        `topic` = '" . $res['refid'] . "',
                        `post` = '$id',
                        `time` = '" . $res['time'] . "',
                        `filename` = '" . mysql_real_escape_string($fname) . "',
                        `filetype` = '$type'
                    ");
        } else {
            echo functions::display_error($error, '<a href="index.php?act=addfile&id=' . $id . '">' . $lng['repeat'] . '</a>');
        }
    } else {
        echo functions::display_error($lng_forum['error_upload_error']);
    }
    $page = ceil(mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `refid` = "' . $res['refid'] . '" AND `id` <= '.$id . ($rights < 7 ? ' AND `close` != "1"' : '')), 0) / $kmess);
    echo '<div class="gmenu"><a href="'.functions::bodau($res2['text']).'.' . $res['refid'] . '.html?page=' . $page . '">' . $lng['back'] . '</a></div>';
}else{
    // Form select the file to upload
    $page = ceil(mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `refid` = "' . $res['refid'] . '" AND `id` <= '.$id . ($rights < 7 ? ' AND `close` != "1"' : '')), 0) / $kmess);
    echo '<form action="index.php?act=addfile&id=' . $id . '" method="post" enctype="multipart/form-data"><div class="menu"><a href="'.functions::bodau($res2['text']).'.' . $res['refid'] . '.html?page=' . $page . '">' . htmlspecialchars($res2['text']) . '</a></div><div class="gmenu">';
    if (stristr($agn, 'Opera/8.01')) {
        echo '<input name="fail1" value =""/><br/><a href="op:fileselect">' . $lng_forum['select_file'] . '</a>';
    }else{
        echo '<input type="file" name="fail"/>';
    }
    echo '</div><div class="gmenu"><input type="submit" name="submit" value="' . $lng_forum['upload'] . '"/></div></form>' .
        '<div class="rmenu">' . $lng_forum['max_size'] . ': ' . $set['flsz'] . 'KB.</div>';
}