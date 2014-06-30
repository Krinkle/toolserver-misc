<?php
/**
 * header.php :: Configuration and HTML head
 *
 * @package MoreContributions
 * Created on August 30th, 2010
 *
 * Copyright © 2010 Krinkle <krinklemail@gmail.com>
 *
 * MoreContributions by Krinkle [1] is licensed under
 * a Creative Commons Attribution-Share Alike 3.0 Unported License [2]
 *
 * [1] commons.wikimedia.org/wiki/User:Krinkle
 * [2] creativecommons.org/licenses/by-sa/3.0/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once '../CommonStuff.php';
$revID = '0.0.3';
$revDate = '2013-01-10';

$c['title'] = 'MoreContributions';
$c['baseurl'] = '//toolserver.org/~krinkle/MoreContributions/';

krLog('-- Configuration done');

/**
 *  Input
 * -------------------------------------------------
 */
if (!isset($_POST['username']) && !isset($_GET['username']) && $c['pagetitle'] !== 'input') {
	header('Location: input.php');
	die;
}

/**
 *  Settings
 * -------------------------------------------------
 */
// SQL servers
$dbServerSQL = 'sql';
$dbServerS1 = 'sql-s1-rr';
$dbServerS2 = 'sql-s2-rr';
$dbServerS3 = 'sql-s3-rr';
$dbServerS4 = 'sql-s4-rr';
$dbServerS5 = 'sql-s5-rr';
$dbServerS6 = 'sql-s6-rr';
$dbServerS7 = 'sql-s7-rr';

// Login
$toolserver_mycnf = parse_ini_file('/home/' . get_current_user() . '/.my.cnf');
$s['mysql_user'] = $toolserver_mycnf['user'];
$s['mysql_pwd'] = $toolserver_mycnf['password'];
unset($toolserver_mycnf);

// Wiki settings
$s['namespaces'] = array(
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
define('RC_EDIT', 0);
define('RC_NEW', 1);
define('RC_LOG', 3);

// Date head
$s['datehead_format'] = 'l, j F Y';
$s['datehead_lastday'] = '';

krLog('-- Settings done');

/**
 *  Functions
 * -------------------------------------------------
 */
function iw_timefromtimestamp($s) {
	global $c;
	return date('H:i', strtotime($s));
}

function iw_multiarray_sort($a,$subkey, $order='ASC') {
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
	}
	if(strtoupper($order) == 'ASC'){
		asort($b);
	} else {
		arsort($b);
	}
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
	}
	return $c;
}

function iw_dateheader($ts) {
	global $s;
	$d = date($s['datehead_format'], strtotime($ts));
	if($d !== $s['datehead_lastday']){
		if($s['datehead_lastday'] !== ''){
			echo '</ul>';
		}
		echo '<h4>'.$d.'</h4><ul class="ns">';
	}
	$s['datehead_lastday'] = $d;
}
function iw_dateheader2($ts) {
	global $s;
	$d = date($s['datehead_format'], strtotime($ts));
	if($d !== $s['datehead_lastday']){
		echo '<div class="item"><strong>'.$d.'</strong></div>';
	}
	$s['datehead_lastday'] = $d;
}

krLog('-- Functions done');

/**
 *  Input defaults
 * -------------------------------------------------
 */
if (!isset($_POST['submit'])) { // == 'Go' or == 'Edit'
	$_POST['username'] = $_GET['username'] ? $_GET['username'] : '';
	$_POST['wikidb'] = $_GET['wikidb'] ? $_GET['wikidb'] : '';
	$_POST['allwikis'] = $_GET['allwikis'] ? $_GET['allwikis'] : 'on';
}
$_POST['summary'] = !empty($_GET['summary']) ? $_GET['summary'] : '';

/**
 *  Input validation
 * -------------------------------------------------
 */
// Can't have allwikis and a wiki selected. Selected wiki will take preference (done by user) since by default "All wikis" is on.
if ($_POST['wikidb'] == '') {
	$_POST['allwikis'] = 'on';
} else {
	$_POST['allwikis'] = 'off';
}

// Experimental
if ($_POST['summary'] !== '') {
	$_POST['summary'] = "Created page with '==*";
}
// Process page title
$c['pagetitle'] = $c['pagetitle'] ? ' - '.$c['pagetitle'] : '';

krLog('-- Input validation done');
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?><?=$c['pagetitle']?></title>
	<link rel="stylesheet" href="../main.css">
	<style>
/*
	RC OUTPUT
*/
#krIW_list {
	margin: 1em 0;
	width: 100%;
}
#krIW_list .item {
	padding: 3px 5px;
	white-space: nowrap;
	background: #f3f3f3;
}
#krIW_list .item:nth-child(odd) {
	background: #fff;
}

/* Acts like a table with table-rows, using divs to avoid fixed table-layout with forced width and oveflow issues */
#krIW_list .item div {
	display: inline-block;
	overflow: hidden;
}
#krIW_list .item div[first] {
	width: 31%;
}
#krIW_list .item div[user] {
	width: 34%;
}
#krIW_list .item div[other] {
	width: 29%;
}
#krIW_list .item div[size] {
	width: 6%;
	font-size: smaller;
	text-align: right;
}
/* Colored watchlist and recent changes numbers */
.mw-plusminus-pos { color: #006400; } /* dark green */
.mw-plusminus-neg { color: #8b0000; } /* dark red */
.mw-plusminus-null { color: #aaa; } /* gray */
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>
<body>
	<div id="page-wrap" style="width: 900px;">
		<h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<small><em>Version <?=$revID?> as  uploaded on <?=$revDate?> by Krinkle</em></small>
		<hr />
