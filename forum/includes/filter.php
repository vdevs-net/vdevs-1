<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
require('../incfiles/head.php');
if (!$id) {
    echo functions::display_error($lng['error_wrong_data'], '<a href="index.php">' . $lng['to_forum'] . '</a>');
    require('../incfiles/end.php');
    exit;
}
$req = mysql_query('SELECT `text` FROM `forum` WHERE type="t" AND `id`="'.$id.'" LIMIT 1');
if(!mysql_num_rows($req)){
    echo functions::display_error($lng['error_wrong_data'], '<a href="index.php">' . $lng['to_forum'] . '</a>');
    require('../incfiles/end.php');
    exit;
}
$text = mysql_result($req,0);
switch ($do) {
    case 'unset':
        // remove filter
        unset($_SESSION['fsort_id']);
        unset($_SESSION['fsort_users']);
        header('Location: '.functions::bodau($text).'.'.$id.'.html');
        exit;
        break;

    case 'set':
        // setup the filter by author
        $users = isset($_POST['users']) ? $_POST['users'] : '';
        if (empty($_POST['users'])) {
            echo '<div class="rmenu"><p>' . $lng_forum['error_author_select'] . '<br /><a href="index.php?act=filter&id=' . $id . '&start=' . $start . '">' . $lng['back'] . '</a></p></div>';
            require('../incfiles/end.php');
            exit;
        }
        $array = array ();
        foreach ($users as $val) {
            $array[] = intval($val);
        }
        $_SESSION['fsort_id'] = $id;
        $_SESSION['fsort_users'] = serialize($array);
        header('Location: '.functions::bodau($text).'.'.$id.'.html');
        exit;
        break;

    default :
        // Show list of the author's themes, with a choice
        $req = mysql_query("SELECT *, COUNT(`from`) AS `count` FROM `forum` WHERE `refid` = '$id' GROUP BY `from` ORDER BY `from`");
        $req = mysql_query("SELECT *, COUNT(`from`) AS `count` FROM `forum` WHERE `refid` = '$id' GROUP BY `from` ORDER BY `from`");
        $total = mysql_num_rows($req);
        if ($total > 0) {
            echo '<div class="phdr"><a href="'.functions::bodau($text).'.'.$id.'.html?start=' . $start . '"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['filter_on_author'] . '</div>' .
                '<form action="index.php?act=filter&id=' . $id . '&start=' . $start . '&do=set" method="post">';
            $i = 0;
            while ($res = mysql_fetch_array($req)) {
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                echo '<input type="checkbox" name="users[]" value="' . $res['user_id'] . '"/>&#160;' .
                    '<a href="' . SITE_URL . '/users/profile.php?user=' . $res['user_id'] . '">' . $res['from'] . '</a> [' . $res['count'] . ']</div>';
                ++$i;
            }
            echo '<div class="gmenu"><input type="submit" value="' . $lng_forum['filter_to'] . '" name="submit" /></div>' .
                '<div class="phdr"><small>' . $lng_forum['filter_on_author_help'] . '</small></div>' .
                '</form>';
        } else {
            echo functions::display_error($lng['error_wrong_data']);
        }
}
echo '<div class="menu"><a href="'. functions::bodau($text) .'.'.$id.'.html?start=' . $start . '">' . $lng_forum['return_to_topic'] . '</a></div>';