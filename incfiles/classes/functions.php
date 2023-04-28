<?php
defined('_MRKEN_CMS') or die('Restricted access');

class functions extends core
{
    private static $avatars = array();

    public static function get_avatar($user_id) {
        if (!isset(self::$avatars[$user_id])) {
            if (file_exists(ROOTPATH . 'files/users/avatar/' . $user_id . '.png')) {
                self::$avatars[$user_id] = SITE_URL . '/files/users/avatar/' . $user_id . '.png';
            } else {
                self::$avatars[$user_id] = SITE_URL . '/images/empty.png';
            }
        }
        return self::$avatars[$user_id];
    }

    /**
     * Аntiflood
     * Mode:
     *   1 - adaptive
     *   2 - Day / Night
     *   3 - Day
     *   4 - Night
     *
     * @return int|bool
     */
    public static function antiflood()
    {
        $default = array(
            'mode' => 2,
            'day' => 5,
            'night' => 15,
            'dayfrom' => 10,
            'dayto' => 22
        );
        $af = isset(self::$system_set['antiflood']) ? unserialize(self::$system_set['antiflood']) : $default;
        switch ($af['mode']) {
            case 1:
                // Адаптивный режим
                $adm = mysql_result(mysql_query("SELECT COUNT(*) FROM `users` WHERE `rights` > 0 AND `lastdate` > " . (time() - 300)), 0);
                $limit = $adm > 0 ? $af['day'] : $af['night'];
                break;
            case 3:
                // День
                $limit = $af['day'];
                break;
            case 4:
                // Ночь
                $limit = $af['night'];
                break;
            default:
                // По умолчанию день / ночь
                $c_time = date('G', time());
                $limit = $c_time > $af['day'] && $c_time < $af['night'] ? $af['day'] : $af['night'];
        }
        if (self::$user_rights > 0)
            $limit = 4; // Для Администрации задаем лимит в 4 секунды
        $flood = self::$user_data['lastpost'] + $limit - time();
        if ($flood > 0)
            return $flood;
        else
            return FALSE;
    }
	
	public static function forum_tags($text){
		$text = trim($text);
		$return = array();
		if(empty($text)) return '';
		$tags = array_map('trim', explode(',', $text));
		foreach($tags as $tag){
			if(!empty($tag) && mb_strlen($tag) > 3)
				$return[] = $tag;
		}
		$return = array_slice($return, 0, 5);
		return serialize($return);
	}
	/*
	* Show tags
	* $text str
	* $mod
	*    0 - return text of tags
	*    1 - return search tags
	*/
	public static function show_tags($text, $mod = 0)
    {
		if (!function_exists('search_link')) {
			function search_link($text) {
				return '<a href="search.php?search=' . urlencode($text) . '&t=1" class="tag">' . htmlspecialchars($text) . '</a>';
			}
		}
		$tags = unserialize($text);
		if($mod == 1){
			$tags = array_map('search_link', $tags);
            return implode('', $tags);
		} else {
            $tags = array_map('htmlspecialchars', $tags);
            return implode(', ', $tags);
        }
	}

	/* random generator */
	public static function rand_code($length) {
		$vals = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		$result = '';
		for ($i = 1; $i <= $length; $i++) {
			$result .= $vals{rand(0, strlen($vals) -1 )};
		}
		return $result;
	}

    /* Nick color*/
    public static function nick_color($nick, $right = 0) {
        $color = '';
        switch ($right) {
            case '9' : $color = 'ff0000'; break;
            case '6' : $color = '009900';
        }
        if (empty($color)) return $nick;
        return '<span style="color:#' . $color . '">' . $nick . '</span>';
    }

    /**
     * Фильтрация строк
     *
     * @param string $str
     *
     * @return string
     */
    public static function checkin($str)
    {
        if (function_exists('iconv')) {
            $str = iconv('UTF-8', 'UTF-8', $str);
        }

        // Filter the invisible characters
        $str = preg_replace('/[^\P{C}\n]+/u', '', $str);

        return trim($str);
    }

