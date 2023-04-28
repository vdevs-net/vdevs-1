<?php
define('_MRKEN_CMS', 1);
$headmod = 'shop';
require('../incfiles/core.php');
if (!$user_id) {
    $_SESSION['ref'] = SITE_URL . '/shop/';
	header('Location: ' . SITE_URL . '/login.php'); exit;
}
$textl = 'Cửa hàng';
require('../incfiles/head.php');
if (empty($_SESSION['ref'])) {
    $_SESSION['ref'] = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : SITE_URL;
}
$array = array(
	'send_coin'
);
if ($act && in_array($act, $array) && file_exists('includes/' . $act . '.php')) {
	require('includes/' . $act . '.php');
} else {
	echo '<div class="phdr">Dịch vụ</div>'.
	'<div class="list1"><a href="?act=send_coin">Tặng xu</a></div>'.
	'<div class="list1"><a href="?act=history">Lịch sử giao dịch</a></div>';
}
require('../incfiles/end.php');