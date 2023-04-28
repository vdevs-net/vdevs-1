<?php
defined('_MRKEN_CMS') or die('Restricted access');
checkLogin();
mysql_query('UPDATE `users` SET `lastdate`="'.time().'" WHERE `id`="'.$user_id.'" LIMIT 1');
$error = '';
$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_chat`'),0);
if(isset($_GET['kmess'])) $kmess = abs(intval($_GET['kmess']));
$req = mysql_query('SELECT `cms_chat`.*,`users`.`account`,`users`.`rights` FROM `cms_chat` LEFT JOIN `users` ON `users`.`id`=`cms_chat`.`uid` ORDER BY `id` DESC LIMIT '.$start.','.$kmess.'');
while($res = mysql_fetch_assoc($req)){
	$text = functions::checkout($res['text'], 1, 1, 1);
	$data['message'][] = array(
		'uid'     => $res['uid'],
		'author'  => functions::nick_color($res['account'], $res['rights']),
		'text'    => $text,
		'time'    => functions::display_date($res['time']),
	);
}
$data['start'] = 0;
if($do == 'chatroom') $data['start'] = $start;
$data['status'] = 200;
$data['total'] = (int)$total;

header("Content-type: application/json; charset=utf-8");
echo json_encode($data);
exit();