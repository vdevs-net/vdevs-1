<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
require('../incfiles/head.php');
$delf = opendir('../files/forum/topics');
$tm = array();
while ($tt = readdir($delf)) {
    if ($tt != "." && $tt != ".." && $tt != 'index.php' && $tt != '.svn') {
        $tm[] = $tt;
    }
}
closedir($delf);
$totalt = count($tm);
for ($it = 0; $it < $totalt; $it++) {
    $filtime[$it] = filemtime("../files/forum/topics/$tm[$it]");
    $tim = time();
    $ftime1 = $tim - 300;
    if ($filtime[$it] < $ftime1) {
        unlink("../files/forum/topics/$tm[$it]");
    }
}
if (!$id) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
$req = mysql_query("SELECT `text` FROM `forum` WHERE `id` = '$id' AND `type` = 't' AND `close` != '1'");
if (!mysql_num_rows($req)) {
    echo functions::display_error($lng['error_wrong_data']);
    require('../incfiles/end.php');
    exit;
}
$type1 = mysql_fetch_assoc($req);
if (isset($_POST['submit'])) {
    $tema = mysql_query("SELECT * FROM `forum` WHERE `refid` = '$id' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `id` ASC");
    $mod = intval($_POST['mod']);
    switch ($mod) {
        case 1:
            // Save theme in text format
            $text = $type1['text'] . "\r\n\r\n";
            while ($arr = mysql_fetch_assoc($tema)) {
                $txt_tmp = str_replace('[quote]', $lng_forum['cytate'] . ':{', $arr['text']);
                $txt_tmp = str_replace('[/quote]', '}-' . $lng_forum['answer'] . ':', $txt_tmp);
                $txt_tmp = str_replace("&quot;", '"', $txt_tmp);
                $txt_tmp = str_replace("[l]", "", $txt_tmp);
                $txt_tmp = str_replace("[l/]", "-", $txt_tmp);
                $txt_tmp = str_replace("[/l]", "", $txt_tmp);
                $stroka = $arr['from'] . '(' . date("d.m.Y/H:i", $arr['time']) . ")\r\n" . $txt_tmp . "\r\n\r\n";
                $text .= $stroka;
            }
            $num = time() . $id;
            $fp = fopen("../files/forum/topics/$num.txt", "a+");
            flock($fp, LOCK_EX);
            fputs($fp, "$text\r\n");
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            @chmod("$fp", 0777);
            @chmod("../files/forum/topics/$num.txt", 0777);
            echo '<div class="phdr">' . $lng_forum['download_topic'] . '</div><div class="list1"><a href="index.php?act=loadtem&n=' . $num . '">' . $lng['download'] . '</a></div><div class="rmenu">' . $lng_forum['download_topic_help'] . '</div><div class="list1"><a href="'.functions::bodau($type1['text']).'.'.$id.'.html">'.$lng['back'].'</a></div>';
            break;

        case 2:
            // Save the theme in HTML format
            $text = '<!DOCTYPE html PUBLIC \'-//W3C//DTD HTML 4.01 Transitional//EN\'><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>' . $lng['forum'] . '</title><style type="text/css">body { color: #000000; background-color: #FFFFFF }div { margin: 1px 0px 1px 0px; padding: 5px 5px 5px 5px;}.b {background-color: #FFFFFF; }.c {background-color: #EEEEEE; }.quote{font-size: x-small; padding: 2px 0px 2px 4px; color: #878787; border-left: 3px solid #c0c0c0;}</style></head><body><p><b><u>'. htmlspecialchars($type1['text']) .'</u></b></p>';
            $i = 1;
            while ($arr = mysql_fetch_array($tema)) {
                $d = $i / 2;
                $d1 = ceil($d);
                $d2 = $d1 - $d;
                $d3 = ceil($d2);
                if ($d3 == 0) {
                    $div = "<div class='b'>";
                } else {
                    $div = "<div class='c'>";
                }
                $txt_tmp = htmlspecialchars($arr['text']);
                $txt_tmp = bbcode::tags($txt_tmp);
                $txt_tmp = preg_replace('#\[quote[^\]]*\](.*?)\[/quote\]#si', '<div class="quote">\1</div>', $txt_tmp);
                $txt_tmp = str_replace("\r\n", '<br/>', $txt_tmp);
                $stroka = $div . '<b>' . $arr['from'] . '</b>(' . date('d.m.Y/H:i', $arr['time']) . ')<br/>' . $txt_tmp . '</div>';
                $text = $text . ' ' . $stroka;
                ++$i;
            }
            $text = $text . '<p>' . $lng_forum['download_topic_note'] . ': <b>' . htmlspecialchars($set['copyright']) . '</b></p></body></html>';
            $num = time() . $id;
            $fp = fopen("../files/forum/topics/$num.htm", "a+");
            flock($fp, LOCK_EX);
            fputs($fp, "$text\r\n");
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            @chmod("$fp", 0777);
            @chmod("../files/forum/topics/$num.htm", 0777);
            echo '<div class="phdr">' . $lng_forum['download_topic'] . '</div><div class="list1"><a href="index.php?act=loadtem&n=' . $num . '">' . $lng['download'] . '</a></div><div class="rmenu">' . $lng_forum['download_topic_help'] . '</div><div class="list1"><a href="'.functions::bodau($type1['text']).'.'.$id.'.html">'.$lng['back'].'</a></div>';
            break;
    }
} else {
    echo '<div class="phdr">' . $lng_forum['download_topic_format'] . '</div><div class="list1">' .
        '<form action="index.php?act=tema&id=' . $id . '" method="post">' .
        '<select name="mod"><option value="1">.txt</option>' .
        '<option value="2">.htm</option></select>' .
        '<input type="submit" name="submit" value="' . $lng['download'] . '"/>' .
        '</form></div><div class="list2"><a href="'.functions::bodau($type1['text']).'.'.$id.'.html">'.$lng['back'].'</a></div>';
}