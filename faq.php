<?php
define('_MRKEN_CMS', 1);
require('incfiles/core.php');
$lng_faq = core::load_lng('faq');
$lng_smileys = core::load_lng('smileys');
$textl = 'FAQ';
$headmod = 'faq';
require('incfiles/head.php');

// Back link
if (empty($_SESSION['ref'])) {
    $_SESSION['ref'] = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : SITE_URL;
}

switch ($act) {
    case 'forum':
        // Forum rules
        echo '<div class="phdr"><a href="faq.php"><b>FAQ</b></a> | ' . $lng_faq['forum_rules'] . '</div>' .
            '<div class="menu"><p>' . $lng_faq['forum_rules_text'] . '</p></div>';
        break;

    case 'tags':
        // bbCode list
        echo '<div class="phdr"><a href="faq.php"><b>FAQ</b></a> | ' . $lng_faq['tags'] . '</div>' .
            '<div class="menu">' .
            '<table cellpadding="3" cellspacing="0">' .
            '<tr><td align="right"><h3>BBcode</h3></td><td></td></tr>' .
            '<tr><td align="right">[php]...[/php]</td><td>' . $lng['tag_code'] . '</td></tr>' .
            '<tr><td align="right"><a href="#">' . $lng['link'] . '</a></td><td>[url=http://site_url]<span style="color:blue">' . $lng_faq['tags_link_name'] . '</span>[/url]</td></tr>' .
            '<tr><td align="right">[b]...[/b]</td><td><b>' . $lng['tag_bold'] . '</b></td></tr>' .
            '<tr><td align="right">[i]...[/i]</td><td><i>' . $lng['tag_italic'] . '</i></td></tr>' .
            '<tr><td align="right">[u]...[/u]</td><td><u>' . $lng['tag_underline'] . '</u></td></tr>' .
            '<tr><td align="right">[s]...[/s]</td><td><strike>' . $lng['tag_strike'] . '</strike></td></tr>' .
            '<tr><td align="right">[red]...[/red]</td><td><span style="color:red">' . $lng['tag_red'] . '</span></td></tr>' .
            '<tr><td align="right">[green]...[/green]</td><td><span style="color:green">' . $lng['tag_green'] . '</span></td></tr>' .
            '<tr><td align="right">[blue]...[/blue]</td><td><span style="color:blue">' . $lng['tag_blue'] . '</span></td></tr>' .
            '<tr><td align="right">[color=]...[/color]</td><td>' . $lng['color_text'] . '</td></tr>' .
            '<tr><td align="right">[quote]...[/quote]</td><td><span class="quote">' . $lng['tag_quote'] . '</span></td></tr>' .
            '<tr><td align="right" valign="top">[*]...[/*]</td><td><span class="bblist">' . $lng['tag_list'] . '</span></td></tr>' .
            '<tr><td align="right" valign="top">Spoiler</td><td>[spoiler=' . $lng['title'] . ']' . $lng['text'] . '[/spoiler]</td></tr>' .
            '</table>' .
            '</div>';
        break;

    case 'smileys':
        // The main menu catalog smileys
        echo '<div class="phdr"><a href="faq.php"><b>FAQ</b></a> | ' . $lng['smileys'] . '</div>';
        $dir = glob(ROOTPATH . 'images/smileys/user/*', GLOB_ONLYDIR);
        foreach ($dir as $val) {
            $cat = explode('/', $val);
            $cat = array_pop($cat);
            if (array_key_exists($cat, $lng_smileys)) {
                $smileys_cat[$cat] = $lng_smileys[$cat];
            } else {
                $smileys_cat[$cat] = ucfirst($cat);
            }
        }
        asort($smileys_cat);
        foreach ($smileys_cat as $key => $val) {
            echo '<div class="list1">' .
                '<a href="faq.php?act=smusr&amp;cat=' . urlencode($key) . '">' . htmlspecialchars($val) . '</a>' .
                ' (' . count(glob(ROOTPATH . 'images/smileys/user/' . $key . '/*.{gif,jpg,png}', GLOB_BRACE)) . ')' .
                '</div>';
        }
        break;

    case 'smusr':
        // user smileys
        $dir = glob(ROOTPATH . 'images/smileys/user/*', GLOB_ONLYDIR);
        foreach ($dir as $val) {
            $val = explode('/', $val);
            $cat_list[] = array_pop($val);
        }
        $cat = isset($_GET['cat']) && in_array(trim($_GET['cat']), $cat_list) ? trim($_GET['cat']) : $cat_list[0];
        $smileys = glob(ROOTPATH . 'images/smileys/user/' . $cat . '/*.{gif,jpg,png}', GLOB_BRACE);
        $total = count($smileys);
        $end = $start + $kmess;
        if ($end > $total) $end = $total;
        echo '<div class="phdr"><a href="faq.php?act=smileys"><b>' . $lng['smileys'] . '</b></a> | ' .
            (array_key_exists($cat, $lng_smileys) ? $lng_smileys[$cat] : ucfirst(htmlspecialchars($cat))) .
            '</div>';
        if ($total) {
            for ($i = $start; $i < $end; $i++) {
                $smile = preg_replace('#^(.*?).(gif|jpg|png)$#isU', '$1', basename($smileys[$i], 1));
                echo '<div class="list1"><img src="' . SITE_URL . '/images/smileys/user/' . $cat . '/' . basename($smileys[$i]) . '" alt="" /> :' . $smile . ':</div>';
            }
        } else {
            echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
        }
        echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        if ($total > $kmess) {
            echo '<div class="topmenu">' . functions::display_pagination('faq.php?act=smusr&cat=' . urlencode($cat) . '&page=', $start, $total, $kmess) . '</div>';
            echo '<div class="menu"><form action="faq.php?act=smusr&cat=' . urlencode($cat) . '" method="post">' .
                '<input type="text" name="page" size="2"/>' .
                '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></div>';
        }
        break;

    default:
        // main page FAQ
        echo '<div class="phdr"><b>FAQ</b></div>' .
            '<div class="menu"><a href="faq.php?act=forum">' . $lng_faq['forum_rules'] . '</a></div>' .
            '<div class="menu"><a href="faq.php?act=tags">' . $lng_faq['tags'] . '</a></div>' .
            '<div class="menu"><a href="faq.php?act=smileys">' . $lng['smileys'] . '</a></div>';
}

require('incfiles/end.php');