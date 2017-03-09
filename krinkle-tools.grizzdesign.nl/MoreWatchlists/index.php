<?php
/**
 * MoreWatchlists.php :: All-in-One file
 *
 * MoreWatchlists
 * Created on January 11th, 2011
 *
 * Copyright © 2011 Krinkle <krinklemail@gmail.com>
 *
 * This is released in the public domain by the author
 */
//@TODO: Make this into an RTRC-ish blocktable instead of a disaligned list
/**
 * Configuration
 * -------------------------------------------------
 */
require_once( '../common.inc.php' );

$is_submit = false;
$c['title'] = 'MoreWatchlists';
$c['baseurl'] = '../MoreWatchlists/';
$c['revID'] = '0.5.7';
$c['revDate'] = '2012-12-10';
$c['limit_min'] = 1;
$c['limit_max'] = 25;
$c['limit_def'] = 100;

/**
 * Parameters
 * -------------------------------------------------
 */
$params['hidebots'] = getParamCheck( 'hidebots', $_POST );
$params['hideown'] = getParamCheck( 'hideown', $_POST );
$params['gwt'] = getParamVar( 'gwt', $_POST );
$params['limit'] = intval( CacheAndDefault( getParamInt( 'limit', $_POST ), $c['limit_def'] ) );
if ( $params['limit'] < $c['limit_min'] ) {
	$params['limit'] = $c['limit_def'];
} elseif ( $params['limit'] > $c['limit_max'] ) {
	$params['limit'] = $c['limit_max'];
}

/**
 * Parse wikidata
 * -------------------------------------------------
 */
$s['wikidata'] = array();
// Also allow simple demo's via URL
if ( !empty( $_POST['wikidata_raw'] ) && !empty( $_POST['owner'] ) && $_POST['go'] == 'Go' ) {
	$is_submit = true;
	$s['wikidata_raw'] = $_POST['wikidata_raw'];
	$s['owner'] = $_POST['owner'];

} elseif ( !empty( $_GET['wikidata_raw'] ) && !empty( $_GET['owner'] ) && $_GET['go'] == 'Go' ) {
	$is_submit = true;
	$s['wikidata_raw'] = $_GET['wikidata_raw'];
	$s['owner'] = $_GET['owner'];
}


if ( $is_submit ) {
	// Split by line-break
	$data_lines = explode( "\n", $s['wikidata_raw'] );
	foreach ( $data_lines as $data_line ) {

		// "sample12334sample|http://commons.wikimedia.org"
		list ( $wltoken, $wikiurl ) = explode( '|', $data_line, 2 );
		$wikiurl = trim($wikiurl);
		$wltoken = trim($wltoken);

		// avoid errors with blank or incomplete lines
		if ( $wikiurl !== '' && $wltoken !== '' ) {
			$s['wikidata'][$wikiurl] = $wltoken;
		}
	}
}


/***************************************************
 *                                                 *
 *       -=[ START OF SUBMIT-ONLY PART ]=-         *
 *                                                 *
 ***************************************************/
if ( $is_submit ) :

/**
 * Feed configuration
 * -------------------------------------------------
 */

$_SAMPLE['wikidata_raw'] = "
sample12334sample|http://commons.wikimedia.org
l0remipsumt0kendy|http://meta.wikimedia.org
hellw0rldwidewiki|http://nl.wikipedia.org
";

// Base
$s['api_params'] = array(
	'format'		=> 'php',
	'action'		=> 'query',
	'list'			=> 'watchlist',
	'wllimit'		=> $c['limit_def'],
	'wlshow'		=> '',
	'wlprop'		=> 'ids|title|flags|user|comment|timestamp|sizes|loginfo',
	'wlowner'		=> $s['owner'],
	'wldir'			=> 'older',
);

// Parameters
if ( $params['hidebots'] ) {
	$s['api_params']['wlshow'] = '!bot';
}
if ( $params['hideown'] ) {
	$s['api_params']['wlexcludeuser'] = $s['owner'];
}
$s['api_params']['wllimit'] = $params['limit'];

$s['nonEmpty'] = array(); // populated later
$s['feeds'] = array();
$s['apipath'] = '/w/api.php?' . http_build_query( $s['api_params'] );
foreach ( $s['wikidata'] as $wikiurl => $token ) {
	$s['feeds'][$wikiurl] = $wikiurl . $s['apipath'] . '&wltoken=' . $token;

}

/**
 * API Query
 * -------------------------------------------------
 */
