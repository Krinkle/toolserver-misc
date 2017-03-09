<?php

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
define( 'KR_LEAVEHTML', true );


/**
 * Functions - Krinkle
 * -------------------------------------------------
 */
function krDebug(){
	global $c; return $c['debug'];
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
		if ( $html_escape ) {
			echo $before . htmlspecialchars( $c['krlog'] ) . $after;
		} else {
			echo $before . $c['krlog'] . $after;
		}
	}
	$c['krlog'] = '';
}

function krEscapeHTML($str){
	return htmlspecialchars($str);
}

function krStripStr($str){
	return krEscapeHTML(addslashes(strip_tags(trim($str))));
}

function krStrLastReplace($search, $replace, $subject){
	return substr_replace($subject, $replace, strrpos($subject, $search), strlen($search));
}

function krDump($var, $return = false, $before = '', $after = ''){
	if($return === false){
		echo $before . krEscapeHTML(print_r($var, true)) . $after;
	} else {
		return $before . krEscapeHTML(print_r($var, true)) . $after;
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
	$img = $img ? $img : 'http://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Dialog-warning.svg/45px-Dialog-warning.svg.png';
	echo krCheckEnvironment(
		'<div class="msg ns error"><p><img src="'.$img.'" width="45" alt="" title="Error" />'.$msg.'</p><span clear></span></div>',
		$msg,
		__FUNCTION__
	);
	krQuit();
}
function krError($msg, $img = false, $extraclasses = '', $class = 'msg ns error'){
	$img = $img ? $img : 'http://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Dialog-warning.svg/45px-Dialog-warning.svg.png';
	echo krCheckEnvironment(
		'<div class="'.$class.' '.$extraclasses.'"><p><img src="'.$img.'" width="45" alt="" title="Error" />'.$msg.'</p><span clear></span></div>',
		$msg,
		__FUNCTION__
	);
}
function krErrorLine($msg, $img, $extraclasses = '', $class = 'msgline ns error'){
	$img = $img ? $img : 'http://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Dialog-warning.svg/24px-Dialog-warning.svg.png';
	echo krCheckEnvironment(
		'<p class="'.$class.' '.$extraclasses.'"><img src="'.$img.'" width="24" alt="" title="Error" />&nbsp;<small>'.$msg.'</small><span clear></span></p>',
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
function krClosedMsg($configuration){
	echo krMsg('Tool "<tt><code>'.$configuration['title'].'</code></tt>" has been closed.');
	krQuit();
}


/**
 * Functions - Krinkle Load
 * -------------------------------------------------
 */
// Loads jQuery if not already loaded
// @return true (loaded it)
// @return false (not loaded, was already loaded)
function krLoadjQuery(){
	global $c;
	if ( $c['jquery_loaded'] === false ) {
		$c['jquery_loaded'] = true;
		?><script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script><?php
		return true;
	}
	return false;
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
		if ($one && $val !== '' && $val !== false) {
			$link .= '?' . rawurlencode($key) . '=' . rawurlencode($val);
			$one = false;
		} elseif($val !== '' && $val !== false) {
			$link .= '&' . rawurlencode($key) . '=' . rawurlencode($val);
		}
	}
	unset( $one );
	// Return the link only if there were any settings, else return false
	// Except when a custom url has been passed
	return $link == $c['baseurl'] && !$url ? false : $link;
}

// Returns namespace name or (number)
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
		return $boolean ? false : 'empty or shorter than 4 characters';
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
	unset( $exec );
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
					$return['repo_last_rev_link'] = 'http://www.mediawiki.org/wiki/Special:Code/MediaWiki/' . rawurlencode( $return['repo_last_rev'] );
					break;
				case 'Last Changed Author':
					$return['cwd_last_author'] = trim($parts[1]);
					$return['cwd_last_author_link'] = 'http://www.mediawiki.org/wiki/Special:Code/MediaWiki/author/' . rawurlencode( $return['cwd_last_author'] );
					break;
				case 'Last Changed Rev':
					$return['cwd_last_rev'] = trim($parts[1]);
					$return['cwd_last_rev_link'] = 'http://www.mediawiki.org/wiki/Special:Code/MediaWiki/' . rawurlencode( $return['cwd_last_rev']	 );
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

/**
 * Expand protocol-relative urls.
 * @param string $url
 * @param string $protocol: This protocol will be used if
 *  there isn't one already (it is not enforced).
 */
function kfExpandUrl( $url = '', $protocol = 'http' ) {
	if ( substr( $url, 0, 2 ) === '//' ) {
		$url = $protocol . ':' . $url;
	}

	return $url;
}



/**
 * Session, Time and Debug
 * -------------------------------------------------
 */
// Timezone
session_start();
date_default_timezone_set( 'UTC' );
error_reporting(0);
ini_set( 'display_errors', 0 );

// Debug
$_SESSION['krDebug'] = CacheAndDefault(
	array_key_exists('debug', $_GET) ? $_GET['debug'] : false,
	'off'/* can't use '0' because empty() will return true and Cache/Default will be used */,
	$_SESSION['krDebug']
);
$c['debug'] = isset($_SESSION['krDebug']) && $_SESSION['krDebug'] == '1' ? true : false;


/**
 * Configuration
 * -------------------------------------------------
 */
$c['inittime'] = time();
$c['initmicrotime'] = microtime( true );
$c['fulldatefmt'] = 'l, j F Y H:i:s';
$c['krlog'] = '';
$c['may_die'] = true;
$c['toolfeedback_newtalk'] = 'http://meta.wikimedia.org/w/index.php?title=User_talk:Krinkle/Tools&action=edit&section=new&editintro=User_talk:Krinkle/Tools/Editnotice&preload=User_talk:Krinkle/Tools/Preload';
$c['toolfeedback_mailhtml'] = '<em>krinklemail<img src="http://upload.wikimedia.org/wikipedia/commons/thumb/8/88/At_sign.svg/15px-At_sign.svg.png" alt="at" />gmail<span class="dot">&middot;</span>com</em><script>$(function(){$("img[alt=at]").replaceWith("@");$(".dot").text(".");});</script>';

$c['namespaces'] =	 array(
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
$c['user_agent'] = 'KrinkleTools/0.3 (nl) Contact/krinklemail@gmail.com';

// For: file_get_contents() etc.
ini_set('user_agent', $c['user_agent']);

$c['jquery_loaded'] = false;
$c['jqueryui_loaded'] = false;
