<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

ob_end_clean();
ob_start();
$type = isset($_GET['type']) && in_array($_GET['type'], array('txt', 'fb2')) ? $_GET['type'] : redir404();
$image_lib = file_exists('../files/library/orig/' . $id . '.png') 
? chunk_split(base64_encode(file_get_contents('../files/library/orig/' . $id . '.png')))
 : '';
 
$out = '';

switch ($type) {
case 'txt':
  $out .= bbcode::notags(mysql_result(mysql_query("SELECT `text` FROM `library_texts` WHERE `id`=" . $id . " LIMIT 1") , 0));
  break;

case 'fb2':
  $out = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL 
  . '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">' . PHP_EOL 
  . '<stylesheet type="text/css">' . PHP_EOL 
  . '.body{font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;' . PHP_EOL 
  . '}' . PHP_EOL 
  . '.p{margin:0.5em 0 0 0.3em; padding:0.2em; text-align:justify;' . PHP_EOL 
  . '}' . PHP_EOL 
  . '</stylesheet>' . PHP_EOL 
  . '<description>' . PHP_EOL 
  . '<title-info>' . PHP_EOL 
  . '<genre>sf_history</genre>' . PHP_EOL 
  . '<author>' . PHP_EOL 
  . '<first-name>' . $lng_lib['author_name'] . '</first-name>' . PHP_EOL 
  . '<last-name>' . $lng_lib['author_last_name'] . '</last-name>' . PHP_EOL 
  . '</author>' . PHP_EOL 
  . '<book-title>' . $lng_lib['bookname'] . '</book-title>' . PHP_EOL 
  . '<annotation></annotation>' . PHP_EOL 
  . '<date>' . $lng['date'] . '</date>' . PHP_EOL 
  . '<coverpage>' . PHP_EOL;
  
  if ($image_lib) {
    $out.= '<image l:href="#cover.png"/></coverpage>' . PHP_EOL;
  }
  
  $out.= '<lang>'.core::$lng_iso.'</lang>' . PHP_EOL 
  . '</title-info>' . PHP_EOL 
  . '<document-info>' . PHP_EOL 
  . '<author><nickname></nickname>' . PHP_EOL . '</author>' . PHP_EOL 
  . '<program-used>Lib converter jcms</program-used>' . PHP_EOL 
  . '<date value=""></date>' . PHP_EOL 
  . '<src-url>' . SITE_URL . '</src-url>' . PHP_EOL 
  . '<id></id>' . PHP_EOL 
  . '<version>1.0</version>' . PHP_EOL 
  . '<history><p>book</p></history>' . PHP_EOL 
  . '</document-info>' . PHP_EOL 
  . '</description>' . PHP_EOL 
  . '<body>' . PHP_EOL . '<title>';
  
  $out.= '<p>' . mysql_result(mysql_query("SELECT `name` FROM `library_texts` WHERE `id`=" . $id . " LIMIT 1") , 0) . '</p>' . PHP_EOL;
  $out.= '</title>' . PHP_EOL . '<section>';
  $out.= '<p>' . str_replace('<p></p>', '<empty-line/>', str_replace(PHP_EOL, '</p>' . PHP_EOL . '<p>', bbcode::notags(mysql_result(mysql_query("SELECT `text` FROM `library_texts` WHERE `id`=" . $id . " LIMIT 1") , 0)))) . '</p>' . PHP_EOL;
  $out.= '</section>' . PHP_EOL . '</body>' . PHP_EOL;
  
  if ($image_lib) {
    $out.= '<binary id="cover.png" content-type="image/png">' . PHP_EOL;
    $out.= $image_lib;
    $out.= '</binary>' . PHP_EOL;
  }
  
  $out.= '</FictionBook>';
  break;
}
echo $out;
header('Content-Type: application/octet-stream');
header('Content-Description: inline; File Transfer');
header('Content-Disposition: attachment; filename="book' . time() . '.' . $type . '";', false);
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . ob_get_length());
ob_flush();
flush();