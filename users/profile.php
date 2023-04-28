<?php
define('_MRKEN_CMS', 1);

require('../incfiles/core.php');
$lng_profile = core::load_lng('profile');

// Close by unauthorized users
if (!$user_id) {
    require('../incfiles/head.php');
    echo functions::display_error($lng['access_guest_forbidden']);
    require('../incfiles/end.php');
    exit;
}

// get the user data
$user = functions::get_user($user);
if (!$user) {
    require('../incfiles/head.php');
    echo functions::display_error($lng['user_does_not_exist']);
    require('../incfiles/end.php');
    exit;
}

// Switch modes
$array = array(
    'activity'  => 'includes/profile',
    'ban'       => 'includes/profile',
    'edit'      => 'includes/profile',
    'images'    => 'includes/profile',
    'info'      => 'includes/profile',
    'ip'        => 'includes/profile',
    'office'    => 'includes/profile',
    'password'  => 'includes/profile',
    'reset'     => 'includes/profile',
    'settings'  => 'includes/profile',
    'friends'   => 'includes/profile'
);
$path = !empty($array[$act]) ? $array[$act] . '/' : '';
if (array_key_exists($act, $array) && file_exists($path . $act . '.php')) {
    require_once($path . $act . '.php');
} else {
    // user Profile
    $headmod = 'profile,' . $user['id'];
    $textl = 'Trang cá nhân - ' . $user['account'];
    if($user_id && $user['id'] == $user_id)
        $datauser['comm_old'] = $datauser['comm_count'];
    require('../incfiles/head.php');
    echo '<div class="phdr"><b>' . ($user['id'] != $user_id ? $lng_profile['user_profile'] : $lng_profile['my_profile']) . '</b></div>';

    // profiles Menu
    $menu = array();
    if ($user['id'] == $user_id || $rights == 9 || ($rights == 7 && $rights > $user['rights'])) {
        $menu[] = '<a href="profile.php?act=edit&user=' . $user['id'] . '">' . $lng['edit'] . '</a>';
    }
    if ($user['id'] != $user_id && $rights >= 7 && $rights > $user['rights']) {
        $menu[] = '<a href="' . SITE_URL . '/' . $set['admp'] . '/index.php?act=usr_del&id=' . $user['id'] . '">' . $lng['delete'] . '</a>';
    }
    if ($user['id'] != $user_id && $rights > $user['rights']) {
        $menu[] = '<a href="profile.php?act=ban&mod=do&user=' . $user['id'] . '">' . $lng['ban_do'] . '</a>';
    }
    if (!empty($menu)) {
        echo '<div class="topmenu">' . functions::display_menu($menu) . '</div>';
    }

    // Notice of birthday
    if ($user['dayb'] == date('j', time()) && $user['monthb'] == date('n', time())) {
        echo '<div class="gmenu">' . $lng['birthday'] . '!!!</div>';
    }

    // Information about the user
    $arg = array(
        'lastvisit' => 1,
        'iphist'    => 1,
        'header'    => '<br/><img src="' . SITE_URL . '/images/coin.png"> ' . $user['coin'] . ' - <img src="' . SITE_URL . '/images/gold.png"/> '.$user['gold'].''
    );

    if ($user['id'] != core::$user_id) {
        $arg['footer'] = '<span class="gray">' . core::$lng['where'] . ':</span> ' . functions::display_place($user['id'], $user['place']);
    }

    echo '<div class="user"><p>' . functions::display_user($user, $arg) . '</p></div>';
    // If the user is waiting for confirmation of registration, receive a reminder
    if ($rights >= 7 && !$user['preg'] && empty($user['regadm'])) {
        echo '<div class="rmenu">' . $lng_profile['awaiting_registration'] . '</div>';
    }
    // selection Menu
    $total_photo = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_album_files` WHERE `user_id` = '" . $user['id'] . "'"), 0);
    $total_friends = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_contact` WHERE `user_id`="'. $user['id']. '" AND `type`="2" AND `friends`="1"'), 0);
    echo '<div class="menu">' .
        '<a href="profile.php?act=info&user=' . $user['id'] . '">' . $lng['information'] . '</a> · ' .
        '<a href="profile.php?act=activity&user=' . $user['id'] . '">' . $lng_profile['activity'] . '</a> · ' .
        '<a href="album.php?act=list&user=' . $user['id'] . '">' . $lng['photo_album'] . '</a>&#160;(' . $total_photo . ') · ' .
        '<a href="profile.php?act=friends&user=' . $user['id'] . '">' . $lng_profile['friends'] . '</a>&#160;(' . $total_friends . ')' .
        ($user_id == $user['id'] ? ' · <a href="profile.php?act=office">' . $lng['personal'] . '</a>' : ($rights == 9 ? ' · <a href="' . SITE_URL . '/shop/?act=history&id='.$user['id'].'">Lịch sử giao dịch</a>':'')) .
		'</div>';
    if ($user['id'] != $user_id) {
        echo '<div class="menu">';
        // contacts
        if (!functions::is_ignor($user['id']) && functions::is_contact($user['id']) != 2) {
            if (!functions::is_friend($user['id'])) {
                $fr_in = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_contact` WHERE `type`="2" AND `from_id`="'. $user_id .'" AND `user_id`="'. $user['id'] .'"'), 0);
                $fr_out = mysql_result(mysql_query('SELECT COUNT(*) FROM `cms_contact` WHERE `type`="2" AND `user_id`="'. $user_id .'" AND `from_id`="'. $user['id'] .'"'), 0);
                if ($fr_in == 1) {
                    $friend = '<a class="underline" href="profile.php?act=friends&do=ok&id=' . $user['id'] . '">' . $lng_profile['confirm_friendship'] . '</a> · <a class="underline" href="profile.php?act=friends&do=no&id=' . $user['id'] . '">' . $lng_profile['decline_friendship'] . '</a>';
                } else if ($fr_out == 1) {
                    $friend = '<a class="underline" href="profile.php?act=friends&do=cancel&id=' . $user['id'] . '">' . $lng_profile['canceled_demand_friend'] . '</a>';
                } else {
                    $friend = '<a href="profile.php?act=friends&do=add&id=' . $user['id'] . '">' . $lng_profile['in_friend'] . '</a>';
                }
            } else {
                $friend = '<a href="profile.php?act=friends&do=delete&id=' . $user['id'] . '">' . $lng_profile['remov_friend'] . '</a>';
            }
            echo  $friend;
        }

        if (functions::is_contact($user['id']) != 2) {
            if (!functions::is_contact($user['id'])) {
                echo ' · <a href="' . SITE_URL . '/mail/index.php?id=' . $user['id'] . '">' . $lng_profile['add_contacts'] . '</a>';
            } else {
                echo ' · <a href="' . SITE_URL . '/mail/index.php?act=deluser&id=' . $user['id'] . '">' . $lng_profile['delete_contacts'] . '</a>';
            }
        }

        if (functions::is_contact($user['id']) != 2) {
            echo ' · <a href="' . SITE_URL . '/mail/index.php?act=ignor&id=' . $user['id'] . '&add">' . $lng_profile['add_ignor'] . '</a>';
        } else {
            echo ' · <a href="' . SITE_URL . '/mail/index.php?act=ignor&id=' . $user['id'] . '&del">' . $lng_profile['delete_ignor'] . '</a>';
        }

        if (!functions::is_ignor($user['id']) && functions::is_contact($user['id']) != 2 && empty($ban['1']) && empty($ban['3'])) {
            echo ' · <a href="' . SITE_URL . '/mail/index.php?act=write&id=' . $user['id'] . '">' . $lng['message'] . '</a>';
        }
        echo '</div>';
    }
    $context_top = '<div class="phdr">' . $lng['guestbook'] . '</div>';
    $arg = array (
        'comments_table' => 'cms_users_guestbook', // Table Guest
        'object_table' => 'users',                 // Table commented objects
        'script' => 'profile.php?',                 // The name of the script (with the parameters of the call)
        'sub_id_name' => 'user',                   // Name commented object ID
        'sub_id' => $user['id'],                   // Commented object ID
        'owner' => $user['id'],                    // The owner of the object
        'owner_delete' => true,                    // Possibility owner delete comment
        'owner_reply' => true,                     // Possibility owner Reply to comment
        'title' => $lng['comments'],               // section title
        'context_top' => $context_top              // Displayed at the top of the list
    );
    // comments
    $comm = new comments($arg);
    // Updating unread count
    if(!$mod && $user['id'] == $user_id && $user['comm_count'] != $user['comm_old']){
        mysql_query("UPDATE `users` SET `comm_old` = '" . $user['comm_count'] . "' WHERE `id` = '$user_id'");
    }
}

require_once('../incfiles/end.php');