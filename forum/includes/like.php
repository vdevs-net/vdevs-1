<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
if (!$user_id || !$id) {
    header('Location: index.php');
    exit;
}
$req = mysql_query('SELECT `user_id`, `refid` FROM `forum` WHERE `type`="m" AND `id`="'.$id.'" LIMIT 1');
if(mysql_num_rows($req)){
	$res = mysql_fetch_assoc($req);	
	$text = mysql_result(mysql_query('SELECT `text` FROM `forum` WHERE `type`="t" AND `id`="'.$res['refid'].'"'), 0);
	$cpg = ceil(mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type`="m" AND `refid` = "' . $res['refid'] . '" AND `id` <= "'. $id .'"' . ($rights < 7 ? ' AND `close` != "1"' : '')), 0) / $kmess);
	if(isset($_GET['likes'])){
		/* show list users like this post */
		$textl = 'Người dùng thích bài viết';
		require('../incfiles/head.php');
		echo '<div class="phdr"><a href="'. functions::bodau($text) .'.'. $res['refid'] .'.html?page='.$cpg.'#post'.$id.'">Diễn đàn</a> | Người dùng thích bài viết</div>';
		$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_likes` WHERE `type`="1" AND `sub_id` = "'. $id .'"'), 0);
		if($total){
			$req2 = mysql_query('SELECT `cms_likes`.`user_id`,`users`.`account` FROM `cms_likes` LEFT JOIN `users` ON `users`.`id` = `cms_likes`.`user_id` WHERE `cms_likes`.`type`="1" AND `cms_likes`.`sub_id`="'.$id.'" ORDER BY `cms_likes`.`id` DESC LIMIT '.$start.', '.$kmess.'');
			$i = 1;
			while($res2 = mysql_fetch_assoc($req2)){
				echo '<div class="list'. ($i%2 + 1) .'"><a href="' . SITE_URL . '/users/profile.php?user='. $res2['user_id'] .'">'. htmlspecialchars($res2['account']) .'</a></div>';
			}
			if($total > $kmess){
				echo '<div class="topmenu">'. functions::display_pagination('index.php?act=like&id='. $id .'&likes&page=', $start, $total, $kmess) .'</div>';
			}
		}else{
			echo '<div class="rmenu">'. $lng['list_empty'] .'</div>';
		}
	}elseif($res['user_id'] != $user_id ){
		/* check if liked */
		$chkl = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_likes` WHERE `type`="1" AND `user_id`="'. $user_id .'" AND `sub_id`="'. $id .'"'), 0);
		if($chkl){
			mysql_query('DELETE FROM `cms_likes` WHERE `type`="1" AND `user_id`="'.$user_id.'" AND `sub_id`="'. $id .'"');
			mysql_query('DELETE FROM `cms_mail` WHERE `user_id`="0" AND `sys`="1" AND `from_id` = "'.$res['user_id'].'" AND `text` LIKE "%'.$user_id.']'.$login.'%" AND `text` LIKE "%#post'.$id.'%" LIMIT 1');
		}else{
			mysql_query('INSERT INTO `cms_likes` SET `type`="1", `user_id`="'.$user_id.'", `sub_id`="'. $id .'"');
			/* Send notification */
			$msg = '[url=' . SITE_URL . '/users/profile.php?user='.$user_id.']'.$login.'[/url] đã thích bài viết của bạn tại chủ đề [url=' . SITE_URL . '/forum/'. functions::bodau($text) .'.'. $res['refid'] .'.html?page='.$cpg.'#post'.$id.']'. $text .'[/url]';
			mysql_query('INSERT INTO `cms_mail` SET `user_id` = "0", `from_id` = "' . $res['user_id'] . '", `text` = "'. mysql_real_escape_string($msg) .'", `time` = "' . time() . '", `sys` = "1", `them` = "Thông báo"');
		}
		header('location: '. functions::bodau($text) .'.'. $res['refid'] .'.html?page='.$cpg.'#post'.$id.'');
		exit;
	}else{
		header('Location: index.php');
		exit;
	}
}else{
    header('Location: index.php');
    exit;
}