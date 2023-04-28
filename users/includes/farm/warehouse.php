<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = "Nhà kho";
require('../incfiles/head.php');
$req_w = mysql_query('SELECT `farm_warehouse`.`item_id`, `farm_warehouse`.`count`, `farm_item`.`name`, `farm_item`.`cost` FROM `farm_warehouse` LEFT JOIN `farm_item` ON `farm_warehouse`.`item_id` = `farm_item`.`id` WHERE `farm_warehouse`.`user_id` = "'. $user_id .'" AND `farm_warehouse`.`type` = "0" AND `count` > 0');
if(mysql_num_rows($req_w)){
	if(isset($_POST['submit']) && isset($_POST['token']) && $_POST['token'] == $datauser['priv_key']){
		$sell = isset($_POST['sell']) && is_array($_POST['sell']) ? $_POST['sell'] : FALSE;
		if($sell){
			$coin_plus = 0;
			$sell_id = array();
			while($res_w = mysql_fetch_assoc($req_w)){
				if(in_array($res_w['item_id'], $sell)){
					$sell_id[] = $res_w['item_id'];
					$coin_plus += $res_w['cost'] * $res_w['count'];
				}
			}
			if(empty($sell_id)){
				echo '<div class="phdr"><a href="farm.php">Nông trại</a> | <a href="?act=warehouse">Nhà kho</a></div>' .
					'<div class="rmenu">Vật phẩm không tồn tại!</div>';
			}else{
				mysql_query('UPDATE `farm_warehouse` SET `count` = "0" WHERE `item_id` IN ('. implode(', ', $sell_id) .') AND `type` = "0" AND `user_id` = "'. $user_id .'"');
				mysql_query('UPDATE `users` SET `xu` = "'. ($datauser['xu'] + $coin_plus) .'" WHERE `id` = "'. $user_id .'"');
				echo '<div class="phdr"><a href="farm.php">Nông trại</a> | <a href="?act=warehouse">Nhà kho</a></div>' .
					'<div class="menu">Bán thành công! Bạn nhận được ' . $coin_plus . ' xu.</div>';
			}
		}else{
			echo '<div class="phdr"><a href="farm.php">Nông trại</a> | <a href="?act=warehouse">Nhà kho</a></div>' .
				'<div class="rmenu">Bạn chưa chọn vật phẩm cần bán!</div>';
		}
	} else {
		echo '<div class="phdr"><a href="farm.php">Nông trại</a> | Nhà kho</div>' .
			'<div class="topmenu">Xu: '. $datauser['xu'] .'. Lượng: '. $datauser['luong'] .'</div>' .
			'<form action="?act=warehouse" method="post">';
		while($res_w = mysql_fetch_assoc($req_w)){
			echo '<div class="menu"><input type="checkbox" name="sell[]" value="'. $res_w['item_id'] .'" /><img src="' . SITE_URL . '/assets/farm/item/'. $res_w['item_id'] .'.png" /> - '. $res_w['name'] .' ('. $res_w['count'] .')<br />Giá bán: '. ($res_w['cost'] * $res_w['count']) .' xu</div>';
		}
		echo '<div class="notif"><input type="hidden" name="token" value="'. $datauser['priv_key'] .'" /><input type="submit" name="submit" value=" Bán " /></div></form>';
	}
} else {
	echo '<div class="phdr"><a href="farm.php">Nông trại</a> | Nhà kho</div>' .
		'<div class="topmenu">Xu: '. $datauser['xu'] .'. Lượng: '. $datauser['luong'] .'</div>' .
		'<div class="rmenu">Chưa có gì trong kho!</div>';
}