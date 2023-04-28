<?php
defined('_MRKEN_CMS') or die('Restricted access');

class bbcode extends core
{
    // Processing of tags and links
    public static function tags($var)
    {
        $var = self::highlight_code($var);           // Highlighting code
        $var = self::highlight_bb($var);               // Processing references
        $var = self::highlight_url($var);            // Processing references
        $var = self::highlight_bbcode_url($var);       // Processing references in BBcode
        return $var;
    }

    /**
     * Парсинг ссылок
     * За основу взята доработанная функция от форума phpBB 3.x.x
     *
     * @param $text
     * @return mixed
     */
    public static function highlight_url($text)
    {
        if (!function_exists('url_callback')) {
            function url_callback($type, $whitespace, $url, $relative_url)
            {
                $orig_url = $url;
                $orig_relative = $relative_url;
                $url = htmlspecialchars_decode($url);
                $relative_url = htmlspecialchars_decode($relative_url);
                $text = '';
                $chars = array('<', '>', '"');
                $split = false;
                foreach ($chars as $char) {
                    $next_split = strpos($url, $char);
                    if ($next_split !== false) {
                        $split = ($split !== false) ? min($split, $next_split) : $next_split;
                    }
                }
                if ($split !== false) {
                    $url = substr($url, 0, $split);
                    $relative_url = '';
                } else {
                    if ($relative_url) {
                        $split = false;
                        foreach ($chars as $char) {
                            $next_split = strpos($relative_url, $char);
                            if ($next_split !== false) {
                                $split = ($split !== false) ? min($split, $next_split) : $next_split;
                            }
                        }
                        if ($split !== false) {
                            $relative_url = substr($relative_url, 0, $split);
                        }
                    }
                }
                $last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];
                switch ($last_char) {
                    case '.':
                    case '?':
                    case '!':
                    case ':':
                    case ',':
                        $append = $last_char;
                        if ($relative_url) {
                            $relative_url = substr($relative_url, 0, -1);
                        } else {
                            $url = substr($url, 0, -1);
                        }
                        break;

                    default:
                        $append = '';
                        break;
                }
                $short_url = (mb_strlen($url) > 40) ? mb_substr($url, 0, 30) . ' ... ' . mb_substr($url, -5) : $url;
                switch ($type) {
                    case 1:
                        $relative_url = preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
                        $url = $url . '/' . $relative_url;
                        $text = $relative_url;
                        if (!$relative_url) {
                            return $whitespace . $orig_url . '/' . $orig_relative;
                        }
                        break;

                    case 2:
                        $text = $short_url;
                        if (!isset(core::$user_set['direct_url']) || !core::$user_set['direct_url']) {
                            $url = SITE_URL . '/go.php?url=' . rawurlencode($url);
                        }
                        break;

                    case 4:
                        $text = $short_url;
                        $url = 'mailto:' . $url;
                        break;
                }
                $url = htmlspecialchars($url);
                $text = htmlspecialchars($text);
                $append = htmlspecialchars($append);

                return $whitespace . '<a href="' . $url . '" target="_blank">' . $text . '</a>' . $append;
            }
        }

