<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$sft_product_per_level = 10;
$sft_product = 40 + $datauser['sft_level'] * $sft_product_per_level;
$sft_price = array(
	500, 2000, 4500, 8000, 12500, 18000, // 7
	24500, 32000, 40500, 55000, 66550, 79950 // 13
);
require('../incfiles/head.php');
echo '<div class="phdr"><a href="farm.php">Nông trại</a> | '. ($mod ? '<a href="?act=star_fruit_tree">Cây khế</a>' : 'Cây khế') .'</div><div class="farm">';
echo '<div class="center star_fruit_tree_2"><div><img src="' . SITE_URL . '/assets/farm/star_fruit_tree'. ( $sft_timer ? '' : '_1') .'.png" /></div><span class="textbox ib bold">Cây khế Lv.'. $datauser['sft_level'] . ($datauser['sft_level'] < $sft_max_level && $sft_timer && $mod != 'upgrade' ? ' - <a href="?act=star_fruit_tree&mod=upgrade">Tăng</a>':'') . '</strong></span></div><div class="controls">';
if($sft_timer){
	if($mod == 'upgrade'){
		if($datauser['sft_level'] < $sft_max_level){
			if(isset($_POST['submit']) && isset($_POST['token']) && $_POST['token'] == $datauser['priv_key']){
				if($datauser['xu'] >= $sft_price[$datauser['sft_level'] - 1]){
					$datauser['xu'] = $datauser['xu'] - $sft_price[$datauser['sft_level'] - 1];
					mysql_query('UPDATE `users` SET `xu` = "'. $datauser['xu'] .'", `sft_level` = "'. ($datauser['sft_level'] + 1) .'" WHERE `id` = "'. $user_id .'"');
					echo '<div class="textbox">Nâng cấp thành công!</div>';
				}else{
					echo '<div class="textbox bg-notif">Bạn cần '. $sft_price[$datauser['sft_level'] - 1] .' xu mới có thể nâng cấp cây khế!</div>';
				}
			}else{
				echo '<div class="textbox">Cấp tiếp theo: '. ($datauser['sft_level'] + 1) .'<br/>Thời gian sinh trưởng: '. timer($sft_time - $sft_time_per_level, 1) .'<br/>Sản lượng: '. ($sft_product + $sft_product_per_level) .' quả (10 xu/quả).<br/>Phí nâng cấp: '. ($sft_price[$datauser['sft_level'] - 1]) .' xu</div><form action="?act=star_fruit_tree&mod=upgrade" class="mt5" method="post"><input type="hidden" name="token" value = "'. $datauser['priv_key'] .'" /><input type="submit" name="submit" value="Nâng cấp" /></form>';
			}
		}else{
			echo '<div class="textbox">Cây khế đã đạt cấp tối đa!</div>';
		}
	} else {
		echo '<div class="textbox">Sản lượng: '. $sft_product .'<br/>Còn '. timer($sft_timer, 2) .' mới có thể thu hoạch</div>' .
			($datauser['sft_level'] < $sft_max_level ? '<div class="textbox">Cây khế level càng cao phát triển càng nhanh. Khi khế đang phát triển mới có thể tiến hành nâng cấp!<br/>Nâng cấp VIP cũng tăng tốc độ sinh trưởng của khế</div>' : '');
	}
} else {
	if(isset($_POST['submit']) && isset($_POST['token']) && $_POST['token'] == $datauser['priv_key']){
		$req = mysql_query('SELECT `count` FROM `farm_warehouse` WHERE `item_id` = "1" AND `user_id` = "'. $user_id .'" LIMIT 1');
		if(mysql_num_rows($req)){
			$res = mysql_fetch_assoc($req);
			mysql_query('UPDATE `farm_warehouse` SET `count` = "'. ($res['count'] + $sft_product) .'" WHERE `user_id` = "'. $user_id .'" AND `item_id` = "1" LIMIT 1');
		}else{
			mysql_query('INSERT INTO `farm_warehouse` SET `user_id` = "'. $user_id .'", `item_id` = "1", `count` = "'. $sft_product .'"');
		}
		mysql_query('UPDATE `users` SET `sft_time` = "'. time() .'" WHERE `id` = "'. $user_id .'" LIMIT 1');
		echo '<div class="textbox">Thu hoạch thành công! Bạn nhận '. $sft_product .' quả khế vào kho!<br/><a href="?act=star_fruit_tree&mod=upgrade">Nâng cấp</a> cây khế để tăng sản lượng và giảm thời gian sinh trưởng!</div>';
	}else{
		echo '<form action="?act=star_fruit_tree" method="post"><input type="hidden" name="token" value = "'. $datauser['priv_key'] .'" /><input type="submit" name="submit" value="Thu hoạch" /></form>';
	}
}
echo '</div></div>';