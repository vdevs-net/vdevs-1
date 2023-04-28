<?php
define('_MRKEN_CMS', 1);
$headmod = 'library';
require_once('../incfiles/core.php');
require_once('inc.php');

$lng_lib = core::load_lng('library');
$textl = $lng['library'];

// Restrict access to the Library

$error = '';

if (!$set['mod_lib'] && $rights < 7) {
    $error = $lng_lib['library_closed'];
} elseif ($set['mod_lib'] == 1 && !$user_id) {
    $error = $lng['access_guest_forbidden'];
}

if ($error) {
    require_once('../incfiles/head.php');
    echo functions::display_error($error);
    require_once('../incfiles/end.php');
    exit;
}

// Headlines library
if ($do) {
    switch ($do) {
        case 'dir':
            $tab = 'library_cats';
            break;

        default:
            $tab = 'library_texts';
    }

    if ($id) {
        $textl = mysql_result(mysql_query('SELECT `name` FROM `' . $tab . '` WHERE `id` = "' . $id . '" LIMIT 1'), 0);
    }
}

require_once('../incfiles/head.php');

?>

    <!-- style table image -->
    <style type="text/css">
        .avatar {
            display: table-cell;
            vertical-align: top;
        }

        .avatar img {
            height: 32px;
            margin-right: 5px;
            margin-bottom: 5px;
            width: 32px;
        }

        .righttable {
            display: table-cell;
            vertical-align: top;
            width: 100%;
        }
    </style>
    <!-- end style -->

<?php

if (!$set['mod_lib']) {
    echo functions::display_error($lng_lib['library_closed']);
}

$array_includes = array(
    'addnew',
    'comments',
    'del',
    'download',
    'mkdir',
    'moder',
    'move',
    'new',
    'premod',
    'search',
    'top',
    'tags',
    'tagcloud',
    'lastcom'
);
$i = 0;

