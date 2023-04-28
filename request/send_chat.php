<?php
defined('_MRKEN_CMS') or die('Restricted access');
checkLogin();
function forum_link($m)
{
    global $set;
    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);
        if (('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == SITE_URL . '/forum/index.php?id=') || ('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == SITE_URL . '/forum/?id=') || ('http://' . $p['host'] == SITE_URL && isset($p['path']) && preg_match('#/forum/([^\.]+?)\.([\d]+?)\.html#', $p['path']))) {
            if(preg_match('#/forum/([^\.]+?)\.([\d]+?)\.html#', $p['path'])){
                $thid = abs(intval(preg_replace('/^([^\.]+?)\./si', '', $p['path'])));
            }else{
                $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
            }
            $req = mysql_query("SELECT `text` FROM `forum` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");
            if (mysql_num_rows($req) > 0) {
                $res = mysql_fetch_array($req);
                $name = strtr($res['text'], array(
                    '&quot;' => '',
                    '&amp;'  => '',
                    '&lt;'   => '',
                    '&gt;'   => '',
                    '&#039;' => '',
                    '['      => '',
                    ']'      => ''
                ));
                if (mb_strlen($name) > 40)
                    $name = mb_substr($name, 0, 63) . '...';

                return '[url=' . $m[3] . ']' . $name . '[/url]';
            } else {
                return $m[3];
            }
        } else
            return $m[3];
    }
}
mysql_query('UPDATE `users` SET `lastdate`="'.time().'" WHERE `id`="'.$user_id.'" LIMIT 1');
$error = '';
$text = isset($_POST['text']) ? functions::checkin($_POST['text']) : '';
$flood = functions::antiflood();
if(isset($ban['1']) || isset($ban[12])){
$error = 'Bạn đang bị cấm chat!';
}elseif(!(isset($_POST['token']) && $_POST['token'] == $datauser['priv_key'])){
	$error = 'Dữ liệu không đúng!';
}elseif($flood){
	$error = 'Bạn không được gửi tin nhắn quá nhanh! Vui lòng chờ '.$flood.' giây!';
}elseif(mb_strlen($text) < 2 || mb_strlen($text) > 1023){
	$error = 'Độ dài tin nhắn là từ 2 đến 1023 ký tự!';
}
if(empty($error)){
	if($text == '/clear' && $rights >= 6){
		mysql_query('TRUNCATE `cms_chat`');
		mysql_query('INSERT INTO `cms_chat` SET `uid`="2",`text`="đã làm sạch chatbox",`time`="'.time().'"');
		$data['status'] = '200';
		$data['res'] = 1;
	}else{
		$text = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&_=/%-:#]*)~', 'forum_link', $text);
		mysql_query('INSERT INTO `cms_chat` SET `uid`="'.$user_id.'",`text`="'.mysql_real_escape_string($text).'",`time`="'.time().'"');
		mysql_query('UPDATE `users` SET `lastpost`="'.time().'" WHERE `id`="'.$user_id.'" LIMIT 1');
		$data['res'] = $text;
		$data['status'] = '200';
	}
}else{
	$data['res'] = $error;
}
header("Content-type: application/json; charset=utf-8");
echo json_encode($data);
exit();