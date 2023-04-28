<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

$textl = $lng['forum'] . ' | ' . $lng['unread'];
$headmod = 'forumnew';
require('../incfiles/head.php');
unset($_SESSION['fsort_id']);
unset($_SESSION['fsort_users']);
if ($user_id) {
    switch ($do) {
        case 'reset':
            // Select all topics read
            $req = mysql_query("SELECT `forum`.`id`
            FROM `forum` LEFT JOIN `cms_forum_rdm` ON `forum`.`id` = `cms_forum_rdm`.`topic_id` AND `cms_forum_rdm`.`user_id` = '$user_id'
            WHERE `forum`.`type`='t'
            AND `cms_forum_rdm`.`topic_id` IS Null");
            while ($res = mysql_fetch_assoc($req)) {
                mysql_query("INSERT INTO `cms_forum_rdm` SET
                    `topic_id` = '" . $res['id'] . "',
                    `user_id` = '$user_id',
                    `time` = '" . time() . "'
                ");
            }
            $req = mysql_query("SELECT `forum`.`id` AS `id`
            FROM `forum` LEFT JOIN `cms_forum_rdm` ON `forum`.`id` = `cms_forum_rdm`.`topic_id` AND `cms_forum_rdm`.`user_id` = '$user_id'
            WHERE `forum`.`type`='t'
            AND `forum`.`time` > `cms_forum_rdm`.`time`");
            while ($res = mysql_fetch_array($req)) {
                mysql_query("UPDATE `cms_forum_rdm` SET
                    `time` = '" . time() . "'
                    WHERE `topic_id` = '" . $res['id'] . "' AND `user_id` = '$user_id'
                ");
            }
            echo '<div class="menu"><p>' . $lng_forum['unread_reset_done'] . '<br /><a href="index.php">' . $lng_forum['to_forum'] . '</a></p></div>';
            break;

        case 'period':
            // Display topics for the selected period
            $vr = isset($_REQUEST['vr']) ? abs(intval($_REQUEST['vr'])) : 24;
            $vr1 = time() - $vr * 3600;
            if ($rights == 9) {
                $req = mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `time` > '$vr1'");
            } else {
                $req = mysql_query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `time` > '$vr1' AND `close` != '1'");
            }
            $count = mysql_result($req, 0);

            echo '<div class="phdr"><a href="index.php"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['unread_all_for_period'] . ' ' . $vr . ' ' . $lng_forum['hours'] . '</div>';

            // The form of the selection period
            echo '<div class="topmenu"><form action="index.php?act=new&do=period" method="post">' .
                '<input type="text" maxlength="3" name="vr" value="' . $vr . '" size="3"/>' .
                '<input type="submit" name="submit" value="' . $lng['show_for_period'] . '"/>' .
                '</form></div>';

            if ($count > $kmess) {
                echo '<div class="topmenu">' . functions::display_pagination('index.php?act=new&do=period&vr=' . $vr . '&page=', $start, $count, $kmess) . '</div>';
            }

            if ($count > 0) {
                if ($rights == 9) {
                    $req = mysql_query("SELECT * FROM `forum` WHERE `type`='t' AND `time` > '" . $vr1 . "' ORDER BY `time` DESC LIMIT " . $start . "," . $kmess);
                } else {
                    $req = mysql_query("SELECT * FROM `forum` WHERE `type`='t' AND `time` > '" . $vr1 . "' AND `close` != '1' ORDER BY `time` DESC LIMIT " . $start . "," . $kmess);
                }
                for ($i = 0; $res = mysql_fetch_array($req); ++$i) {
                    echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    $q3 = mysql_query("SELECT `id`, `refid`, `text` FROM `forum` WHERE `type`='r' AND `id`='" . $res['refid'] . "'");
                    $razd = mysql_fetch_array($q3);
                    $q4 = mysql_query("SELECT `text` FROM `forum` WHERE `type`='f' AND `id`='" . $razd['refid'] . "'");
                    $frm = mysql_fetch_array($q4);
                    $colmes = mysql_query("SELECT * FROM `forum` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
                    $colmes1 = mysql_num_rows($colmes);
                    $cpg = ceil($colmes1 / $kmess);
                    $nick = mysql_fetch_array($colmes);

                    if ($res['edit']) {
                        echo functions::image('tz.gif');
                    } elseif ($res['close']) {
                        echo functions::image('dl.gif');
                    } else {
                        echo functions::image('np.gif');
                    }

                    if ($res['realid'] == 1) {
                        echo functions::image('rate.gif');
                    }

                    echo ' <a href="'.functions::bodau($res['text']).'.' . $res['id'] .'.html'. ($cpg > 1 ? '?page=' . $cpg : '') . '">' . htmlspecialchars($res['text']) .'</a> [' . $colmes1 . ']';
                    if ($cpg > 1) {
                        echo '<a href="'.functions::bodau($res['text']).'.' . $res['id'] .'.html?page=' . $cpg . '">&#160;&gt;&gt;</a>';
                    }

                    echo '<br /><div class="sub"><a href="'.functions::bodau($razd['text']).'.' . $razd['id'] . '/">' . htmlspecialchars($frm['text']) . '&#160;/&#160;' . htmlspecialchars($razd['text']) . '</a><br />';
                    echo $res['from'];

                    if ($colmes1 > 1) {
                        echo '&#160;/&#160;' . $nick['from'];
                    }

                    echo ' <span class="gray">' . functions::display_date($nick['time']) . '</span>';
                    echo '</div></div>';
                }
            } else {
                echo '<div class="menu"><p>' . $lng_forum['unread_period_empty'] . '</p></div>';
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . $count . '</div>';
            if ($count > $kmess) {
                echo '<div class="topmenu">' . functions::display_pagination('index.php?act=new&do=period&vr=' . $vr . '&page=', $start, $count, $kmess) . '</div>' .
                    '<p><form action="index.php?act=new&do=period&vr=' . $vr . '" method="post">
                    <input type="text" name="page" size="2"/>
                    <input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/></form></p>';
            }
            break;

		case 'all':
			$total = mysql_result(mysql_query('SELECT COUNT(*) FROM `forum` WHERE `type`="t" AND `close`="0"'),0);
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['forum'] . '</b></a> | Danh sách chủ đề</div>';
			if($total > 0){
				$req = mysql_query('SELECT * FROM `forum` WHERE `type`="t" AND `close`="0" ORDER BY `id` DESC LIMIT '.$start.','.$kmess.'');
				$i=0;
				while($res = mysql_fetch_assoc($req)){
					echo '<div class="list'.($i%2+1).'">';
					$icons = array(
                        (isset($np) ? (!$res['vip'] ? functions::image('op.gif') : '') : functions::image('np.gif')),
                        ($res['vip'] ? functions::image('pt.gif') : ''),
                        ($res['realid'] ? functions::image('rate.gif') : ''),
                        ($res['edit'] ? functions::image('tz.gif') : '')
                    );
                    echo functions::display_menu($icons, '');
					echo '<a href="' . functions::bodau($res['text']) . '.' . $res['id'] . '.html">' . htmlspecialchars($res['text']) . '</a> (' . $res['from'] . ')';
					echo '</div>';
					$i++;
				}
				if($total > $kmess)
					echo '<div class="topmenu">' . functions::display_pagination('?act=new&do=all&page=', $start, $total, $kmess) . '</div>';
			}else{
				echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
			}
			break;

        default:
            // Displays unread topics (for registered)
            $total = counters::forum_new();
            echo '<div class="phdr"><a href="index.php"><b>' . $lng['forum'] . '</b></a> | ' . $lng['unread'] . '</div>';
            if ($total > $kmess)
                echo '<div class="topmenu">' . functions::display_pagination('index.php?act=new&page=', $start, $total, $kmess) . '</div>';
            if ($total > 0) {
                $req = mysql_query("SELECT * FROM `forum`
                LEFT JOIN `cms_forum_rdm` ON `forum`.`id` = `cms_forum_rdm`.`topic_id` AND `cms_forum_rdm`.`user_id` = '$user_id'
                WHERE `forum`.`type`='t'" . ($rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "
                AND (`cms_forum_rdm`.`topic_id` Is Null
                OR `forum`.`time` > `cms_forum_rdm`.`time`)
                ORDER BY `forum`.`time` DESC
                LIMIT $start, $kmess");
                for ($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
                    if ($res['close'])
                        echo '<div class="rmenu">';
                    else
                        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                    $q3 = mysql_query("SELECT `id`, `refid`, `text` FROM `forum` WHERE `type` = 'r' AND `id` = '" . $res['refid'] . "' LIMIT 1");
                    $razd = mysql_fetch_assoc($q3);
                    $q4 = mysql_query("SELECT `id`, `text` FROM `forum` WHERE `type`='f' AND `id` = '" . $razd['refid'] . "' LIMIT 1");
                    $frm = mysql_fetch_assoc($q4);
                    $colmes = mysql_query("SELECT `from`, `time` FROM `forum` WHERE `refid` = '" . $res['id'] . "' AND `type` = 'm'" . ($rights >= 7 ? '' : " AND `close` != '1'") . " ORDER BY `time` DESC");
                    $colmes1 = mysql_num_rows($colmes);
                    $cpg = ceil($colmes1 / $kmess);
                    $nick = mysql_fetch_assoc($colmes);
                    // icons
                    $icons = array(
                        (isset($np) ? (!$res['vip'] ? functions::image('op.gif') : '') : functions::image('np.gif')),
                        ($res['vip'] ? functions::image('pt.gif') : ''),
                        ($res['realid'] ? functions::image('rate.gif') : ''),
                        ($res['edit'] ? functions::image('tz.gif') : '')
                    );
                    echo functions::display_menu($icons, '');
                    echo '<a href="'.functions::bodau($res['text']).'.' . $res['id'] .'.html' . ($cpg > 1 ? '?page=' . $cpg : '') . '">' . htmlspecialchars($res['text']) . '</a> [' . $colmes1 . ']';
                    if ($cpg > 1)
                        echo '&#160;<a href="'.functions::bodau($res['text']).'.' . $res['id'] .'.html?page=' . $cpg . '">&gt;&gt;</a>';
                    echo '<div class="sub">' . $res['from'] . ($colmes1 > 1 ? ' / ' . $nick['from'] : '') .
                        ' <span class="gray">(' . functions::display_date($nick['time']) . ')</span><br />' .
                        '<a href="'.functions::bodau($frm['text']).'.' . $frm['id'] . '/">' . htmlspecialchars($frm['text']) . '</a> / <a href="'.functions::bodau($razd['text']).'.' . $razd['id'] . '/">' . htmlspecialchars($razd['text']) . '</a>' .
                        '</div></div>';
                }
            } else {
                echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
            }
            echo '<div class="phdr">' . $lng['total'] . ': ' . $total . '</div>';
            if ($total > $kmess) {
                echo '<div class="topmenu">' . functions::display_pagination('index.php?act=new&page=', $start, $total, $kmess) . '</div>' .
                    '<p><form action="index.php" method="get">' .
                    '<input type="hidden" name="act" value="new"/>' .
                    '<input type="text" name="page" size="2"/>' .
                    '<input type="submit" value="' . $lng['to_page'] . ' &gt;&gt;"/>' .
                    '</form></p>';
            }

            if ($total) {
                echo '<p><a href="index.php?act=new&amp;do=reset">' . $lng_forum['unread_reset'] . '</a></p>';
            }

    }
} else {
    // Displays the 10 most recent (unregistered)
    echo '<div class="phdr"><a href="index.php"><b>' . $lng['forum'] . '</b></a> | ' . $lng_forum['unread_last_10'] . '</div>';
    $req = mysql_query("SELECT * FROM `forum` WHERE `type` = 't' AND `close` != '1' ORDER BY `time` DESC LIMIT 10");
    if (mysql_num_rows($req)) {
        for ($i = 0; $res = mysql_fetch_assoc($req); ++$i) {
            $q3 = mysql_query("select `id`, `refid`, `text` from `forum` where type='r' and id='" . $res['refid'] . "' LIMIT 1");
            $razd = mysql_fetch_assoc($q3);
            $q4 = mysql_query("select `id`, `refid`, `text` from `forum` where type='f' and id='" . $razd['refid'] . "' LIMIT 1");
            $frm = mysql_fetch_assoc($q4);
            $nikuser = mysql_query("SELECT `from`, `time` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "'ORDER BY `time` DESC");
            $colmes1 = mysql_num_rows($nikuser);
            $cpg = ceil($colmes1 / $kmess);
            $nam = mysql_fetch_assoc($nikuser);
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            // icons
            $icons = array(
                ($res['vip'] ? functions::image('pt.gif') : ''),
                ($res['realid'] ? functions::image('rate.gif') : ''),
                ($res['edit'] ? functions::image('tz.gif') : '')
            );
            echo functions::display_menu($icons, '');
            echo '<a href="'.functions::bodau($res['text']).'.' . $res['id'] . '.html">' . htmlspecialchars($res['text']) . '</a> [' . $colmes1 . ']';
            if ($cpg > 1)
                echo '&#160;<a href="'.functions::bodau($res['text']).'.' . $res['id'] . '.html?page=' . $cpg . '">&gt;&gt;</a>';
            echo '<br/><div class="sub"><a href="'.functions::bodau($razd['text']).'.' . $razd['id'] . '/">' . htmlspecialchars($frm['text']) . ' / ' . htmlspecialchars($razd['text']) . '</a><br />';
            echo $res['from'];
            if (!empty($nam['from'])) {
                echo '&#160;/&#160;' . $nam['from'];
            }
            echo ' <span class="gray">' . date("d.m.y / H:i", $nam['time']) . '</span>';
            echo '</div></div>';
        }
    } else {
        echo '<div class="menu"><p>' . $lng['list_empty'] . '</p></div>';
    }
    echo '<div class="phdr"><a href="index.php">' . $lng['to_forum'] . '</a></div>';
}