if (in_array($act, $array_includes)) {
    require_once('includes/' . $act . '.php');
} else {
    if (!$id) {
        echo '<div class="phdr"><strong>' . $lng['library'] . '</strong></div>';
        echo '<div class="topmenu"><a href="?act=search">' . $lng['search'] . '</a> | <a href="?act=tagcloud">' . $lng_lib['tagcloud'] . '</a></div>';

        echo '<div class="gmenu"><p>';
        if ($adm) {
            // get the number of articles awaiting moderation
            $res = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_texts` WHERE `premod`=0"), 0);
            if ($res > 0) {
                echo '<div>' . $lng['on_moderation'] . ': <a href="?act=premod">' . $res . '</a></div>';
            }
        }
        $res = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_texts` WHERE `time` > '" . (time() - 259200) . "' AND `premod`=1"), 0);
        if ($res) {
            echo functions::image('new.png', array('width' => 16, 'height' => 16)) . '<a href="?act=new">' . $lng_lib['new_articles'] . '</a> (' . $res . ')<br/>';
        }

        echo functions::image('rate.gif', array('width' => 16, 'height' => 16)) . '<a href="?act=top">' . $lng_lib['rated_articles'] . '</a><br/>' .
            functions::image('talk.gif', array('width' => 16, 'height' => 16)) . '<a href="?act=lastcom">' . $lng_lib['last_comments'] . '</a>' .
            '</p></div>';
        $sql = mysql_query("SELECT `id`, `name`, `dir`, `description` FROM `library_cats` WHERE `parent`=0 ORDER BY `pos` ASC");
        $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_cats` WHERE `parent`=0"), 0);
        $y = 0;
        if ($total) {
            while ($row = mysql_fetch_assoc($sql)) {
                $y++;
                echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
                    . '<a href="?do=dir&amp;id=' . $row['id'] . '">' . functions::checkout($row['name']) . '</a> ('
                    . mysql_result(mysql_query("SELECT COUNT(*) FROM `" . ($row['dir'] ? 'library_cats' : 'library_texts') . "` WHERE " . ($row['dir'] ? '`parent`=' . $row['id'] : '`cat_id`=' . $row['id'])), 0) . ')';

                if (!empty($row['description'])) {
                    echo '<div style="font-size: x-small; padding-top: 2px"><span class="gray">' . functions::checkout($row['description']) . '</span></div>';
                }

                if ($adm) {
                    echo '<div class="sub">' . ($y != 1 ? '<a href="?act=move&amp;moveset=up&amp;posid=' . $y . '">' . $lng['up'] . '</a> | ' : $lng['up'] . ' | ') . ($y != $total ? '<a href="?act=move&amp;moveset=down&amp;posid=' . $y . '">' . $lng['down'] . '</a>' : $lng['down']) . ' | <a href="?act=moder&amp;type=dir&amp;id=' . $row['id'] . '">' . $lng['edit'] . '</a> | <a href="?act=del&amp;type=dir&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a></div>';
                }

                echo '</div>';
            }
        } else {
            echo '<div class="menu">' . $lng['list_empty'] . '</div>';
        }

        echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        if ($adm) {
            echo '<div class="menu"><a href="?act=mkdir&id=0">' . $lng_lib['create_category'] . '</a></div>';
        }
    } else {
        $dir_nav = new Tree($id);
        $dir_nav->process_nav_panel();
        switch ($do) {
            case 'dir':
                // dir
                $actdir = mysql_fetch_assoc(mysql_query("SELECT `id`, `dir` FROM `library_cats` WHERE " . ($id ? '`id`=' . $id : 1) . " LIMIT 1"));
                $actdir = $actdir['id'] > 0 ? $actdir['dir'] : redir404();
                echo '<div class="phdr">' . $dir_nav->print_nav_panel() . '</div>';

                if ($actdir) {
                    $total = mysql_result(mysql_query("SELECT COUNT(*) FROM `library_cats` WHERE " . ($id !== null ? '`parent`=' . $id : '`parent`=0')), 0);
                    $nav = ($total > $kmess) ? '<div class="topmenu">' . functions::display_pagination('?do=dir&id=' . $id . '&page=', $start, $total, $kmess) . '</div>' : '';
                    $y = 0;

                    if ($total) {
                        $sql = mysql_query("SELECT `id`, `name`, `dir`, `description` FROM `library_cats` WHERE " . ($id !== null ? '`parent`=' . $id : '`parent`=0') . ' ORDER BY `pos` ASC LIMIT ' . $start . ',' . $kmess);
                        echo $nav;

                        while ($row = mysql_fetch_assoc($sql)) {
                            $y++;
                            echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
                                . '<a href="?do=dir&amp;id=' . $row['id'] . '">' . functions::checkout($row['name']) . '</a>('
                                . mysql_result(mysql_query("SELECT COUNT(*) FROM `" . ($row['dir'] ? 'library_cats' : 'library_texts') . "` WHERE " . ($row['dir'] ? '`parent`=' . $row['id'] : '`cat_id`=' . $row['id'])), 0) . ' '
                                . ($row['dir'] ? ' thư mục' : ' bài viết') . ')'
                                . (!empty($row['description']) ? '<div class="sub"><span class="gray">' . functions::checkout($row['description']) . '</span></div>':'');
                            if ($adm) {
                                echo '<div class="sub">'
                                    . ($y != 1 ? '<a href="?do=dir&amp;id=' . $id . '&amp;act=move&amp;moveset=up&amp;posid=' . $y . '">' . $lng_lib['up']
                                        . '</a> | ' : '' . $lng_lib['up'] . ' | ')
                                    . ($y != $total
                                        ? '<a href="?do=dir&amp;id=' . $id . '&amp;act=move&amp;moveset=down&amp;posid=' . $y . '">' . $lng_lib['down'] . '</a>'
                                        : $lng_lib['down'])
                                    . ' | <a href="?act=moder&amp;type=dir&amp;id=' . $row['id'] . '">' . $lng['edit'] . '</a> | <a href="?act=del&amp;type=dir&amp;id=' . $row['id'] . '">' . $lng['delete'] . '</a></div>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
                    }

                    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
                    echo $nav;

                    if ($adm) {
                        echo '<div class="menu"><a href="?act=moder&amp;type=dir&amp;id=' . $id . '">' . $lng['edit'] . '</a></div>'
                            . '<div class="menu"><a href="?act=del&amp;type=dir&amp;id=' . $id . '">' . $lng['delete'] . '</a></div>'
                            . '<div class="menu"><a href="?act=mkdir&amp;id=' . $id . '">' . $lng_lib['create_category'] . '</a></div>';
                    }
                } else {
                    $total = mysql_result(mysql_query('SELECT COUNT(*) FROM `library_texts` WHERE `premod`=1 AND `cat_id`=' . $id), 0);
                    $page = $page >= ceil($total / $kmess) ? ceil($total / $kmess) : $page;
                    $start = $page == 1 ? 0 : ($page - 1) * $kmess;
                    $nav = ($total > $kmess) ? '<div class="topmenu">' . functions::display_pagination('?do=dir&id=' . $id . '&page=', $start, $total, $kmess) . '</div>' : '';

                    if ($total) {
                        $sql2 = mysql_query("SELECT `id`, `name`, `time`, `uploader`, `uploader_id`, `count_views`, `count_comments`, `comments`, `announce` FROM `library_texts` WHERE `premod`=1 AND `cat_id`=" . $id . " ORDER BY `id` DESC LIMIT " . $start . "," . $kmess);
                        echo $nav;

                        while ($row = mysql_fetch_assoc($sql2)) {
                            echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
                                . (file_exists('../files/library/small/' . $row['id'] . '.png')
                                    ? '<div class="avatar"><img src="' . SITE_URL . '/files/library/small/' . $row['id'] . '.png" alt="screen" /></div>'
                                    : '')
                                . '<div class="righttable"><h4><a href="index.php?id=' . $row['id'] . '">' . functions::checkout($row['name']) . '</a></h4>'
                                . '<div><small>' . functions::checkout(bbcode::notags($row['announce'])) . '</small></div></div>';

                            // Description of the article
                            $obj = new Hashtags($row['id']);
                            $rate = new Rating($row['id']);
                            $uploader = $row['uploader_id'] ? '<a href="' . SITE_URL . '/users/profile.php?user=' . $row['uploader_id'] . '">' . functions::checkout($row['uploader']) . '</a>' : functions::checkout($row['uploader']);
                            echo '<table class="desc">'
                                // tags
                                . ($obj->get_all_stat_tags() ? '<tr><td class="caption">' . $lng_lib['tags'] . ':</td><td>' . $obj->get_all_stat_tags(1) . '</td></tr>' : '')
                                //Who added?
                                . '<tr>'
                                . '<td class="caption">' . $lng_lib['added'] . ':</td>'
                                . '<td>' . $uploader . ' (' . functions::display_date($row['time']) . ')</td>'
                                . '</tr>'
                                // rating
                                . '<tr>'
                                . '<td class="caption">' . $lng['rating'] . ':</td>'
                                . '<td>' . $rate->view_rate() . '</td>'
                                . '</tr>';
                            echo '</table></div>';
                        }
                    } else {
                        echo '<div class="menu">' . $lng['list_empty'] . '</div>';
                    }

                    echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
                    echo $nav;

                    if (($adm || (mysql_result(mysql_query("SELECT `user_add` FROM `library_cats` WHERE `id`=" . $id), 0) > 0)) && isset($id) && $user_id) {
                        echo '<p><a href="?act=addnew&amp;id=' . $id . '">' . $lng_lib['write_article'] . '</a>'
                            . ($adm ? ('<br/><a href="?act=moder&amp;type=dir&amp;id=' . $id . '">' . $lng['edit'] . '</a><br/>'
                                . '<a href="?act=del&amp;type=dir&amp;id=' . $id . '">' . $lng['delete'] . '</a>') : '')
                            . '</p>';
                    }
                }

                break;

            default:
                $res = mysql_fetch_assoc(mysql_query("SELECT * FROM `library_texts` WHERE `id`=" . $id));
                if ($res['premod'] || $adm) {

                    // The meter readings
                    if (!isset($_SESSION['lib']) || isset($_SESSION['lib']) && $_SESSION['lib'] != $id) {
                        $_SESSION['lib'] = $id;
                        mysql_query('UPDATE `library_texts` SET  `count_views`=' . ($res['count_views'] ? ++$res['count_views'] : 1) . ' WHERE `id`=' . $id);
                    }

                    // Querying selected articles from the database
                    $symbols = $wap ? 3000 : 7000;
                    $count_pages = ceil(mysql_result(mysql_query("SELECT CHAR_LENGTH(`text`) FROM `library_texts` WHERE `id`= '" . $id . "' LIMIT 1"), 0) / $symbols);
                    if ($count_pages) {

                        // That is always the last page is considered correct
                        $page = $page >= $count_pages ? $count_pages : $page;
                        $text = mysql_result(mysql_query("SELECT SUBSTRING(`text`, " . ($page == 1 ? 1 : ($page - 1) * $symbols) . ", " . ($symbols + 100) . ") FROM `library_texts` WHERE `id`='" . $id . "'"), 0);
                        $tmp = mb_substr($text, $symbols, 100);
                    } else {
                        redir404();
                    }

                    $nav = $count_pages > 1 ? '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&page=', $page == 1 ? 0 : ($page - 1) * 1, $count_pages, 1) . '</div>' : '';
                    $catalog = mysql_fetch_assoc(mysql_query("SELECT `id`, `name` FROM `library_cats` WHERE `id` = " . $res['cat_id'] . " LIMIT 1"));
                    echo '<div class="phdr"><a href="?"><strong>' . $lng['library'] . '</strong></a> | <a href="?do=dir&amp;id=' . $catalog['id'] . '">' . functions::checkout($catalog['name']) . '</a>' . ($page > 1 ? ' | ' . functions::checkout($res['name']) : '') . '</div>';

                    // Upper pagination
                    if ($count_pages > 1) {
                        echo '<div class="topmenu">' . functions::display_pagination('index.php?id=' . $id . '&page=', $page == 1 ? 0 : ($page - 1) * 1, $count_pages, 1) . '</div>';
                    }

                    if ($page == 1) {
                        echo '<div class="list2">';
                        // article Title
                        echo '<h2>' . functions::checkout($res['name']) . '</h2>';

                        // Description of the article
                        $obj = new Hashtags($res['id']);
                        $rate = new Rating($res['id']);
                        $uploader = $res['uploader_id'] ? '<a href="' . SITE_URL . '/users/profile.php?user=' . $res['uploader_id'] . '">' . functions::checkout($res['uploader']) . '</a>' : functions::checkout($res['uploader']);
                        echo '<table class="desc">'
                            // tags
                            . ($obj->get_all_stat_tags() ? '<tr><td class="caption">' . $lng_lib['tags'] . ':</td><td>' . $obj->get_all_stat_tags(1) . '</td></tr>' : '')
                            // who added?
                            . '<tr>'
                            . '<td class="caption">' . $lng_lib['added'] . ':</td>'
                            . '<td>' . $uploader . ' (' . functions::display_date($res['time']) . ')</td>'
                            . '</tr>'
                            // rating
                            . '<tr>'
                            . '<td class="caption">' . $lng['rating'] . ':</td>'
                            . '<td>' . $rate->view_rate(1) . '</td>'
                            . '</tr>'
                            // reads
                            . '<tr>'
                            . '<td class="caption">' . $lng_lib['reads'] . ':</td>'
                            . '<td>' . $res['count_views'] . '</td>'
                            . '</tr>'
                            // Comments
                            . '<tr>';
                        if ($res['comments']) {
                            echo '<td class="caption"><a href="?act=comments&amp;id=' . $res['id'] . '">' . $lng['comments'] . '</a>:</td><td>' . $res['count_comments'] . '</td>';
                        } else {
                            echo '<td class="caption">' . $lng['comments'] . ':</td><td>' . $lng['comments_closed'] . '</td>';
                        }
                        echo '</tr></table>';

                        // Tags authors
                        echo '</div>';
                    }

                    $text = functions::checkout(mb_substr($text, ($page == 1 ? 0 : min(position($text, PHP_EOL), position($text, ' '))), (($count_pages == 1 || $page == $count_pages) ? $symbols : $symbols + min(position($tmp, PHP_EOL), position($tmp, ' ')) - ($page == 1 ? 0 : min(position($text, PHP_EOL), position($text, ' '))))), 1, 1, 1);

                    echo '<div class="list2" style="padding: 8px">';
                    if ($page == 1) {
                        // Picture article
                        if (file_exists('../files/library/big/' . $id . '.png')) {
                            $img_style = 'width: 50%; max-width: 240px; height: auto; float: left; clear: both; margin: 10px';
                            echo '<a href="' . SITE_URL . '/files/library/orig/' . $id . '.png"><img style="' . $img_style . '" src="' . SITE_URL . '/files/library/big/' . $id . '.png" alt="screen" /></a>';
                        }
                    }
                    // Print the article
                    echo $text .
                        '<div style="clear: both"></div>' .
                        '</div>';

                    echo '<div class="phdr">' . $lng['download'] . ' <a href="?act=download&amp;type=txt&amp;id=' . $id . '">txt</a> | <a href="?act=download&amp;type=fb2&amp;id=' . $id . '">fb2</a></div>';

                    echo $nav
                        . ($user_id && $page == 1 ? $rate->print_vote() : '');

                    if ($adm || mysql_result(mysql_query("SELECT `uploader_id` FROM `library_texts` WHERE `id` = " . $id), 0) == $user_id && $user_id) {
                        echo '<p><a href="?act=moder&amp;type=article&amp;id=' . $id . '">' . $lng['edit'] . '</a><br/>'
                            . '<a href="?act=del&amp;type=article&amp;id=' . $id . '">' . $lng['delete'] . '</a></p>';
                    }
                } else {
                    redir404();
                }
        } // end switch
    } // end else !id
} // end else $act
require_once('../incfiles/end.php');