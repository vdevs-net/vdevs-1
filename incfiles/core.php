<?php
defined('_MRKEN_CMS') or die('Error: restricted access');
//Error_Reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);
ini_set('session.use_trans_sid', '0');
ini_set('arg_separator.output', '&amp;');
date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');
// Root dir
define('ROOTPATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

// Autoload class
spl_autoload_register('autoload');
function autoload($name){
    $file = ROOTPATH . 'incfiles/classes/' . $name . '.php';
    if (file_exists($file)) require_once($file);
}

// Start system core
new core;

// System variable
$root = ROOTPATH;
$ip = core::$ip; // IP
$agn = core::$user_agent; // User Agent
$set = core::$system_set; // system settings
$lng = core::$lng; // language
$device = core::$device; // device
$wap = $web = $touch = false;
// device
if($device == 'web') $web = true;
elseif($device == 'touch') $touch = true;
else $wap = true;

$user_rights = array(
	0 => 'Thành viên',
	3 => 'F-Mod',
	5 => 'L-Mod',
	6 => 'Super Mod',
	7 => 'Admin',
	9 => 'Trùm!'
);
// Custom variable
$user_id = core::$user_id; // User ID
$rights = core::$user_rights; // User Rights
$datauser = core::$user_data; // all data of user
$set_user = core::$user_set; // user settings
$ban = core::$user_ban; // Ban
$login = isset($datauser['account']) ? $datauser['account'] : false;
$kmess = $set_user['kmess'] > 4 && $set_user['kmess'] < 100 ? $set_user['kmess'] : 10;

function validate_referer()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    if (@!empty($_SERVER['HTTP_REFERER'])) {
        $ref = parse_url(@$_SERVER['HTTP_REFERER']);
        if ($_SERVER['HTTP_HOST'] === $ref['host']) return;
    }
    die('Invalid request');
}

if ($rights) {
    validate_referer();
}
$prefixs = array(
	0 => 'Không tiền tố',
	1 => 'Hỏi',
	2 => 'Thảo luận',
	3 => 'Yêu cầu',
	4 => 'Vote',
	5 => 'Share',
	6 => 'Wapego',
	7 => 'JohnCMS',
	8 => 'Thông báo'
);
$script = '';
// Request variable
$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : false;
$user = isset($_REQUEST['user']) ? abs(intval($_REQUEST['user'])) : false;
$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
$mod = isset($_REQUEST['mod']) ? trim($_REQUEST['mod']) : '';
$do = isset($_REQUEST['do']) ? trim($_REQUEST['do']) : false;
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);
$headmod = isset($headmod) ? $headmod : '';

// Redirect of site is closed
if (($set['site_access'] == 0 || $set['site_access'] == 1) && $headmod != 'login' && !$user_id) {
    header('Location: ' . SITE_URL . '/closed.php'); exit;
}

// output buffering
if ($set['gzip'] && @extension_loaded('zlib')) {
    ini_set('zlib.output_compression_level', 3);
    @ob_start('ob_gzhandler');
} else {
    @ob_start();
}