<?php

$language_data = array(
    'LANG_NAME' => 'Twig',
    'COMMENT_SINGLE' => array('{#' => '#}'),
    'COMMENT_MULTI' => array('{#' => '#}'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        //TWIG
        //Tags
        1 => array(
            'autoescape', 'endautoescape', 'block', 'endblock', 'do', 'embed', 'endembed',
            'extends', 'filter', 'endfilter', 'for', 'endfor', 'from', 'if', 'else', 'elseif', 'endif',
            'import', 'include', 'macro', 'endmacro', 'raw', 'endraw', 'sandbox', 'set', 'endset',
            'spaceless', 'endspaceless', 'use', 'verbatim', 'endverbatim',
            'trans', 'endtrans', 'transchoice', 'endtranschoice'
        ),
        //Filters
        2 => array(
            'abs', 'batch', 'capitalize', 'convert_encoding', 'date', 'date_modify', 'default',
            'escape', 'first', 'format', 'join', 'json_encode', 'keys', 'last', 'length', 'lower',
            'merge', 'nl2br', 'number_format', 'raw', 'replace', 'reverse', 'slice', 'sort', 'split',
            'striptags', 'title', 'trans', 'trim', 'upper', 'url_encode'
        ),
        //Functions
        3 => array(
            'attribute', 'block', 'constant', 'cycle', 'date', 'dump', 'include',
            'parent', 'random', 'range', 'source', 'template_from_string'
        ),
        //Tests
        4 => array(
            'constant', 'defined', 'divisibleby', 'empty', 'even', 'iterable', 'null',
            'odd', 'sameas'
        ),
        //Operators
        5 => array(
            'in', 'is', 'and', 'b-and', 'or', 'b-or', 'b-xor', 'not', 'into',
            'starts with', 'ends with', 'matches'
        ),
        6 => array(
            '{{', '}}', '{%', '%}'
        ),
    ),
    'SYMBOLS' => array(
        '+', '-', '/', '/', '*', '**', //Math operators
        '==', '!=', '<', '>', '>=', '<=', '===', //Logic operators
        '..', '|', '~', '[', ']', '.', '?', ':', '(', ')', //Other
        '=' //HTML (attributes)
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        //Twig
        1 => true,
        2 => true,
        3 => true,
        4 => true,
        5 => true,
        6 => false
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0600FF;', //Tags
            2 => 'color: #008000;', //Filters
            3 => 'color: #0600FF;', //Functions
            4 => 'color: #804040;', //Tests
            5 => 'color: #008000;', //Operators
            6 => 'color: #008000;'  // {{ and {%
        ),
        'COMMENTS' => array(
            'MULTI' => 'color: #008080; font-style: italic;'
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
        ),
        'BRACKETS' => array(
            0 => 'color: #D36900;'
        ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
        ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
        ),
        'METHODS' => array(
            1 => 'color: #006600;'
        ),
        'SYMBOLS' => array(
            0 => 'color: #D36900;'
        ),
        'SCRIPT' => array(
            0 => '',
            1 => 'color: #808080; font-style: italic;',
            2 => 'color: #009000;'
        ),
        'REGEXPS' => array(
            0 => 'color: #00aaff;',
            1 => 'color: #00aaff;'
        )
    ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
    ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        1 => '.',
        ),
    'REGEXPS' => array(
        0 => array(
            GESHI_SEARCH => "([[:space:]])([a-zA-Z_][a-zA-Z0-9_]*)",
            GESHI_REPLACE => '\\2',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '\\1',
            GESHI_AFTER => ''
            ),
        1 => array(
            GESHI_SEARCH => "\.([a-zA-Z_][a-zA-Z0-9_]*)",
            GESHI_REPLACE => '.\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            )
    ),
    'STRICT_MODE_APPLIES' => GESHI_ALWAYS,
    'SCRIPT_DELIMITERS' => array(
        0 => array(
            '{{' => '}}',
            '{%' => '%}'         
        ),
        1 => array(
            '{#' => '#}',          
        )
    ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        0 => true,
        1 => true,
        2 => true
    ),
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
        )
    )
);