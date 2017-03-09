<?php
/**
 * GetWatchlistTokens.php :: Main index file
 *
 * GetWatchlistTokens
 * Created on January 11th, 2011
 *
 * Copyright © 2011 Krinkle <krinklemail@gmail.com>
 *
 * This is released in the public domain by the author
 */

/**
 *  Definitions
 * -------------------------------------------------
 */
define( 'GWT_MAIN', true );

/**
 * Configuration
 * -------------------------------------------------
 */
require_once( '../common.inc.php' );
error_reporting( -1 );

$is_submit = false;

$c['revID'] = '0.3.1';
$c['revDate'] = '2011-01-13';
$c['title'] = 'GetWatchlistTokens';
$c['baseurl'] = '../MoreWatchlists/GetWatchlistTokens.php';
$c['MaxSkipsInLoop'] = 20;


/**
 * Functions
 * -------------------------------------------------
 */

function htmlSafeDump( $data = null ) {
	return htmlspecialchars( print_r( $data, true ) );
}

function getLocalApiLink( $server/* = 'http://commons.wikimedia.org'*/ ) {
	global $s;
	return $server . $s['scriptpath'] . 'api.php';
}

// Checks the existing cookie or bakes a new cookie and saves location in cookie
function prepareCookie(){
	if ( !isset( $_SESSION['cookiefile'] ) ) {
		$_SESSION['cookiefile'] = tempnam( dirname(__DIR__) . '/cookiebin', 'CURLWGT_' );
	}
	// Else: Do nothing, re-use the same as linked from SESSION
}

// Eats the cookie and removes the link to it in SESSION
function eatCookie(){
	if ( isset( $_SESSION['cookiefile'] ) ) {
		unlink( $_SESSION['cookiefile'] );
		unset( $_SESSION['cookiefile'] );
	}
	// Else: Do nothing, we dont have any known cookies
}

// Executes a cURL post request to the given URL with postdata
// Also passes our cookiefile
function curlRequestWithPostNcookie( $target = '', $postdataArr = array() ) {
	global $s;

	if ( empty( $target ) || empty( $postdataArr ) || !is_string( $target ) || !is_array( $postdataArr ) ) {
		return array('target' => $target, 'postdataArr' => $postdataArr);
	}

	$postdata = http_build_query( $postdataArr );

	$cHand = curl_init();
	curl_setopt_array( $cHand, $s['curloptions'] );
	curl_setopt( $cHand, CURLOPT_URL, $target );
	curl_setopt( $cHand, CURLOPT_POSTFIELDS, $postdata );
	$cReturn = curl_exec( $cHand );
	if ( curl_errno( $cHand ) ) {
	    	krDie( 'Error retrieving data from ' . $target . ': ' . curl_error( $cHand ) );
	}
	curl_close( $cHand );

	return $cReturn;
}

// - Parse cookies from original domain
// - Set centralauth cookies for other projects
function doCentralAuthCookies(){
	require_once( 'GetWatchlistTokens_centralauth.php' );
	if ( !defined( 'GWT_CENTRALAUTHED' ) ) {
		krDie( 'Error in processing original cookie for SUL-login. Aborting.' );
	}
}


/**
 * Settings
 * -------------------------------------------------
 */
$s['commons_apiurl'] = 'http://commons.wikimedia.org/w/api.php';
$s['scriptpath'] = '/w/';
$s['login_status'] = false;
$s['watchlist_tokens'] = array(); // populated later


/**
 * Parameters
 * -------------------------------------------------
 */
$params['username'] = $_POST['gwt-username'];
$params['password'] = $_POST['gwt-password'];
$params['go'] = ( $_POST['gwt-go'] == 'Go' );

if ( $params['go'] && !empty( $params['username'] ) && !empty( $params['password'] ) ) {
	$is_submit = true;
} else {
	$is_submit = false;
}

/**
 * cURL & cookie initialization
 * -------------------------------------------------
 */
if ( $_GET['action'] == 'logout' ) {
	eatCookie();
	$_SESSION = array();
	session_destroy();
	die('Logged out.');
}

prepareCookie();

$s['curloptions'] = array(
	CURLOPT_COOKIEFILE => $_SESSION['cookiefile'],
	CURLOPT_COOKIEJAR => $_SESSION['cookiefile'],
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_USERAGENT => $c['user_agent'],
	CURLOPT_POST => true,
);


