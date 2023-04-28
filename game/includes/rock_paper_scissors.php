<?php
defined('_MRKEN_CMS') or die('ERROR!');
$textl = 'Chơi oẳn tù tì';
require('../incfiles/head.php');
echo '<div class="phdr"><a href="index.php"><b>Game</b></a> | Oẳn tù tì</div>';
if(!isset($_SESSION['lt'])) $_SESSION['lt'] = 0;
$phi = 50;
$an = 120;
$them = 120;
if(isset($_POST['submit']) && isset($_POST['select'])){
	if($datauser['coin'] < $phi){
		echo functions::display_error('Bạn không đủ xu để chơi vòng này!', '<a href="?act=rock_paper_scissors">Trở lại</a>');
		require('../incfiles/end.php');
		exit;
	}
	if(mysql_result(mysql_query('SELECT `coin` FROM `users` WHERE `id`= "2"'), 0) < $an){
		echo functions::display_error('Nhà cái phá sản rồi! Vui lòng quay lại sau!', '<a href="?act=rock_paper_scissors">Trở lại</a>');
		require('../incfiles/end.php');
		exit;
	}
	$flood = functions::antiflood();
	if($flood){
		echo functions::display_error('Vui lòng chờ'. $flood . 'giây để chơi tiếp!', '<a href="?act=rock_paper_scissors">Trở lại</a>');
		require('../incfiles/end.php');
		exit;
	}
	$select = intval($_POST['select']);
	if($select < 1 || $select > 3){
		echo functions::display_error($lng['error_wrong_data'], '<a href="?act=rock_paper_scissors">Thử lại</a>');
		require('../incfiles/end.php');
		exit;
	}
	$mang = array(
		1 => 'Kéo',
		2 => 'Búa',
		3 => 'Bao'
	);
	$bot = mt_rand(1, 3);
	$win = array(
		'Gà quá thím ei! <img src="' . SITE_URL . '/images/smileys/user/other/m4.png"/>',
		'Haha! Lại thắng nữa rồi! <img src="' . SITE_URL . '/images/smileys/user/other/m7.png"/>',
		'Thật là dễ dàng! <img src="' . SITE_URL . '/images/smileys/user/other/win.png"/>',
		'Đừng mơ bắt gà! <img src="' . SITE_URL . '/images/smileys/user/other/yao.png"/>',
		'Tui là BOT, tui có quyền! <img src="' . SITE_URL . '/images/smileys/user/other/gay.png"/>',
		'Cố lên thím! <img src="' . SITE_URL . '/images/smileys/user/other/troll.png"/>',
		'Đừng nản nha! <img src="' . SITE_URL . '/images/smileys/user/other/ngo.png"/>'
	);
	$lose = array(
		'Chỉ là may mắn thôi! <img src="' . SITE_URL . '/images/smileys/user/other/hum.png"/>',
		'Ta mà thua à! <img src="' . SITE_URL . '/images/smileys/user/other/wtf2.png"/>',
		'Lại nữa! <img src="' . SITE_URL . '/images/smileys/user/other/why.png"/>',
		'Đừng chơi hack nha! <img src="' . SITE_URL . '/images/smileys/user/other/huh.png"/>',
		'Nhường thím ván này! <img src="' . SITE_URL . '/images/smileys/user/other/notok.png"/>'
	);
	if($select == $bot){
		$_SESSION['lt'] = 0;
		echo '<div class="notif">Cả hai cùng chọn '. $mang[$bot] .'! Kết quả hòa!</div>';
		echo '<div class="list1"><b><a href="users/profile.php?user=2" title="Vừa xong">BOT</a></b>: '. $win[mt_rand(0, count($win) - 1)] .'</div>';
		mysql_query('UPDATE `users` SET `coin` = "'. ($datauser['coin'] - $phi) .'", `lastpost`="'. time() .'" WHERE `id` = "'.$user_id.'" LIMIT 1');
		mysql_query('UPDATE `users` SET `coin` = (`coin` + '. $phi .') WHERE `id` = "2" LIMIT 1');
	} elseif(($select == 1 && $bot == 2) || ($select == 2 && $bot == 3) || ($select == 3 && $bot == 1)) {
		$_SESSION['lt'] = 0;
		echo '<div class="rmenu">Đối phương chọn '. $mang[$bot] .'! Bạn chọn '. $mang[$select] .'! Bạn thua rồi!</div>';
		echo '<div class="list1"><b><a href="users/profile.php?user=2" title="Vừa xong">BOT</a></b>: '. $win[mt_rand(0, count($win) - 1)] .'</div>';
		mysql_query('UPDATE `users` SET `coin` = "'. ($datauser['coin'] - $phi) .'", `lastpost`="'. time() .'" WHERE `id` = "'.$user_id.'" LIMIT 1');
		mysql_query('UPDATE `users` SET `coin` = (`coin` + '. $phi .') WHERE `id` = "2" LIMIT 1');
	} else {
		$_SESSION['lt']++;
		$coin_plus = 0;
		if($_SESSION['lt'] == 3){
			$coin_plus = $them;
            mysql_query('INSERT INTO `cms_chat` SET `uid`="2", `text`="Chúc mừng [url=' . SITE_URL . '/users/profile.php?user='. $user_id .']'. $login .'[/url] đã thắng 3 lần liên tiếp trong [url=' . SITE_URL . '/game/?act=rock_paper_scissors]Oẳn Tù Tì[/url] và nhận '. ($an + $coin_plus) .' xu!", `time`="'. time() .'"');
			unset($_SESSION['lt']);
		}
		$cong = $an + $coin_plus - $phi;
		echo '<div class="gmenu">Đối phương chọn '. $mang[$bot] .'! Bạn chọn '. $mang[$select] .'! Chúc mừng bạn đã dành chiến thắng! Bạn được cộng '. ($an + $coin_plus) .' xu!</div>';
		echo '<div class="list1"><b><a href="users/profile.php?user=2" title="Vừa xong">BOT</a></b>: '. $lose[mt_rand(0, count($lose) - 1)] .'</div>';
		mysql_query('UPDATE `users` SET `coin` = "'. ($datauser['coin'] + $cong) .'", `lastpost`="'. time() .'" WHERE `id` = "'.$user_id.'" LIMIT 1');
		mysql_query('UPDATE `users` SET `coin` = (`coin` - '. $cong .') WHERE `id` = "2" LIMIT 1');
	}
	echo '<div class="menu"><a href="?act=rock_paper_scissors">Chơi lại</a></div>';
}else{
	echo '<div class="notif">QUY TẮC: mỗi lượt chơi sẽ tốn '. $phi .' xu! Thắng nhận được '. $an .' xu! Thắng 3 lần liên tiếp thưởng thêm '. $them .' xu! Chúc các bạn may mắn!</div>';
	echo '<form action="?act=rock_paper_scissors" method="post">' .
	'<table width="100%" class="menu" border="0" cellpadding="0" cellspacing="0"><tr valign="middle"><td width="33%" align="center"><label for="s_1"><img src="images/ott/keo.png" max-width="100%"/></label></td><td width="34%" align="center"><label for="s_2"><img src="images/ott/bua.png" max-width="100%"/></label></td><td width="33%" align="center"><label for="s_3"><img src="images/ott/bao.png" max-width="100%"/></label></td></tr><tr valign="middle"><td width="33%" align="center"><input type="radio" name="select" value="1" id="s_1"/></td><td width="34%" align="center"><input type="radio" name="select" value="2" id="s_2"/></td><td width="33%" align="center"><input type="radio" name="select" value="3" id="s_3"/></td></tr></table>'.
	'<div class="menu"><input type="submit" name="submit" value="Chơi"/></div>' .
	'</form>';
}