$app['seperate_results'] = array();
// Push out those queries and gather the results
foreach ( $s['feeds'] as $wiki => $feed_link ) {
	$tmp = file_get_contents( kfExpandUrl( $feed_link ) );
	$tmp = unserialize( $tmp );
	$tmp = $tmp['query']['watchlist'];
	$app['seperate_results'][$wiki] = $tmp;
}


/**
 * Mix the results together and sort them
 * -------------------------------------------------
 */
$app['all_results'] = array();

foreach ( $app['seperate_results'] as $wikiurl => $results ) {
	foreach ( (array)$results as $item ) {
		// Get unix timestamp from the api date format
		$unix_ts = strtotime( $item['timestamp'] );
		// New in recent MediaWiki version: Log entries in watchlist
		// Item is only set if there is a value, avoid E_NOTICE
		$item['logid'] = isset( $item['logid'] ) ? $item['logid'] : 0;
		// Generate a unique identifier that we will use to sort everything and avoid key-colissions
		$unique = $unix_ts . '_' .  $wikiurl . '_' .$item['revid'] . '_' . $item['logid'];
		// Add fields to the item for use in the main loop later
		$item['unix_ts'] = $unix_ts;
		$item['url'] = $wikiurl;
		$item['host'] = parse_url( kfExpandUrl( $wikiurl ), PHP_URL_HOST );
		$s['nonEmpty'][$wikiurl] = 1;

		$app['all_results'][$unique] = $item;

	}
}

krsort( $app['all_results'] );

// Apparently all this causes one ghost entry at the end
// Getting rid of it here. TODO: Find out why ?
array_pop( $app['all_results'] );


/**
 * Functions
 * -------------------------------------------------
 */
$app['prevDay'] = '';
function ahrefLink( $server, $params = array() ) {
	return $server . '/?' . krEscapeHTML( http_build_query( $params ) );
}

function getIcon( $domain, $width = 16 ) {
	/*
	File:Wikimedia-logo-circle.svg | Wikimedia's emblem
	File:HSWPedia.svg | Wikipedia's puzzle globe
	File:W-circle.svg | Wikipedia's W
	File:HSCommons.svg | Commons' emblem
	File:HSWtionary.svg | Wiktionnary's tiles emblem
	File:HSWBooks.svg | Wikibooks's emblem
	File:HSWNews.svg | Wikinews's emblem
	File:HSWQuote.svg | Wikiquote's emblem
	File:HSWSource.svg | Wikisource's emblem
	File:HSWVersity.svg | Wikiversity's emblem
	File:HSWSpecies.svg | Wikispecies's emblem
	File:HSWikimedia.svg | Meta-Wiki's emblem
	File:HSMediaWiki.svg | Mediawiki's emblem
	File:HSIncubator.svg | Incubator's emblem
	File:HSWMania.svg | Wikimania's emblem
	*/

	list( $subPrefix, $project ) = explode( '.', $domain ); // lang.project
	$img_b = 'http://commons.wikimedia.org/wiki/Special:FilePath?file=';
	$img_f = '';
	$img_a = '&width=' . $width;
	switch ( $project ) {
		case 'wikipedia' :
			$img_f = 'Black_W_for_promotion.png';
			break;
		case 'wikiquote' :
			$img_f = 'Wikiquote-logo.svg';
			break;
		case 'wikibooks' :
			$img_f = 'Wikibooks-logo.svg';
			break;
		case 'wikisource' :
			$img_f = 'Wikisource-logo.svg';
			break;
		case 'wiktionary' :
			$img_f = 'Wiktionary_small.svg';
			break;
		case 'wikinews' :
			$img_f = 'Wikinews_favicon.svg';
			break;
		case 'wikiversity' :
			$img_f = 'Wikiversity-favicon.png';
			break;
		case 'mediawiki' :
			$img_f = 'MediaWiki-notext.svg';
			// The PNG version looks better at small scale
			$img_f = 'Mediawiki-logo.png';
			break;
		case 'wikidata' :
			$img_f = 'Wikidata-favicon.png';
			break;
		case 'wikimedia' :
			switch ( $subPrefix ) {
				case 'meta' :
					$img_f = 'Wikimedia Community Logo.svg';
					break;
				case 'commons' :
					$img_f = 'Commons-logo.svg';
					break;
				default : // ie. chapterwikis and other *.wikimedia.org wikis (usability, incubator, strategy..)
					$img_f = 'Wikimedia-logo.svg';
					break;
			}
			break;
		default :
			$img_f = 'Blank MediaWiki Logo.png';
			break;
	}

	return $img_b . rawurlencode( $img_f ) . $img_a;

}

