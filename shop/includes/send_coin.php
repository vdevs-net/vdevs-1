<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
echo '<div class="phdr"><a href="' . SITE_URL . '/shop/"><b>Cửa hàng</b></a> | Chuyển xu</div>';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$coin = isset($_POST['coin']) ? abs(intval($_POST['coin'])) : 0;
if(isset($_POST['submit'])){
	if(empty($name) || $coin < 100 || $name == $login || $coin%10 != 0 || intval($coin * 1.1) > $datauser['coin']){
		echo functions::display_error('Dữ liệu không đúng!');
		require('../incfiles/end.php');
		exit;
	}
	$req = mysql_query('SELECT `id`,`coin` FROM `users` WHERE `account`="'.mysql_real_escape_string($name).'" LIMIT 1');
	if(mysql_num_rows($req)){
		$res = mysql_fetch_assoc($req);
		$cr_new = $res['coin'] + $coin; // new coin value of receiving user
		$cs_new = $datauser['coin'] - intval($coin*1.1); // new coin value of sending user
		mysql_query('UPDATE `users` SET `coin`="'.$cr_new.'" WHERE `id`="'.$res['id'].'" LIMIT 1');
		mysql_query('UPDATE `users` SET `coin`="'.$cs_new.'" WHERE `id`="'.$user_id.'" LIMIT 1');
		mysql_query('INSERT INTO `cms_log` (`type`,`uid`,`pid`,`time`,`text`) VALUES ("2","'.$user_id.'","'.$res['id'].'","'.time().'","'.$coin.'"),("3","'.$res['id'].'","'.$user_id.'","'.time().'","'.$coin.'")');
		mysql_query('INSERT INTO `cms_chat` SET `uid`="2", `text`="'.$login.' vừa chuyển cho '.$name.' '.$coin.' xu!", `time`="'.time().'"');
		mysql_query('INSERT INTO `cms_mail` SET `user_id` = "0", `from_id` = "' . $res['id'] . '", `text` = "Bạn vừa nhận được '.$coin.' xu từ [url=' . SITE_URL . '/users/profile.php?user='.$user_id.']'.$login.'[/url]", `time` = "' . time() . '", `sys` = "1", `them` = "'.$login.' đã chuyển xu cho bạn"');
		echo '<div class="gmenu">Chuyển xu thành công cho '.$name.'. Bạn bị trừ '.$coin.' xu và 10% phí giao dịch</div>';
		require('../incfiles/end.php');
		exit;
	}else{
		echo functions::display_error('Người dùng không tồn tại!');
	}
}
echo '<div class="menu">Chuyển tối thiểu 100 xu và là bội số của 10.<br/>Phí chuyển đổi: 10%.<br/>VD: Gửi 100xu -> mất 110 xu, người nhận được 100 xu</div>'.
	'<form action="?act=send_coin" method="post" class="gmenu">'.
	'<div><h3>Tên người nhận</h3><input type="text" name="name" value="'.$name.'" required/></div>'.
	'<div><h3>Số xu chuyển</h3><input type="text" name="coin" value="'.$coin.'" required/></div>'.
	'<div><br/><input type="submit" name="submit" value="Xác nhận" required/></div>'.
	'</form>';