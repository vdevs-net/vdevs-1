<?php
defined('_MRKEN_CMS') or die('Error: restricted access');

// ADS 3
if (!empty($cms_ads[2])) {
    echo '<div class="gmenu">' . $cms_ads[2] . '</div>';
}

echo '</div>';
echo '<div id="footer"><div class="phdr"><a href="' . SITE_URL . '">' . $lng['homepage'] . '</a> </div><div class="menu center">' .
    '<div>Copyright &copy; 2015 - '.date('Y',time()).' ' . htmlspecialchars($set['copyright']) . '</div>';

// ADS 4
if (!empty($cms_ads[3])) {
    echo '<div>' . $cms_ads[3] . '</div>';
}
echo '</div></div><!--/ #footer --></div><!--/ #container -->';
if(!$wap){
	echo '<script type="text/javascript" src="' . SITE_URL . '/assets/js/jquery.js"></script>';
	echo '<script type="text/javascript" src="' . SITE_URL . '/assets/js/apps.js?t=20160405"></script>';
}
echo $script;
echo '</body></html>';