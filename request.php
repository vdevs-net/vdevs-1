<?php
define('_MRKEN_CMS', 1);
require('incfiles/core.php');
if($wap) {
    header('Location: ' . SITE_URL . '/?err'); exit;
}
function checkLogin(){
	global $user_id;
	if(!$user_id)
		exit('Bạn chưa đăng nhập');
}
$data = array(
	'status' => '417'
);
$acts = array(
	'load_chat',
	'send_chat'
);
if($act && in_array($act, $acts) && file_exists('request/'.$act.'.php')){
	require('request/'.$act.'.php');
}