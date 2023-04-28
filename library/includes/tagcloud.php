<?php


defined('_MRKEN_CMS') or die('Error: restricted access');

$obj = new Hashtags();

$sort = isset($_GET['sort']) && $_GET['sort'] == 'rel' ? 'cmprang' : 'cmpalpha';

$menu[] = $sort == 'cmpalpha' ? '<strong>' . $lng_lib['alphabet'] . '</strong>' : '<a href="?act=tagcloud&amp;sort=alpha">' . $lng_lib['alphabet'] . '</a>';
$menu[] = $sort == 'cmprang' ? '<strong>' . $lng_lib['relevance'] . '</strong>' : '<a href="?act=tagcloud&amp;sort=rel">' . $lng_lib['relevance'] . '</a> ';

echo '<div class="phdr">' . 
    '<strong><a href="?">' . $lng['library'] . '</a></strong> | ' . $lng_lib['cloud_of_tags'] . '</div>' .
    '<div class="topmenu">' . $lng_lib['sort'] . ': ' . functions::display_menu($menu) . '</div>' .
    '<div class="gmenu">' . $obj->get_cache($sort) . '</div>' .
    '<p><a href="?">' . $lng_lib['to_library'] . '</a></p>';
