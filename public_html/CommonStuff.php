<?php
// THIS FILE IS DEPRECATED !
// Use /krinkle/common/BaseTool.php from now on (GlobalFunctions, GlobalConfig class, BaseTool class)
// DO NOT FIX, CHANGE OR REMOVE ANYTHING HERE !


/**
 * Definitions
 * -------------------------------------------------
 */
// MW Definitions
define( 'NS_TEMPLATE', 10 );

// krFunctions
define( 'KR_FLUSHLOG' , true );
define( 'KR_LEAVELOG' , false );
define( 'KR_ESCAPEHTML', true );
define( 'KR_NOESCAPEHTML', false );
define( 'KR_LEAVEHTML', true );
define( 'KR_ECHO', true );
define( 'KR_RETURN', false );


/**
 * Functions - Krinkle
 * -------------------------------------------------
 */
function krDebug(){
        global $c;
        return !!$c['debug'];
}

function krShowSource($file = null, $do = KR_ECHO ){
        if ( is_null($file) ) {
                $file = $_SERVER['SCRIPT_FILENAME'];
        }
        // show_source === highlight_file
        $ret = '<hr/>Source:<br />' . highlight_file( $file, /* return = */ true );
        if ( $do === KR_RETURN ) {
                return $ret;
        }
        echo $ret;
}

function krLog($msg, $echo = false){
        global $c;
        if ( $echo ) {
                echo $msg;
        } elseif ( $c['commandline'] ) {
                echo '[krLog] ' . $msg . "\n";
        } else {
                $c['krlog'] .= $msg . "\n";
        }
}
// Spits out the logged notes saved in the memory
// By default flushes the memory and escapes any special characters
// before/after wrap is never escaped and left as-is.
function krLogFlush($flush_line = KR_FLUSHLOG, $html_escape = KR_ESCAPEHTML, $before = '', $after = ''){
        global $c;
                $c['krflushes']++;
                if( $flush_line ){
                        $c['krlog'] .= "\n--------- [ krLog flush ".$c['krflushes']." @ ".date("Y-m-d H:i:s")." ] ----------\n";
                }
                if( krDebug() ){
                        if ( $html_escape !== KR_NOESCAPEHTML ) {
                                echo $before . krEscapeHTML( $c['krlog'] ) . $after;
                        } else {
                                echo $before . $c['krlog'] . $after;
                        }
                }
                $c['krlog'] = '';
}

