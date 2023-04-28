<?php
defined('_IS_MRKEN') or die('Error: restricted access');

// Check right
if ($rights < 9) {
    header('Location: ' . SITE_URL . '/?err');
    exit;
}
/*
-----------------------------------------------------------------
Читаем каталог с файлами языков
-----------------------------------------------------------------
*/
$lng_list = array();
$lng_desc = array();
foreach (glob('../incfiles/languages/*/_core.ini') as $val) {
    $dir = explode('/', dirname($val));
    $iso = array_pop($dir);
    $desc = parse_ini_file($val);
    $lng_list[$iso] = isset($desc['name']) && !empty($desc['name']) ? $desc['name'] : $iso;
    $lng_desc[$iso] = $desc;
}

/*
-----------------------------------------------------------------
Автоустановка языков
-----------------------------------------------------------------
*/
if(isset($_GET['refresh'])){
    core::$lng_list = array();
    echo '<div class="gmenu"><p>' . $lng['refresh_descriptions_ok'] . '</p></div>';
}
$lng_add = array_diff(array_keys($lng_list), array_keys(core::$lng_list));
$lng_del = array_diff(array_keys(core::$lng_list), array_keys($lng_list));
if (!empty($lng_add) || !empty($lng_del)) {
    if (!empty($lng_del) && in_array($set['lng'], $lng_del)) {
        // If system language has been deleted, set to first available
        mysql_query('UPDATE `cms_settings` SET `val` = "' . key($lng_list[]) . '" WHERE `key` = "lng" LIMIT 1');
    }
    mysql_query('UPDATE `cms_settings` SET `val` = "' . mysql_real_escape_string(serialize($lng_list)) . '" WHERE `key` = "lng_list" LIMIT 1');
}

switch ($mod) {
    case 'set':
        /*
        -----------------------------------------------------------------
        Меняем системный язык
        -----------------------------------------------------------------
        */
        $iso = isset($_POST['iso']) ? trim($_POST['iso']) : false;
        if ($iso && array_key_exists($iso, $lng_list)) {
            mysql_query('UPDATE `cms_settings` SET `val` = "' . mysql_real_escape_string($iso) . '" WHERE `key` = "lng"');
        }
        header('Location: index.php?act=languages'); exit;
        break;

    default:
        /*
        -----------------------------------------------------------------
        Выводим список доступных языков
        -----------------------------------------------------------------
        */
        echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['language_default'] . '</div>';  
        echo '<div class="menu"><form action="index.php?act=languages&amp;mod=set" method="post"><p>';
        echo '<table><tr><td>&nbsp;</td><td style="padding-bottom:4px"><h3>' . $lng['language_system'] . '</h3></td></tr>';
        foreach ($lng_desc as $key => $val) {            
            echo '<tr>' .
                 '<td valign="top"><input type="radio" value="' . $key . '" name="iso" ' . ($key == $set['lng'] ? 'checked="checked"' : '') . '/></td>' .
                 '<td style="padding-bottom:6px">' .
                 (file_exists('../images/flags/' . $key . '.gif') ? '<img src="' . SITE_URL . '/images/flags/' . $key . '.gif" alt=""/>&#160;' : '') .
                 '<b>' . $val['name'] . '</b> <span class="green">[' . $key . ']</span>' .
                 '</td>' .
                 '</tr>';
        }
        echo '<tr><td>&nbsp;</td><td><input type="submit" name="submit" value="' . $lng['save'] . '" /></td></tr>' .
             '</table></p>' .
             '</form></div>' .
             '<div class="phdr">' . $lng['total'] . ': <b>' . count($lng_desc) . '</b></div>' .
             '<p><a href="index.php?act=languages&amp;refresh">' . $lng['refresh_descriptions'] . '</a></p>';
}