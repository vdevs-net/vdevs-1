<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = "Cửa hàng nông trại";
require('../incfiles/head.php');
$max_count = 99;
$lng['xu'] = 'xu';
$lng['luong'] = 'lượng';
echo '<div class="phdr"><a href="farm.php">Nông trại</a> | '. ($id ? '<a href="?act=store">Cửa hàng</a>' : 'Cửa hàng') .'</div>' .
	'<div class="topmenu">Xu: '. $datauser['xu'] .'. Lượng: '. $datauser['luong'] .'</div>';
if($id){
	$req_i = mysql_query('SELECT * FROM `farm_item` WHERE `type` = "1" AND `id` = "'. $id .'" LIMIT 1');
	if(mysql_num_rows($req_i)){
		$res_i = mysql_fetch_assoc($req_i);
		$curr = $res_i['currency'] ? 'luong' : 'xu';
		if(isset($_POST['submit']) && isset($_POST['token']) && $_POST['token'] == $datauser['priv_key']){
			$buy = isset($_POST['submit']) ? abs(intval($_POST['buy'])) : 0;
			if($buy < 1 || $buy > $max_count){
				echo '<div class="rmenu">Số lượng mua phải từ 1 đến '. $max_count .'!</div>';
				require('../incfiles/end.php');
				exit;
			}
			$pay = $buy * $res_i['price'];
			if($pay > $datauser[$curr]){
				echo '<div class="rmenu">Bạn không đủ '. $lng[$curr] .' để mua!</div>';
				require('../incfiles/end.php');
				exit;
			}
			// Update count
			$req2 = mysql_query('SELECT `id`, `count` FROM `farm_warehouse` WHERE `user_id` = "'. $user_id .'" AND `item_id` = "'. $id .'" AND `type` = "1" LIMIT 1');
			$res2 = mysql_fetch_assoc($req2);
			if($res2['count'] + $buy > $max_count){
				echo '<div class="rmenu">Hiện bạn đang có '. $res2['count'] .' vật phẩm này. Bạn chỉ có thể mua thêm tối đa '. ($max_count - $res2['count']) .' vật phẩm!</div>';
				require('../incfiles/end.php');
				exit;
			}
			mysql_query('UPDATE `farm_warehouse` SET `count` = "'. ($res2['count'] + $buy) .'" WHERE `id` = "'. $res2['id'] .'"');
			// Update money
			mysql_query('UPDATE `users` SET `'. $curr .'` = "'. ($datauser[$curr] - $pay) .'" WHERE `id` = "'. $user_id .'"');
			echo '<div class="gmenu">Mua thành công! Bạn bị trừ ' . $pay . ' ' . $lng[$curr] . '!<br /><a href="?act=store">Mua tiếp</a></div>';
		} else {
			echo '<div class="menu"><img src="' . SITE_URL . '/assets/farm/item/'. $res_i['id'] .'.png" /> <a href="?act=store&id='. $res_i['id'] .'">'. $res_i['name'] .'</a> ('. ($res_i['time'] / 3600) .' giờ)<br />Giá: '. $res_i['price'] .' xu - Sản lượng: '. $res_i['max'] .'</div><div class="menu"><form action="?act=store&id='. $res_i['id'] .'" method="post"><input type="hidden" name="token" value="'. $datauser['priv_key'] .'" /><input type="text" name="buy" pattern="([\d]{1,2})" required autocomplete="off" size="3" /> <input type="submit" name="submit" value="Mua" /></form></div>';
		}
	} else {
		echo '<div class="rmenu">Vật phẩm không tồn tại!</div>';
	}
} else {
	$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `farm_item` WHERE `type` = "1"'), 0);
	if($total){
		$req_w = mysql_query('SELECT * FROM `farm_item` WHERE `type` = "1" LIMIT '. $start .', '. $kmess .'');
		while($res_w = mysql_fetch_assoc($req_w)){
			echo '<div class="menu"><img src="' . SITE_URL . '/assets/farm/item/'. $res_w['id'] .'.png" /> <a href="?act=store&id='. $res_w['id'] .'">'. $res_w['name'] .'</a> ('. ($res_w['time'] / 3600) .' giờ)<br />Giá: '. $res_w['price'] .' xu - Sản lượng: '. $res_w['max'] .'</div>';
		}
		if($total > $kmess){
			echo '<div class="topmenu">'. functions::display_pagination('?act=store&page=', $start, $total, $kmess) . '</div>';
		}
	} else {
		echo '<div class="rmenu">Cửa hàng tạm thời đóng cửa!</div>';
	}
}