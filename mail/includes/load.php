<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
$textl = $lng['mail'];
require_once('../incfiles/head.php');
if($id) {
	$req = mysql_query("SELECT * FROM `cms_mail` WHERE (`user_id`='$user_id' OR `from_id`='$user_id') AND `id` = '$id' AND `file_name` != '' AND `delete`!='$user_id' LIMIT 1");
    if (mysql_num_rows($req) == 0) {
		echo functions::display_error($lng_mail['file_does_not_exist']);
        require_once("../incfiles/end.php");
        exit;
    }
	$res = mysql_fetch_assoc($req);
	if(file_exists('../files/mail/' . $res['file_name'])) {
		mysql_query("UPDATE `cms_mail` SET `count` = `count`+1 WHERE `id` = '$id' LIMIT 1");
		Header('Location: ../files/mail/' . $res['file_name']);
		exit;
	} else {
		echo functions::display_error($lng_mail['file_does_not_exist']);
	}
} else {
	echo functions::display_error($lng_mail['file_is_not_chose']);
}