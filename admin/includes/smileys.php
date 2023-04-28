<?php
defined('_IS_MRKEN') or die('Error: restricted access');

echo '<div class="phdr"><a href="index.php"><b>' . $lng['admin_panel'] . '</b></a> | ' . $lng['smileys'] . '</div>';

$ext = array('gif', 'jpg', 'jpeg', 'png'); // List of allowed extensions
$smileys = array();

// Handle simple smilies
foreach (glob(ROOTPATH . 'images' . DIRECTORY_SEPARATOR . 'smileys' . DIRECTORY_SEPARATOR . 'simply' . DIRECTORY_SEPARATOR . '*') as $var) {
    $file = basename($var);
    $name = explode(".", $file);
    if (in_array($name[1], $ext)) {
        $smileys['simply'][':' . $name[0]] = '<img src="' . SITE_URL . '/images/smileys/simply/' . $file . '" alt="[*]" />';
    }
}


// Handle the smilies directory
foreach (glob(ROOTPATH . 'images' . DIRECTORY_SEPARATOR . 'smileys' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*') as $var) {
    $file = basename($var);
    $name = explode('.', $file);
    if (in_array($name[1], $ext)) {
        $path = SITE_URL . '/images/smileys/user/' . basename(dirname($var));
        if (basename(dirname($var)) == 'other') {
            $smileys['other'][':' . $name[0] . ':'] = '<img src="' . $path . '/' . $file . '" alt="[*]" />';
        } else {
            $smileys['default'][':' . $name[0] . ':'] = '<img src="' . $path . '/' . $file . '" alt="[*]" />';
        }
    }
}

// Write cache file
if (file_put_contents(ROOTPATH . 'files/system/cache/smileys.dat', serialize($smileys))) {
    $total = count($smileys['simply']) + count($smileys['default']) + count($smileys['other']);
    echo '<div class="gmenu"><p>' . $lng['smileys_updated'] . ' ' . $lng['total'] . ': ' . $total . '</p></div>';
} else {
    echo '<div class="rmenu"><p>' . $lng['smileys_error'] . '</p></div>';
}