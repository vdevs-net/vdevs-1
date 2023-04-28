<?php
define('_MRKEN_CMS', 1);
$headmod = 'game';
require('../incfiles/core.php');
if(!$user_id){
	echo functions::display_error($lng['access_guest_forbidden']);
	require('../incfiles/end.php');
	exit;
}
$mods = array(
	'rock_paper_scissors'
);
if($act && in_array($act, $mods) && file_exists('includes/'. $act .'.php')){
	require('includes/'. $act .'.php');
}else{
	$textl = 'Game';
	require('../incfiles/head.php');
	echo '<div class="phdr">Danh sách trò chơi</div>' .
		'<div class="menu"><a href="?act=rock_paper_scissors">Oẳn tù tì</a></div>';
}
require('../incfiles/end.php');