/***************************************************
 *                                                 *
 *        -=[ END OF SUBMIT-ONLY PART ]=-          *
 *                                                 *
 ***************************************************/
 endif;

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?php echo $c['title']; ?></title>
	<link rel="stylesheet" href="//toolserver.org/~krinkle/main.css">

	<?php krLoadjQuery(); ?>
	<script src="//toolserver.org/~krinkle/main.js"></script>
	<style>
	.box {	background:rgb(249, 249, 249); background:rgba(245, 245, 245, 0.8);
			border:1px solid #DFDFDF; font-size:12px }
	.box ul { list-style:none }
	.kr-morewatchlists-output li { margin:0; padding:3px 0px; list-style:none }
	.kr-morewatchlists-output li:nth-child(even) { background:#f3f3f3 }

	.side-descr { width:300px; float:right; font-size:smaller; padding:3px; margin-top:-10px }
	.side-descr ul,
	.side-descr ul li { list-style:none; margin:0 }
	</style>
</head>
<body>
	<div id="page-wrap">
		<h1><a href="../"><small>Krinkle</small></a> | <a href="./"><?=$c['title']?></a></h1>
		<small><em>Version <?=$c['revID']?> as uploaded on <?=$c['revDate']?></em></small>
<?php
/***************************************************
 *                                                 *
 *       -=[ START OF SUBMIT-ONLY PART ]=-         *
 *                                                 *
 ***************************************************/
if ( $is_submit ) :
?>

		<hr>
		<div style="max-height: 150px; overflow-y: scroll;">
			<table class="v-top" style="width: 100%;"><tr>
				<td><?php
					$loaded = array_reverse( array_keys( $s['wikidata'] ) );
					echo '<strong>Loaded (' . count( $loaded ) . ')</strong><ul>';
					foreach( $loaded as $wiki ) { echo '<li><a href="' . $wiki . '/wiki/Special:Watchlist">' . $wiki . '</a></li>'; }
					echo '</ul>';
				?></td>
				<td><?php
					$nonEmpty = array_reverse( array_keys( $s['nonEmpty'] ) );
					echo '<strong>Non-empty (' . count( $nonEmpty ) . ')</strong><ul>';
					foreach( $nonEmpty as $wiki ) { echo '<li><a href="' . $wiki . '/wiki/Special:Watchlist">' . $wiki . '</a></li>'; }
					echo '</ul>';
				?></td>
				<td>
					<strong>Settings:</strong>
					<br>Owner: <?=$s['owner']?>
					<br>Hidebots: <?=$params['hidebots']?>
					<br>Hideown: <?=$params['hideown']?>
					<br>Limit: <?=$params['limit']?><br>
				</td>
			</tr></table>
		</div>
		<hr>

		<div id="toc" style="position: fixed; top:21px; right:5px; padding:8px 20px 8px 8px; overflow:scroll; height:80%" class="box js-only">
			<strong>Table of Contents</strong>
			<ul>
			</ul>
		</div>

		<h2 id="watchlist">Combined watchlists</h2>
<ul class="ns kr-morewatchlists-output">
<?php
// Only close list (</ul>) when it's not the first time
$not_first = false;

// Let's loop through each item in the feed.
foreach( $app['all_results'] as $item ) :
/*
Format (edit or create): {
	"pageid": 10596477,
	"revid": 48135202,
	"ns": 4,
	"title": "Commons:License review\/requests",
	"user": "Juliancolton",
	"timestamp": "2011-01-10T22:05:23Z",
	"oldlen": 5204,
	"newlen": 5478,
	"comment": "\/* Beria *\/ support"
}
Format (log): {
	"pageid": 0,
	"revid": 0,
	"old_revid": 0,
	"ns": 8,
	"title": "MediaWiki:Gadget-AjaxPatrolLinks",
	"user": "George Orwell III",
	"timestamp": "2012-12-10T14:11:43Z",
	"oldlen": 0,
	"newlen": 0,
	"comment": "[[WS:CSD]] M1 - Process deletion",
	"logid": 3970505,
	"logtype": "delete",
	"logaction": "delete"
}
*/
	$subPrefix = explode('.', $item['host'] ); $subPrefix = $subPrefix[0];

	// Day heading
	$thisDay = date( 'j F Y', $item['unix_ts'] );
	if ( $app['prevDay'] != $thisDay ) {
	?>

		<li><strong id="H<?php echo date( 'Ymd', $item['unix_ts'] ); ?>"><?php echo $thisDay; ?></strong>
		<script>$(function(){$('#toc>ul').append('<li><a href="#H<?php echo date( 'Ymd', $item['unix_ts'] ); ?>"><?php echo $thisDay; ?></a></li>');});</script></li>
		<?php

		$app['prevDay'] = $thisDay;
	}

	// Diff
	$diff = $item['newlen'] - $item['oldlen'];
	$diffElem = ( $diff > 500 || $diff < -500 ) ? 'strong' : 'span';
	if ( $diff > 0 ) {
		$diffTxt = "<{$diffElem} class='mw-plusminus-pos'>(+" . $diff . ")</{$diffElem}>";
	} elseif ( $diff < 0 ) {
		$diffTxt = "<{$diffElem} class='mw-plusminus-neg'>(" . $diff . ")</{$diffElem}>";
	} else {
		$diffTxt = "<{$diffElem} class='mw-plusminus-null'>(" . $diff . ")</{$diffElem}>";
	}

	// Generate list item
	$pipeLinks = array();
	$extra = '';
	if ( !$item['revid'] || $item['logid'] ) {
		$pipeLinks[] = 'diff';
		$extra = '[' . $item['logaction'] . ']  . . ';
	} else {
		$pipeLinks[] = '<a href="' . ahrefLink( $item['url'], array( 'curid' => $item['pageid'], 'diff' => $item['revid'] ) ) . '">diff</a>';
	}
	$pipeLinks[] = '<a href="' . ahrefLink( $item['url'], array( 'curid' => $item['pageid'], 'action' => 'history' ) ) . '">hist</a>';
	echo
		'<li>'
	.		'(' . implode( '&nbsp;| ', $pipeLinks ) . ') . . '
	.		'<img width="16" src="' . getIcon( $item['host'], 16 ) . '" title="' . $item['host'] . '" alt="' . $item['host'] . '">&nbsp;<small>' . $subPrefix . '</small>'
	.		' . . '
	.		$extra
	.		'<a href="' . ahrefLink( $item['url'], array( 'curid' => $item['pageid'] ) ) . '">' . krEscapeHTML( $item['title'] ) . '</a>'
	.		'; ' . date( 'H:i', $item['unix_ts'] ) . ' . . '
	.		$diffTxt
	.		' . . '
	.		'<a href="' . ahrefLink( $item['url'], array( 'title' => 'User:' . $item['user'] ) ) . '">' . krEscapeHTML( $item['user'] ) . '</a>'
	.		' '
	.		'<span class="mw-usertoollinks">'
	.			'(<a href="' . ahrefLink( $item['url'], array( 'title' => 'User_talk:' . $item['user'] ) ) . '">Talk</a>'
	.			'&nbsp;| '
	.			'<a href="' . ahrefLink( $item['url'], array( 'title' => 'Special:Contributions/' . $item['user'] ) ) . '">contribs</a>'
	.		')</span>'
	.		' '
	.		'<span class="comment">(' . krEscapeHTML( $item['comment'] ) . ')</span>'
	.	'</li>';

endforeach;
?>
</ul>
<?php
/***************************************************
 *                                                 *
 *        -=[ END OF SUBMIT-ONLY PART ]=-          *
 *                                                 *
 ***************************************************/
else :
?>
	<form class="colly ns" action="./" method="post">
		<fieldset>
			<legend>Settings</legend>

			<label for="owner">Owner</label>
			<input type="text" id="owner" name="owner" placeholder="Nickname..." value="<?php echo krEscapeHTML( postParamVar( 'mwt-owner' ) ); ?>"/>
			<br>

			<label for="wikidata_raw">Raw watchlist tokens</label>
			<div class="wikitable side-descr"><ul><li>Use the following link to automatically gather the watchlist tokens from all your accounts through SUL.</li><li><a href="GetWatchlistTokens.php" title="Gather the watchlist tokens">GetWatchlistTokens&nbsp;&raquo;</a></li></div>
			<textarea id="wikidata_raw" name="wikidata_raw" placeholder="sample12345sample|http://commons.wikimedia.org" cols="70" rows="14" style="height:125px"><?php

					echo krEscapeHTML( postParamVar( 'mwt-wikidata_raw' ) );

				?></textarea>
			<br>

			<label for="hidebots">Hide bots</label>
			<input type="checkbox" id="hidebots" name="hidebots" value="on" <?php echo postParamCheck( 'mwt-hidebots' ) ? 'checked="checked"' : ''; ?>>
			<br>

			<label for="hideown">Hide own</label>
			<input type="checkbox" id="hideown" name="hideown" value="on" <?php echo postParamCheck( 'mwt-hideown' ) ? 'checked="checked"' : ''; ?>>
			<br>

			<label></label>
			<input type="submit" nof id="go" name="go" value="Go">
			<br>
		</fieldset>
	</form>
<?php
endif;
?>
	</div>
</body>
</html>
