<?php
define('_MRKEN_CMS', 1);
require_once ('../incfiles/core.php');
header('content-type: application/rss+xml');
echo '<?xml version="1.0" encoding="utf-8"?>' .
     '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel>' .
     '<title>' . htmlspecialchars($set['copyright']) . ' | News</title>' .
     '<link>' . SITE_URL . '</link>' .
     '<description>News</description>' .
     '<language>vi-VN</language>';

// Новости
$req = mysql_query('SELECT * FROM `news` ORDER BY `time` DESC LIMIT 15');
if (mysql_num_rows($req)) {
    while ($res = mysql_fetch_assoc($req)) {
        echo '<item>' .
             '<title>News: ' . $res['name'] . '</title>' .
             '<link>' . SITE_URL . '/news/index.php</link>' .
             '<author>' . htmlspecialchars($res['avt']) . '</author>' .
             '<description>' . htmlspecialchars($res['text']) . '</description>' .
             '<pubDate>' . date('r', $res['time']) .
             '</pubDate>' .
             '</item>';
    }
}

// Библиотека
$req = mysql_query('SELECT `forum`.`id`,`forum`.`from`,`forum`.`time`,`forum`.`text`,`msg`.`text` as `post` FROM `forum` LEFT JOIN (SELECT `refid`,`text` FROM `forum` WHERE `type`="m" ORDER BY `id` ASC) as `msg` ON `msg`.`refid`=`forum`.`id` WHERE `forum`.`type`="t" and `forum`.`close`="0" GROUP BY `forum`.`id`ORDER BY `forum`.`time` DESC LIMIT 15');
if (mysql_num_rows($req)) {
    while ($res = mysql_fetch_array($req)) {
		// get description
		$matches = preg_split('#(\r\n|[\r\n]|\.)#',$res['post']);
		$description = '';
		foreach($matches as $match){
			if(mb_strlen($description) < 200) $description .= $match.' ';
			else break;
		}
		$description = functions::checkout($description,2,2);
        echo '<item>' .
             '<title>Forum: ' . htmlspecialchars($res['text']) . '</title>' .
             '<link>' . SITE_URL . '/forum/'.functions::bodau($res['text']).'.' . $res['id'] . '.html</link>' .
             '<author>' . htmlspecialchars($res['from']) . '</author>' .
             '<description>' . $description .'</description>' .
             '<pubDate>' . date('r', $res['time']) . '</pubDate>' .
             '</item>';
    }
}
echo '</channel></rss>';