?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?></title>
	<link rel="stylesheet" href="//toolserver.org/~krinkle/main.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script>
	<script src="//toolserver.org/~krinkle/main.js"></script>
</head>
<body>
<div id="page-wrap">

	<h1><a href="../"><small>Krinkle</small></a> | <a href="./"><?=$c['title']?></a></h1>
	<?php
	if ( !empty( $_SESSION['login_lgusername'] ) ) {
		?><small style="float:right">(<a href="<?=$c['baseurl']?>?action=logout">clear cookies</a>)</small><?php
	}
	?><small><em>Version <?=$c['revID']?> as uploaded on <?=$c['revDate']?></em></small>
	<hr>
	<strong>Loading the watchlist tokens from the (possibly) 700+ wikis may take multiple minutes. Please don't close this window.</strong>
	<hr>

	<h3 id="login">Login</h3>
	<form class="ns colly" action="<?=$c['baseurl']?>" method="post" id="login-form">
		<fieldset>
			<legend>Log in with your Wikimedia SUL credentials</legend>

			<label for="gwt-username">Username</label>
			<input type="text" id="gwt-username" name="gwt-username" value="">
			<br>

			<label for="gwt-password">Password</label>
			<input type="password" id="gwt-password" name="gwt-password" value="">
			<br>

			<label></label>
			<input type="submit" value="Go" class="nof" name="gwt-go">
		</fieldset>
	</form>
<?php
/**
 * Log in
 * -------------------------------------------------
 */
if ( $is_submit ) :

	// Already logged in ?
	if ( !empty( $_SESSION['login_lgusername'] ) && $s['login_status'] === false ) {

		$s['login_status'] = true;
		doCentralAuthCookies();
		krMsg( 'Already logged in as: ' . $_SESSION['login_lgusername'] );
		?><script type="text/javascript">$( function(){ $( '#login-form' ).slideUp(); } );</script><?php

	} else {


		// Do initial login
		$postdataArr = array(
			'format'		=> 'php',
			'action'		=> 'login',
			'lgname'		=> $params['username'],
			'lgpassword'	=> $params['password'],

		);
		$cReturn = curlRequestWithPostNcookie( $s['commons_apiurl'], $postdataArr );

		// Was the result valid serialized php ?
		if ( !$cReturn || !is_string( $cReturn ) || !unserialize( $cReturn ) ) {
			krDie( 'Server did not return valid data. <!-- responsedata: ' . htmlSafeDump( $cReturn ) . ' -->' );
		}
		$cData = unserialize( $cReturn );

		// On WMF a token is required. Verify:
		if ( $cData['login']['result'] !== 'NeedToken' ) {
			krDie( 'Unexpected absence of token verification requirement. <!-- responsedata: ' . htmlSafeDump( $cReturn ) . ' -->' );
		}

		if ( empty( $cData['login']['token'] ) ) {
			krDie( 'Server did not return a token.' );
		}

		$_SESSION['logintoken'] = $cData['login']['token'];

		// Do second login with token
		$postdataArr = array(
			'format'		=> 'php',
			'action'		=> 'login',
			'lgname'		=> $params['username'],
			'lgpassword'	=> $params['password'],
			'lgtoken'		=> $_SESSION['logintoken'],

		);
		$cReturn = curlRequestWithPostNcookie( $s['commons_apiurl'], $postdataArr );

		// Was the result valid serialized php ?
		if ( !$cReturn || !is_string( $cReturn ) || !unserialize( $cReturn ) ) {
			krDie( 'Server did not return valid data. <!-- responsedata: ' . htmlSafeDump( $cReturn, true ) . ' -->' );
		}
		$cData = unserialize( $cReturn );

		// Login succes ?
		if ( $cData['login']['result'] !== 'Success' ) {
			krDie( 'Login failure: ' . $cData['login']['result'] .'. <!-- responsedata: ' . htmlSafeDump( $cData, true ) . ' -->' );
		}

		$_SESSION['login_lguserid'] = $cData['login']['lguserid'];
		$_SESSION['login_lgusername'] = $cData['login']['lgusername'];

		krSuccess( 'Login OK!' );
		?><script>$( function(){ $( '#login-form' ).slideUp(); } );</script><?php

	}

endif;


