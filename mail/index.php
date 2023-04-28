<?php
define('_MRKEN_CMS', 1);

require_once('../incfiles/core.php');
$headmod = 'mail';
$lng_mail = core::load_lng('mail');
if (isset($_SESSION['ref']))
    unset($_SESSION['ref']);

// authorization check
if (!$user_id) {
    Header('Location: ' . SITE_URL . '/?err');
    exit;
}

function formatsize($size)
{
    // Formatting file size
    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' Gb';
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' Mb';
    } elseif ($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' Kb';
    } else {
        $size = $size . ' b';
    }

    return $size;
}

// An array of connected functions
$mods = array(
    'ignor',
    'write',
    'systems',
    'deluser',
    'load',
    'files',
    'input',
    'output',
    'delete',
    'new'
);

// Check the function
if ($act && ($key = array_search($act, $mods)) !== FALSE && file_exists('includes/' . $mods[$key] . '.php')) {
    require('includes/' . $mods[$key] . '.php');
} else {
    $textl = $lng['mail'];
    require_once('../incfiles/head.php');
    echo '<div class="phdr"><b>' . $lng_mail['contacts'] . '</b></div>';

    if ($id) {
        $req = mysql_query("SELECT * FROM `users` WHERE `id` = '$id' LIMIT 1;");
        if (mysql_num_rows($req) == 0) {
            echo functions::display_error($lng['error_user_not_exist']);
            require_once("../incfiles/end.php");
            exit;
        }

        $res = mysql_fetch_assoc($req);

        if ($id == $user_id) {
            echo '<div class="rmenu">' . $lng_mail['impossible_add_contact'] . '</div>';
        } else {
            //Add to the locked
            if (isset($_POST['submit'])) {
                $q = mysql_query("SELECT * FROM `cms_contact`
				WHERE `user_id`='" . $user_id . "' AND `from_id`='" . $id . "';");
                if (mysql_num_rows($q) == 0) {
                    mysql_query("INSERT INTO `cms_contact` SET
					`user_id` = '" . $user_id . "',
					`from_id` = '" . $id . "',
					`time` = '" . time() . "';");
                }
                echo '<div class="gmenu"><p>' . $lng_mail['add_contact'] . '</p><p><a href="index.php">' . $lng['continue'] . '</a></p></div>';
            } else {
                echo '<div class="menu">' .
                    '<form action="index.php?id=' . $id . '&amp;add" method="post">' .
                    '<div><p>' . $lng_mail['really_add_contact'] . '</p>' .
                    '<p><input type="submit" name="submit" value="' . $lng['add'] . '"/></p>' .
                    '</div></form></div>';
            }
        }
    } else {
        echo '<div class="topmenu"><b>' . $lng_mail['my_contacts'] . '</b> | <a href="index.php?act=ignor">' . $lng_mail['blocklist'] . '</a></div>';
        //get the list of contacts
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_contact` WHERE `user_id`='" . $user_id . "' AND `ban`!='1'"), 0);
        if ($total) {
            if ($total > $kmess) echo '<div class="topmenu">' . functions::display_pagination('index.php?page=', $start, $total, $kmess) . '</div>';
            $req = mysql_query("SELECT `users`.*, `cms_contact`.`from_id` AS `id`
                FROM `cms_contact`
			    LEFT JOIN `users` ON `cms_contact`.`from_id`=`users`.`id`
			    WHERE `cms_contact`.`user_id`='" . $user_id . "'
			    AND `cms_contact`.`ban`!='1'
			    ORDER BY `users`.`account` ASC
			    LIMIT $start, $kmess"
            );

            for ($i = 0; ($row = mysql_fetch_assoc($req)) !== FALSE; ++$i) {
                echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
                $subtext = '<a href="index.php?act=write&amp;id=' . $row['id'] . '">' . $lng_mail['correspondence'] . '</a> | <a href="index.php?act=deluser&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a> | <a href="index.php?act=ignor&amp;id=' . $row['id'] . '&amp;add">' . $lng_mail['ban_contact'] . '</a>';
                $count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='{$row['id']}' AND `from_id`='$user_id') OR (`user_id`='$user_id' AND `from_id`='{$row['id']}')) AND `sys`!='1' AND `spam`!='1' AND `delete`!='$user_id';"), 0);
                $new_count_message = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='{$row['id']}' AND `cms_mail`.`from_id`='$user_id' AND `read`='0' AND `sys`!='1' AND `spam`!='1' AND `delete`!='$user_id';"), 0);
                $arg = array(
                    'header' => '(' . $count_message . ($new_count_message ? '/<span class="red">+' . $new_count_message . '</span>' : '') . ')',
                    'sub' => $subtext
                );
                echo functions::display_user($row, $arg);
                echo '</div>';
            }
        } else {
            echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
        }

        echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        if ($total > $kmess) {
            echo '<div class="topmenu">' . functions::display_pagination('index.php?page=', $start, $total, $kmess) . '</div>';
            echo '<div class="menu"><form action="index.php" method="get">
				<input type="text" name="page" size="2"/>
				<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></div>';
        }
        echo '<div class="menu"><a href="' . SITE_URL . '/users/profile.php?act=office">' . $lng['personal'] . '</a></div>';
    }
}

require_once(ROOTPATH . 'incfiles/end.php');