    /**
     * text processing before displaying
     *
     * @param string $str
     * @param int $br      Parameter handling line breaks
     *                        0 - not process (default)
     *                        1 - process
     *                        2 - instead of line breaks inserted blanks
     *                        3 - process with paragraph
     * @param int $tags    Parameter tag processing
     *                        0 - not process (default)
     *                        1 - process
     *                        2 - cut tags
     * @param int $smileys Parameter smiley processing
     *                        0 - not process (default)
     *                        1 - process with user settings
     *                        2 - process without user settings
     *
     * @return string
     */
    public static function checkout($str, $br = 0, $tags = 0, $smileys = 0)
    {
        $str = htmlspecialchars(trim($str));
        $smileys = (($smileys == 1 && self::$user_set['smileys']) || $smileys == 2);
        if ($br == 1) {
            // Insert line breaks
            $str = nl2br($str);
        } elseif ($br == 2) {
            $str = preg_replace('/([\r\n]|\r\n)/is', ' ', $str);
        } elseif ($br == 3) {
            $str = preg_replace('/([\r\n]|\r\n)/is', '</p><p>', $str);
            $str = str_replace('<p><div', '<div', $str);
            $str = str_replace('div></p>', 'div>', $str);
            $str = '<p>' . $str . '</p>';
        }
        
        $str = preg_replace_callback(
            '#\[code=([^\]]+)](.+?)\[/code]#is', function($matches) {
                $matches[2] = strtr($matches[2], array(
                    ':' => '_PNPH_0_jhasgdkas',
                    '[' => '_PNPH_1_jhasgdkas',
                    ']' => '_PNPH_2_jhasgdkas'
                ));
                return '[code=' . $matches[1] . ']' . $matches[2] . '[/code]';
            }, $str
        );
        if ($tags == 1) {
            $str = bbcode::tags($str);
        } elseif ($tags == 2) {
            $str = bbcode::notags($str);
        }
        if ($smileys) {
            $str = self::smileys($str);
        }
        $str = strtr($str, array(
            '_PNPH_0_jhasgdkas' => ':',
            '_PNPH_1_jhasgdkas' => '[',
            '_PNPH_2_jhasgdkas' => ']'
        ));

        return trim($str);
    }

    /**
     * Показываем дату с учетом сдвига времени
     *
     * @param int $var Время в Unix формате
     *
     * @return string Отформатированное время
     */
    public static function display_date($var)
    {
        if (date('Y', $var) == date('Y', time())) {
            if (date('z', $var) == date('z', time()))
                return self::$lng['today'] . ', ' . date("H:i", $var);
            if (date('z', $var) == date('z', time()) - 1)
                return self::$lng['yesterday'] . ', ' . date("H:i", $var);
        }

        return date('d.m.Y / H:i', $var);
    }

    /**
     * Сообщения об ошибках
     *
     * @param string|array $error Сообщение об ошибке (или массив с сообщениями)
     * @param string $link  Необязательная ссылка перехода
     *
     * @return bool|string
     */
    public static function display_error($error = '', $link = '')
    {
        if (!empty($error)) {
            return '<div class="rmenu"><p><b>' . self::$lng['error'] . '!</b><br />' .
            (is_array($error) ? implode('<br />', $error) : $error) . '</p>' .
            (!empty($link) ? '<p>' . $link . '</p>' : '') . '</div>';
        } else {
            return FALSE;
        }
    }