// Did we just login or were we already ?
if ( !empty( $_SESSION['login_lgusername'] ) && $s['login_status'] === false ) {
	$s['login_status'] = true;
	krMsg( 'Logged in as: ' . $_SESSION['login_lgusername'] );
	?><script>$( function(){ $( '#login-form' ).slideUp(); } );</script><?php
}

/**
 * Get wikis from CentralAuth API (globaluserinfo)
 * -------------------------------------------------
 */
// Only if we're logged in
if ( !empty( $_SESSION['login_lgusername'] ) ) :

	krLog( '[] Get info from CentralAuth' );

	/* FORMAT:
	$checkWikis['codewiki'] = array(
		'project' => 'wikibooks',
		'url' => 'http://link',
		'apiurl' => 'http://link/api.php',
	);
	*/

	doCentralAuthCookies();

	// Get MergeAccount info from API
	$postdataArr = array(
		'format'	=> 'php',
		'action'	=> 'query',
		'meta'		=> 'globaluserinfo',
		'guiuser'	=> $_SESSION['login_lgusername'],
		'guiprop'	=> 'merged',
	);
	$cReturn = curlRequestWithPostNcookie( $s['commons_apiurl'], $postdataArr );
	$cData = unserialize( $cReturn );
	// Was the result valid ?
	if ( !$cData || empty( $cData['query']['globaluserinfo']['merged'] ) ) {
		krDie( 'Server did not return valid data. <!-- responsedata: ' . htmlSafeDump( $cReturn ) . ' -->' );
	}

	$checkWikis = array();

	foreach ( $cData['query']['globaluserinfo']['merged'] as $site ) {
		$checkWikis[$site['wiki']] = array(
			'project' => NULL,
			'url' => NULL,
			'apiurl' => NULL,
		);
	}

	// Get data from getWikiAPI
	// array_splice
	$postdataArr = array(
		'format'	=> 'json',
		'wikiids'	=> implode( '|', array_keys( $checkWikis ) ),
	);
	$cReturn = curlRequestWithPostNcookie( 'http://toolserver.org/~krinkle/getWikiAPI/', $postdataArr );

	// Was the result valid serialized php ?
	if ( !$cReturn || !is_string( $cReturn ) || !json_decode( $cReturn ) ) {
		krDie( 'getWikiAPI did not return valid data. <!-- checkWikis: ' . htmlSafeDump( $checkWikis ) . ' --><!-- responsedata: ' . htmlSafeDump( $cReturn ) . ' -->' );
	}
	$cData = json_decode( $cReturn, true );

	foreach ( $cData as $wikiid_input => $wiki ) {
		if ( $wiki['data']['is_closed'] == '1' ) {
			unset( $checkWikis[$wikiid_input] );
		} else {
			$checkWikis[$wikiid_input]['project'] = $wiki['data']['family'];
			$checkWikis[$wikiid_input]['url'] = $wiki['data']['url'];
			$checkWikis[$wikiid_input]['apiurl'] = $wiki['data']['apiurl'];
		}
	}

	krLogFlush();

endif;

/**
 * Generate token table
 * -------------------------------------------------
 */
