<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

// For details, contact details
$textl = $user['account'] . ': ' . $lng['information'];
require('../incfiles/head.php');
echo '<div class="phdr"><a href="profile.php?user=' . $user['id'] . '"><b>' . $lng['profile'] . ' '.$user['account'].'</b></a> | ' . $lng['information'] . '</div>';
if ($user['id'] == $user_id || ($rights >= 7 && $rights > $user['rights']))
    echo '<div class="topmenu"><a href="profile.php?act=edit&user=' . $user['id'] . '">' . $lng['edit'] . '</a></div>';
echo '<div class="user"><p>' . functions::display_user($user, array('iphide' => 1, 'header' => '<br/>Giới tính: '.($user['sex'] == 'm' ? 'Nam' : 'Nữ'))) . '</p></div>' .
    '<div class="list2"><p>' .
    '<h3><img src="' . SITE_URL . '/images/contacts.png" width="16" height="16" class="left" />&#160;' . $lng_profile['personal_data'] . '</h3>' .
    '<ul>';
if (file_exists('../files/users/photo/' . $user['id'] . '_small.jpg'))
    echo '<a href="' . SITE_URL . '/files/users/photo/' . $user['id'] . '.jpg"><img src="' . SITE_URL . '/files/users/photo/' . $user['id'] . '_small.jpg" alt="' . $user['account'] . '" border="0" /></a>';
echo '<li><span class="gray">' . $lng_profile['name'] . ':</span> ' . (empty($user['imname']) ? '' : $user['imname']) . '</li>' .
    '<li><span class="gray">' . $lng_profile['birt'] . ':</span> ' . (empty($user['dayb']) ? '' : sprintf("%02d", $user['dayb']) . '.' . sprintf("%02d", $user['monthb']) . '.' . $user['yearb']) . '</li>' .
    '<li><span class="gray">' . $lng_profile['city'] . ':</span> ' . (empty($user['live']) ? '' : htmlspecialchars($user['live'])) . '</li>' .
    '<li><span class="gray">' . $lng_profile['about'] . ':</span> ' . (empty($user['about']) ? '' : '<br />' . functions::checkout($user['about'], 1, 1, 2)) . '</li>' .
    '</ul></p><p>' .
    '<h3><img src="' . SITE_URL . '/images/mail.png" width="16" height="16" class="left" />&#160;' . $lng_profile['communication'] . '</h3><ul>' .
    '<li><span class="gray">' . $lng_profile['phone_number'] . ':</span> ' . (empty($user['mobile']) ? '' : '0' . $user['mobile']) . '</li>' .
    '<li><span class="gray">E-mail:</span> ';
if (!empty($user['mail']) && $user['mailvis'] || $rights >= 7 || $user['id'] == $user_id) {
    echo htmlspecialchars($user['mail']) . ($user['mailvis'] ? '' : '<span class="gray"> [' . $lng_profile['hidden'] . ']</span>');
}
echo '</li>' .
    '<li><span class="gray">Facebook:</span> ' . (empty($user['facebook']) ? '' : htmlspecialchars($user['facebook'])) . '</li>' .
    '</ul></p></div>';
// stats
echo '<div class="list2">' .
    '<p><h3>' . functions::image('rate.gif') . $lng['statistics'] . '</h3><ul>';
if ($rights >= 7) {
    if (!$user['preg'] && empty($user['regadm']))
        echo '<li>' . $lng_profile['awaiting_registration'] . '</li>';
    elseif ($user['preg'] && !empty($user['regadm']))
        echo '<li>' . $lng_profile['registration_approved'] . ': ' . $user['regadm'] . '</li>'; else
        echo '<li>' . $lng_profile['registration_free'] . '</li>';
}
echo'<li><span class="gray">' . $lng_profile['registered'] . ':</span> ' . date("d.m.Y", $user['datereg']) . '</li>' .
    '<li><span class="gray">' . $lng_profile['stayed'] . ':</span> ' . ceil($user['total_on_site']/60) . ' '.$lng['minutes'].'</li>';
$lastvisit = time() > $user['lastdate'] + 300 ? date("H:i d.m.Y", $user['lastdate']) : false;
if ($lastvisit)
    echo '<li><span class="gray">' . $lng['last_visit'] . ':</span> ' . $lastvisit . '</li>';
// Ban count
$bancount = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'"), 0);
if ($bancount) {
    echo '<li><a href="profile.php?act=ban&user=' . $user['id'] . '">' . $lng['infringements'] . '</a> (' . $bancount . ')</li>';
}
echo'</ul></p><p>' .
    '<h3>' . functions::image('activity.gif') . $lng_profile['activity'] . '</h3><ul>' .
    '<li><span class="gray">' . $lng['forum'] . ':</span> <a href="profile.php?act=activity&amp;user=' . $user['id'] . '">' . $user['postforum'] . '</a></li>' .
    '<li><span class="gray">' . $lng['comments'] . ':</span> ' . $user['komm'] . '</li>' .
    '</ul></p>' .
    '<p><h3>' . functions::image('award.png') . $lng_profile['achievements'] . '</h3>';
$num = array(
    50,
    100,
    500,
    1000,
    5000
);
$query = array(
    'postforum' => $lng['forum'],
    'komm' => $lng['comments']
);
echo '<table border="0" cellspacing="0" cellpadding="0"><tr>';
foreach ($num as $val) {
    echo '<td width="28" align="center"><small>' . $val . '</small></td>';
}
echo '<td></td></tr>';
foreach ($query as $key => $val) {
    echo '<tr>';
    foreach ($num as $achieve) {
        echo'<td align="center">' . functions::image(($user[$key] >= $achieve ? 'green' : 'red') . '.gif') . '</td>';
    }
    echo'<td><small><b>' . $val . '</b></small></td></tr>';
}
echo'</table></p></div>';