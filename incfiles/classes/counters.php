<?php
defined('_MRKEN_CMS') or die('Restricted access');

class counters
{
    // Counter photo albums for ordinary users
    static function album()
    {
        $file = ROOTPATH . 'files/system/cache/count_album.dat';
        if (file_exists($file) && filemtime($file) > (time() - 600)) {
            $res = unserialize(file_get_contents($file));
            $album = $res['album'];
            $photo = $res['photo'];
            $new = $res['new'];
            $new_adm = $res['new_adm'];
        } else {
            $album = mysql_result(mysql_query("SELECT COUNT(DISTINCT `user_id`) FROM `cms_album_files`"), 0);
            $photo = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_files`"), 0);
            $new = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_files` WHERE `time` > '" . (time() - 259200) . "' AND `access` = '4'"), 0);
            $new_adm = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_files` WHERE `time` > '" . (time() - 259200) . "' AND `access` > '1'"), 0);
            file_put_contents($file, serialize(array('album' => $album, 'photo' => $photo, 'new' => $new, 'new_adm' => $new_adm)));
        }

        $newcount = 0;
        if (core::$user_rights >= 6 && $new_adm) {
            $newcount = $new_adm;
        } elseif ($new) {
            $newcount = $new;
        }

        return $album . ' / ' . $photo .
        ($newcount ? ' / <span class="red"><a href="' . SITE_URL . '/users/album.php?act=top">+' . $newcount . '</a></span>' : '');
    }

    // statistics Forum
    static function forum($mod = 0)
    {
        $file = ROOTPATH . 'files/system/cache/count_forum.dat';
        $new = '';
        if (file_exists($file) && filemtime($file) > (time() - 600)) {
            $res = unserialize(file_get_contents($file));
            $top = $res['top'];
            $msg = $res['msg'];
			$fls = $res['files'];
        } else {
            $top = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `close` != '1'"), 0);
            $msg = mysql_result(mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `close` != '1'"), 0);
			$fls = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_forum_files` WHERE `del` != "1"'), 0);
            file_put_contents($file, serialize(array('top' => $top, 'msg' => $msg, 'files' => $fls)));
        }
        if (core::$user_id && ($new_msg = self::forum_new()) > 0) {
            $new = ' / <span class="red"><a href="' . SITE_URL . '/forum/index.php?act=new">+' . $new_msg . '</a></span>';
        }
		if($mod) return 'Có <b class="red">'.$msg.'</b> bài đăng và <b class="red">'.$fls.'</b> tập tin trong <b class="red">'.$top.'</b> chủ đề';
        return $top . ' / ' . $msg . $new;
    }

    /*
    -----------------------------------------------------------------
    Counter unread topics on the forum
    -----------------------------------------------------------------
    $mod = 0   Returns the number of unread
    $mod = 1   Displays links to unread
    -----------------------------------------------------------------
    */
    static function forum_new($mod = 0)
    {
        if (core::$user_id) {
            $req = mysql_query("SELECT COUNT(*) FROM `forum`
                LEFT JOIN `cms_forum_rdm` ON `forum`.`id` = `cms_forum_rdm`.`topic_id` AND `cms_forum_rdm`.`user_id` = '" . core::$user_id . "'
                WHERE `forum`.`type`='t'" . (core::$user_rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "
                AND (`cms_forum_rdm`.`topic_id` Is Null
                OR `forum`.`time` > `cms_forum_rdm`.`time`)");
            $total = mysql_result($req, 0);
            if ($mod) {
                return '<a href="index.php?act=new&do=period">' . core::$lng['show_for_period'] . '</a>' .
                ($total ? ' | <a href="index.php?act=new">' . core::$lng['unread'] . '</a>&#160;<span class="red">(<b>' . $total . '</b>)</span>' : '');
            } else {
                return $total;
            }
        } else {
            if ($mod) {
                return '<a href="index.php?act=new">' . core::$lng['last_activity'] . '</a>';
            } else {
                return false;
            }
        }
    }

    /*
    -----------------------------------------------------------------
    library Statistics
    -----------------------------------------------------------------
    */
    static function library()
    {
        $file = ROOTPATH . 'files/system/cache/count_library.dat';
        if (file_exists($file) && filemtime($file) > (time() - 3200)) {
            $res = unserialize(file_get_contents($file));
            $total = $res['total'];
            $new = $res['new'];
            $mod = $res['mod'];
        } else {
            $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_texts` WHERE `premod` = '1'"), 0);
            $new = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_texts` WHERE `time` > '" . (time() - 259200) . "' AND `premod` = '1'"), 0);
            $mod = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_texts` WHERE `premod` = '0'"), 0);
            file_put_contents($file, serialize(array('total' => $total, 'new' => $new, 'mod' => $mod)));
        }
        if ($new) $total .= ' / <span class="red"><a href="' . SITE_URL . '/library/index.php?act=new">+' . $new . '</a></span>';
        if ((core::$user_rights == 5 || core::$user_rights >= 6) && $mod) {
            $total .= ' / <span class="red"><a href="' . SITE_URL . '/library/index.php?act=premod">M:' . $mod . '</a></span>';
        }
        return $total;
    }

    /*
    -----------------------------------------------------------------
    The counter of visitors online
    -----------------------------------------------------------------
    */
    static function online()
    {
        $users = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > '" . (time() - 300) . "'"), 0);
        $guests = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > '" . (time() - 300) . "'"), 0);
        return (core::$user_id || core::$system_set['active'] ? '<a href="' . SITE_URL . '/users/index.php?act=online">' . $users . ' / ' . $guests . '</a>' : core::$lng['online'] . ': ' . $users . ' / ' . $guests);
    }

    /*
    -----------------------------------------------------------------
    Number of registered users
    -----------------------------------------------------------------
    */
    static function users()
    {
        $file = ROOTPATH . 'files/system/cache/count_users.dat';
        if (file_exists($file) && filemtime($file) > (time() - 600)) {
            $res = unserialize(file_get_contents($file));
            $total = $res['total'];
            $new = $res['new'];
        } else {
            $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `users`"), 0);
            $new = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `datereg` > '" . (time() - 86400) . "'"), 0);
            file_put_contents($file, serialize(array('total' => $total, 'new' => $new)));
        }
        if ($new) $total .= ' / <span class="red">+' . $new . '</span>';
        return $total;
    }
}