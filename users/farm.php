<?php
define('_MRKEN_CMS', 1);
$headmod = 'farm';
$title = 'Nông trại vui vẻ';
require('../incfiles/core.php');
if (!$user_id) {
    $_SESSION['ref'] = SITE_URL . '/users/farm.php';
	header('Location: ' . SITE_URL . '/login.php'); exit;
}

$add = '<link rel="stylesheet" href="' . SITE_URL . '/assets/farm/style.css" />';
$ref = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : '';
$price = array(
	10800, 14700, 19200, 24300, 30000, 36300, // 2
	43200, 50700, 58800, 67500, 76800, 86700, // 3
	97200, 108300, 120000, 132300, 145200, 158700, // 4
	172800, 187500, 202800, 212300, 235200, 252300, // 5
	270000, 288300, 307200, 326700, 364800, 367500, // 6
	388800, 410700, 433200, 456300, 480000, 504300, // 7
	529200, 554700, 580800, 607500, 634800, 662700 // 8
);
$exp = array(
	10, 20, 40, 80, 140, 245, 429, 643, 965, 1302, // 11
	1302, 1758, 2373, 3086, 3857, 4725, 6662, 7661, 8695, 9782, // 21
	11005, 12381, 13928, 15669, 17628, 19832, 22311, 25099, 28237, 32049, // 31
	36375, 41286, 46860, 53186, 60366, 68515, 77765, 88263, 100178, 117710, // 41
	138309, 162513, 190953, 224369, 263634, 309770, 363980, 427676, 502519, 585435, // 51
	682032, 794567, 925671, 1078406, 1256343, 1463640, 1705140, 1986489, 2314259, 2672969, // 61
	3087280, 3565808, 4118508, 4756877, 5494193, 6345793, 7329390, 8465446, 9777590, 12710867, // 71
	16524127, 21481365, 27925775, 37699796, 50894725, 68707878, 92755636, 125220108, 169047146 // 80
);
$vip_exp = array(
	0, 60, 300, 600, 1000, 1500
);
$vip_max_plots = array(
	0, 1, 3, 5, 8, 12
);
$max_vip_level = count($vip_exp) - 1;
$vip_level = 0;
for($i = 0; $i < $max_vip_level; $i++){
	if($datauser['vip_exp'] >= $vip_exp[$i]){
		$vip_level = $i; break;
	}
}
$max_plot = 24 + $vip_max_plots[$vip_level];
// Star fruit tree
$sft_max_level = 13;
$sft_time = 28800; // 8 hours
// percent of reduce time (VIP users)
$sft_time_vip = array(
	100, 95, 90, 85, 80, 75
);
$sft_time_per_level = 600;
// Real sft time
$sft_time = $sft_time * $sft_time_vip[$vip_level] / 100 - ($datauser['sft_level'] - 1) * $sft_time_per_level;
// minimun stf time
if($sft_time < 7200) $sft_time = 7200;
$sft_timer = $datauser['sft_time'] + $sft_time >= time() ? $datauser['sft_time'] + $sft_time - time() : 0;
function timer($time, $mod = 0){
	if($time <= 0) $time = 0;
	$h = floor($time / 3600);
	$m = floor(($time - $h * 3600) / 60);
	$s = $time - $h * 3600 - $m * 60;
	if($mod){
		return ($h ? $h . ' giờ' : '') . ($m ? ($h ? ' ':'') . $m  . ' phút' : '') . ($mod == 2 ? ($s ? ($h || $m ? ' ':'') . $s  . ' giây' : '') : '' );
	}
	return $h . ':' . ($m < 10 ? '0' : '') . $m.':' . ($s < 10 ? '0' : '') . $s;
}
function status($item = 0, $time = 0, $end_time = 0, $water_time = 0){
	if($item){
		if($end_time == 0){
			return '6';
		}
		$time_count = time() - $time;
		$water_time = time() - $water_time;
		$interval = ($end_time - $time) / 6;
		$w_interval = 2 * $interval;
		if(time() >= $end_time){
			return '5_' . ($water_time > $w_interval ? '1' : '0');
		}
		if($time_count >= $interval * 5){
			return '4_' . ($water_time > $w_interval ? '1' : '0');
		}
		if($time_count >= $interval * 4){
			return '3_' . ($water_time > $w_interval ? '1' : '0');
		}
		if($time_count >= $interval * 2){
			return '2_' . ($water_time > $w_interval ? '1' : '0');
		}		
		if($time_count >= $interval){
			return '1_0';
		}
		return '0_0';
	} else {
		return '0';
	}
}
function ns($ns = 100, $time = 0, $end_time = 0, $water_time = 0){
	if($end_time == 0) return '0';
	$time_1 = ($end_time - $time) / 3;
	$time_2 = time() - $water_time;
	if($time_2 >= $time_1){
		$ns -= floor(100 * ($time_2 - $time_1) * 0.9 / $time_1);
		if($ns < 10) $ns = 10;
	} else {
		$time_2 = min(time(), $end_time) - $water_time;
		$ns += ceil(100 * $time_2 * 3.6 / $time_1);
		if($ns > 100) $ns = 100;
	}
	return $ns;
}
$count = mysql_result(mysql_query('SELECT COUNT(*) FROM `farm_area` WHERE `user_id` = "'. $user_id .'"'), 0);
$mods = array(
	'buy_plot',
	'star_fruit_tree',
	'store',
	'warehouse'
);
if($count){ // Nếu đã có ô đất
	if($act && in_array($act, $mods) && file_exists('includes/farm/'. $act .'.php')){
		require('includes/farm/'. $act .'.php');
	} else {
		require('../incfiles/head.php');
		$req = mysql_query('SELECT `farm_area`.*, `farm_item`.`name`, `farm_item`.`max` FROM `farm_area` LEFT JOIN `farm_item` ON `farm_area`.`item_id` = `farm_item`.`id` WHERE `user_id` = "'. $user_id .'"');
		if(isset($_POST['submit']) && isset($_POST['token']) && $_POST['token'] == $datauser['priv_key']){
			// Check selected plots
			$area = isset($_POST['area']) &&  is_array($_POST['area']) ? $_POST['area'] : FALSE;
			// Check action
			$action = isset($_POST['action']) ? trim($_POST['action']) : '';
			if(!$area){
				echo '<div class="phdr"><a href="?act=farm">Nông trại</a></div>' .
					'<div class="rmenu">Chưa chọn ô đất!</div>';
				require('../incfiles/end.php');
				exit;
			}
			// Check user plots
			$planted = false; // Check if at least one area planted
			$sel_id = array(); // Check select plots
			$can_water = array(); // Check for watering
			$can_harvest = array();
			$ns = array();
			while($res = mysql_fetch_assoc($req)){
				if(in_array($res['id'], $area)){
					$sel_id[] = $res['id'];
					if($res['item_id']){
						if($res['end_time'] != 0) $planted = true;
						// id for watering
						if(time() < $res['end_time']){
							$can_water[] = $res['id'];
							$ns[$res['id']]['time'] = $res['time'];
							$ns[$res['id']]['end_time'] = $res['end_time'];
							$ns[$res['id']]['water_time'] = $res['water_time'];
							$ns[$res['id']]['ns'] = $res['ns'];
						} else { //  for harvest
							$can_harvest[] = $res['item_id'];
							$ns['area_id'][] = $res['id'];
							$ns[$res['item_id']]['time'] = $res['time'];
							$ns[$res['item_id']]['end_time'] = $res['end_time'];
							$ns[$res['item_id']]['water_time'] = $res['water_time'];
							$ns[$res['item_id']]['ns'] = $res['ns'];
							$ns[$res['item_id']]['max'] = $res['max'];
						}
					}
				}
			}
			if(empty($sel_id)){
				echo '<div class="phdr"><a href="?act=farm">Nông trại</a></div>' .
					'<div class="rmenu">Dữ liệu không đúng!</div>';
				require('../incfiles/end.php');
				exit;
			}
			switch($action){
				case 'plant':
					if($planted){
						echo '<div class="phdr"><a href="?act=farm">Nông trại</a></div>' .
							'<div class="rmenu">Ô đất bạn chọn đang có cây trồng!</div>';
						require('../incfiles/end.php');
						exit;
					}
					$tree = isset($_POST['tree']) ? abs(intval($_POST['tree'])) : 0;
					if(!$tree){
						echo '<div class="phdr"><a href="?act=farm">Nông trại</a></div>' .
							'<div class="rmenu">Dữ liệu không đúng!</div>';
						require('../incfiles/end.php');
						exit;
					}
					$req2 = mysql_query('SELECT `farm_warehouse`.`id`, `farm_warehouse`.`count`, `farm_item`.`time` FROM `farm_warehouse` LEFT JOIN `farm_item` ON `farm_item`.`id` = `farm_warehouse`.`item_id` WHERE `farm_warehouse`.`item_id` = "'. $tree .'" AND `farm_warehouse`.`user_id` = "'. $user_id .'" AND `farm_warehouse`.`type` = "1" AND `farm_warehouse`.`count` > 0 LIMIT 1');
					if(!mysql_num_rows($req2)){
						echo '<div class="phdr"><a href="?act=farm">Nông trại</a></div>' .
							'<div class="rmenu">Hạt giống bạn chọn chưa có!</div>';
						require('../incfiles/end.php');
						exit;	
					}
					$res2 = mysql_fetch_assoc($req2);
					$plant = min($res2['count'], count($sel_id));
					mysql_query('UPDATE `farm_warehouse` SET `count` = "'. ($res2['count'] - $plant)  .'" WHERE `id` = "'. $res2['id'] .'"');
					$plant_id = array();
					foreach($sel_id as $sel){
						if($plant > 0){
							$plant_id[] = $sel;
							$plant--;
							continue;
						}
						break;
					}
					mysql_query('UPDATE `farm_area` SET `item_id` = "'. $tree .'", `time` = "'. time().'", `end_time` = "'. (time() + $res2['time']) .'", `water_time` = "'. time() .'", `ns` = "100"  WHERE `id` IN ('. implode(', ', $plant_id) .')');
					header('Location: ?act=farm');
					exit;
					break;
				case 'watering':
					if($can_water){
						$sql = 'UPDATE `farm_area` SET `ns` = CASE ';
						foreach($can_water as $wid){
							$sql .= 'WHEN `id` = "'. $wid .'" THEN "'. ns($ns[$wid]['ns'], $ns[$wid]['time'], $ns[$wid]['end_time'], $ns[$wid]['water_time']) .'" ';
						}
						$sql .= 'ELSE "" END, `water_time` = "' . time() . '" WHERE `id` IN (' . implode(', ', $can_water) . ')';
						mysql_query($sql) or die(mysql_error());
					}
					header('Location: ?act=farm');
					exit;
					break;
				case 'harvest':
					if($can_harvest){
						$cth = array();
						$update = array();
						$sql = 'UPDATE `farm_warehouse` SET `count` = CASE ';
						foreach($can_harvest as $wid){
							if(isset($cth[$wid])){
								$cth[$wid] += ceil($ns[$wid]['max'] *  ns($ns[$wid]['ns'], $ns[$wid]['time'], $ns[$wid]['end_time'], $ns[$wid]['water_time']) / 100);
							} else {
								$cth[$wid] = ceil($ns[$wid]['max'] *  ns($ns[$wid]['ns'], $ns[$wid]['time'], $ns[$wid]['end_time'], $ns[$wid]['water_time']) / 100);
							}
							if(!in_array($wid, $update)) $update[] = $wid;
						}
						foreach($update as $upd){
							$sql .= 'WHEN `item_id` = "'. $upd .'" THEN (`count` + ' . $cth[$upd] . ') ';
						}
						$sql .= 'ELSE "0" END WHERE `item_id` IN (' . implode(', ', $update) . ') AND `user_id` = "' . $user_id . '" AND `type` = "0"';
						mysql_query($sql) or die(mysql_error());
						mysql_query('UPDATE `farm_area` SET `end_time` = "0" WHERE `id` IN (' . implode(', ', $ns['area_id']) . ')');
					}
					header('Location: ?act=farm');
					exit;
					break;
				default:
					echo '<div class="phdr"><a href="?act=farm">Nông trại</a></div>' .
						'<div class="rmenu">Bạn chưa chọn hành động!</div>';
					require('../incfiles/end.php');
					exit;	
					
			}
			
		} else {
			echo '<div class="phdr"><strong>Nông trại</strong></div>';
			$count2 = mysql_query('SELECT COUNT(*) FROM `farm_warehouse` WHERE `user_id` = "'. $user_id .'" AND `type` = "1" AND `count` > 0');
			echo '<div id="farm" class="farm">'.
				'<div class="farm_bg"><marquee behavior="scroll" direction="left" scrollamount="1" class="cloud_1"><img src="' . SITE_URL . '/assets/farm/cloud_1.png"></marquee><marquee behavior="scroll" direction="left" scrollamount="2" class="cloud_2"><img src="' . SITE_URL . '/assets/farm/cloud_2.png"></marquee></div>' .
				'<div class="farm_body"><div class="construction"><a href="?act=store" class="to_store"></a><a href="?act=warehouse" class="to_warehouse"></a><a href="?act=star_fruit_tree" class="to_star_fruit_tree'. ($sft_timer ? '' : ' star_fruit_tree_1') .'"><span class="timer" id="timer" data-timer="'. $sft_timer .'">'. ($sft_timer ? timer($sft_timer) : 'Đã chín!') .'</span></a></div>' .
				'<form action="?act=farm" method="post"><div class="plant_area">';
			while($res = mysql_fetch_assoc($req)){
				if($id && $res['id'] == $id){
					$data = $res;
				}
				echo '<label class="plot"><a href="?act=farm&id='. $res['id'] .'" class="item_'. $res['item_id'] .'" style="background-image:url(' . SITE_URL . '/assets/farm/item/' . $res['item_id'] . '_' . status($res['item_id'], $res['time'], $res['end_time'], $res['water_time']) . '.png)"></a><input type="checkbox" name="area[]" value="'. $res['id'] .'"'. ($id == $res['id'] ? 'checked ':'') .'></label>';
			}
			echo ($count < $max_plot ? '<label class="plot buy_plot"><a href="?act=buy_plot"></a></label>' : '') . '</div><!--/ plant area -->' .
				'<div class="controls">';
			if($count2){
				$req2 = mysql_query('SELECT `farm_warehouse`.`item_id`, `farm_warehouse`.`count`, `farm_item`.`name` FROM `farm_warehouse` LEFT JOIN `farm_item` ON `farm_warehouse`.`item_id` = `farm_item`.`id` WHERE `farm_warehouse`.`user_id` = "'. $user_id .'" AND `farm_warehouse`.`type` = "1" AND `farm_warehouse`.`count` > "0"');
				echo '<select name="tree"><option value="0">Chọn giống</option>';
				while($res2 = mysql_fetch_assoc($req2)){
					echo '<option value="' . $res2['item_id'] . '">'. $res2['name'] .' ('. $res2['count'] .')</option>';
				}
				echo '</select>&nbsp;<select name="action"><option value="0">Chọn hành động</option><option value="plant">Gieo hạt</option>';
			} else {
				echo  '<select name="action"><option value="0">Chọn hành động</option>';
			}
			echo '<option value="watering">Tưới nước</option><option value="harvest">Thu hoạch</option></select>&nbsp;<input type="hidden" name="token" value="'. $datauser['priv_key'] .'" /><input type="submit" name="submit" value="Thực hiện" /></div></form>' . 
				'</div></div>';
			if(isset($data)){
				echo '<div class="phdr">Thông tin ô đất</div>';
				if(empty($data['name']) || ns($data['ns'], $data['time'], $data['end_time'], $data['water_time']) == 0){
					echo '<div class="menu">Cây trồng: Chưa có</div>';
				} else {
					echo '<div class="menu">Cây trồng: '. $data['name'] . ' ('. ns($data['ns'], $data['time'], $data['end_time'], $data['water_time']) .'%)</div><div class="menu">'. ($data['end_time'] > time() ? 'Thời gian còn: '. timer($data['end_time'] - time()) .'' : 'Đã chín') .'</div>';
				}
			}
		}
	}
} else {
	// Create plots
	$sql = 'INSERT INTO `farm_area` (`user_id`) VALUES ("'. $user_id .'")';
	for($i = 1; $i < 6; $i++){
		$sql .= ', ("'. $user_id .'")';
	}
	mysql_query($sql);
	if(mysql_result(mysql_query('SELECT COUNT(*) FROM `farm_item` WHERE `type` = "1"'), 0)){
		$sql = 'INSERT INTO `farm_warehouse` (`user_id`, `item_id`, `type`) VALUES ';
		$sql2 = array();
		$req = mysql_query('SELECT `id` FROM `farm_item` WHERE `type` = "1"');
		while($res = mysql_fetch_assoc($req)){
			$sql2[] = '("'. $user_id .'", "'. $res['id'] .'", "0")';
			$sql2[] = '("'. $user_id .'", "'. $res['id'] .'", "1")';
		}
		$sql = $sql . implode(', ', $sql2);
		mysql_query($sql) or die(mysql_error());
	}
	mysql_query('UPDATE `users` SET `xu` = "5000", `sft_time` = "'. time() .'" WHERE `id` = "'. $user_id .'"');
	header('Location: farm.php');
	exit;
}
require('../incfiles/end.php');