if ( is_array( $checkWikis ) && !empty( $checkWikis ) && !empty( $_SESSION['login_lgusername'] ) ) :

	$htmlTableOuput =
		'<h3 id="table">Complete table</h3>'
	.	'<table class="wikitable sortable">'
	.		'<tr>'
	.			'<th>Code</th>'
	.			'<th>Project</th>'
	.			'<th>Watchlist token</th>'
	.			'<th>Status</th>'
	.		'</tr>';

	$tablerow = '';
	$skippyCount = 0;
	$checkWikis = array_reverse($checkWikis);
	foreach ( $checkWikis as $code => $site ) {

		$tablecells =
			'<td><a href="' . $site['url'] . '/wiki/Special:Watchlist" title="Special:Watchlist">' . $code . '</a></td>'
		.	'<td>' . $site['project'] . '</td>';
		$postdataArr = array(
			'format'	=> 'php',
			'action'	=> 'query',
			'meta'		=> 'userinfo',
			'uiprop'	=> 'options',

		);

		// Some new wikis or wikis that aren't in toolserver's database may not have a url in their array...
		if ( empty( $site['apiurl'] ) ) {
			$skippyCount++;

			// Finish table row
			$tablecells .= '<td></td><td>apiurl_missing</td>';
			$htmlTableOuput .= '<tr style="background:#DFDFDF">'  . $tablecells . '</tr>';
			continue;
		}

		$cReturn = curlRequestWithPostNcookie( $site['apiurl'], $postdataArr );

		// Was the result valid serialized php ?
		if ( !$cReturn || !is_string( $cReturn ) || !unserialize( $cReturn ) ) {
			krError( 'API at ' . $site['project'] . ' did not return valid data. <!-- responsedata: ' .  htmlSafeDump( $cReturn ) . ' -->' );
		}
		$cData = unserialize( $cReturn );

		$wlToken = $cData['query']['userinfo']['options']['watchlisttoken'];

		// Was the result valid userinfo ?
		if ( !$cData || empty( $cData['query']['userinfo'] ) ) {
			$skippyCount++;

			// Finish table row
			$tablecells .= '<td></td><td>invalid</td>';
			$htmlTableOuput .= '<tr style="background:#DFDFDF">'  . $tablecells . '</tr>';
			continue;
		}

		// Does the wiki accept our session ?
		if ( isset( $cData['query']['userinfo']['anon'] ) ) {
			$skippyCount++;

			// Finish table row
			$tablecells .= '<td></td><td>logged_out</td>';
			$htmlTableOuput .= '<tr style="background:#DFDFDF">'  . $tablecells . '</tr>';

		// We're logged in the wiki and got a valid respond
		// If the token is empty it means the user has never ever visited "Special:Watchlist
		// and thus the initial token has never been set... (or he somehow blanked his token)
		} elseif ( empty( $wlToken ) ) {
			// Finish table row
			$tablecells .= '<td>' . $wlToken . '</td><td>OK (<small>no watchlist</small>)</td>';
			$htmlTableOuput .= '<tr style="background:#FFE">'  . $tablecells . '</tr>';
		} else {
			// Finish table row
			$tablecells .= '<td>' . $wlToken . '</td><td>OK</td>';
			$htmlTableOuput .= '<tr style="background:#EFE">'  . $tablecells . '</tr>';
			// Save to array so we can spit out the <textarea> later
			$s['watchlist_tokens'][$site['url']] = $wlToken;
		}

		// Stop the loop if we've reached the maximum number of failures
		if ( $skippyCount > $c['MaxSkipsInLoop'] ) {
			krMsg( 'Too many skipped wikis. Aborting.' );
			break;
		}

	}

	$htmlTableOuput .= '</table>';

	?>
	<h3 id="textarea"><a href="./">MoreWatchlists</a> Template</h3>
	<form class="colly" action="download.php" method="post" name="mwtform">
		<fieldset>
			<legend>Settings</legend>

			<label for="mwt-owner">Owner</label>
			<input type="type" readonly="readonly" name="mwt-owner" value="<?php echo krEscapeHTML( $_SESSION['login_lgusername'] ); ?>">
			<br>

			<label for="mwt-wikidata_raw">Raw watchlist tokens</label>
			<textarea readonly="readonly" cols="70" rows="14" name="mwt-wikidata_raw" id="mwt-wikidata_raw"><?php
				foreach ( $s['watchlist_tokens'] as $wikiurl => $wltoken ) {

					echo krEscapeHTML( $wltoken . '|' . $wikiurl ) . "\n";

				}
			?></textarea>
			<br>

			<label for="mwt-hidebots">Hide bots</label>
			<input type="checkbox" id="mwt-hidebots" name="mwt-hidebots" value="on" checked="checked">
			<br>

			<label for="mwt-hideown">Hide own</label>
			<input type="checkbox" id="mwt-hideown" name="mwt-hideown" value="on">
			<br>

			<label></label>
			<input type="submit" nof name="mwt-save" value="Save to disk &darr;" onClick="document.mwtform.action='download.php';">
			<br>
			<span>Due to the time it can take to load this page, you can download this template to your computer to save time. Then all you have to do is open that file and click the "Continue to MoreWatchlists" button.<br><br>If this page loads quick enough or if you just want to test the tool, you may also click "Continue to MoreWatchlists" below right away.</span>
			<br>

			<label></label>
			<input type="submit" nof name="mwt-load" value="Continue to MoreWatchlists &rarr;" onClick="document.mwtform.action='index.php';" >
			<br>
		</fieldset>
	</form>
	<?php

	echo $htmlTableOuput;

endif;

krLogFlush();
?>
</div>
<script src="//toolserver.org/~krinkle/sortable.js"></script>
</body>
</html>
