<?php
defined('_MRKEN_CMS') or die('Restricted access');

class core
{
    public static $root = '../';
    public static $ip; // Путь к корневой папке
    public static $ip_via_proxy = 0; // IP адрес за прокси-сервером
    public static $ip_count = array(); // Счетчик обращений с IP адреса
    public static $user_agent; // User Agent
    public static $system_set; // Системные настройки
    public static $lng_iso = 'vi'; // Двухбуквенный ISO код языка
    public static $lng_list = array(); // Список имеющихся языков
    public static $lng = array(); // Массив с фразами языка
    public static $deny_registration = false; // Запрет регистрации пользователей
    public static $device = 'wap'; // Мобильный браузер
    public static $core_errors = array(); // Ошибки ядра
    public static $smileys_cache = array();

    public static $user_id = 0; // Идентификатор пользователя
    public static $user_rights = 0; // Права доступа
    public static $user_data = array(); // Все данные пользователя
    public static $user_set = array(); // Пользовательские настройки
    public static $user_ban = array(); // Бан

    private $flood_chk = 1; // Enabling - Disabling IP in flood
    private $flood_interval = '60'; // The time interval in seconds
    private $flood_limit = '120'; // The number of requests allowed per interval

    function __construct()
    {
        // Получаем IP адреса
        $ip = ip2long($_SERVER['REMOTE_ADDR']) or die('Invalid IP');
        self::$ip = sprintf("%u", $ip);

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $vars)) {
            foreach ($vars[0] AS $var) {
                $ip_via_proxy = ip2long($var);
                if ($ip_via_proxy && $ip_via_proxy != $ip && !preg_match('#^(10|172\.16|192\.168)\.#', $var)) {
                    self::$ip_via_proxy = sprintf("%u", $ip_via_proxy);
                    break;
                }
            }
        }

        // Получаем UserAgent
        if (isset($_SERVER["HTTP_X_OPERAMINI_PHONE_UA"]) && strlen(trim($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])) > 5) {
            self::$user_agent = 'Opera Mini: ' . htmlspecialchars(mb_substr(trim($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']), 0, 255));
        } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
            self::$user_agent = htmlspecialchars(mb_substr(trim($_SERVER['HTTP_USER_AGENT']), 0, 255));
        } else {
            self::$user_agent = 'Not Recognised';
        }

        $this->ip_flood(); // Проверка адреса IP на флуд
        if (get_magic_quotes_gpc()) $this->del_slashes(); // Удаляем слэши
        $this->db_connect(); // Соединяемся с базой данных
        $this->ip_ban(); // Проверяем адрес IP на бан
        $this->session_start(); // Стартуем сессию
        self::$device = $this->device_detect(); // Определение мобильного браузера
        $this->system_settings(); // Получаем системные настройки
        $this->auto_clean(); // Автоочистка системы
        $this->authorize(); // Авторизация пользователей
        $this->site_access(); // Доступ к сайту
		$this->user_key();
        $this->lng_detect(); // Определяем язык системы
        $this->get_smileys_cache();
        self::$lng = self::load_lng(); // Загружаем язык
    }

    /*
    -----------------------------------------------------------------
    Валидация IP адреса
    -----------------------------------------------------------------
    */
    public static function ip_valid($ip)
    {
        if (preg_match('#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#', $ip)) {
            return true;
        }

        return false;
    }

    /*
    -----------------------------------------------------------------
    Загружаем фразы языка из файла
    -----------------------------------------------------------------
    */
    public static function load_lng($module = '_core', $lng = null)
    {
        $lng_set = $lng !== null && in_array($lng, self::$lng_list) ? $lng : self::$lng_iso;

        if (!is_dir(ROOTPATH . 'incfiles/languages/' . $lng_set)) {
            self::$lng_iso = 'vi';
            $lng_set = 'vi';
        }

        $lng_file = ROOTPATH . 'incfiles/languages/' . $lng_set . '/' . $module . '.lng';

        if (file_exists($lng_file)) {
            $out = parse_ini_file($lng_file) or die('ERROR: language file');
            return $out;
        }

        self::$core_errors[] = 'Language file <b>' . $module . '.lng</b> is missing';

        return false;
    }

    /*
    -----------------------------------------------------------------
    Показываем ошибки ядра (если есть)
    -----------------------------------------------------------------
    */
    public static function display_core_errors()
    {
        return !empty(self::$core_errors) ? '<p style="color:#FF0000"><b>CORE ERROR</b>: ' . implode('<br />', self::$core_errors) . '</p>' : '';
    }

    /*
    -----------------------------------------------------------------
    Подключаемся к базе данных
    -----------------------------------------------------------------
    */
    private function db_connect()
    {
        require(ROOTPATH . 'incfiles/config.php');
        $db_host = isset($db_host) ? $db_host : 'localhost';
        $db_user = isset($db_user) ? $db_user : '';
        $db_pass = isset($db_pass) ? $db_pass : '';
        $db_name = isset($db_name) ? $db_name : '';
        $connect = @mysql_connect($db_host, $db_user, $db_pass) or die('Error: cannot connect to database server');
        @mysql_select_db($db_name) or die('Error: specified database does not exist');
        @mysql_query('SET NAMES "utf8"', $connect);
    }

    /*
    -----------------------------------------------------------------
    Проверка адреса IP на флуд
    -----------------------------------------------------------------
    */
    private function ip_flood()
    {
        if ($this->flood_chk) {
            //if ($this->ip_whitelist(self::$ip))
            //    return true;
            $file = ROOTPATH . 'files/system/cache/ip_flood.dat';
            $tmp = array();
            $requests = 1;
            if (!file_exists($file)) $in = fopen($file, "w+");
            else $in = fopen($file, "r+");
            flock($in, LOCK_EX) or die("Cannot flock ANTIFLOOD file.");
            $now = time();
            while ($block = fread($in, 8)) {
                $arr = unpack("Lip/Ltime", $block);
                if (($now - $arr['time']) > $this->flood_interval) continue;
                if ($arr['ip'] == self::$ip) $requests++;
                $tmp[] = $arr;
                self::$ip_count[] = $arr['ip'];
            }
            fseek($in, 0);
            ftruncate($in, 0);
            for ($i = 0; $i < count($tmp); $i++) fwrite($in, pack('LL', $tmp[$i]['ip'], $tmp[$i]['time']));
            fwrite($in, pack('LL', self::$ip, $now));
            fclose($in);
            if ($requests > $this->flood_limit) {
                die('FLOOD: exceeded limit of allowed requests');
            }
        }
    }

    /*
    -----------------------------------------------------------------
    Обрабатываем "белый" список IP адресов
    -----------------------------------------------------------------
    */
    private function ip_whitelist($ip)
    {
        $file = ROOTPATH . 'files/system/cache/ip_wlist.dat';
        if (file_exists($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $val) {
                $tmp = explode(':', $val);
                if (!$tmp[1]) $tmp[1] = $tmp[0];
                if ($ip >= $tmp[0] && $ip <= $tmp[1]) return true;
            }
        }

        return false;
    }

    /*
    -----------------------------------------------------------------
    Удаляем слэши из глобальных переменных
    -----------------------------------------------------------------
    */
    private function del_slashes()
    {
        $in = array(
            &$_GET,
            &$_POST,
            &$_COOKIE
        );
        while ((list($k, $v) = each($in)) !== false) {
            foreach ($v as $key => $val) {
                if (!is_array($val)) {
                    $in[$k][$key] = stripslashes($val);
                    continue;
                }
                $in[] = &$in[$k][$key];
            }
        }
        unset($in);
        if (!empty($_FILES)) foreach ($_FILES as $k => $v) $_FILES[$k]['name'] = stripslashes((string)$v['name']);
    }

    /*
    -----------------------------------------------------------------
    Проверяем адрес IP на Бан
    -----------------------------------------------------------------
    */
    private function ip_ban()
    {
        $req = mysql_query("SELECT `ban_type`, `link` FROM `cms_ban_ip`
            WHERE '" . self::$ip . "' BETWEEN `ip1` AND `ip2`
            " . (self::$ip_via_proxy ? " OR '" . self::$ip_via_proxy . "' BETWEEN `ip1` AND `ip2`" : "") . "
            LIMIT 1
        ") or die('Error: table "cms_ban_ip"');
        if (mysql_num_rows($req)) {
            $res = mysql_fetch_array($req);
            switch ($res['ban_type']) {
                case 2:
                    if (!empty($res['link'])) header('Location: ' . $res['link']);
                    else header('Location: ' . SITE_URL . '/close.php');
                    exit;
                    break;
                case 3:
                    self::$deny_registration = true;
                    break;
                default :
                    header('HTTP/1.0 404 Not Found');
                    exit;
            }
        }
    }

    /*
    -----------------------------------------------------------------
    Стартуем Сессию
    -----------------------------------------------------------------
    */
    private function session_start()
    {
        session_name('SESID');
        session_start();
    }

    /*
    -----------------------------------------------------------------
    Получаем системные настройки
    -----------------------------------------------------------------
    */
    private function system_settings()
    {
        $set = array();
        $req = mysql_query("SELECT * FROM `cms_settings`");
        while (($res = mysql_fetch_row($req)) !== false) $set[$res[0]] = $res[1];
        if (isset($set['lng']) && !empty($set['lng'])) self::$lng_iso = $set['lng'];
        if (isset($set['lng_list'])) self::$lng_list = unserialize($set['lng_list']);
        self::$system_set = $set;
    }

    /*
    -----------------------------------------------------------------
    Определяем язык
    -----------------------------------------------------------------
    */
    private function lng_detect()
    {
        $setlng = isset($_POST['setlng']) ? substr(trim($_POST['setlng']), 0, 2) : '';
        if (!empty($setlng) && array_key_exists($setlng, self::$lng_list)) {
			$_SESSION['lng'] = $setlng;
		}
        if (isset($_SESSION['lng']) && array_key_exists($_SESSION['lng'], self::$lng_list)) {
			self::$lng_iso = $_SESSION['lng'];
		} elseif (self::$user_id && isset(self::$user_set['lng']) && array_key_exists(self::$user_set['lng'], self::$lng_list)) {
			self::$lng_iso = self::$user_set['lng'];
		} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $accept = explode(',', strtolower(trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
            foreach ($accept as $var) {
                $lng = substr($var, 0, 2);
                if (array_key_exists($lng, self::$lng_list)) {
                    self::$lng_iso = $lng;
                    break;
                }
            }
        }
    }

    /*
    -----------------------------------------------------------------
    Авторизация пользователя и получение его данных из базы
    -----------------------------------------------------------------
    */
    private function authorize()
    {
        $user_id = 0;
        $user_ps = false;
        if (isset($_SESSION['uid']) && isset($_SESSION['ups'])) {
            // Авторизация по сессии
            $user_id = abs(intval($_SESSION['uid']));
            $user_ps = $_SESSION['ups'];
        } elseif (isset($_COOKIE['cuid']) && isset($_COOKIE['cups'])) {
            // Авторизация по COOKIE
            $user_id = abs(intval(base64_decode(trim($_COOKIE['cuid']))));
            $_SESSION['uid'] = $user_id;
            $user_ps = trim($_COOKIE['cups']);
            $_SESSION['ups'] = $user_ps;
        }
        if ($user_id && $user_ps) {
            $req = mysql_query("SELECT * FROM `users` WHERE `id` = '$user_id' LIMIT 1");
            if (mysql_num_rows($req)) {
                $user_data = mysql_fetch_assoc($req);
                $permit = $user_data['failed_login'] < 3 || $user_data['failed_login'] > 2 && $user_data['ip'] == self::$ip && $user_data['browser'] == self::$user_agent ? true : false;
                if ($permit && $user_ps === $user_data['password']) {
                    // Если авторизация прошла успешно
                    self::$user_id = $user_data['preg'] ? $user_id : false;
                    self::$user_rights = $user_data['rights'];
                    self::$user_data = $user_data;
                    self::$user_set = !empty($user_data['set_user']) ? unserialize($user_data['set_user']) : $this->user_setings_default();
                    $this->user_ip_history();
                    $this->user_ban_check();
                } else {
                    // Если авторизация не прошла
                    mysql_query("UPDATE `users` SET `failed_login` = '" . ($user_data['failed_login'] + 1) . "' WHERE `id` = '" . $user_data['id'] . "'");
                    $this->user_unset();
                }
            } else {
                // Если пользователь не существует
                $this->user_unset();
            }
        } else {
            // Для неавторизованных, загружаем настройки по-умолчанию
            self::$user_set = $this->user_setings_default();
        }
    }

    /*
    -----------------------------------------------------------------
    Проверка пользователя на Бан
    -----------------------------------------------------------------
    */
    private function user_ban_check()
    {
        $req = mysql_query("SELECT * FROM `cms_ban_users` WHERE `user_id` = '" . self::$user_id . "' AND `ban_time` > '" . time() . "'");
        if (mysql_num_rows($req)) {
            self::$user_rights = 0;
            while (($res = mysql_fetch_row($req)) !== false) self::$user_ban[$res[4]] = 1;
        }
    }

    /*
    -----------------------------------------------------------------
    Фиксация истории IP адресов пользователя
    -----------------------------------------------------------------
    */
    private function user_ip_history()
    {
        if (self::$user_data['ip'] != self::$ip || self::$user_data['ip_via_proxy'] != self::$ip_via_proxy) {
            // Удаляем из истории текущий адрес (если есть)
            @mysql_query("DELETE FROM `cms_users_iphistory`
                WHERE `user_id` = '" . self::$user_id . "'
                AND `ip` = '" . self::$ip . "'
                AND `ip_via_proxy` = '" . self::$ip_via_proxy . "'
                LIMIT 1
            ");
            if (!empty(self::$user_data['ip']) && self::ip_valid(long2ip(self::$user_data['ip']))) {
                // Вставляем в историю предыдущий адрес IP
                mysql_query("INSERT INTO `cms_users_iphistory` SET
                    `user_id` = '" . self::$user_id . "',
                    `ip` = '" . self::$user_data['ip'] . "',
                    `ip_via_proxy` = '" . self::$user_data['ip_via_proxy'] . "',
                    `time` = '" . self::$user_data['lastdate'] . "'
                ");
            }
            // Обновляем текущий адрес в таблице `users`
            mysql_query("UPDATE `users` SET
                `ip` = '" . self::$ip . "',
                `ip_via_proxy` = '" . self::$ip_via_proxy . "'
                WHERE `id` = '" . self::$user_id . "'
            ");
        }
    }

    /*
    -----------------------------------------------------------------
    Пользовательские настройки по умолчанию
    -----------------------------------------------------------------
    */
    private function user_setings_default()
    {
        return array(
            'avatar'     => 1, // Показывать аватары
            'direct_url' => 0, // Внешние ссылки
            'field_h'    => 3, // Высота текстового поля ввода
            'kmess'      => 10, // Число сообщений на страницу
            'smileys'    => 1 // Включить(1) выключить(0) смайлы
        );
    }
	private function user_key()
    {
		if(!self::$user_id) return;
		$key = self::$user_data['priv_key'];
		if (empty($key)) {
			$key = time() . '@' . functions::rand_code(5);
			mysql_query('UPDATE `users` SET `priv_key` = "' . $key . '" WHERE `id`="' . self::$user_id . '" LIMIT 1');
		} else {
			$txt = explode('@',$key);
			if (date('d',time()) != date('d', intval($txt[0])) && time() - self::$user_data['lastdate'] > 300) {
				$key = time() . '@' . functions::rand_code(5);
				mysql_query('UPDATE `users` SET `priv_key`= "' . $key . '" WHERE `id` = "' . self::$user_id . '" LIMIT 1');
			}
		}
	}

    /*
    -----------------------------------------------------------------
    Уничтожаем данные авторизации юзера
    -----------------------------------------------------------------
    */
    private function user_unset()
    {
        self::$user_id = 0;
        self::$user_rights = 0;
        self::$user_set = $this->user_setings_default();
        self::$user_data = array();
        unset($_SESSION['uid']);
        unset($_SESSION['ups']);
        setcookie('cuid', '');
        setcookie('cups', '');
    }

    /*
    -----------------------------------------------------------------
    Автоочистка системы
    -----------------------------------------------------------------
    */
    private function auto_clean()
    {
        if (self::$system_set['clean_time'] < time() - 86400) {
            mysql_query('DELETE FROM `cms_sessions` WHERE `lastdate` < "' . (time() - 86400) . '"');
            mysql_query('DELETE FROM `cms_users_iphistory` WHERE `time` < "' . (time() - 2592000) . '"');
            mysql_query('UPDATE `cms_settings` SET  `val` = "' . time() . '" WHERE `key` = "clean_time" LIMIT 1');
            mysql_query('OPTIMIZE TABLE `cms_sessions` , `cms_users_iphistory`, `cms_mail`, `cms_contact`');
        }
    }

    // detect device
    private function device_detect(){
		if (isset($_SESSION['device'])) {
            return $_SESSION['device'] == 'web' ? 'web' : ($_SESSION['device'] == 'touch' ? 'touch' : 'wap');
        }
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_strtolower($_SERVER['HTTP_USER_AGENT']) : '';
		$accept = isset($_SERVER['HTTP_ACCEPT']) ? mb_strtolower($_SERVER['HTTP_ACCEPT']) : '';
		if((strpos($accept,'text/vnd.wap.wml')!==FALSE)||(strpos($accept,'application/vnd.wap.xhtml+xml')!==FALSE)){
			return 'wap';
		}
		if(isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])){
			return 'wap';
		}
		if (strpos($agent,'opera mini')>0){
			return 'wap';
		}
		if(preg_match('/(android|iphone|windows\sphone|ipod|webos)/',$agent)){
			return 'touch';
		}
		$mobile_ua = mb_substr($agent, 0, 4);
		$mobile_agents = array('w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','xda ','xda-');
		if (in_array($mobile_ua,$mobile_agents)){
			return 'wap';
		}
		return 'web';
	}

    private function get_smileys_cache()
    {
        $file = ROOTPATH . 'files/system/cache/smileys.dat';
        if (file_exists($file) && ($smileys = file_get_contents($file)) !== FALSE) {
            self::$smileys_cache = unserialize($smileys);
        }
    }
    /*
    ---------------------------------------------------------------------------------
    Закрытие сайта / выгоняем всех онлайн юзеров и редиректим их на страницу ожидания
    ---------------------------------------------------------------------------------
    */

    private function site_access()
    {
        if (self::$system_set['site_access'] == 0 && (self::$user_id && self::$user_rights < 9))   // выгоняем всех, кроме SV!
        {
            self::user_unset();
            session_destroy();
            header('Location: ' . SITE_URL . '/closed.php');
            exit;
        }

        if (self::$system_set['site_access'] == 1 && (self::$user_id && self::$user_rights == 0))   // выгоняем всех, кроме администрации
        {
            self::user_unset();
            session_destroy();
            header('Location: ' . SITE_URL . '/closed.php');
            exit;
        }
    }

}