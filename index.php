<?php
define('_MRKEN_CMS', 1);
$headmod = 'mainpage';
require('incfiles/core.php');
if (isset($_SESSION['ref'])) unset($_SESSION['ref']);
if (isset($_GET['err'])) $act = 404;
switch ($act) {
    case '404':
        $headmod = 'error404';
        require('incfiles/head.php');
        echo functions::display_error($lng['error_404']);
        break;

	case 'logout':
		setcookie('cuid', '');
		setcookie('cups', '');
		session_destroy();
		header('Location: ' . SITE_URL . '/');
		exit;
		break;

    default:
        // homepage
        $headmod = 'mainpage';
        require('incfiles/head.php');
		$mp = new mainpage();
		// information
		echo '<div class="phdr"><a href="news/index.php"><b>' . $lng['news'] . '</b></a> (' . $mp->newscount . ')</div>';
		echo $mp->news;
		if ($user_id) {
			$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_chat`'),0);
			$req = mysql_query('SELECT `cms_chat`.*,`users`.`account`,`users`.`rights` FROM `cms_chat` LEFT JOIN `users` ON `users`.`id`=`cms_chat`.`uid` ORDER BY `cms_chat`.`id` DESC LIMIT ' . $kmess . '');
			$i=0;
			echo '<div class="phdr"><a href="chat.php"><b>Chatbox</b></a> (<span id="total">'.$total.'</span>)</div>'.
'<div class="notif"><a href="game/?act=rock_paper_scissors">[New] Oẳn tù tì!</a></div>' .
'<div class="notif"><a href="game/">Thêm trò chơi</a></div>' .
				'<div id="error" class="hide rmenu"></div><div class="topmenu"><form action="chat.php?in" method="post" id="chat"><div><textarea name="text" rows="'.($set_user['field_h']-1).'" id="chat_input" required></textarea></div><div><input type="hidden" name="token" value="'.$datauser['priv_key'].'"/><input type="submit" name="submit" value="Gửi" id="chat_submit"></div></form></div>'.
				'<div id="chatbox">';
			while($res = mysql_fetch_assoc($req)){
				$text = functions::checkout($res['text'], 1, 1, 1);
				echo ($i%2 ? '<div class="list1">':'<div class="list2">').'<b><a href="users/profile.php?user='.$res['uid'].'" title="'.functions::display_date($res['time']).'">'. functions::nick_color(htmlspecialchars($res['account']), $res['rights']). '</a></b>: '.$text.'</div>';
				$i++;
			}
			echo '</div>';
		}
		// Forum
		if ($set['mod_forum'] || $rights >= 7){
			echo '<div class="phdr">' . $lng['information'] . '</div>' .
                '<div class="menu"><a href="faq.php">' . $lng['information_faq'] . '</a></div>';
            if (mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type` = "t" AND `vip` = "1" AND `close` = "0"'), 0)) {
                $req = mysql_query('SELECT `id`, `text`, `prefix` FROM `forum` WHERE `type` = "t" AND `vip` = "1" AND `close` = "0" ORDER BY `time` DESC LIMIT 5');
                while($res = mysql_fetch_assoc($req)) {
                    $count = mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type` = "m" AND `close` = "0" AND `refid` = "' . $res['id'] . '"'), 0);
                    $cpg = ceil($count / $kmess);
                    $nam = mysql_fetch_assoc(mysql_query('SELECT `user_id`, `from` FROM `forum` WHERE `type` = "m" AND `close` = "0" AND `refid` = "' . $res['id'] . '" ORDER BY `time` DESC LIMIT 1'));
                    echo '<div class="list1 bg-notif"><img src="images/pt.gif" alt="[*]" style="vertical-align:middle" /> ' . ($res['prefix'] ? '<span class="label label-' . $res['prefix'] . '">' . $prefixs[$res['prefix']] . '</span>' : '') . '<a href="forum/' . functions::bodau($res['text']) . '.' . $res['id'] . '.html">' . htmlspecialchars($res['text']) . '</a> (<span class="red">' . $count . '</span>)' .($cpg > 1 ? ' <a href="forum/'.functions::bodau($res['text']).'.' . $res['id'] . '.html?page=' . $cpg . '">&raquo;</a>' : '') . ' [' . ((($user_id || $set['active']) && $user_id != $nam['user_id']) ? '<a href="users/profile.php?user=' . $nam['user_id'] . '">' . $nam['from'] . '</a>': $nam['from']) . ']' . '</div>';
                }
            } else {
                echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
            }
			echo '<div class="phdr"><a href="forum/"><b>' . $lng['forum'] . '</b></a> (' . counters::forum() . ') | <a href="forum/?act=new&do=all">Có gì mới?</a></div>';
			echo '<div class="topmenu"><form action="//google.com/search" method="get"><input type="hidden" name="sitesearch" value="phonho.net"><input type="text" name="q" placeholder="Nhập từ khóa..." autocomplete="off"><input type="submit" value="Tìm Kiếm"></form><div><small><b class="green">Tìm kiếm trước khi hỏi</b></small></div></div>';
            if (mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type` = "t" AND `close` = "0"'), 0)) {
                $req = mysql_query('SELECT `id`, `realid`, `text`, `prefix`, `vip`, `edit` FROM `forum` WHERE `type` = "t" AND `close` = "0" ORDER BY `time` DESC LIMIT ' . $kmess . '');
                while($res = mysql_fetch_assoc($req)) {
                    $count = mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type` = "m" AND `close` = "0" AND `refid` = "' . $res['id'] . '"'), 0);
                    $cpg = ceil($count / $kmess);
                    $nam = mysql_fetch_assoc(mysql_query('SELECT `user_id`, `from` FROM `forum` WHERE `type` = "m" AND `close` = "0" AND `refid` = "' . $res['id'] . '" ORDER BY `time` DESC LIMIT 1'));
                    // icons
                    $icons = array(
                        ($res['vip'] ? functions::image('pt.gif') : ''),
                        ($res['realid'] ? functions::image('rate.gif') : ''),
                        ($res['edit'] ? functions::image('tz.gif') : '')
                    );
                    echo '<div class="list1">' . functions::display_menu($icons, '') . ($res['prefix'] ? '<span class="label label-' . $res['prefix'] . '">' . $prefixs[$res['prefix']] . '</span>' : '') . '<a href="forum/' . functions::bodau($res['text']) . '.' . $res['id'] . '.html">' . htmlspecialchars($res['text']) . '</a> (<span class="red">' . $count . '</span>)' .($cpg > 1 ? ' <a href="forum/'.functions::bodau($res['text']).'.' . $res['id'] . '.html?page=' . $cpg . '">&raquo;</a>' : '') . ' [' . ((($user_id || $set['active']) && $user_id != $nam['user_id']) ? '<a href="users/profile.php?user=' . $nam['user_id'] . '">' . $nam['from'] . '</a>': $nam['from']) . ']' . '</div>';
                }
            } else {
                echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
            }
		}

		// block useful    
		echo '<div class="phdr"><b>MENU</b></div>';
		// Link to library
		if ($set['mod_lib'] || $rights >= 7)
			echo '<div class="menu"><a href="library/">' . $lng['library'] . '</a> (' . counters::library() . ')</div>';
		if ($user_id || $set['active']) {
			echo '<div class="menu"><a href="users/album.php">' . $lng['photo_albums'] . '</a> (' . counters::album() . ')</div>';
		}
		// Thành viên trực tuyến
		$onltime = time() - 300;
		$gbot = 0;$msn = 0;$baidu = 0;$bing = 0;$mj = 0;$coccoc = 0;$facebook = 0;$yandex = 0;
		$users = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > '" . $onltime . "' AND `preg`='1'"), 0);
		$guests = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > '" . $onltime . "'"), 0);
		$count = $users + $guests;
		$spider = mysql_query("SELECT * FROM `cms_sessions` WHERE `lastdate` > '" . $onltime . "' ORDER BY `lastdate` DESC");
		while ($res = mysql_fetch_assoc($spider)) {
			if(stristr($res['browser'], 'Google')) {$gbot = $gbot + 1;}
			if(stristr($res['browser'], 'msnbot')) {$msn = $msn + 1;}
			if(stristr($res['browser'], 'Baidu')) {$baidu = $baidu + 1;}
			if(stristr($res['browser'], 'bingbot')) {$bing = $bing + 1;}
			if(stristr($res['browser'], 'MJ12')) {$mj = $mj + 1;}
			if(stristr($res['browser'], 'coccoc')) {$coccoc = $coccoc + 1;}
			if(stristr($res['browser'], 'facebook')) {$facebook = $facebook + 1;}
			if(stristr($res['browser'], 'Yandex')) {$yandex = $yandex + 1;}
		}
		$robots = $gbot + $msn + $baidu + $bing + $mj + $coccoc + $facebook + $yandex;
		echo '<div class="phdr">Thống kê</div>';
		echo '<div class="menu">'.counters::forum(1).'</div>';
		$newUser=mysql_fetch_assoc(mysql_query("SELECT `id`,`account` FROM `users` ORDER BY `datereg` DESC LIMIT 1"));
		echo '<div class="menu">'.($user_id || $set['active'] ? '<a href="users/index.php">' . $lng['users'] . '</a>' : '' . $lng['users'] . '').': <b>' . counters::users() . '</b>. Mới nhất: '.($user_id || $set['active'] ? '<a href="users/profile.php?user='.$newUser['id'].'">'.$newUser['account'].'</a>' : $newUser['account']).'</div>';
		echo '<div class="menu">Có '.($user_id || $set['active'] ? '<a href="users/index.php?act=online">'.$count.' người trực tuyến</a>':''.$count.' người trực tuyến').', '.$users.' thành viên, '.($guests - $robots).' khách, '.$robots.' robots</div>';
		$req = mysql_query('SELECT `id`, `account`, `rights` FROM `users` WHERE `preg`="1" and `lastdate` > "'. $onltime .'" ORDER BY `account` ASC');
		if(mysql_num_rows($req)){
			while ($res = mysql_fetch_assoc($req)) {
				if($user_id || $set['active']){
					$user_on[] = '<a href="users/profile.php?user='.$res['id'].'">' . functions::nick_color($res['account'], $res['rights']) . '</a>';
				}else{
					$user_on[] = $res['account'];
				}
			}
			echo '<div class="menu">' . implode(', ', $user_on) . ($robots ? ', ' : '');
		} else {
			echo '<div class="menu">';
		}
		if ($robots) {
			if ($gbot) $robot[] = '('.$gbot.') Google';
			if ($msn) $robot[] = '('.$msn.') MSN';
			if ($baidu) $robot[] = '('.$baidu.') Baidu';
			if ($bing) $robot[] = '('.$bing.') Bing';
			if ($mj) $robot[] = '('.$mj.') MJ12';
			if ($coccoc) $robot[] = '('.$coccoc.') CốcCốc';
			if ($facebook) $robot[] = '('.$facebook.') Facebook';
			if ($yandex)$robot[] = '('.$yandex.') Yandex';
			echo '' . implode(', ', $robot) . '</div>';
		} else {
			echo '</div>';
		}
}

require('incfiles/end.php');