function krEscapeHTML($str){
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

function krStripStr($str){
        return krEscapeHTML(addslashes(strip_tags(trim($str))));
}

function krStrLastReplace($search, $replace, $subject){
        return substr_replace($subject, $replace, strrpos($subject, $search), strlen($search));
}

function krDumpWTF( $dumpVar, $return = false, $before = '', $after = '', $html_escape = true ) {
        $stuff = print_r( $dumpVar, true );
        return htmlentities( print_r( $dumpVar, true ), ENT_QUOTES, 'UTF-8' ); // FIXME: with UTF-8 TSUser debug array becomes NULL. Without eveyrthing is fine. Possibly bug in PHP itself.
        /*
        if ( $html_escape === true ) {
                $stuff = krEscapeHTML( $stuff );
        }
if ( $return === false ) {
                echo $before . $stuff . $after;
        } else {
                return $before . $stuff . $after;
        }
*/
}

function krDump($var, $return = KR_RETURN, $before = '', $after = '', $html_escape = KR_ESCAPEHTML ){
        $print = print_r( $var, true );
        if ( $html_escape != KR_NOESCAPEHTML ) {
                $return = krEscapeHTML( $return ); //FIXME should set $print ofcourse.. however this breaks.. see krDumpWTF
        }
        if ( $return == KR_ECHO ) {
                echo $before . $print . $after;
        } else {
                return $before . $print . $after;
        }
}

// Message functions
function krQuit(){
        global $c;
        if ( $c['may_die'] ) {
                die(
                        krCheckEnvironment(
                                '<!-- krQuit --><br /></div></body></html>',
                                'dying',
                                __FUNCTION__
                        )
                );
        } else {
                return false;
        }
}
function krCheckEnvironment($html = '(html undefined)', $msg = '(msg undefined)', $type = ''){
        global $c;
        if ( $c['commandline'] ) {
                return '[' . $type . '] ' . $msg . "\n";
        }
        return $html;
}
function krDie($msg, $img = false){
        $img = $img ? $img : '//upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Dialog-warning.svg/45px-Dialog-warning.svg.png';
        echo krCheckEnvironment(
                '<div class="msg ns error"><p><img src="'.$img.'" width="45" alt="" title="Error" />'.$msg.'</p><span clear></span></div>',
                $msg,
                __FUNCTION__
        );
        krQuit();
}
function krError($msg, $img = false, $extraclasses = '', $class = 'msg ns error'){
        $img = $img ? $img : '//upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Dialog-warning.svg/45px-Dialog-warning.svg.png';
        echo krCheckEnvironment(
                '<div class="'.$class.' '.$extraclasses.'"><p><img src="'.$img.'" width="45" alt="" title="Error" />'.$msg.'</p><span clear></span></div>',
                $msg,
                __FUNCTION__
        );
}
function krErrorLine($msg, $img, $extraclasses = '', $class = 'msgline ns error'){
        $img = $img ? $img : '//upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Dialog-warning.svg/24px-Dialog-warning.svg.png';
        echo krCheckEnvironment(
                '<p class="'.$class.' '.$extraclasses.'"><img src="'.$img.'" width="24" alt="" title="Error" />&nbsp;<small>'.$msg.'</small><span clear></span></p>',
                $msg,
                __FUNCTION__
        );
}
function krWarn($msg, $img = false, $extraclasses = '', $class = 'msg ns'){
        $img = $img ? $img : '//upload.wikimedia.org/wikipedia/commons/thumb/5/53/Crystal_Clear_app_error.png/45px-Crystal_Clear_app_error.png';
        echo krCheckEnvironment(
                '<div class="'.$class.' '.$extraclasses.'"><p><img src="'.$img.'" width="45" alt="" title="Warning" />'.$msg.'</p><span clear></span></div>',
                $msg,
                __FUNCTION__
        );
}
function krSuccess($msg, $extraclasses = '', $class = 'msg ns success'){
        echo krCheckEnvironment(
                '<div class="'.$class.' '.$extraclasses.'"><p>'.$msg.'</p></div>',
                $msg,
                __FUNCTION__
        );
}
function krMsg($msg, $extraclasses = '', $class = 'msg ns'){
        echo krCheckEnvironment(
                '<div class="'.$class.' '.$extraclasses.'"><p>'.$msg.'</p></div>',
                $msg,
                __FUNCTION__
        );
}
function krMsgLine($msg, $extraclasses = '', $class = 'msgline ns'){
        echo krCheckEnvironment(
                '<p class="'.$class.' '.$extraclasses.'"><small>'.$msg.'</small></p>',
                $msg,
                __FUNCTION__
        );
}
function krClosedMsg($configuration, $more){
        echo krMsg('Tool "<tt><code>' . $configuration['title'] . '</code></tt>" has been closed. ' . $more );
        krQuit();
}


function krCreateLink($url, $text, $options, $data){
        $is_new = '';
        if ( is_array( $url ) ) {
                $url = join( '', $url );
        }
        if ( is_array( $text ) ) {
                $text = join( '', $text );
        }
        if ( !empty($options['redlink_nullcheck'] ) ) {
                if ( is_null( $data[$options['redlink_nullcheck']] ) ) {
                        return '<a target="_blank" href="' . $url . '" class="new">' . $text . '</a>';
                } else {
                        return '<del><a target="_blank" href="' . $url . '">' . $text . '</a></del>';
                }
        }
        if ( !empty($options['is_new'] ) ) {
                return '<a target="_blank" href="' . $url . '" class="new">' . $text . '</a>';
        }
        return '<a target="_blank" href="' . $url . '">' . $text . '</a>';
}

function krReturnMath($math){
        $out = false;
        if ( is_array( $math ) ) {
                $math = join( '', $math );
        }
        eval( '$out = ' . $math . ';' );
        return $out;
}

function krDatabaseTagReplace(&$input_val, $input_key, $stuff){
        $var = $stuff[0];
        $row = $stuff[1];
        $options = $stuff[2];
        // if option exists with same name, dispay that
        if ( array_key_exists( $input_val, $options ) ) {
                $input_val = $options[$input_val];
        
        // If variable exists with same name, display that
        } elseif ( array_key_exists( $input_val, $var ) ) {
                $input_val = $var[$input_val];
        
        // If query-cel exists with same name, display that
        } elseif ( array_key_exists( $input_val, $row ) ) {
                $input_val = $row[$input_val];
        
        } else {
                $input_val = $input_val;
        }

}


/**
 * Prints out a chronlist
 * @param       $datarows       array   Database rows
 * @param       $options        array   Options
 * @param       $var            array   Static variables to use within messages
 * options:
 * - id                 string  id of the chronlist
 * - title_col  string  name of the db column the title is in
 * - ns_col             int/str name of the db column the namespace number is in
 */
function krBuildChronlist( $datarows, $options, $var = array() ) {
        global $c;
        krLoadChronlistCSS();
        // Start list:
        echo "\n".'<div ' . ( !empty( $options['id'] ) ? ' id="' . $options['id'] . '" ' : '' ) . ' class="kr-chronlist ns">';
        $hit = 0;
        foreach($datarows as $row) {
                $hit++;
                // Handle titles
                if ( is_string($options['title_col']) && is_string($options['ns_col']) ) {
                        $var['title'] = $row[$options['title_col']];
                        $var['ns'] = $c['namespaces'][$row[$options['ns_col']]];
                        $var['title'] = str_replace( '_', ' ', $var['title'] );
                        $var['pagename'] = $var['ns'] . $var['title'];
                        $var['pagename_url'] = rawurlencode( $var['ns'] . $row[$options['title_col']] );
                        $var['hit'] = $hit;
                        $var['i'] = $hit;
                        $var['redlink_nullcheck'] = $options['redlink_nullcheck'];
                        $var['is_new'] = $options['is_new'];
                        // back compat.
                        $var['hit_total'] = $hit+$options['offset'];
                }

                // Handle columns
                if ( !is_array($options['table_cols']) ) {
                        $options['table_cols'] = array($options['table_cols']);
                }

                echo "\n".'<div class="item">'; // Start item

                // Iterate over columns
                foreach ( $options['table_cols'] as $table_col => $table_val ) {
                        // Start cell
                        echo '<div ' . $table_col . '>';

                        if ( !is_array($table_val) ) {
                                $table_val = array($table_val);
                        }
                        array_walk_recursive($table_val, 'krDatabaseTagReplace', array($var, $row, $options));

                        foreach ( $table_val as $text_part ) {

                                // If it's a link, create a link
                                if( is_array( $text_part ) && $text_part[0] == 'link' ) {
                                        echo krCreateLink( $text_part[1], $text_part[2], $var, $row);

                                // If it's math, calculate it
                                } else if( is_array( $text_part ) && $text_part[0] == 'math' ) {
                                        echo krReturnMath( $text_part[1] );

                                // Else display raw code
                                } else {
                                        echo $text_part;
                                }

                        }

                        echo '</div>'; // End cell
                }

                echo '</div>'; // End item


        }
        if ( empty( $datarows ) ) {
                echo '<div class="item"><em>';
                echo !empty($options['empty_msg']) ? $options['empty_msg'] : 'No results.';
                echo '</em></div>';
        }
        echo "\n".'</div>'; // End list
}

/**
 * Function to read n-number of lines from the top of the file
 * If the file has less lines it simply returns the lines it does have.
 *
 * Note: This resets the position of the file pointer
 *
 * @param Resource $handle A valid file handler gotten from fopen()
 * @param Integer $lines (optional)
 * @return Mixed Boolean false or a string containing the lines
 */
function krReadLinesFromFile( $handle = false, $lines = 1 ) {
        if ( get_resource_type( $handle) == 'file' ) {
                krError( 'read_nlines_from_file() received an invalid filehandler ' );
                return false;
        } else {
                krLog( '$filehandler is a valid resource!' );
        }
        rewind( $handle );
        $i = 0;
        $str = '';
        while ( !feof( $handle ) ) {
                $i++;
                $str .= fgets( $handle, 4096 );
                
                if ( $i == $lines || $i > $lines ) {
                        break;
                }
        }
        return $str;
}

/**
 * Function to extract the path from the url
 *   and the file extension from the path.
 * Especially handy with urls like "/~something/some.script.min.js?param=this#here" to get 'js'
 * @param String $url The address (may start with "prot://dom.tld". Not required)
 * @return String of file extension like 'jpg', 'php' or empty string
 */
function krFileExtensionFromUrl( $url = false ) {
        return pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION );
}

