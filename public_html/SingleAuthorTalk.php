<?php
/**
 * SingleAuthorTalk.php :: All-in-One file
 * Created on December 27th, 2010
 *
 * Version: 0.0.2 (2012)
 *
 * State: proof of concept
 * - Basic content output
 * - Provide input via url query parameters manually (no input form)
 * - No page layout or styling yet
 *
 * @package SingleAuthorTalk
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 *  Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

//TODO: MAke $_GET['wiki'] default to commonswiki
	// limit
	$s['limit'] = 50;

// Settings
if ( isset( $c['url'][$_GET['wiki']] ) ) {
	// wikicode
	$s['wiki'] = $_GET['wiki'];
	$s['anononly'] = getParamBool( 'anononly' );
	// url
	$s['url'] = $c['url'][$_GET['wiki']];
	// offset
	$offset = intval($_GET['offset']);
	$s['offset'] = $offset > 1 && $offset < 5000 ? $offset : 0;
	krLog( 'Wiki valid: ' . $s['wiki'] );
} else {
	krDie( 'Unknown wiki' );
}

// Messages
// Default
$s['ns'] = 'Talk';
$s['hist'] = 'hist';

// Dutch
if ( substr($_GET['wiki'], 0, 2) == 'nl' ) {
	$s['ns'] = 'Overleg';
	$s['hist'] = 'gesch';
}

// Opening the HTML document
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>SingleAuthorTalk</title>
</head>
<body>
<h1>Talkpages edited by a single author (-bots<? echo $s['anononly'] ? ', +anononly' : ''; ?>, -redirects)</h1>
<em>(includes page edited by multiple bots but 1 non-bot)</em><br><em>(pages edited solely by bots are excluded)</em><br><em>Output limited to <?=$s['limit']?> results.</em><br><em>Last update: <?php echo date('r'); ?> (0 seconds ago)</em>
<p>Wiki: <?=$s['url']?></p>
<?php


// Warning
	echo '<strong>- Be sure to check the history</strong><br><strong>- Don\'t delete talkpages just for being outdated, imperfect or blanked by someone</strong> it can be valid history.';
if ( $s['wiki'] == 'commonswiki' ) {
		echo '<br><strong>- Speedy guidelines: <a href="//commons.wikimedia.org/wiki/COM:SPEEDY#Speedy_deletion" target="_blank">//commons.wikimedia.org/wiki/COM:SPEEDY#Speedy_deletion</a></strong>';
}

// Parameters and URL
$c['baseurl'] = $c['tshome'] . '/SingleAuthorTalk.php';
$params = array();
$params['wiki'] = $s['wiki'];
$params['limit'] = $s['limit'];
$params['offset'] = $s['offset'];
$params['anononly'] = $s['anononly'];

$toolserver_mycnf = parse_ini_file( '/home/krinkle/.my.cnf' );

// Database connection
$dbConnect = mysql_connect( $s['wiki'].'-p.rrdb.toolserver.org', $toolserver_mycnf['user'], $toolserver_mycnf['password'] );
if ( !$dbConnect ) {
	krDie( 'dbConnect: ERROR: <br>' . mysql_error() );
} else {
	krLog( 'dbConnect: OK' );
}
$dbSelect = mysql_select_db( $s['wiki'].'_p', $dbConnect );
if ( !$dbSelect ) {
	krDie( 'dbSelect: ERROR; <br>' . mysql_error() );
	return false;
} else {
	krLog( 'dbSelect: OK' );
}

// Extra clauses
$whereClauses = array();

if ( $s['anononly'] ) {
	$whereClauses[] = "
	AND		(SELECT	rev_user
			FROM	revision
			WHERE	rev_page=page_id
			AND	NOT	rev_user IN( '10942'/*Magalhães*/ )
			AND	NOT	rev_user_text IN( 'CommonsTicker', 'E85Bot', 'Jcb' )
			AND NOT EXISTS (
					SELECT	*
					FROM	user_groups
					WHERE	ug_user=rev_user
					AND		ug_group='bot'
					)
			LIMIT 1
			)=0
	";
}

$whereClause = implode( ' ', $whereClauses );

