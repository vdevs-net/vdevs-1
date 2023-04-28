<?php
defined('_IS_MRKEN') or die('Error: restricted access');
define('ROOT_DIR', '..');

// Check right
if ($rights < 7) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}
class scaner {
    // Antispyware scanner
    public $scan_folders = array (
        '',
        '/admin',
        '/files',
        '/forum',
        '/images',
        '/incfiles',
        '/library',
        '/mail',
        '/news',
        '/rss',
        '/users'
    );
    public $good_files = array (
        '../.htaccess',
        '../assets/captcha.php',
        '../assets/re_captcha.php',
        '../closed.php',
        '../faq.php',
        '../go.php',
        '../index.php',
        '../login.php',
        '../registration.php',
        '../files/.htaccess',
        '../files/system/cache/.htaccess',
        '../files/forum/attach/index.php',
        '../files/forum/index.php',
        '../files/forum/topics/index.php',
        '../files/library/index.php',
        '../files/mail/index.php',
        '../files/users/album/index.php',
        '../files/users/avatar/index.php',
        '../files/users/index.php',
        '../files/users/photo/index.php',
        '../files/users/pm/index.php',
        '../forum/includes/addfile.php',
        '../forum/includes/addvote.php',
        '../forum/includes/close.php',
        '../forum/includes/curators.php',
        '../forum/includes/deltema.php',
        '../forum/includes/delvote.php',
        '../forum/includes/editpost.php',
        '../forum/includes/editvote.php',
        '../forum/includes/file.php',
        '../forum/includes/files.php',
        '../forum/includes/filter.php',
        '../forum/includes/loadtem.php',
        '../forum/includes/new.php',
        '../forum/includes/nt.php',
        '../forum/includes/per.php',
        '../forum/includes/post.php',
        '../forum/includes/ren.php',
        '../forum/includes/restore.php',
        '../forum/includes/say.php',
        '../forum/includes/tema.php',
        '../forum/includes/users.php',
        '../forum/includes/vip.php',
        '../forum/includes/vote.php',
        '../forum/includes/who.php',
        '../forum/contents.php',
        '../forum/index.php',
        '../forum/search.php',
        '../forum/thumbinal.php',
        '../forum/vote_img.php',
        '../images/avatars/index.php',
        '../images/captcha/.htaccess',
        '../images/index.php',
        '../images/smileys/index.php',
        '../images/smileys/simply/index.php',
        '../images/smileys/user/index.php',
        '../incfiles/.htaccess',
        '../incfiles/classes/bbcode.php',
        '../incfiles/classes/CleanUser.php',
        '../incfiles/classes/comments.php',
        '../incfiles/classes/core.php',
        '../incfiles/classes/counters.php',
        '../incfiles/classes/functions.php',
        '../incfiles/classes/mainpage.php',
        '../incfiles/core.php',
        '../incfiles/db.php',
        '../incfiles/end.php',
        '../incfiles/func.php',
        '../incfiles/head.php',
        '../incfiles/index.php',
        '../incfiles/lib/class.upload.php',
        '../library/.htaccess',
        '../library/contents.php',
        '../library/inc.php',
        '../library/includes/addnew.php',
        '../library/includes/comments.php',
        '../library/includes/del.php',
        '../library/includes/download.php',
        '../library/includes/lastcom.php',
        '../library/includes/mkdir.php',
        '../library/includes/moder.php',
        '../library/includes/move.php',
        '../library/includes/new.php',
        '../library/includes/premod.php',
        '../library/includes/search.php',
        '../library/includes/tagcloud.php',
        '../library/includes/tags.php',
        '../library/includes/top.php',
        '../library/index.php',
        '../mail/includes/delete.php',
        '../mail/includes/deluser.php',
        '../mail/includes/files.php',
        '../mail/includes/ignor.php',
        '../mail/includes/input.php',
        '../mail/includes/load.php',
        '../mail/includes/new.php',
        '../mail/includes/output.php',
        '../mail/includes/systems.php',
        '../mail/includes/write.php',
        '../mail/index.php',
        '../news/index.php',
        '../admin/includes/ads.php',
        '../admin/includes/access.php',
        '../admin/includes/antiflood.php',
        '../admin/includes/antispy.php',
        '../admin/includes/ban_panel.php',
        '../admin/includes/forum.php',
        '../admin/includes/ipban.php',
        '../admin/includes/ip_whois.php',
        '../admin/includes/languages.php',
        '../admin/includes/mail.php',
        '../admin/includes/news.php',
        '../admin/includes/reg.php',
        '../admin/includes/search_ip.php',
        '../admin/includes/settings.php',
        '../admin/includes/smileys.php',
        '../admin/includes/usr.php',
        '../admin/includes/usr_adm.php',
        '../admin/includes/usr_clean.php',
        '../admin/includes/usr_del.php',
        '../admin/index.php',
        '../rss/rss.php',
        '../users/album.php',
        '../users/image.php',
        '../users/includes/admlist.php',
        '../users/includes/album/comments.php',
        '../users/includes/album/delete.php',
        '../users/includes/album/edit.php',
        '../users/includes/album/image_delete.php',
        '../users/includes/album/image_download.php',
        '../users/includes/album/image_edit.php',
        '../users/includes/album/image_move.php',
        '../users/includes/album/image_upload.php',
        '../users/includes/album/list.php',
        '../users/includes/album/show.php',
        '../users/includes/album/sort.php',
        '../users/includes/album/top.php',
        '../users/includes/album/users.php',
        '../users/includes/album/vote.php',
        '../users/includes/birth.php',
        '../users/includes/online.php',
        '../users/includes/profile/activity.php',
        '../users/includes/profile/ban.php',
        '../users/includes/profile/edit.php',
        '../users/includes/profile/friends.php',
        '../users/includes/profile/images.php',
        '../users/includes/profile/info.php',
        '../users/includes/profile/ip.php',
        '../users/includes/profile/office.php',
        '../users/includes/profile/password.php',
        '../users/includes/profile/reset.php',
        '../users/includes/profile/settings.php',
        '../users/search.php',
        '../users/includes/top.php',
        '../users/includes/userlist.php',
        '../users/index.php',
        '../users/profile.php',
        '../users/skl.php'
    );
    public $snap_base = 'scan_snapshot.dat';
    public $snap_files = array ();
    public $bad_files = array ();
    public $snap = false;
    public $track_files = array ();
    private $checked_folders = array ();
    private $cache_files = array ();
    function scan() {
        // Scan to the appropriate distribution
        foreach ($this->scan_folders as $data) {
            $this->scan_files(ROOT_DIR . $data);
        }
    }
    function snapscan() {
        // Scan the image
        if (file_exists('../files/system/cache/' . $this->snap_base)) {
            $filecontents = file('../files/system/cache/' . $this->snap_base);
            foreach ($filecontents as $name => $value) {
                $filecontents[$name] = explode("|", trim($value));
                $this->track_files[$filecontents[$name][0]] = $filecontents[$name][1];
            }
            $this->snap = true;
        }

        foreach ($this->scan_folders as $data) {
            $this->scan_files(ROOT_DIR . $data);
        }
    }
    function snap() {
        // Adding picture files in a secure database
        foreach ($this->scan_folders as $data) {
            $this->scan_files(ROOT_DIR . $data, true);
        }
        $filecontents = "";

        foreach ($this->snap_files as $idx => $data) {
            $filecontents .= $data['file_path'] . "|" . $data['file_crc'] . "\r\n";
        }
        $filehandle = fopen('../files/system/cache/' . $this->snap_base, "w+");
        fwrite($filehandle, $filecontents);
        fclose($filehandle);
        @chmod('../files/system/cache/' . $this->snap_base, 0666);
    }
    function scan_files($dir, $snap = false) {
        // A utility function scan
        if (!isset($file))
            $file = false;
        $this->checked_folders[] = $dir . '/' . $file;

        if ($dh = @opendir($dir)) {
            while (false !== ($file = readdir($dh))) {
                if ($file == '.' or $file == '..' or $file == '.svn' or $file == '.DS_store') {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) {
                    if ($dir != ROOT_DIR)
                        $this->scan_files($dir . '/' . $file, $snap);
                } else {
                    if ($this->snap or $snap)
                        $templates = "|tpl";
                    else
                        $templates = "";
                    if (preg_match("#.*\.(php|cgi|pl|perl|php3|php4|php5|php6|phtml|py|htaccess" . $templates . ")$#i", $file)) {
                        $folder = str_replace("../..", ".", $dir);
                        $file_size = filesize($dir . '/' . $file);
                        $file_crc = strtoupper(dechex(crc32(file_get_contents($dir . '/' . $file))));
                        $file_date = date("d.m.Y H:i:s", filectime($dir . '/' . $file));
                        if ($snap) {
                            $this->snap_files[] = array (
                                'file_path' => $folder . '/' . $file,
                                'file_crc' => $file_crc
                            );
                        } else {
                            if ($this->snap) {
                                if ($this->track_files[$folder . '/' . $file] != $file_crc and !in_array($folder . '/' . $file, $this->cache_files))
                                    $this->bad_files[] = array (
                                        'file_path' => $folder . '/' . $file,
                                        'file_name' => $file,
                                        'file_date' => $file_date,
                                        'type' => 1,
                                        'file_size' => $file_size
                                    );
                            } else {
                                if (!in_array($folder . '/' . $file, $this->good_files) or $file_size > 300000)
                                    $this->bad_files[] = array (
                                        'file_path' => $folder . '/' . $file,
                                        'file_name' => $file,
                                        'file_date' => $file_date,
                                        'type' => 0,
                                        'file_size' => $file_size
                                    );
                            }
                        }
                    }
                }
            }
        }
    }
}
$scaner = new scaner();
switch ($mod) {
    case 'scan':
        // Scan for compliance distro
        $scaner->scan();
        echo '<div class="phdr"><a href="index.php?act=antispy"><b>' . $lng['antispy'] . '</b></a> | ' . $lng['antispy_dist_scan'] . '</div>';
        if (count($scaner->bad_files)) {
            echo '<div class="rmenu"><small>' . $lng['antispy_dist_scan_bad'] . '</small></div>';
            echo '<div class="menu">';
            foreach ($scaner->bad_files as $idx => $data) {
                echo $data['file_path'] . '<br />';
            }
            echo '</div><div class="phdr">' . $lng['total'] . ': ' . count($scaner->bad_files) . '</div>';
        } else {
            echo '<div class="gmenu">' . $lng['antispy_dist_scan_good'] . '</div>';
        }
        echo '<p><a href="index.php?act=antispy&amp;mod=scan">' . $lng['antispy_rescan'] . '</a></p>';
        break;

    case 'snapscan':
        // Scan for compliance with the previously created snapshot
        $scaner->snapscan();
        echo '<div class="phdr"><a href="index.php?act=antispy"><b>' . $lng['antispy'] . '</b></a> | ' . $lng['antispy_snapshot_scan'] . '</div>';
        if (count($scaner->track_files) == 0) {
            echo functions::display_error($lng['antispy_no_snapshot'], '<a href="index.php?act=antispy&amp;mod=snap">' . $lng['antispy_snapshot_create'] . '</a>');
        } else {
            if (count($scaner->bad_files)) {
                echo '<div class="rmenu">' . $lng['antispy_snapshot_scan_bad'] . '</div>';
                echo '<div class="menu">';
                foreach ($scaner->bad_files as $idx => $data) {
                    echo $data['file_path'] . '<br />';
                }
                echo '</div>';
            } else {
                echo '<div class="gmenu">' . $lng['antispy_snapshot_scan_ok'] . '</div>';
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . count($scaner->bad_files) . '</div>';
        }
        break;

    case 'snap':
        // Create a picture file
        echo '<div class="phdr"><a href="index.php?act=antispy"><b>' . $lng['antispy'] . '</b></a> | ' . $lng['antispy_snapshot_create'] . '</div>';
        if (isset($_POST['submit'])) {
            $scaner->snap();
            echo '<div class="gmenu"><p>' . $lng['antispy_snapshot_create_ok'] . '</p></div>' .
                '<div class="phdr"><a href="index.php?act=antispy">' . $lng['continue'] . '</a></div>';
        } else {
            echo '<form action="index.php?act=antispy&amp;mod=snap" method="post">' .
                '<div class="menu"><p>' . $lng['antispy_snapshot_warning'] . '</p>' .
                '<p><input type="submit" name="submit" value="' . $lng['antispy_snapshot_create'] . '" /></p>' .
                '</div></form>' .
                '<div class="phdr"><small>' . $lng['antispy_snapshot_help'] . '</small></div>';
        }
        break;

    default:
        // Main Menu Scanner
        echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['antispy'] . '</div>' .
            '<div class="menu"><p><h3>' . $lng['antispy_scan_mode'] . '</h3><ul>' .
            '<li><a href="index.php?act=antispy&amp;mod=scan">' . $lng['antispy_dist_scan'] . '</a><br />' .
            '<small>' . $lng['antispy_dist_scan_help'] . '</small></li>' .
            '<li><a href="index.php?act=antispy&amp;mod=snapscan">' . $lng['antispy_snapshot_scan'] . '</a><br />' .
            '<small>' . $lng['antispy_snapshot_scan_help'] . '</small></li>' .
            '<li><a href="index.php?act=antispy&amp;mod=snap">' . $lng['antispy_snapshot_create'] . '</a><br />' .
            '<small>' . $lng['antispy_snapshot_create_help'] . '</small></li>' .
            '</ul></p></div><div class="phdr">&#160;</div>';
}
echo '<p>' . ($mod ? '<a href="index.php?act=antispy">' . $lng['antispy_menu'] . '</a><br />' : '') . '<a href="index.php">' . $lng['admin_panel'] . '</a></p>';
?>