        // Обработка внутренних ссылок
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])(' . preg_quote(SITE_URL, '#') . ')/((?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iu',
            function ($matches) {
                return url_callback(1, $matches[1], $matches[2], $matches[3]);
            },
            $text
        );

        // Обработка обычных ссылок типа xxxx://aaaaa.bbb.cccc. ...
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])([a-z][a-z\d+]*:/{2}(?:(?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\])(?::\d*)?(?:/(?:[a-z0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-z0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iu',
            function ($matches) {
                return url_callback(2, $matches[1], $matches[2], '');
            },
            $text
        );

        return $text;
    }

    /*
    -----------------------------------------------------------------
    Удаление bbCode из текста
    -----------------------------------------------------------------
    */
    static function notags($var = '')
    {
        $var = preg_replace('#\[color=(.+?)\](.+?)\[/color]#si', '$2', $var);
        $var = preg_replace('#\[code=(.+?)\](.+?)\[/code]#si', '$2', $var);
        $var = preg_replace('#\[spoiler=(.+?)](.+?)\[/spoiler]#si', '$2', $var);
        $var = preg_replace('#\[url=(.+?)](.+?)\[/url]#si', '$2', $var);
        $var = preg_replace('#\[quote=([^\]]+?)](.+?)\[/quote]#si', '$2', $var);
        $var = preg_replace('#\[img](.+?)\[/img]#i', '', $var);
        $replace = array(
            '[small]'  => '',
            '[/small]' => '',
            '[big]'    => '',
            '[/big]'   => '',
            '[green]'  => '',
            '[/green]' => '',
            '[red]'    => '',
            '[/red]'   => '',
            '[blue]'   => '',
            '[/blue]'  => '',
            '[b]'      => '',
            '[/b]'     => '',
            '[i]'      => '',
            '[/i]'     => '',
            '[u]'      => '',
            '[/u]'     => '',
            '[s]'      => '',
            '[/s]'     => '',
            '[quote]'  => '',
            '[/quote]' => '',
            '[*]'      => '',
            '[/*]'     => '',
            '[php]' => '',
            '[/php]' => ''
        );

        return strtr($var, $replace);
    }


    /*
    -----------------------------------------------------------------
    Подсветка кода
    -----------------------------------------------------------------
    */
    private static function highlight_code($var)
    {
        $var = preg_replace_callback('#\[php\](.+?)\[\/php\]#s', 'self::phpCodeCallback', $var);
        $var = preg_replace_callback('#\[code=(.+?)\](.+?)\[\/code]#is', 'self::codeCallback', $var);

        return $var;
    }

    private static $geshi;

    private static function phpCodeCallback($code)
    {
        return self::codeCallback(array(1 => 'php', 2 => $code[1]));
    }

    private static function codeCallback($code)
    {
        $parsers = array(
            'php'  => 'php',
            'css'  => 'css',
            'html' => 'html5',
            'js'   => 'javascript',
            'sql'  => 'sql',
            'xml'  => 'xml',
        );

        $parser = isset($code[1]) && isset($parsers[$code[1]]) ? $parsers[$code[1]] : 'php';

        if (null === self::$geshi) {
            require_once( ROOTPATH . 'incfiles/lib/geshi.php');
            self::$geshi = new \GeSHi;
            self::$geshi->set_link_styles(GESHI_LINK, 'text-decoration: none');
            self::$geshi->set_link_target('_blank');
            self::$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
            self::$geshi->set_line_style('background: rgba(255, 255, 255, 0.5)', 'background: rgba(255, 255, 255, 0.35)', false);
            self::$geshi->set_code_style('padding-left: 6px; white-space: pre-wrap');
        }

        self::$geshi->set_language($parser);
        $php = strtr($code[2], array('<br />' => ''));
        $php = html_entity_decode(trim($php), ENT_QUOTES, 'UTF-8');
        self::$geshi->set_source($php);

        return '<div class="bbCodeBlock bbCodePHP"><div class="type">' . mb_strtoupper($code[1]) . '</div><div class="code" style="overflow-x: auto">' . self::$geshi->parse_code() . '</div></div>';
    }

    /*
    -----------------------------------------------------------------
    Обработка URL в тэгах BBcode
    -----------------------------------------------------------------
    */
    private static function highlight_bbcode_url($var)
    {
        if (!function_exists('process_url')) {
            function process_url($url)
            {
                $home = parse_url(SITE_URL);
                $tmp = parse_url($url[1]);
                if ($home['host'] == $tmp['host'] || isset(core::$user_set['direct_url']) && core::$user_set['direct_url']) {
                    return '<a href="' . $url[1] . '">' . $url[2] . '</a>';
                } else {
                    return '<a href="' . SITE_URL . '/go.php?url=' . urlencode(htmlspecialchars_decode($url[1])) . '" target="_blank">' . $url[2] . '</a>';
                }
            }
        }

        return preg_replace_callback('~\\[url=(https?://.+?)\\](.+?)\\[/url\\]~', 'process_url', $var);
    }

    /*
    -----------------------------------------------------------------
    Обработка bbCode
    -----------------------------------------------------------------
    */
    private static function highlight_bb($var)
    {
        // search list
        $search = array(
            '#(\r\n|[\r\n])#',
            '#\[b](.+?)\[/b]#is', // Bold
            '#\[i](.+?)\[/i]#is', // Italic
            '#\[u](.+?)\[/u]#is', // Underline
            '#\[s](.+?)\[/s]#is', // Strikethrough
            '#\[small](.+?)\[/small]#is', // Small Font
            '#\[big](.+?)\[/big]#is', // Big font
            '#\[red](.+?)\[/red]#is', // red
            '#\[green](.+?)\[/green]#is', // green
            '#\[blue](.+?)\[/blue]#is', // blue
            '!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/color]!is', // font color
            '#\[quote](.+?)\[/quote]#is', // quote
            '#\[quote=([\d]+?),([\d]+?),([\da-z.@_]+?)](.+?)\[/quote]#is', // quote
            '#\[\*](.+?)\[/\*]#is', // list
            '#\[spoiler=(.+?)](.+?)\[/spoiler]#is', // spoiler
            '#\[img](https?://)([\da-z.-/]+)\.(png|jpg)\[/img]#is'
        );
        // List of replacement
        $replace = array(
            '',
            '<span style="font-weight: bold">$1</span>', // Жирный
            '<span style="font-style:italic">$1</span>', // Курсив
            '<span style="text-decoration:underline">$1</span>', // Подчеркнутый
            '<span style="text-decoration:line-through">$1</span>', // Зачеркнутый
            '<span style="font-size:x-small">$1</span>', // Маленький шрифт
            '<span style="font-size:large">$1</span>', // Большой шрифт
            '<span style="color:red">$1</span>', // Красный
            '<span style="color:green">$1</span>', // Зеленый
            '<span style="color:blue">$1</span>', // Синий
            '<span style="color:$1">$2</span>', // Цвет шрифта
            '<div class="quote"><blockquote>$1</blockquote></div>', // Цитата
            '<div class="bbCodeBlock bbCodeQuote"><div class="attribution type"><a href="' . SITE_URL . '/users/profile.php?id=$2">$3</a> đã viết <a href="' . SITE_URL . '/forum/index.php?act=post&id=$1">↑</a></div><blockquote>$4</blockquote></div>', // Цитата
            '<div class="bblist">$1</div>', // Список
            '<div><div class="spoilerhead" onclick="var _n=this.parentNode.getElementsByTagName(\'div\')[1];if(_n.style.display==\'none\'){_n.style.display=\'\';}else{_n.style.display=\'none\';}">$1 (+/-)</div><div class="spoilerbody" style="display:none">$2</div></div>',
            '<div style="text-align:center"><img src="$1$2.$3" alt="[*]" /></div>'
        );

        return preg_replace($search, $replace, $var);
    }

    /*
    -----------------------------------------------------------------
    Панель кнопок bbCode (для компьютеров)
    -----------------------------------------------------------------
    */
    public static function auto_bb($form, $field)
    {
        $colors = array(
            'ffffff', 'bcbcbc', '708090', '6c6c6c', '454545',
            'fcc9c9', 'fe8c8c', 'fe5e5e', 'fd5b36', 'f82e00',
            'ffe1c6', 'ffc998', 'fcad66', 'ff9331', 'ff810f',
            'd8ffe0', '92f9a7', '34ff5d', 'b2fb82', '89f641',
            'b7e9ec', '56e5ed', '21cad3', '03939b', '039b80',
            'cac8e9', '9690ea', '6a60ec', '4866e7', '173bd3',
            'f3cafb', 'e287f4', 'c238dd', 'a476af', 'b53dd2'
        );

        $font_color = '';
        foreach ($colors as $value) {
            $font_color .= '<a href="javascript:tag(\'[color=#' . $value . ']\', \'[/color]\'); show_hide(\'color\');" style="background-color:#' . $value . ';" tabindex="-1"></a>';
        }

        $smileys = !empty(self::$smileys_cache['default']) ? self::$smileys_cache['default'] : array();
        if (!empty($smileys)) {
            $res_sm = '';
            foreach ($smileys as $key => $value) {
                $key = preg_replace('/^:|:$/', '', $key);
                $res_sm .= '<a href="javascript:tag(\':' . $key . '\', \':\'); show_hide(\'sm\');" tabindex="-1">:' . $key . ':</a> ';
            }
            $bb_smileys = functions::smileys($res_sm);
        } else {
            $bb_smileys = '';
        }
        $code = array(
            'php',
            'css',
            'js',
            'html',
            'sql',
            'twig'
        );
        $codebtn = '';
        foreach ($code as $val) {
            $codebtn .= '<a href="javascript:tag(\'[code=' . $val . ']\', \'[/code]\'); show_hide(\'code\');" tabindex="-1">' . strtoupper($val) . '</a>';
        }
        $out = '<style>.codepopup {margin-top: 3px;}.codepopup a {border: 1px solid #a7a7a7;border-radius: 3px;background-color: #dddddd;color: black;font-weight: bold;padding: 2px 6px 2px 6px;display: inline-block;margin-right: 6px;margin-bottom: 3px;text-decoration: none;}.color a {float:left; display: block; width: 10px; height: 10px; margin: 1px; border: 1px solid black;}</style>'.
            '<script type="text/javascript">'.
            'function tag(text1,text2){if((document.selection)){document.' . $form . '.' . $field . '.focus();document.' . $form . '.document.selection.createRange().text = text1+document.' . $form . '.document.selection.createRange().text+text2}else if(document.forms[\'' . $form . '\'].elements[\'' . $field . '\'].selectionStart!=undefined){var element=document.forms[\'' . $form . '\'].elements[\'' . $field . '\'];var str=element.value;var start=element.selectionStart;var length=element.selectionEnd-element.selectionStart;element.value=str.substr(0,start)+text1+str.substr(start,length)+text2+str.substr(start+length)}else{document.' . $form . '.' . $field . '.value+=text1+text2}}'.
            'function show_hide(a){b=document.getElementById(a);if(b.style.display=="none"){b.style.display="block"}else{b.style.display="none"}}'.
            '</script>'.
            '<div class="toolbar"><a href="javascript:tag(\'[b]\', \'[/b]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/bold.gif" alt="b" title="' . self::$lng['tag_bold'] . '" border="0"/></a> '.
            '<a href="javascript:tag(\'[i]\', \'[/i]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/italics.gif" alt="i" title="' . self::$lng['tag_italic'] . '" border="0"/></a> '.
            '<a href="javascript:tag(\'[u]\', \'[/u]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/underline.gif" alt="u" title="' . self::$lng['tag_underline'] . '" border="0"/></a> '.
            '<a href="javascript:tag(\'[s]\', \'[/s]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/strike.gif" alt="s" title="' . self::$lng['tag_strike'] . '" border="0"/></a> '.
            '<a href="javascript:tag(\'[*]\', \'[/*]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/list.gif" alt="s" title="' . self::$lng['tag_list'] . '" border="0"/></a> '.
            '<a href="javascript:tag(\'[spoiler=]\', \'[/spoiler]\');" tabindex="-1"><img src="' . SITE_URL . '/images/bb/sp.gif" alt="spoiler" title="Spoiler" border="0"/></a> '.
            '<a href="javascript:tag(\'[quote]\', \'[/quote]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/quote.gif" alt="quote" title="' . self::$lng['tag_quote'] . '" border="0"/></a> '.
            '<a href="javascript:tag(\'[url=]\', \'[/url]\')" tabindex="-1"><img src="' . SITE_URL . '/images/bb/link.gif" alt="url" title="' . self::$lng['tag_link'] . '" border="0"/></a> '.
            '<a href="javascript:show_hide(\'code\');" tabindex="-1"><img src="' . SITE_URL . '/images/bb/code.gif" title="Code" border="0"/></a>' .
            '<a href="javascript:show_hide(\'color\');" tabindex="-1"><img src="' . SITE_URL . '/images/bb/color.gif" title="' . self::$lng['color_text'] . '" border="0"/></a> ';

        if (self::$user_id) {
            $out .= '<a href="javascript:show_hide(\'sm\');" tabindex="-1"><img src="' . SITE_URL . '/images/bb/smileys.gif" alt="sm" title="' . self::$lng['smileys'] . '" border="0"/></a></div>'.
                '<div id="sm" style="display:none">' . $bb_smileys . '</div>';
        } else $out .= '</div>';

        $out .= '<div id="code" class="codepopup" style="display:none;">' . $codebtn . '</div>' .
            '<div id="color" class="bbpopup" style="display:none;">Màu chữ: ' . $font_color . '</div>';

        return $out;
    }
}