// Based on query from:
// https://jira.toolserver.org/browse/DBQ-111
$dbQuery_excludeAndSelectRevAndTime = " /* SingleAuthorTalk::dbQuery_excludeAndSelectRevAndTime */
SELECT /* LIMIT: 20 */
	page_title,
	(SELECT	rev_user_text
	FROM	revision
	WHERE	rev_page=page_id
	AND	NOT	rev_user IN( '10942'/*Magalhães*/ )
	AND	NOT	rev_user_text IN( 'CommonsTicker', 'E85Bot', 'Jcb' )
	AND NOT EXISTS (
			SELECT	*
			FROM	user_groups
			WHERE	ug_user=rev_user
			AND		ug_group='bot'
			)
	LIMIT 1
	) as revver,
	(SELECT	rev_timestamp
	FROM	revision
	WHERE	rev_page=page_id
	AND	NOT	rev_user IN( '10942'/*Magalhães*/ )
	AND	NOT	rev_user_text IN( 'CommonsTicker', 'E85Bot', 'Jcb' )
	AND NOT EXISTS (
			SELECT	*
			FROM	user_groups
			WHERE	ug_user=rev_user
			AND		ug_group='bot'
			)
	LIMIT 1
	) as revtime

FROM page

WHERE	page_is_redirect=0
AND		page_namespace=1
AND		(
		SELECT
			COUNT(distinct rev_user_text)

		FROM	revision

		WHERE	rev_page=page_id
		AND	NOT	rev_user IN( '10942'/*Magalhães*/)
		AND	NOT	rev_user_text IN( 'CommonsTicker', 'E85Bot', 'Jcb' )
		AND NOT EXISTS (
				SELECT	*
				FROM	user_groups
				WHERE	ug_user=rev_user
				AND		ug_group='bot'
				)
		)=1

" . $whereClause . "

LIMIT " . $s['offset'] . "," . $s['limit'] . ";
";
$dbReturn = mysql_query( $dbQuery_excludeAndSelectRevAndTime, $dbConnect );

// Output
if ( !!$dbReturn ) {

	$dbResults = mysql_fetch_all( $dbReturn ); unset( $dbReturn );

	/* Nav */
	$nav =  '<p>';
	// Anonymous toggle
	if ( $s['anononly'] ) {
		$link = generatePermalink(
					array_merge( $params, array( 'anononly' => '0' ) )
				);
		$nav .=  '<a href="' . $link . '">Both anons and logged-in users</a><br>';
	} else {
		$link = generatePermalink(
					array_merge( $params, array( 'anononly' => '1' ) )
				);
		$nav .=  '<a href="' . $link . '">Only anonymous</a><br>';
	}
	// Prev-link (hidden when down to 0)
	if ( $s['offset'] > 1) {
		$newoffset = ($s['offset'] - $s['limit']);
		$newoffset = $newoffset > 0 ? $newoffset : 0;
		$link = generatePermalink(
					array_merge( $params, array( 'offset' => $newoffset ) )
				);
		$nav .=  '<a href="' . $link . '">&laquo; prev (' . $newoffset . ' - ' . ( $newoffset + $s['limit'] ) . ')</a> &middot; ';
	}
	// Next link
	$newoffset = ($s['offset'] + $s['limit']);
	$link = generatePermalink(
				array_merge( $params, array( 'offset' => $newoffset ) )
			);
	$nav .=  '<a href="' . $link . '">next &raquo; (' . $newoffset . ' - ' . ( $newoffset + $s['limit'] ) . ')</a>';
	$nav .=  '</p>';

	echo $nav;

	// List
	echo '<ul>';
	foreach ( $dbResults as $i => $hit ) {

		echo '<li>(<a href="//' . $s['url'] . '.org/?action=history&title=Talk:' . krEscapeHTML( rawurlencode( $hit['page_title'] ) ) . '" target="_blank">' . $s['hist'] . '</a>) <a href="//' . $s['url'] . '.org/?diff=curr&title=Talk:' . krEscapeHTML( rawurlencode( $hit['page_title'] ) ) . '" target="_blank">' . $s['ns'] . ':' . krEscapeHTML( $hit['page_title'] )  . '</a> &middot ' . krEscapeHTML( $hit['revver'] ) . ' <small>&ndash;' . krEscapeHTML( date( 'Y-M-d H:i', strtotime( $hit['revtime'] ) ) ) . '</small></li>';
		if ( $i == 99 ) {
			echo '<li>&hellip;</li>';
		}
	}
	echo '</ul>';

	echo $nav;


} else {
	krDie( 'No results or an error occured.' );
}

// Finish
if ( krDebug() ) {
	krDump( $dbResults );
}
mysql_close( $dbConnect );
krLogFlush();
?>
</pre></body></html>
