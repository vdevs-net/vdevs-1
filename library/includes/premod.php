<?php

 
defined('_MRKEN_CMS') or die('Error: restricted access');
$lng_gal = core::load_lng('gallery');

  echo '<div class="phdr"><strong><a href="?">' . $lng['library'] . '</a></strong> | ' . $lng_lib['articles_moderation'] . '</div>';
  if ($id && isset($_GET['yes'])) {
    $sql = "UPDATE `library_texts` SET `premod`=1 WHERE `id`=" . $id;
    echo '<div class="rmenu">' . $lng_lib['article'] . ' <strong>' . functions::checkout(mysql_result(mysql_query("SELECT `name` FROM `library_texts` WHERE `id`=" . $id) , 0)) . '</strong> ' . $lng_lib['added_to_database'] . '</div>';
  }
  elseif (isset($_GET['all'])) {
    $sql = 'UPDATE `library_texts` SET `premod`=1';
    echo '<div>' . $lng_lib['added_all'] . '</div>';
  }
  if (isset($_GET['yes']) || isset($_GET['all'])) {
    mysql_query($sql);
  }
  $total = mysql_result(mysql_query('SELECT COUNT(*) FROM `library_texts` WHERE `premod`=0') , 0);
  $page = $page >= ceil($total / $kmess) ? ceil($total / $kmess) : $page;
  $start = $page == 1 ? 0 : ($page - 1) * $kmess;
  if ($total) { 
    $sql = mysql_query('SELECT `id`, `name`, `time`, `uploader`, `uploader_id`, `cat_id` FROM `library_texts` WHERE `premod`=0 ORDER BY `time` DESC LIMIT ' . $start . ',' . $kmess);
    $i = 0;
    while ($row = mysql_fetch_assoc($sql)) {
        $dir_nav = new tree($row['cat_id']);
        $dir_nav->process_nav_panel();
        echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">' 
        . (file_exists('../files/library/small/' . $row['id'] . '.png') 
        ? '<div class="avatar"><img src="' . SITE_URL . '/files/library/small/' . $row['id'] . '.png" alt="screen" /></div>' 
        : '') 
        . '<div class="righttable"><a href="index.php?id=' . $row['id'] . '">' . functions::checkout($row['name']) . '</a></div>'
        . '<div class="sub">' . $lng_lib['added'] . ': ' . functions::checkout($row['uploader']) . ' (' . functions::display_date($row['time']) . ')</div>' 
        . '<div>' . $dir_nav->print_nav_panel() . '</div>' 
        . '<a href="?act=premod&amp;yes&amp;id=' . $row['id'] . '">' . $lng_lib['approve'] . '</a> | <a href="?act=del&amp;type=article&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a>' 
        . '</div>';
    }
  }
  echo '<div class="phdr">' . $lng['total'] . ': ' . intval($total) . '</div>';
  echo ($total > $kmess) ? '<div class="topmenu">' . functions::display_pagination('?act=premod&page=', $start, $total, $kmess) . '</div>' : '';
  echo $total ? '<div><a href="?act=premod&amp;all">' . $lng_lib['approve_all'] . '</a></div>' : '';
  echo '<p><a href="?">' . $lng_lib['to_library'] . '</a></p>' . PHP_EOL;