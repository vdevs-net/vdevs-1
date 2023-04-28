<?php
define('_MRKEN_CMS', 1);

require('incfiles/core.php');

$referer = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : SITE_URL;
$url = isset($_REQUEST['url']) ? strip_tags(rawurldecode(trim($_REQUEST['url']))) : false;

if (isset($_GET['lng'])) {
	// SELECT LANGUAGE
	require('incfiles/head.php');
	echo '<form action="' . $referer . '" method="post"><div class="phdr b">' . $lng['language_select'] . '</div><div class="menu">';
	if (count(core::$lng_list) > 1) {
		foreach (core::$lng_list as $key => $val) {
			echo '<div><label class="radio"><input type="radio" value="' . $key . '" name="setlng" ' . ($key == core::$lng_iso ? 'checked="checked"' : '') . '/>&#160;' .
				(file_exists('images/flags/' . $key . '.gif') ? '<img src="images/flags/' . $key . '.gif" alt=""/>&#160;' : '') .
                 $val .
				 ($key == $set['lng'] ? ' <small class="red">[' . $lng['default'] . ']</small>' : '') .
				 '</label></div>';
		}
	}
	echo '<div class="mv"><input type="submit" name="submit" value="' . $lng['apply'] . '" /></div>' .
		'</div><div class="phdr"><a href="' . $referer . '">' . $lng['back'] . '</a></div></form>';
	require('incfiles/end.php');
} elseif ($url) {
    // Redirect the links in the text, processed function tags ()
	if (isset($_POST['submit'])) {
		header('Location: ' . $url); exit;
	} else {
		require('incfiles/head.php');
		echo '<div class="phdr"><b>' . $lng['external_link'] . '</b></div>' .
			'<div class="rmenu">' .
			'<form action="go.php?url=' . rawurlencode($url) . '" method="post">' .
			'<p>' . $lng['redirect_1'] . ':<br /><span class="red">' . htmlspecialchars($url) . '</span></p>' .
			'<p>' . $lng['redirect_2'] . '.<br />' .
			$lng['redirect_3'] . ' <span class="green">' . SITE_URL . '</span> ' . $lng['redirect_4'] . '.</p>' .
			'<p><input type="submit" name="submit" value="' . $lng['redirect_5'] . '" /></p>' .
			'</form></div>';
		require('incfiles/end.php');
	}
} elseif ($id) {
	// Redirect for advertising link
	$req = mysql_query('SELECT * FROM `cms_ads` WHERE `id` = "' . $id . '"');
	if (mysql_num_rows($req)) {
		$res = mysql_fetch_assoc($req);
		$count_link = $res['count'] + 1;
		mysql_query('UPDATE `cms_ads` SET `count` = "' . $count_link . '"  WHERE `id` = "' . $id . '"');
		header('Location: ' . $res['link']); exit;
	} else {
		header('Location: ' . SITE_URL . '/?err'); exit;
	}
}