// Based on GlobalFunctions::wfUrlencode() (1.18alpha)
// urlencode->rawurlencode
// added '%20' -> '_'
function krWikiUrlencode( $str ) {
        $needle =       array( '%3B', '%40', '%24', '%21', '%2A', '%28', '%29', '%2C', '%2F', '%3A', '%20' );

        $str = rawurlencode( $str );
        $str = str_ireplace(
                $needle,
                                array( ';', '@', '$', '!', '*', '(', ')', ',', '/', ':', '_' ),
                $str
        );

        return $str;
}

function krGetNSId( $str ) {
        global $c;
        return array_search( str_replace( ' ', '_', ucfirst( $str ) ) . ':', $c['namespaces'] );
}

function krGetAllWikiSelect( $formName, $current, $exclude = array() ) {
        // Get wikis
        $dbLinkSQL = mysql_connect( 'sql.toolserver.org', dbUsername(), dbPassword() );
        mysql_select_db( 'toolserver', $dbLinkSQL );
        $dbReturn = mysql_query( "SELECT * FROM wiki WHERE is_closed = 0", $dbLinkSQL );
        $dbResults = mysql_object_all( $dbReturn );
        if ( !$dbResults ) {
                krDie( 'Wiki information acquirement failed.' );
        }
        if ( $dbLinkSQL ){
                mysql_close( $dbLinkSQL );
        }
        // Spit it out
        $html =  '<select id="' . $formName . '" name="' . $formName . '"><option value="">(select wiki)</option>';
        $outputA = '';
        $outputB = '';
        $selectAttr = ' selected="selected"';
        foreach( $dbResults as $wiki ) {
                if ( !in_array( $wiki->dbname, $exclude ) ) {
                        if ( in_array( $wiki->dbname, array( 'enwiki_p', 'commonswiki_p', 'nlwiki_p', 'dewiki_p', 'eswiki_p' ) ) ) {
                                $outputA .= 
                                        '<option value="' . $wiki->dbname . '" ' . ( $current == $wiki->dbname ? $selectAttr : '') . ' >'
                                        . $wiki->dbname
                                        . '</option>';
                        } else {
                                $outputB .= 
                                        '<option value="' . $wiki->dbname . '" ' . ( $current == $wiki->dbname ? $selectAttr : '') . ' >'
                                        . $wiki->dbname
                                        . '</option>';
                        }
                }
        }
        $html .= '<optgroup label="Most used wikis">' . $outputA . '</optgroup><optgroup label="All wikis alphabetically">' . $outputB . '</optgroup>';
        $html .= '</select>';
        return $html;
}


/**
 * Functions - Krinkle Load
 * -------------------------------------------------
 */
// Loads jQuery if not already loaded
// @return true (loaded it)
// @return false (not loaded, was already loaded)
function krGetjQueryURL(){
        return '//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js';
}
function krLoadjQuery(){
        global $c;
        if ( $c['jquery_loaded'] === false ) {
                $c['jquery_loaded'] = true;
                echo '<script src="' . krGetjQueryURL() . '"></script>';
                return true;
        }
        return false;
}

// Loads jQuery UI and jQuery if not already loaded
// @return true (loaded it)
// @return false (not loaded, was already loaded)
function krLoadjQueryUI(){
        global $c;
        krLoadjQuery();
        if ( $c['jqueryui_loaded'] === false ) {
                $c['jqueryui_loaded'] = true;
                ?><link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/themes/smoothness/jquery-ui.css" type="text/css" media="all" /><script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js"></script><script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/i18n/jquery-ui-i18n.min.js"></script><?php
                return true;
        }
        return false;
}


