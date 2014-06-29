<?php
/**
 * CommonsHelpmeDetect.php :: All-in-One file
 *
 * CommonsHelpmeDetect
 * Created on January 28th, 2011
 *
 * Copyright 2011 Krinkle <krinklemail@gmail.com>
 *
 * This file is released in the public domain.
 */

/**
 * Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

/**
 * Parameters
 * -------------------------------------------------
 */
$params['format'] = getParamVar( 'format', $_GET );
$params['callback'] = CacheAndDefault( getParamVar( 'callback', $_GET ), 'CommonsHelpmeDetect' );
$params['diff'] = CacheAndDefault( getParamInt( 'diff', $_GET ), false );
$params['oldid'] = CacheAndDefault( getParamInt( 'oldid', $_GET ), false );

if ( !$params['diff'] ) {
	die( 'Missing essential parameters.' );
}

/**
 * Functions
 * -------------------------------------------------
 */
function getRawHtmlByRevid( $revid = false ) {
	if ( $revid ) {
		$rawHtml = file_get_contents( 'http://commons.wikimedia.org/w/index.php?oldid=' . $revid . '&action=render&smaxage=18000&maxage=18000' );
		if ( !$rawHtml ) {
			return false;
		}
		return $rawHtml;
	}
	return false;
}

function getHelpmeMatches( $rawHtml ) {

	if ( !is_string( $rawHtml ) || $rawHtml == '' ) {
		return false;
	}

	preg_match_all(
		'|' . preg_quote( '<span class="mw-tpl-helpme"' ) . '[^>]+>(.*)' . preg_quote( '</span>' ) . '|U',
		$rawHtml,
		$matches
	);

	if ( empty( $matches[1] ) ) {
		return false;
	}
	$hits = array_values( array_unique( $matches[1] ) );
	return $hits;

}

function doApi() {
	global $params, $c;
	ksort( $c['data'] );
	krApiExport( $c['data'], $params['format'], $params['callback'] );
}


/**
 * Do it
 * -------------------------------------------------
 */
// Begin data return
$c['data'] = array(
	'revision'		=> $params['diff'],
	'is_hit'		=> false,
	'is_error'		=> false,
	'error'			=> '',
	'hits_count'	=> 0,
	'hits'			=> array(),
);

// Get html
$rawHtmlDiff = getRawHtmlByRevid( $params['diff'] );
if ( !$rawHtmlDiff ) {
	$c['data']['is_error'] = true;
	$c['data']['error'] = 'Raw Html for revision ' . $params['diff'] . ': ERROR';
	doApi();
} else {
	krLog( 'Raw Html for revision ' . $params['diff'] . ': OK' );

	// Process
	$hitsDiff = getHelpmeMatches( $rawHtmlDiff );
}

if ( $params['oldid'] ) {
	$rawHtmlOld = getRawHtmlByRevid( $params['oldid'] );
	if ( !$rawHtmlOld ) {
		// Don't error, this is not so important
		krLog( 'Raw html for old revision ' . $params['oldid'] . ': ERROR' );
	} else {
		$c['data']['oldid'] = $params['oldid'];
		krLog( 'Raw Html for revision ' . $params['oldid'] . ': OK' );

		// Process
		$hitsOld = getHelpmeMatches( $rawHtmlOld );
	}
}

if ( !$hitsDiff ) {
	doApi();
}
if ( $hitsOld ) {
	$hitsDiff = array_diff( $hitsDiff, $hitsOld );
}
foreach ( $hitsDiff as $hit ) {
	list( $langCode, $langName, $type, $revUser ) = explode( '|', $hit );
	$c['data']['hits'][] = array(
		'_text' => $hit,
		'_template' => '{{helpme|' . $langCode . '|type=' . $type . '}}',
		'langcode' => $langCode,
		'langname' => $langName,
		'type' => $type,
		'revuser' => $revUser
	);
}


/**
 * Finish
 * -------------------------------------------------
 */
doApi();
krLog( krDump( $c['data'] ) );
krLogFlush( KR_FLUSHLOG, KR_ESCAPEHTML, '<pre>', '</pre>');
