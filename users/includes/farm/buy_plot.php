<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
require('../incfiles/head.php');
echo '<div class="phdr"><a href="farm.php">Nông trại</a> | Mở ô đất</div>' .
	'<div class="topmenu">Xu: '. $datauser['xu'] .'. Lượng: '. $datauser['luong'] .'</div>';
if($count < $max_plot){
	$pay = $price[$count - 6];
	if($datauser['xu'] >= $pay){
		if(isset($_POST['submit']) && isset($_POST['token']) && $_POST['token'] == $datauser['priv_key']){
			mysql_query('INSERT INTO `farm_area` SET `user_id` = "'. $user_id .'"');
			mysql_query('UPDATE `users` SET `xu` = "'. ($datauser['xu'] - $pay) .'" WHERE `id` = "'. $user_id .'"');
			header('Location: farm.php');
			exit;
		}else{
			echo '<div class="notif">Bạn có chắc chắn muốn mở thêm một ô đất? Giá của ô đất này là: '. $pay .' xu</div>' . 
			'<form action="?act=buy_plot" method="post" class="menu"><input type="hidden" name="token" value="'. $datauser['priv_key'] .'" /><input type="submit" name="submit" value="Đồng ý" /></form>';
		}
	}else{
		echo '<div class="rmenu">Bạn cần '. $pay .' xu mới có thể mở ô đất này!</div>';
	}
} else {
	echo '<div class="rmenu">Số ô đất của bạn đã đạt tối đa!</div>';
}