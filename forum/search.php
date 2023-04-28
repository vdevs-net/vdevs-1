<?php
define('_MRKEN_CMS', 1);

$headmod = 'forumsearch';
require('../incfiles/core.php');
$lng_forum = core::load_lng('forum');

// The backlight function query results
function ReplaceKeywords($search, $text){
	$search = str_replace('*', '', $search);
    return mb_strlen($search) < 3 ? $text : preg_replace('|(' . preg_quote($search, '/') . ')|siu', '<span style="background-color:#FFFF33">$1</span>', $text);
}

        // search form
        $search_post = isset($_POST['search']) ? functions::checkin($_POST['search']) : false;
        $search_get = isset($_GET['search']) ? rawurldecode(functions::checkin($_GET['search'])) : false;
        $search = $search_post ? $search_post : $search_get;
        if($search){
            $search = str_replace('%', '', $search);
        }
        $textl = ($search ? $search . ' - ' : '') . $lng_forum['search_forum'];
        require('../incfiles/head.php');
        if($search){
            echo '<div class="phdr"><span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="itemscope"><a itemprop="url" href="index.php"><span itemprop="title">'.$lng['forum'].'</a></span> | <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemscope="itemscope"><a itemprop="url" href="search.php"><span itemprop="title">' . $lng['search'] . '</a></span></div>';
        }else{
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['forum'] . '</b></a> | ' . $lng['search'] . '</div>';
        }
        $search_t = isset($_REQUEST['t']);
        $to_history = false;
        echo '<div class="gmenu"><form action="search.php" method="post"><p>' .
             '<input type="text" value="' . ($search ? functions::checkout($search) : '') . '" name="search" />' .
             '<input type="submit" value="' . $lng['search'] . '" name="submit" /><br />' .
             '<input name="t" type="checkbox" value="1" ' . ($search_t ? 'checked="checked"' : '') . ' />&nbsp;' . $lng_forum['search_topic_name'] .
             '</p></form></div>';

        // Check for errors
        $error = $search && mb_strlen($search) < 4 || mb_strlen($search) > 64 ? true : false;

        if ($search && !$error) {
            // Conclusions The results of the query
            $array = explode(' ', $search);
            $count = count($array);
            $query = mysql_real_escape_string($search);
            $total = mysql_result(mysql_query("
                SELECT COUNT(*) FROM `forum`
                WHERE MATCH (`text`) AGAINST ('$query' IN BOOLEAN MODE)
                AND `type` = '" . ($search_t ? 't' : 'm') . "'" . ($rights >= 7 ? "" : " AND `close` != '1'
            ")), 0);
            echo '<div class="phdr">' . $lng['search_results'] . '</div>';
            if ($total > $kmess)
                echo '<div class="topmenu">' . functions::display_pagination('search.php?' . ($search_t ? 't=1&' : '') . 'search=' . urlencode($search) . '&page=', $start, $total, $kmess) . '</div>';
            if ($total) {
                $to_history = true;
                $req = mysql_query("
                    SELECT *, MATCH (`text`) AGAINST ('$query' IN BOOLEAN MODE) as `rel`
                    FROM `forum`
                    WHERE MATCH (`text`) AGAINST ('$query' IN BOOLEAN MODE)
                    AND `type` = '" . ($search_t ? 't' : 'm') . "'
                    ORDER BY `rel` DESC
                    LIMIT $start, $kmess
                ");
                $i = 0;
                while (($res = mysql_fetch_assoc($req)) !== false) {
					$url = '';
                    echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    if ($search_t) {
                        // Search topic title
                        $req_p = mysql_query("SELECT `text` FROM `forum` WHERE `refid` = '" . $res['id'] . "' ORDER BY `id` ASC LIMIT 1");
                        $res_p = mysql_fetch_assoc($req_p);
						$url = functions::bodau($res['text']);
                        $res['text'] = htmlspecialchars($res['text']);
                        foreach ($array as $val) {
                            $res['text'] = ReplaceKeywords($val, $res['text']);
                        }
                        echo '<b>' . $res['text'] . '</b><br />';
                    } else {
                        // Only search in the text
                        $req_t = mysql_query("SELECT `id`,`text`,`soft` FROM `forum` WHERE `id` = '" . $res['refid'] . "'");
                        $res_t = mysql_fetch_assoc($req_t);
						$url = functions::bodau($res_t['text']);
                        echo '<b>' . htmlspecialchars($res_t['text']) . '</b><br />';
                    }
					if(($search_t && !empty($res['soft'])) || (!$search_t && !empty($res_t['soft']))){
						$tags = $search_t ? $res['soft'] : $res_t['soft'];
						$tags = functions::show_tags($tags);
						foreach ($array as $val) {
                            $tags = ReplaceKeywords($val, $tags);
                        }
						echo '<b>Tags</b>: <i>' . $tags . '</i><br/>';
					}
                    echo '<a href="' . SITE_URL . '/users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a> ';
                    echo ' <span class="gray">(' . functions::display_date($res['time']) . ')</span><br/>';
                    $text = $search_t ? $res_p['text'] : $res['text'];
                    foreach ($array as $srch)
                        if (($pos = mb_strpos(strtolower($res['text']), strtolower(str_replace('*', '', $srch)))) !== false) break;
                    if (!isset($pos) || $pos < 100) $pos = 100;
                    $text = functions::checkout($text, 1, 2);
                    $text = mb_substr($text, ($pos - 100), 400);
                    if (!$search_t) {
                        foreach ($array as $val) {
                            $text = ReplaceKeywords($val, $text);
                        }
                    }
                    echo $text;
                    if (mb_strlen($res['text']) > 500)
                        echo '...<a href="index.php?act=post&id=' . $res['id'] . '">' . $lng_forum['read_all'] . ' &gt;&gt;</a>';
                    echo '<br /><a href="'.$url.'.'. ($search_t ? $res['id'] : $res_t['id']) . '.html">' . $lng_forum['to_topic'] . '</a>' . ($search_t ? ''
                            : ' | <a href="index.php?act=post&id=' . $res['id'] . '">' . $lng_forum['to_post'] . '</a>');
                    echo '</div>';
                    ++$i;
                }
            } else {
                echo '<div class="rmenu"><p>' . $lng['search_results_empty'] . '</p></div>';
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
        } else {
            if ($error) echo functions::display_error($lng['error_wrong_lenght']);
            echo '<div class="notif"><small>' . $lng['search_help'] . '</small></div>';
        }

        // pagination
        if (isset($total) && $total > $kmess) {
            echo '<div class="topmenu">' . functions::display_pagination('search.php?' . ($search_t ? 't=1&' : '') . 'search=' . urlencode($search) . '&page=', $start, $total, $kmess) . '</div>' .
                 '<div class="menu"><form action="search.php?' . ($search_t ? 't=1&amp;' : '') . 'search=' . urlencode($search) . '" method="post">' .
                 '<input type="text" name="page" size="2"/>' .
                 '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
                 '</form></div>';
        }

        if($search) echo '<div class="menu"><a href="search.php">' . $lng['search_new'] . '</a></div>';

require('../incfiles/end.php');