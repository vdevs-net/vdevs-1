<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$headmod = isset($headmod) ? mysql_real_escape_string($headmod) : '';
$textl = (isset($textl) ? $textl . ' | ' : '') . $set['copyright'];
$add = isset($add) ? $add : false;
$keyword = (isset($keyword) ? $keyword . ', ' : '') . $set['meta_key'];
$meta_desc = isset($meta_desc) ? $meta_desc : $set['meta_desc'];
// Load ADS
$cms_ads = array();
if (!isset($_GET['err']) && $act != '404' && $headmod != 'admin') {
    $view = $user_id ? 2 : 1;
    $layout = ($headmod == 'mainpage' && !$act) ? 1 : 2;
    $req = mysql_query("SELECT * FROM `cms_ads` WHERE `to` = '0' AND (`layout` = '$layout' or `layout` = '0') AND (`view` = '$view' or `view` = '0') ORDER BY  `mesto` ASC");
    if (mysql_num_rows($req)) {
        while (($res = mysql_fetch_assoc($req)) !== FALSE) {
            $name = explode("|", $res['name']);
            $name = htmlentities($name[mt_rand(0, (count($name) - 1))], ENT_QUOTES, 'UTF-8');
            if (!empty($res['color'])) $name = '<span style="color:#' . $res['color'] . '">' . $name . '</span>';
            // Apply theme
            $font = $res['bold'] ? 'font-weight: bold;' : FALSE;
            $font .= $res['italic'] ? ' font-style:italic;' : FALSE;
            $font .= $res['underline'] ? ' text-decoration:underline;' : FALSE;
            if ($font) $name = '<span style="' . $font . '">' . $name . '</span>';
            @$cms_ads[$res['type']] .= '<a href="' . ($res['show'] ? functions::checkout($res['link']) : SITE_URL . '/go.php?id=' . $res['id']) . '">' . $name . '</a><br/>';
            if (($res['day'] != 0 && time() >= ($res['time'] + $res['day'] * 3600 * 24)) || ($res['count_link'] != 0 && $res['count'] >= $res['count_link']))
                mysql_query("UPDATE `cms_ads` SET `to` = '1'  WHERE `id` = '" . $res['id'] . "'");
        }
    }
}
// Update visitor locations
$sql = '';
if ($user_id) {
	$movings = $datauser['movings'];
	if ($datauser['lastdate'] < (time() - 300)) {
		$movings = 0;
		$sql .= " `sestime` = '" . time() . "', ";
	}
	if ($datauser['place'] != $headmod) {
		++$movings;
		$sql .= " `place` = '" . mysql_real_escape_string($headmod) . "', ";
	}
	if ($datauser['browser'] != $agn){
		$sql .= " `browser` = '" . mysql_real_escape_string($agn) . "', ";
	}
	$totalonsite = $datauser['total_on_site'];
	if ($datauser['lastdate'] > (time() - 300)){
		$totalonsite = $totalonsite + time() - $datauser['lastdate'];
	}
    if(date('d', $datauser['day_time']) != date('d', time()) || date('m', $datauser['day_time']) != date('m', time()) || date('Y', $datauser['day_time']) != date('Y', time())){
        $coin_plus = rand(40,50) + ($rights ? 50 : 0);
        $datauser['coin'] = $datauser['coin'] + $coin_plus;
        $sql .= ' `coin` = "' . $datauser['coin'] . '", `day_time` = "'.time().'", ';
        $notif = '<div class="rmenu">Bạn nhận được '.$coin_plus.' xu cho việc đăng nhập trong ngày hôm nay!</div>';
    }
    $datauser['lastdate'] = time();
    mysql_query('UPDATE `users` SET ' . $sql .'
        `movings` = "' . $movings. '",
        `total_on_site` = "' . $totalonsite . '",
        `lastdate` = "' .  $datauser['lastdate'] . '"
        WHERE `id` = "' . $user_id . '"
    ');
} else {
    $movings = 0;
    $session = md5(core::$ip . core::$ip_via_proxy . core::$user_agent);
    $req = mysql_query("SELECT * FROM `cms_sessions` WHERE `session_id` = '$session' LIMIT 1");
    if (mysql_num_rows($req)) {
        // If there is in the database, then update the data
        $res = mysql_fetch_assoc($req);
        $movings = ++$res['movings'];
        if ($res['sestime'] < (time() - 300)) {
            $movings = 1;
            $sql .= " `sestime` = '" . time() . "', ";
        }
        if ($res['place'] != $headmod) {
            $sql .= " `place` = '" . mysql_real_escape_string($headmod) . "', ";
        }
        mysql_query("UPDATE `cms_sessions` SET $sql
            `movings` = '$movings',
            `lastdate` = '" . time() . "'
            WHERE `session_id` = '$session'
        ");
    } else {
        // 	If still was not in the database, the record is added
        mysql_query("INSERT INTO `cms_sessions` SET
            `session_id` = '" . $session . "',
            `ip` = '" . core::$ip . "',
            `ip_via_proxy` = '" . core::$ip_via_proxy . "',
            `browser` = '" . mysql_real_escape_string($agn) . "',
            `lastdate` = '" . time() . "',
            `sestime` = '" . time() . "',
            `place` = '" . mysql_real_escape_string($headmod) . "'
        ");
    }
}
// UPDATE BOT time
mysql_query('UPDATE `users` SET `lastdate`="'.time().'" WHERE `id`="2"');
$s_json = array(
	'context'         => 'http://schema.org',
	'@type'           => 'WebSite',
	'url'             => SITE_URL . '/',
	'name'            => htmlspecialchars($set['copyright']),
	'potentialAction' => array(
		'@type'       => 'SearchAction',
		'target'      => SITE_URL . '/forum/search.php?search={search_term_string}',
		'query-input' => 'required name=search_term_string'
	)
);

echo '<!DOCTYPE html>' .
    '<html lang="' . core::$lng_iso . '">' .
    '<head>' .
    '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
    '<title>' . htmlspecialchars($textl) . '</title>' .
    '<link rel="shortcut icon" href="' . SITE_URL . '/favicon.ico">' .
	'<link rel="apple-touch-icon" href="' . SITE_URL . '/favicon.ico">' .
    '<meta name="keywords" content="' . htmlspecialchars($keyword) . '" />' .
    '<meta name="description" content="' . htmlspecialchars($meta_desc) . '" />' .
	($add ? "\n".$add : '') . 
    '<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>' .
    '<meta name="HandheldFriendly" content="true">' .
    '<meta name="MobileOptimized" content="width">' .
    '<meta content="yes" name="apple-mobile-web-app-capable">' .
'<meta name="google-site-verification" content="qH10cEd1P9-mpkbHp5vLCcLOlFaFAMcEr6tH5vYP8vQ" />' .
    '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/mobile.css">' .
    '<link rel="alternate" type="application/rss+xml" title="RSS | ' . $lng['site_news'] . '" href="' . SITE_URL . '/rss/rss.php">' .
    '<script type="text/javascript">headmod = "'.$headmod.'", browser = "'.$device.'";user = {id:'.$user_id.', kmess: '.$set_user['kmess'].'}</script>' .
    '<script type="application/ld+json">'. json_encode($s_json) .'</script>' .
    '</head><body basesrc="' . SITE_URL . '"><div id="container">' . core::display_core_errors();

// Display ADS 1
if (isset($cms_ads[0])) echo $cms_ads[0];

// Gretting
echo '<div id="header"><a id="top">&nbsp;</a><div class="box">';
if($user_id){
	$money = '<img src="' . SITE_URL . '/images/coin.png"> '.$datauser['coin'].' - <img src="' . SITE_URL . '/images/gold.png"/> '.$datauser['gold'].'';
	echo '<div class="phdr"><a href="' . SITE_URL . '"><b>Home</b></a> · <a href="' . SITE_URL . '/shop/">Cửa hàng</a> · <a href="' . SITE_URL . '/users/profile.php">' . $lng['personal'] . '</a> · <a href="' . SITE_URL . '/users/farm.php">Nông trại</a> · <a href="' . SITE_URL . '/mail/?act=input">' . $lng['mail'] . '</a> · <a href="' . SITE_URL . '/?act=logout">' . $lng['exit'] . '</a></div>'.
	'<div class="menu">' . functions::display_user($datauser, array('iphide' => 1, 'stshide' => 1, 'ofhide' => 1, 'header' => '<br/>'.$money)) . '</div>';
}else{
	echo '<div class="phdr"><a href="' . SITE_URL . '"><b>Home</b></a> · <a href="' . SITE_URL . '/login.php">' . $lng['login'] . '</a> · <a href="' . SITE_URL . '/faq.php" class="reg_link"><b>FAQ</b></a></div>'.
	'<div class="list1"><form action="' . SITE_URL . '/login.php" method="post"><input type="text" name="account" maxlength="32" size="5" class="name" autocomplete="off"/><input type="password" name="password" maxlength="32" size="5" class="pass" autocomplete="off"/><input type="hidden" name="mem" value="1"/><input type="submit" value="&#160;' . $lng['login'] . '&#160;"/></form></div>'.
	'<div class="topmenu"><a href="' . SITE_URL . '/registration.php"><b><font color="red">' . $lng['registration'] . '</font></b></a> · <a href="' . SITE_URL . '/users/skl.php?continue" title="Quên mật khẩu">' . $lng['forgotten_password'] . '</a></div>';
}

// Main menu User
echo '</div></div><div id="body" class="maintxt">';

// ADS 2
if (!empty($cms_ads[1])) echo '<div class="gmenu">' . $cms_ads[1] . '</div>';

// I get a message Ban
if (!empty($ban)) echo '<div class="alarm">' . $lng['ban'] . '&#160;<a href="' . SITE_URL . '/users/profile.php?act=ban">' . $lng['in_detail'] . '</a></div>';

// Links to unread
if ($user_id) {
    $list = array();
	// system mail
    $new_sys_mail = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='$user_id' AND `read`='0' AND `sys`='1' AND `delete`!='$user_id';"), 0);
	if ($new_sys_mail) $list[] = '<a href="' . SITE_URL . '/mail/index.php?act=systems">Hệ thống</a> (+' . $new_sys_mail . ')';
	// user mail
	$new_mail = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_mail` LEFT JOIN `cms_contact` ON `cms_mail`.`user_id`=`cms_contact`.`from_id` AND `cms_contact`.`user_id`='$user_id' WHERE `cms_mail`.`from_id`='$user_id' AND `cms_mail`.`sys`='0' AND `cms_mail`.`read`='0' AND `cms_mail`.`delete`!='$user_id' AND `cms_contact`.`ban`!='1' AND `cms_mail`.`spam`='0'"), 0);
	if ($new_mail) $list[] = '<a href="' . SITE_URL . '/mail/index.php?act=new">' . $lng['mail'] . '</a> (+' . $new_mail . ')';
    if ($datauser['comm_count'] > $datauser['comm_old']) $list[] = '<a href="' . SITE_URL . '/users/profile.php?user=' . $user_id . '">' . $lng['guestbook'] . '</a> (' . ($datauser['comm_count'] - $datauser['comm_old']) . ')';
    $new_album_comm = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_files` WHERE `user_id` = '" . core::$user_id . "' AND `unread_comments` = 1"), 0);
    if ($new_album_comm) $list[] = '<a href="' . SITE_URL . '/users/album.php?act=top&mod=my_new_comm">' . $lng['albums_comments'] . '</a>';
    if (!empty($list)) echo '<div class="rmenu">' . $lng['unread'] . ': ' . functions::display_menu($list, ', ') . '</div>';
	if(isset($notif)) echo $notif;
}