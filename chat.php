<?php
define('_MRKEN_CMS', 1);
$headmod = 'chatroom';
require('incfiles/core.php');
if (!$user_id) {
    $_SESSION['ref'] = SITE_URL . '/chat.php';
	header('Location: ' . SITE_URL . '/login.php'); exit;
}
function forum_link($m)
{
    global $set;
    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);
        if (('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == SITE_URL . '/forum/index.php?id=') || ('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == SITE_URL. '/forum/?id=') || ('http://' . $p['host'] == SITE_URL && isset($p['path']) && preg_match('#/forum/([^\.]+?)\.([\d]+?)\.html#', $p['path']))) {
            if(preg_match('#/forum/([^\.]+?)\.([\d]+?)\.html#', $p['path'])){
                $thid = abs(intval(preg_replace('/^([^\.]+?)\./si', '', $p['path'])));
            }else{
                $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
            }
            $req = mysql_query("SELECT `text` FROM `forum` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");
            if (mysql_num_rows($req) > 0) {
                $res = mysql_fetch_array($req);
                $name = strtr($res['text'], array(
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
$text = '';
$error = '';
if(isset($_POST['submit'])){
	$text = isset($_POST['text']) ? functions::checkin($_POST['text']) : '';
	$flood = functions::antiflood();
	if(isset($ban['1']) || isset($ban['12'])){
		$error = 'Bạn đang bị cấm chat!';
	}elseif(!(isset($_POST['token']) && $_POST['token'] == $datauser['priv_key'])){
		$error = 'Dữ liệu không đúng!';
	}elseif($flood){
		$error = 'Bạn không được gửi tin nhắn quá nhanh! Vui lòng chờ '.$flood.' giây!';
	}elseif(empty($text) || mb_strlen($text) < 2 || mb_strlen($text) > 1023){
		$error = 'Độ dài tin nhắn là từ 2 đến 1023 ký tự!';
	}
	if(empty($error)){
		if($text == '/clear' && $rights >= 6){
			mysql_query('TRUNCATE `cms_chat`');
			mysql_query('INSERT INTO `cms_chat` SET `uid`="2",`text`="đã làm sạch chatbox",`time`="'.time().'"');
			header('Location: '. SITE_URL); exit;
		}else{
			$text = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&_=/%-:#]*)~', 'forum_link', $text);
			$query = mysql_query('INSERT INTO `cms_chat` SET `uid`="'.$user_id.'",`text`="'.mysql_real_escape_string($text).'",`time`="'.time().'"');
			if($query){
				mysql_query('UPDATE `users` SET `lastpost`="'.time().'" WHERE `id`="'.$user_id.'" LIMIT 1');
				if(isset($_GET['in'])){
					header('Location: ' . SITE_URL);
				}else{
					header('Location: ' . SITE_URL . '/chat.php?r='.rand(1000,9999).'');
				}
				exit;
			}
		}
	}
}
require('incfiles/head.php');
$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_chat`'),0);
echo '<div class="phdr"><b>Phòng chat</b> (<span id="total">'.$total.'</span>)</div>';
if(!empty($error)) echo functions::display_error($error);
echo '<div id="error" class="hide rmenu"></div><div class="topmenu"><form action="chat.php" method="post" name="chat" id="chat">'.bbcode::auto_bb('chat','chat_input').'<div><textarea name="text" id="chat_input" rows="'.$set_user['field_h'].'" required></textarea></div><div><input type="hidden" name="token" value="'.$datauser['priv_key'].'" /><input type="submit" name="submit" value="Gửi" id="chat_submit"></div></div><div id="chatbox">';
$req = mysql_query('SELECT `cms_chat`.*,`users`.`account`,`users`.`rights` FROM `cms_chat` LEFT JOIN `users` ON `users`.`id`=`cms_chat`.`uid` ORDER BY `cms_chat`.`id` DESC LIMIT '.$start.','.$kmess.'');
$i=0;
while($res = mysql_fetch_assoc($req)){
	$text = functions::checkout($res['text'], 1, 1, 1);
	echo ($i%2 ? '<div class="list1">':'<div class="list2">').'<b><a href="users/profile.php?user='.$res['uid'].'" title="'.functions::display_date($res['time']).'">'.functions::nick_color($res['account'], $res['rights']).'</a></b>: '.$text.'</div>';
	$i++;
}
echo '</div>'.(!$wap || $total > $kmess ? '<div class="topmenu" id="pagination">' : '');
if($total > $kmess) echo ''.functions::display_pagination('chat.php?page=',$start,$total,$kmess).'';
echo (!$wap || $total > $kmess ? '</div>' : '');
require('incfiles/end.php');