// Loads CSS for a krChronlist if not already loaded
// @return true (loaded it)
// @return false (not loaded, was already loaded)
function krLoadChronlistCSS(){
        global $c;
        if ( $c['krchronlist_css_loaded'] === false ) {
                $c['krchronlist_css_loaded'] = true;
                ?><style>
/*
        CHRONLIST OUTPUT
*/
.kr-chronlist { margin:0; width:100% }
.kr-chronlist .item { padding:0px 5px; white-space:nowrap }
.kr-chronlist .item:nth-child(odd) { background:#f3f3f3 }
/* Colored watchlist and recent changes numbers */
.mw-plusminus-pos { color: #006400 } /* dark green */
.mw-plusminus-neg { color: #8b0000; } /* dark red */
.mw-plusminus-null { color: #aaa; } /* gray */
.kr-chronlist .item div[size] *[title] { cursor:pointer }
</style><?php
                return true;
        }
        return false;
}

// Loads extra css that needs to wait untill the page is ready (ie. to avoid a flash of "enable javascript"-ish things
function krLoadBottomCSS(){
        global $c;
        ?><style>
        <?php require_once( $c['tshomepath'] . '/public_html/main-bottomload.css'); ?>
        </style><?php

}

// Function for API modules
function krApiExport( $data = array( 'krApiExport' => 'Example' ), $format = '', $callback = '' ) {
        
        if ( empty( $format ) ) {
                $format = 'php_print';
        }

        if ( $format == 'php' ) {
                
                header( 'Content-Type: text/plain; charset=utf-8' );
                die( serialize( $data ) );

        } elseif ( $format == 'json' ) {
        
                header(' Content-Type: text/javascript; charset=utf-8' );
                die( $callback . '(' .  json_encode( $data ) .');' );

        } elseif ( $format == 'php_dump' ) {
                
                header( 'Content-Type: text/html; charset=utf-8' );
                echo '<pre>'; var_dump( $data ); echo '</pre>';
                die;
                
        } elseif ( $format == 'php_print' ) {
                
                header( 'Content-Type: text/html; charset=utf-8' );
                die( krDump( $data, false, '<pre>', '</pre>' ) );
                
        } elseif( !empty( $format ) ) {
                
                header( 'Content-Type: text/plain; charset=utf-8' );
                die( 'Invalid format.' );
        
        } else {
                
                header( 'Content-Type: text/plain; charset=utf-8' );
                die( 'Export error.' );
        }


}


/**
 * Functions - MySQL
 * -------------------------------------------------
 */
function mysql_fetch_all( $result ) {
        $all = array();
        while ( $all[] = mysql_fetch_assoc( $result ) ) { /* */ }
        unset( $all[count( $all ) - 1] ); // while() goes on 1 too many, remove it
        return $all;
}

function mysql_object_all( $result ) {
        $all = array();
        while ( $all[] = mysql_fetch_object( $result ) ) { /* */ }
        unset( $all[count( $all ) - 1] ); // while() goes on 1 too many, remove it
        return $all;
}

function mysql_add_quotes($str) {
        // Based on Database.php :: addQuotes()
        if ( $str === null ) {
                return 'NULL';
        } else {
                # This will also quote numeric values. This should be harmless.
                return "'" . addslashes( $str ) . "'";
        }
}

function mysql_make_list($a) {
        // Based on Database.php :: makeList(, LIST_COMMA)
        if ( !is_array( $a ) ) {
                krDie('mysql_make_list called with incorrect parameters' );
        }

        $first = true;
        $list = '';

        foreach ( $a as $field => $value ) {
                if ( $first ) {
                        $first = false;
                } else {
                        $list .= ',';
                }

                if ( $value === null ) {
                        $list .= 'NULL';
                } else {
                        $list .= mysql_add_quotes( $value );
                }
        }

        return $list;
}

//Function to sanitize values received from the form. Prevents SQL injection
function sql_clean($str){
        $str = @trim($str);
        if(get_magic_quotes_gpc()) {
                $str = stripslashes($str);
        }
        return mysql_real_escape_string($str);
}


/**
 * Functions - Other
 * -------------------------------------------------
 */



// Variable fallback
function CacheAndDefault($variable = false, $default = false, $cache = false){
        if ( !empty($variable) ) {
                return $variable;
        } elseif ( !empty($cache) ) {
                return $cache;
        } else {
                return $default;
        }
}

function generatePermalink( $setings = array(), $url = false ) {
        global $c;
        $link = $url ? $url : $c['baseurl'];
        $one = true;
        foreach( $setings as $key => $val ) {
                if ( $one && $val !== '' && $val !== false && $val !== 0 ) {
                        $link .= '?' . rawurlencode($key) . '=' . rawurlencode($val);
                        $one = false;
                } elseif( $val !== '' && $val !== false && $val !== 0 ) {
                        $link .= '&' . rawurlencode($key) . '=' . rawurlencode($val);
                }
        }
        unset( $one );
        // Return the link only if there were any settings, else return false
        // Except when a custom url has been passed
        return $link == $c['baseurl'] && !$url ? false : $link;
}

/**
 * Extract parameter values from wikitext
 * VERY BASIC!
 *
 * - Case insensitive
 * - But space sensitive, paremeters need to be on seperate lines, whitespace is ignored
 * ie. {{Template
                |paramA = value
                |paramB=muliple
                lines value
                |paramC
                }}
 */
function get_template_parameters( $wikitext, $wanted_parameters ) {

        // Seperate all lines
        $raw_lines = explode( "\n", $wikitext );

        // Processed lines
        $grouped_lines = array();

        // Processed line counter
        $grouped_lines_count = 0;

        // Array that will contain the extracted values
        $param_values = array();


        // Merge and split lines into groups per parameter
        foreach ( $raw_lines as $line_nr => $raw_line ) {

                if ( strpos( $raw_line, '|' ) === 0 || strpos( $raw_line, '}}' ) === 0
                        || strpos( $raw_line, ' |' ) === 0 || strpos( $raw_line, ' }}' ) === 0 ) {
                        // This line begins with a pipe or is end of template, so it's a new thing
                        $grouped_lines_count++;
                        $grouped_lines[$grouped_lines_count] = $raw_line;
                } else {
                        // This is not a new parameter, add to the previous line
                        $grouped_lines[$grouped_lines_count] .= "\n" . $raw_line;

                }

        }

        // Search for the parameters we want
        foreach ( $grouped_lines as $line_nr => $grouped_line ) {

                foreach ( $wanted_parameters as $parameter ) {

                        $count = 0;
                        // See if this line contains this particular wanted parameter,
                        // Get rid of the |PARAM= part and save the rest into $values under $parameter's name
                        $pat = '/^\s*' . preg_quote( '|' ) . '\s*' . preg_quote( $parameter ) . '\s*=\s*(.*)/i';
                        $result = preg_replace( $pat, '$1', $grouped_line, -1, $count );

                        // If count is higher then 0, there was a replace and we're good.
                        if ( $result !== $grouped_line ) {
                                $param_values[$parameter] = $result;
                        }

                }

        }
        krLog($pat);

        return $param_values;

}


// Function to return commons thumb url
function commons_thumb_url( $f, $w ) {
        // upload.wikimedia.org/wikipedia/commons/thumb/7/75/Berthe_Morisot_005.jpg/100px-Berthe_Morisot_005.jpg
        $md5 = md5( $f );
        return '//upload.wikimedia.org/wikipedia/commons/thumb/'
                         . $md5[0] . '/' . $md5[0] . $md5[1] . '/' . rawurlencode( $f ) . '/'
                         . (int)$w . 'px-' . rawurlencode( $f );
}

// Returns namespace name from number, falls back to "(number)"
// Only works for default MediaWiki namespaces
function namespacename( $str ) {
        global $c;
        $str = (int)$str;
        if ( isset( $c['namespaces'][$str] ) ) {
                return $c['namespaces'][$str];
        } else {
                return '(' . $str . ')';
        }
}

// Returns 1 or 0
function getParamBool( $key = 1, $map = NULL ) {
        if ( is_null( $map ) ) { $map = $_GET; }

        if ( array_key_exists( $key, $map ) ) {
                if ( $map[$key] == '1' ) {
                        return 1;
                } else {
                        return 0;
                }
        } else {
                return 0;
        }
}

// Returns 'on' or false
function getParamCheck( $key, $map = NULL ) {
        if ( is_null( $map ) ) { $map = $_GET; }

        if ( array_key_exists( $key, $map ) ) {
                if ( $map[$key] == 'on' ) {
                        return 'on';
                } else {
                        return false;
                }
        } else {
                return false;
        }
}

// Returns intval of parameter value, 0 if nothing
function getParamInt( $key, $map = NULL ) {
        if ( is_null( $map ) ) { $map = $_GET; }

        if ( array_key_exists( $key, $map ) ) {
                if ( !empty($map[$key]) ) {
                        return intval($map[$key]);
                } else {
                        return 0;
                }
        } else {
                return 0;
        }
}

// Returns strval of parameter value, '' if nothing
function getParamVar( $key, $map = NULL ) {
        if ( is_null( $map ) ) { $map = $_GET; }

        if ( array_key_exists( $key, $map ) ) {
                if ( strlen($map[$key]) ) {
                        return strval($map[$key]);
                } else {
                        return '';
                }
        } else {
                return '';
        }
}

function postParamBool( $key ) {
        return getParamBool( $key, $_POST );
}

function postParamCheck( $key ) {
        return getParamCheck( $key, $_POST );
}

function postParamInt( $key ) {
        return getParamInt( $key, $_POST );
}

function postParamVar( $key ) {
        return getParamVar( $key, $_POST );
}

// Makes a path safe to go no higher than itself
// Will strip out '.', '..' and '/' paths
// No leading or trailing slash allowed either
function path_is_limit_to_self( $path, $boolean = false ) {
        // Make sure it's not empty
        if ( !empty( $path ) && strlen( $path ) > 4 ) {

                // Disallow '.', './', '/' etc. in leading two characters
                if ( in_array( $path[0], array( '.', '/' ) ) ) {
                        return $boolean ? false : 'char0 is dot or slash';
                }
                if ( in_array( $path[1], array( '.', '/' ) ) ) {
                        return $boolean ? false : 'char1 is dot or slash';
                }

                // Disallow trailing slash or dot
                if ( in_array( substr($path, -1), array( '.', '/' ) ) ) {
                        return $boolean ? false : 'last char is dot or slash';
                }


                // See if there's any ../ further in the path
                // in /home/demo/folder/this going to '/that/../../' WILL get you up higher then you started
                // Thus check for that too!
                $parts = explode( '/', strval( $path ) );
                if ( in_array( '.', $parts ) ) {
                        return $boolean ? false : 'dot in an exploded part';
                }
                if ( in_array( '..', $parts ) ) {
                        return $boolean ? false : 'dotdot in an exploded part';
                }
                if ( in_array( '/', $parts ) ) {
                        return $boolean ? false : 'flash in an exploded part';
                }
                if ( in_array( '', $parts ) ) {
                        return $boolean ? false : 'two slashes next to eachother in the path';
                }

        } else {
                return $boolean ? false : 'empty or shorter than 4 characters'.$path;
        }

        return $boolean ? true : 'good';
}

function get_load_time(){
        global $c;
        if ( isset( $c['inittime'] ) ) {
                return time() - $c['inittime'];
        } else {
                return 0;
        }
}

function get_load_microtime(){
        global $c;
        if ( isset( $c['initmicrotime'] ) ) {
                return microtime( true ) - $c['initmicrotime'];
        } else {
                return 0;
        }
}

function get_svnversion(){
/*
> $ svnversion
78414
*/
        $return = array();
        unset( $exec );
        exec( 'svnversion', $exec['output'], $exec['return_var'] );

        if ( !empty( $exec['output'] ) ) {
                return $exec['output'];
        } else {
                return $exec['return_var'];
        }
}

function get_svn_info( $argument = false ){
/*
> $ svn info
Path: .
URL: http://svn.wikimedia.org/svnroot/mediawiki/trunk/phase3/resources/mediawiki.util
Repository Root: http://svn.wikimedia.org/svnroot/mediawiki
Repository UUID: dd0e9695-b195-4be7-bd10-2dea1a65a6b6
Revision: 78414
Node Kind: directory
Schedule: normal
Last Changed Author: krinkle
Last Changed Rev: 78392
Last Changed Date: 2010-12-14 17:21:40 +0000 (Tue, 14 Dec 2010)
*/
        $return = array();
        $exec = array();
        if ( empty( $argument ) || !is_string( $argument ) ) {
                $argument = '';
        }
        exec( 'svn info ' . $argument, $exec['output'], $exec['return_var'] );

        if ( is_array( $exec['output'] ) ) {
                $lines = $exec['output'];
                foreach ( $lines as $line ) {
                        $parts = explode( ':', $line, 2 );
                        $parts[0] = trim( $parts[0] );
                        switch ( $parts[0] ) {
                                case 'Revision':
                                        $return['repo_last_rev'] = trim($parts[1]);
                                        $return['repo_last_rev_link'] = '//www.mediawiki.org/wiki/Special:Code/MediaWiki/' . rawurlencode( $return['repo_last_rev'] );
                                        break;
                                case 'Last Changed Author':
                                        $return['cwd_last_author'] = trim($parts[1]);
                                        $return['cwd_last_author_link'] = '//www.mediawiki.org/wiki/Special:Code/MediaWiki/author/' . rawurlencode( $return['cwd_last_author'] );
                                        break;
                                case 'Last Changed Rev':
                                        $return['cwd_last_rev'] = trim($parts[1]);
                                        $return['cwd_last_rev_link'] = '//www.mediawiki.org/wiki/Special:Code/MediaWiki/' . rawurlencode( $return['cwd_last_rev']        );
                                        break;
                                case 'Last Changed Date':
                                        $return['cwd_last_date'] = explode( '(', $parts[1] );
                                        $return['cwd_last_date'] = $return['cwd_last_date'][0];
                                        $return['cwd_last_date_text'] = date( 'd F Y H:i', strtotime( $return['cwd_last_date'] ) ) . ' (UTC)';
                                        break;
                        }
                }
                return $return;
        } else {
                return $exec['return_var'];
        }
}

function is_odd( $num ) {
        return (bool)($num % 2 );
}

// Thanks to nickr at visuality dot com
// Posted on 08-Feb-2010 09:54 at http://www.php.net/manual/en/function.time.php#96097
function get_time_ago( $opts ) {
        // Defaults
        $datefrom_str = isset( $opts['datefrom_str'] ) ? $opts['datefrom_str'] : false;
        $datefrom_ts = isset( $opts['datefrom_ts'] ) ? $opts['datefrom_ts'] : false;
        $dateto = isset( $opts['dateto'] ) ? $opts['dateto'] : -1;

        // Assume if 0 is passed in that
        // its an error rather than the epoch
        if ( $datefrom_ts === 0 || $datefrom_str === 0 ) { return "A long time ago"; }
        if ( $dateto == -1 ) { $dateto = time(); }

        // Make the entered date into Unix timestamp from MySQL datetime field
        // Timestamp from string or timestamp directly if passed.
        $datefrom = !empty( $datefrom_str ) ? strtotime( $datefrom_str ) : $datefrom_ts;

        // Calculate the difference in seconds betweeen
        // the two timestamps
        $difference = $dateto - $datefrom;

        // Based on the interval, determine the
        // number of units between the two dates
        // From this point on, you would be hard
        // pushed telling the difference between
        // this function and DateDiff. If the $datediff
        // returned is 1, be sure to return the singular
        // of the unit, e.g. 'day' rather 'days'
        switch ( true ) {
                
                // If difference is less than 60 seconds,
                // seconds is a good interval of choice
                case(strtotime('-1 min', $dateto) < $datefrom):
                        $datediff = $difference;
                        $res = ($datediff==1) ? $datediff.' second ago' : $datediff.' seconds ago';
                        break;
                
                // If difference is between 60 seconds and
                // 60 minutes, minutes is a good interval
                case(strtotime('-1 hour', $dateto) < $datefrom):
                        $datediff = floor($difference / 60);
                        $res = ($datediff==1) ? $datediff.' minute ago' : $datediff.' minutes ago';
                        break;
                
                // If difference is between 1 hour and 24 hours
                // hours is a good interval
                case(strtotime('-1 day', $dateto) < $datefrom):
                        $datediff = floor($difference / 60 / 60);
                        $res = ($datediff==1) ? $datediff.' hour ago' : $datediff.' hours ago';
                        break;
                
                // If difference is between 1 day and 7 days
                // days is a good interval
                case(strtotime('-1 week', $dateto) < $datefrom):
                        $day_difference = 1;
                        while (strtotime('-'.$day_difference.' day', $dateto) >= $datefrom)
                        {
                                $day_difference++;
                        }

                        $datediff = $day_difference;
                        $res = ($datediff==1) ? 'yesterday' : $datediff.' days ago';
                        break;
                
                // If difference is between 1 week and 30 days
                // weeks is a good interval                     
                case(strtotime('-1 month', $dateto) < $datefrom):
                        $week_difference = 1;
                        while (strtotime('-'.$week_difference.' week', $dateto) >= $datefrom)
                        {
                                $week_difference++;
                        }

                        $datediff = $week_difference;
                        $res = ($datediff==1) ? 'last week' : $datediff.' weeks ago';
                        break;
                
                // If difference is between 30 days and 365 days
                // months is a good interval, again, the same thing
                // applies, if the 29th February happens to exist
                // between your 2 dates, the function will return
                // the 'incorrect' value for a day
                case(strtotime('-1 year', $dateto) < $datefrom):
                        $months_difference = 1;
                        while (strtotime('-'.$months_difference.' month', $dateto) >= $datefrom)
                        {
                                $months_difference++;
                        }

                        $datediff = $months_difference;
                        $res = ($datediff==1) ? $datediff.' month ago' : $datediff.' months ago';

                        break;
                
                // If difference is greater than or equal to 365
                // days, return year. This will be incorrect if
                // for example, you call the function on the 28th April
                // 2008 passing in 29th April 2007. It will return
                // 1 year ago when in actual fact (yawn!) not quite
                // a year has gone by
                case(strtotime('-1 year', $dateto) >= $datefrom):
                        $year_difference = 1;
                        while (strtotime('-'.$year_difference.' year', $dateto) >= $datefrom)
                        {
                                $year_difference++;
                        }

                        $datediff = $year_difference;
                        $res = ($datediff==1) ? $datediff.' year ago' : $datediff.' years ago';
                        break;

        }
        return $res;
}

// Make an <el title="date">..ago</el> (input being a string)
function get_timeago_el_str( $timestring = 0, $el = 'abbr' ) {
        global $c;
        return date( $c['fulldatefmt'], strtotime( $timestring ) ) . ' (<' . $el . ' title="' . krEscapeHTML( date( 'Y-m-d H:i:s', strtotime( $timestring ) ) ) . ' (UTC)">' . krEscapeHTML( get_time_ago( array('datefrom_str' => $timestring) ) ) . '</' . $el . '>)';
}
// Make an <el title="date">..ago</el> (input  being a valid unix stamp)
function get_timeago_el_ts( $timestamp = 0, $el = 'abbr' ) {
        global $c;
        return date( $c['fulldatefmt'], $timestamp ) . ' (<' . $el . ' title="' . krEscapeHTML( date( 'Y-m-d H:i:s', $timestamp ) ) . ' (UTC)">' . krEscapeHTML( get_time_ago( array('datefrom_ts' => $timestamp) ) ) . '</' . $el . '>)';
}

function getWikiData( $search ) {
        // Prepare request
        $p = array(
                'wikiids' => $search,
                'format' => 'php',
        );
        // Get the data
        $return = file_get_contents( 'http://toolserver.org/~krinkle/getWikiAPI.php?' . http_build_query( $p ) );
        if ( $return === false ) {
                return false;
        }
        $return = unserialize( $return );
        if ( !is_array( $return ) ) {
                return false;
        }
        // Do we have results ?
        $data = $return[$search]['data'];
        if ( empty( $data ) ) {
                return false;
        }
        // Return it
        return $data;
}

// SELECT * FROM toolserver.wiki WHERE , mysql_fetch_object
function wikiDataFromRow( $row, $input = '', $search = '' ) {
        return array_merge( (array)$row, array(
                'wikicode' => substr( $row->dbname, 0, -2 ),
                'localdomain' => krStrLastReplace( '.org', '', $row->domain ),
                'url' => 'http://' . $row->domain,
                'apiurl' => 'http://' . $row->domain . $row->script_path . 'api.php',
        ));
}

// @param $wikiData [data] from getWikiAPI or url-string like 'http://domain.tl' without trailing slash
function getWikiLink( $wikiData, $pagename, $p ) {
        if ( is_array( $wikiData ) ) {
                $url = $wikiData['canonical_url'];
        } else {
                $url = $wikiData;
        }
        $append = '';
        if ( is_array( $p ) ) {
                $append = '&' . http_build_query( $p );
        }
        return $url . '/?title=' . krWikiUrlencode( $pagename ) . $append;
}

// Untill a better solution exists, call the real api or use raw sql
// Most of the time raw sql will be used (which has downsides)
// other times, for more complicated stuff (multiple joins, caching, paging,
// generators for other properties etc.) we lazy-opt for using the live api
// That's what this function does.
/**
 * getAPIData
 * Get's the query, forces format=php, makes the request
 * checks for errors, returns false or the unserialized return of the API.
 *
 * @param Array $wikiData       - all data (dbname, sitename, url, apiurl etc.) for the selected
 *                                                        wiki (from function getWikiData() ).
 * @param Array $p                      - api query (eg. array( 'action' => 'query' etc. ) ).
 * @return Array                        - unserialized result of the API.
 * @return Boolean False        - ... if something went wrong.
 */
function getAPIData( $wikiData , $p ) {
        if ( !is_array( $wikiData ) || !is_array( $p ) || !isset( $wikiData['apiurl'] ) ) {
                return false;
        }
        $p['format'] = 'php';
        $return = file_get_contents( $wikiData['apiurl'] . '?' . http_build_query( $p ) );
        if ( $return === false ) {
                return false;
        }
        $return = unserialize( $return );
        if ( !is_array( $return ) ) {
                return false;
        }
        if ( isset( $return['error'] ) ) {
                return false;
        }
        return $return;
}
// ^ [Krinkle] 2011-01-05 lists.wikimedia.org/pipermail/toolserver-l/2011-February/003873.html

function dbUsername(){
        global $dbUsername;

        // Cache
        if ( is_string( $dbUsername ) ) {
                return $dbUsername;
        } else {

                $toolserver_mycnf = parse_ini_file( '/home/krinkle/.my.cnf' );
                $dbUsername = $toolserver_mycnf['user'];
                unset( $toolserver_mycnf );
                return $dbUsername;
        }

}

function dbPassword(){
        global $dbPassword;

        // Cache
        if ( is_string( $dbPassword ) ) {
                return $dbPassword;
        } else {

                $toolserver_mycnf = parse_ini_file( '/home/krinkle/.my.cnf' );
                $dbPassword = $toolserver_mycnf['password'];
                unset( $toolserver_mycnf );
                return $dbPassword;
        }

}

// @return dbConnect
function connectRRServerByHostname( $hostname = false, $database = false /* optional */ ) {

        // Make sure the input is valid
        if (    !is_string( $hostname )
                ||      substr( $hostname, -15, 15 ) !== '.toolserver.org'
                ||      krStripStr( strtolower( $hostname ) ) !== $hostname
                ||      substr_count ( $hostname, 'toolserver.org' ) !== 1
                ) {
                return false; 
        }

        $dbConnect = mysql_connect( $hostname, dbUsername(), dbPassword() );
        if ( !$dbConnect ) {
                krDie( "dbConnect_$hostname: ERROR: <br />" . mysql_error() );
                return false;
        } else {
                krLog( "dbConnect_$hostname: OK" );
        }
        if ( $database ) {
                $dbSelect = mysql_select_db( $database, $dbConnect );
                if ( !$dbSelect ) {
                        krDie( "dbSelect_$hostname: ERROR; <br />" . mysql_error() );
                        return false;
                } else {
                        krLog( "dbSelect_$hostname: OK" );
                }
        }
        return $dbConnect;
}

// sets global dbConnect & dbSelect
// @return Boolean
function connectRRServerByDBName( $dbname = false ) {
        global $dbConnect, $dbSelect;

        // Make sure the input is valid
        if (    !is_string( $dbname )
                ||      substr( $dbname, -2, 2 ) !== '_p'
                ||      krStripStr( strtolower( $dbname ) ) !== $dbname
                ||      substr_count ( $dbname, '_p' ) !== 1
                ) {
                return false; 
        }
        
        $subdomain = str_replace( '_p', '-p', $dbname );

        $dbConnect = connectRRServerByHostname( $subdomain . '.rrdb.toolserver.org', $dbname );

        return (bool)$dbConnect;
}



/**
 * Session, Time and Debug
 * -------------------------------------------------
 */
// Timezone
session_start();
date_default_timezone_set( 'UTC' );

// Debug
$_SESSION['krDebug'] = CacheAndDefault(
        array_key_exists('debug', $_GET) ? $_GET['debug'] : false, // possible new value
        'false',        // default value
        $_SESSION['krDebug'] // cached value
);
$c['debug'] = isset($_SESSION['krDebug']) && $_SESSION['krDebug'] == 'true' ? true : false;


/**
 * Configuration
 * -------------------------------------------------
 */
$c['commandline'] =  array_key_exists( 'commandline', $c ) && $c['commandline'] ? true : false; // Don't override in case already set. But avoid E_NOTICE later
$c['inittime'] = time();
$c['initmicrotime'] = microtime( true );
$c['fulldatefmt'] = "l, j F Y H:i:s";
$c['krlog'] = '';
$c['krflushes'] = 0;
$c['may_die'] = true;
$c['tshome'] = '//toolserver.org/~krinkle';
$c['tshomepath'] = '/home/krinkle';
$c['toolfeedback_newtalk'] = '//meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&preload=User_talk:Krinkle/Tools/Preload';
$c['toolfeedback_mailhtml'] = '<em>krinklemail<img src="//upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail<span class="dot">&middot;</span>com</em><script>$(function(){$("img[alt=at]").replaceWith("@");$(".dot").text(".");});</script>';
$c['wikis_rcp'] = array('cawiki', 'commonswiki', 'enwikisource', 'enwiktionary', 'nlwiki', 'itwikibooks', 'itwikinews', 'nlwikibooks', 'ptwiki');
$c['wikis_npp'] = array('enwiki', 'eswiki', 'metawiki', 'nlwiktionary');
$c['wikis_all'] = array_merge($c['wikis_rcp'],$c['wikis_npp']);
$c['url'] = array(

        // rcp:
        'cawiki' => 'ca.wikipedia',
        'commonswiki' => 'commons.wikimedia',
        'enwikisource' => 'en.wikisource',
        'enwiktionary' => 'en.wiktionary',
        'itwikibooks' => 'it.wikibooks',
        'itwikinews' => 'it.wikinews',
        'nlwiki' => 'nl.wikipedia',
        'nlwiki' => 'nl.wikipedia',
        'nlwikibooks' => 'nl.wikibooks',
        'ptwiki' => 'pt.wikipedia',

        // npp:
        'enwiki' => 'en.wikipedia',
        'eswiki' => 'es.wikipedia',
        'metawiki' => 'meta.wikimedia',
        'nlwiktionary' => 'nl.wiktionary',

);

$c['nav'] = 'Navigation: <a href="'.$c['tshome'].'/getTopRCusers.php?wiki=' . getParamVar( 'wiki') . '">Get Top RC Users</a> &middot; <a href="'.$c['tshome'].'/getTopRCpages.php?wiki=' . getParamVar( 'wiki') . '">Get Top RC Pages</a> &middot; <a href="//meta.wikimedia.org/wiki/User:Krinkle/Tools"><em>more...</em></a>';

$s['namespaces'] = /* backwards compatible, need to fix usage of this and remove it */
        $c['namespaces'] =       array(
        '-2' => 'Media:',
        '-1' => 'Special:',
        '0' => '',
        '1' => 'Talk:',
        '2' => 'User:',
        '3' => 'User_talk:',
        '4' => 'Project:',
        '5' => 'Project_talk:',
        '6' => 'File:',
        '7' => 'File_talk:',
        '8' => 'MediaWiki:',
        '9' => 'MediaWiki_talk:',
        '10' => 'Template:',
        '11' => 'Template_talk:',
        '12' => 'Help:',
        '13' => 'Help_talk:',
        '14' => 'Category:',
        '15' => 'Category_talk:'
);

// Define user agent
$c['user_agent'] = 'KrinkleTools/1.0 (Toolserver; toolserver.org/~krinkle) Contact/krinklemail@gmail.com';

// For file_get_contents() etc.
ini_set( 'user_agent', $c['user_agent'] );

$c['jquery_loaded'] = false;
$c['jqueryui_loaded'] = false;
$c['krchronlist_css_loaded'] = false;


/**
 * Other
 * -------------------------------------------------
 */
// global k-hack
if(isset($_GET['k'])){
        $k = true;
}
// global x-hack
if(isset($_GET['x'])){
        $x = true;
}

// PHP's time() -> JavaScript:
// new Date(new Date().toUTCString())/1000

// Using get_current_user() won't play nice when executing from shell or cron on toolserver.
// So if a script connects to a database with '/my.cnf', or when reading a file, don't use this
// function if the script will be used (also) from shell / cron.
// Instead hardcode 'krinkle' in those cases, to make sure it won't fail.