    /**
     * Отображение различных меню
     *
     * @param array $val
     * @param string $delimiter Разделитель между пунктами
     * @param string $end_space Выводится в конце
     *
     * @return string
     */
    public static function display_menu($val = array(), $delimiter = ' | ', $end_space = '')
    {
        return implode($delimiter, array_diff($val, array(''))) . $end_space;
    }
	/**
	* unmark
	**/
	public static function bodau($text, $mod = 0){
		if(empty($text)) return false;
		$text = html_entity_decode(trim($text), ENT_QUOTES, 'UTF-8');
		$text = str_replace('́', '', $text);
		$text = str_replace('̀', '', $text);
		$text = str_replace('̃', '', $text);
		$text = str_replace('̣', '', $text);
		$text = str_replace('̉', '', $text);
		$text = mb_strtolower($text);
		$text = preg_replace('/(à|á|ả|ã|ạ|â|ầ|ấ|ẩ|ẫ|ậ|ă|ằ|ắ|ẳ|ẵ|ặ)/','a', $text);
		$text = preg_replace('/(è|é|ẻ|ẽ|ẹ|ê|ề|ế|ể|ễ|ệ)/','e', $text);
		$text = preg_replace('/(ì|í|ỉ|ĩ|ị)/', 'i', $text);
		$text = preg_replace('/(ò|ó|ỏ|õ|ọ|ô|ồ|ố|ổ|ỗ|ộ|ơ|ờ|ớ|ở|ỡ|ợ)/', 'o', $text);
		$text = preg_replace('/(ù|ú|ủ|ũ|ụ|ư|ừ|ứ|ử|ữ|ự)/', 'u', $text);
		$text = preg_replace('/(ỳ|ý|ỷ|ỹ|ỵ)/', 'y', $text);
		$text = preg_replace('/(đ|đ)/', 'd', $text);
        if (!$mod) {
            $text = preg_replace('/[^a-z0-9-]/', '-', $text);
            $text = preg_replace('/--+/', '-', $text);
            $text = preg_replace('/^-/', '', $text);
            $text = preg_replace('/-$/', '', $text);
        }
		return $text;
	}
    /**
     * Постраничная навигация
     * За основу взята доработанная функция от форума SMF 2.x.x
     *
     * @param string $url
     * @param int $start
     * @param int $total
     * @param int $kmess
     *
     * @return string
     */
    public static function display_pagination($url, $start, $total, $kmess, $after = '')
    {
        $neighbors = 2;
        if ($start >= $total)
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        else
            $start = max(0, (int)$start - ((int)$start % (int)$kmess));
        $base_link = '<a class="pagenav" href="' . strtr($url, array('%' => '%%')) . '%d' . $after .'">%s</a>';
        $out[] = $start == 0 ? '' : sprintf($base_link, $start / $kmess, '&lt;&lt;');
        if ($start > $kmess * $neighbors)
            $out[] = sprintf($base_link, 1, '1');
        if ($start > $kmess * ($neighbors + 1))
            $out[] = '<span style="font-weight: bold;">...</span>';
        for ($nCont = $neighbors; $nCont >= 1; $nCont--)
            if ($start >= $kmess * $nCont) {
                $tmpStart = $start - $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        $out[] = '<span class="currentpage"><b>' . ($start / $kmess + 1) . '</b></span>';
        $tmpMaxPages = (int)(($total - 1) / $kmess) * $kmess;
        for ($nCont = 1; $nCont <= $neighbors; $nCont++)
            if ($start + $kmess * $nCont <= $tmpMaxPages) {
                $tmpStart = $start + $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        if ($start + $kmess * ($neighbors + 1) < $tmpMaxPages)
            $out[] = '<span style="font-weight: bold;">...</span>';
        if ($start + $kmess * $neighbors < $tmpMaxPages)
            $out[] = sprintf($base_link, $tmpMaxPages / $kmess + 1, $tmpMaxPages / $kmess + 1);
        if ($start + $kmess < $total) {
            $display_page = ($start + $kmess) > $total ? $total : ($start / $kmess + 2);
            $out[] = sprintf($base_link, $display_page, '&gt;&gt;');
        }

        return implode(' ', $out);
    }

    /**
     * Показываем местоположение пользователя
     *
     * @param int $user_id
     * @param string $place
     *
     * @return mixed|string
     */
    public static function display_place($user_id = 0, $place = '')
    {
        global $headmod;
        $place = explode(",", $place);
        $placelist = parent::load_lng('places');
        if (array_key_exists($place[0], $placelist)) {
            if ($place[0] == 'profile') {
                if ($place[1] == $user_id) {
                    return '<a href="' . SITE_URL . '/users/profile.php?user=' . $place[1] . '">' . $placelist['profile_personal'] . '</a>';
                } else {
                    $user = self::get_user($place[1]);

                    return $placelist['profile'] . ': <a href="' . SITE_URL . '/users/profile.php?user=' . $user['id'] . '">' . $user['account'] . '</a>';
                }
            } elseif ($place[0] == 'online' && isset($headmod) && $headmod == 'online') {
                return $placelist['here'];
            } else {
                return str_replace('#home#', SITE_URL, $placelist[$place[0]]);
            }
        }

        return '<a href="' . SITE_URL . '/index.php">' . $placelist['homepage'] . '</a>';
    }

    /**
     * Отображения личных данных пользователя
     *
     * @param int $user Массив запроса в таблицу `users`
     * @param array $arg  Массив параметров отображения
     *                    [lastvisit] (boolean)   Дата и время последнего визита
     *                    [stshide]   (boolean)   Скрыть статус (если есть)
     *                    [iphide]    (boolean)   Скрыть (не показывать) IP и UserAgent
     *                    [iphist]    (boolean)   Показывать ссылку на историю IP
     *
     *                    [header]    (string)    Текст в строке после Ника пользователя
     *                    [body]      (string)    Основной текст, под ником пользователя
     *                    [sub]       (string)    Строка выводится вверху области "sub"
     *                    [footer]    (string)    Строка выводится внизу области "sub"
     *
     * @return string
     */
    public static function display_user($user = 0, $arg = array())
    {
        global $mod;
        $out = FALSE;

        if (!$user['id']) {
            $out = '<b>' . self::$lng['guest'] . '</b>';
            if (!empty($user['account']))
                $out .= ': ' . $user['account'];
            if (!empty($arg['header']))
                $out .= ' ' . $arg['header'];
        } else {
            if (self::$user_set['avatar']) {
                $out .= '<table cellpadding="0" cellspacing="0" width="100%"><tr valign="top"><td width="38">';
                if (file_exists((ROOTPATH . 'files/users/avatar/' . $user['id'] . '.png')))
                    $out .= '<img src="' . SITE_URL . '/files/users/avatar/' . $user['id'] . '.png" width="32" height="32" alt="" />';
                else
                    $out .= '<img src="' . SITE_URL . '/images/empty.png" width="32" height="32" alt="" />';
                $out .= '</td><td>';
            }
            $out .= !self::$user_id || self::$user_id == $user['id'] ? '<b>' . $user['account'] . '</b>' : '<a href="' . SITE_URL . '/users/profile.php?user=' . $user['id'] . '"><b>' . $user['account'] . '</b></a>';
            $rank = array(
                0 => 'Thành viên',
                3 => 'F-Mod',
                5 => 'L-Mod',
                6 => 'Super Mod',
                7 => 'Admin',
                9 => 'Trùm!'
            );
            $rights = isset($user['rights']) ? $user['rights'] : 0;
            if(!isset($arg['ofhide']))
                $out .= ' <img src="' . SITE_URL . '/images/o'.(time() > $user['lastdate'] + 300 ? 'ff' : 'n').'.gif" alt="*"/>';
            if (!empty($arg['header']))
                $out .= ' ' . $arg['header'];
            if (self::$user_set['avatar'])
                $out .= '</td><td align="right"><div>'.$rank[$rights].'</div>'.((!isset($arg['stshide']) && !empty($user['status'])) ? '<div class="status">' . htmlspecialchars($user['status']) . '</div>' : '').'</td></tr></table>';
        }
        if (isset($arg['body'])){
            $out .= '<div>' . $arg['body'] . '</div>';
        }
        $ipinf = !isset($arg['iphide']) && self::$user_rights ? 1 : 0;
        $lastvisit = time() > $user['lastdate'] + 300 && isset($arg['lastvisit']) ? self::display_date($user['lastdate']) : FALSE;
        if ($ipinf || $lastvisit || isset($arg['sub']) && !empty($arg['sub']) || isset($arg['footer'])) {
            $out .= '<div class="sub">';
            if (isset($arg['sub'])) {
                $out .= '<div>' . $arg['sub'] . '</div>';
            }
            if ($lastvisit) {
                $out .= '<div><span class="gray">' . self::$lng['last_visit'] . ':</span> ' . $lastvisit . '</div>';
            }
            $iphist = '';
            if ($ipinf) {
                $out .= '<div><span class="gray">' . self::$lng['browser'] . ':</span> ' . htmlspecialchars($user['browser']) . '</div>' .
                    '<div><span class="gray">' . self::$lng['ip_address'] . ':</span> ';
                $hist = $mod == 'history' ? '&mod=history' : '';
                $ip = long2ip($user['ip']);
                if (self::$user_rights && isset($user['ip_via_proxy']) && $user['ip_via_proxy']) {
                    $out .= '<b class="red"><a href="' . SITE_URL . '/' . self::$system_set['admp'] . '/index.php?act=search_ip&ip=' . $ip . $hist . '">' . $ip . '</a></b>';
                    $out .= '&#160;[<a href="' . SITE_URL . '/' . self::$system_set['admp'] . '/index.php?act=ip_whois&ip=' . $ip . '">?</a>]';
                    $out .= ' / ';
                    $out .= '<a href="' . SITE_URL . '/' . self::$system_set['admp'] . '/index.php?act=search_ip&ip=' . long2ip($user['ip_via_proxy']) . $hist . '">' . long2ip($user['ip_via_proxy']) . '</a>';
                    $out .= '&#160;[<a href="' . SITE_URL . '/' . self::$system_set['admp'] . '/index.php?act=ip_whois&ip=' . long2ip($user['ip_via_proxy']) . '">?</a>]';
                } elseif (self::$user_rights) {
                    $out .= '<a href="' . SITE_URL . '/' . self::$system_set['admp'] . '/index.php?act=search_ip&ip=' . $ip . $hist . '">' . $ip . '</a>';
                    $out .= '&#160;[<a href="' . SITE_URL . '/' . self::$system_set['admp'] . '/index.php?act=ip_whois&ip=' . $ip . '">?</a>]';
                } else {
                    $out .= $ip . $iphist;
                }
                if (isset($arg['iphist'])) {
                    $iptotal = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_users_iphistory` WHERE `user_id` = '" . $user['id'] . "'"), 0);
                    $out .= '<div><span class="gray">' . self::$lng['ip_history'] . ':</span> <a href="' . SITE_URL . '/users/profile.php?act=ip&user=' . $user['id'] . '">[' . $iptotal . ']</a></div>';
                }
                $out .= '</div>';
            }
            if (isset($arg['footer']))
                $out .= $arg['footer'];
            $out .= '</div>';
        }

        return $out;
    }

    /**
     * Форматирование имени файла
     *
     * @param string $name
     *
     * @return string
     */
    public static function format($name)
    {
        $f1 = strrpos($name, ".");
        $f2 = substr($name, $f1 + 1, 999);
        $fname = strtolower($f2);

        return $fname;
    }

    /**
     * Получаем данные пользователя
     *
     * @param int $id Идентификатор пользователя
     *
     * @return array|bool
     */
    public static function get_user($id = 0)
    {
        if ($id && $id != self::$user_id) {
            $req = mysql_query("SELECT * FROM `users` WHERE `id` = '$id'");
            if (mysql_num_rows($req)) {
                return mysql_fetch_assoc($req);
            } else {
                return FALSE;
            }
        } else {
            return self::$user_data;
        }
    }

    public static function image($name, $args = array())
    {
        if (is_file(ROOTPATH . 'images/' . $name)) {
            $src = SITE_URL . '/images/' . $name;
        } else {
            return false;
        }

        return '<img src="' . $src . '" alt="' . (isset($args['alt']) ? $args['alt'] : '') . '"' .
        (isset($args['width']) ? ' width="' . $args['width'] . '"' : '') .
        (isset($args['height']) ? ' height="' . $args['height'] . '"' : '') .
        ' class="' . (isset($args['class']) ? $args['class'] : 'icon') . '"/>';
    }

    /**
     * Является ли выбранный юзер другом?
     *
     * @param int $id   Идентификатор пользователя, которого проверяем
     *
     * @return bool
     */
    public static function is_friend($id = 0)
    {
        static $user_id = NULL;
        static $return = FALSE;

        if (!self::$user_id && !$id) {
            return FALSE;
        }

        if (is_null($user_id) || $id != $user_id) {
            $query = mysql_result(mysql_query("SELECT COUNT(*) FROM `cms_contact` WHERE `type` = '2' AND ((`from_id` = '$id' AND `user_id` = '" . self::$user_id . "') OR (`from_id` = '" . self::$user_id . "' AND `user_id` = '$id'))"), 0);
            $return = $query == 2 ? TRUE : FALSE;
        }

        return $return;
    }

    /**
     * Находится ли выбранный пользователь в контактах и игноре?
     *
     * @param int $id Идентификатор пользователя, которого проверяем
     *
     * @return int Результат запроса:
     *             0 - не в контактах
     *             1 - в контактах
     *             2 - в игноре у меня
     */
    public static function is_contact($id = 0)
    {
        static $user_id = NULL;
        static $return = 0;

        if (!self::$user_id && !$id) {
            return 0;
        }

        if (is_null($user_id) || $id != $user_id) {
            $user_id = $id;
            $req_1 = mysql_query("SELECT * FROM `cms_contact` WHERE `user_id` = '" . self::$user_id . "' AND `from_id` = '$id'");
            if (mysql_num_rows($req_1)) {
                $res_1 = mysql_fetch_assoc($req_1);
                if ($res_1['ban'] == 1) {
                    $return = 2;
                } else {
                    $return = 1;
                }
            } else {
                $return = 0;
            }
        }

        return $return;
    }

    /**
     * Проверка на игнор у получателя
     *
     * @param $id
     *
     * @return bool
     */
    public static function is_ignor($id)
    {
        static $user_id = NULL;
        static $return = FALSE;

        if (!self::$user_id && !$id) {
            return FALSE;
        }

        if (is_null($user_id) || $id != $user_id) {
            $user_id = $id;
            $req_2 = mysql_query("SELECT * FROM `cms_contact` WHERE `user_id` = '$id' AND `from_id` = '" . self::$user_id . "'");
            if (mysql_num_rows($req_2)) {
                $res_2 = mysql_fetch_assoc($req_2);
                if ($res_2['ban'] == 1) {
                    $return = TRUE;
                }
            }
        }

        return $return;
    }

    /*
    -----------------------------------------------------------------
    Обработка смайлов
    -----------------------------------------------------------------
    */
    public static function smileys($str)
    {
        if (empty(self::$smileys_cache)) {
            return $str;
        } else {
            return strtr($str, array_merge(self::$smileys_cache['simply'], self::$smileys_cache['default'], self::$smileys_cache['other']));
        }
    }

    /*
    -----------------------------------------------------------------
    Функция пересчета на дни, или часы
    -----------------------------------------------------------------
    */
    public static function timecount($var)
    {
        global $lng;
        if ($var < 0) $var = 0;
        $day = ceil($var / 86400);
        if ($var > 345600) return $day . ' ' . $lng['timecount_days'];
        if ($var >= 172800) return $day . ' ' . $lng['timecount_days_r'];
        if ($var >= 86400) return '1 ' . $lng['timecount_day'];

        return date('G:i:s', mktime(0, 0, $var));
    }
}