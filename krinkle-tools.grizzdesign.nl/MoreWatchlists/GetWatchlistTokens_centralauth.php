<?php
/**
 * GetWatchlistTokens_centralauth.php :: Included in GetWatchlistTokens.php. Takes the cookie and adds (if not already) cookies for the other WMF domains for SUL-login through CentralAuth-cookies
 *
 * @package GetWatchlistTokens
 * Created on January 11th, 2011
 *
 * Copyright © 2011 Krinkle <krinklemail@gmail.com>
 *
 * This is released in the public domain by the author
 */

if ( !defined( 'GWT_MAIN' ) && $_GET['debug'] != '1' ) {
	die( 'This is not a valid entry point.' );
}
if ( $_GET['debug'] == '1' ) {
	require_once( '../common.inc.php' );
}
function gwtDumper( $var = null, $name = null ) {
	
	// Only dump stuff if we're not in the main application
	if ( !defined( 'GWT_MAIN' ) ) {
		if ( !is_null( $name ) ) {
			echo '<h3>' . htmlspecialchars( $name ) . '</h3>';
		}
		echo '<pre>' . htmlspecialchars( print_r( $var, true ) ) . '</pre>'; 
	}
}
/**
 * Settings
 * -------------------------------------------------
 */
$cookie_file = $_SESSION['cookiefile'];

if ( empty( $cookie_file ) ) {
	die( 'Error in processing original cookie for SUL-login. Aborting.' );
}

$original_domain = '_.commons.wikimedia.org';

$target_domains = array(
	'_.wikipedia.org',
	'_.wikimedia.org', // experimental
	'_.meta.wikimedia.org',
	'_.wiktionary.org',
	'_.wikibooks.org',
	'_.wikiquote.org',
	'_.wikisource.org',
	'_.commons.wikimedia.org',
	'_.wikinews.org',
	'_.wikiversity.org',
	'_.mediawiki.org',
	'_.species.wikimedia.org',
	'_.wikivoyage.org',
	'_.wikidata.org',
);
$centralauth_cookienames = array( 'centralauth_User', 'centralauth_Token', 'centralauth_Session' );
$newcookie_data = array();

/**
 * Current cookie
 * -------------------------------------------------
 */
$cookie_content = file_get_contents( $cookie_file );

gwtDumper( $cookie_content, 'current cookie_content' );



/**
 * Process CentralAuth
 * -------------------------------------------------
 */
// Rip the cookie apart in seperate lines
$cookie_lines = explode( "\n", $cookie_content );
	
	
// Go through and find the centralauth cookies from the original domain
// and create new cookies for our target domains
foreach ( $cookie_lines as $cookie_line ) {
	foreach ( $centralauth_cookienames as $centralauth_cookiename ) {

		// If this line matches our domain and one of the keys :
		if ( strpos( $cookie_line, $original_domain ) > 1 && strpos( $cookie_line, $centralauth_cookiename ) > 2 ) {

			// We have a match! Create new cookie entries
			// and store them temporarily by domain by cookiename
			// so we can unset() later if we have them already before
			// we append them for real
			foreach ( $target_domains as $target_domain ) {
				$newcookie_data[$target_domain][$centralauth_cookiename]
					 = str_replace( $original_domain, $target_domain, $cookie_line ) . "\n";
			}

		}

	}
}

// Go through and find the centralauth cookies from the target domains
// and remove them from the new cookie
foreach ( $cookie_lines as $cookie_line ) {
	foreach ( $centralauth_cookienames as $centralauth_cookiename ) {
		foreach ( $target_domains as $target_domain ) {

			// If this line matches a target domain and one of the keys :
			if ( strpos( $cookie_line, $target_domain ) > 1 && strpos( $cookie_line, $centralauth_cookiename ) > 2 ) {
	
				// We have a match! Remove these from the new cookie
				// since we have this one already in the current cookie
				unset( $newcookie_data[$target_domain][$centralauth_cookiename] );	
			}

		}
	}
}

/**
 * Create the new version and save it 
 * -------------------------------------------------
 */
// Merge into appendable string
$newcookie_addstring = '';
foreach ( $newcookie_data as $set ) {
	$newcookie_addstring .= implode( '', $set );
}

gwtDumper( $newcookie_data, 'newcookie_data' );
gwtDumper( $newcookie_addstring, 'newcookie_addstring' );

// Append to old cookie
$newcookie_content = $cookie_content . $newcookie_addstring;
gwtDumper( $newcookie_content, 'final cookie_content' );

// Save new cookie
if ( $_GET['debug'] !== 'on' ) {
	$result = file_put_contents( $cookie_file, $newcookie_content );
	gwtDumper( $result, 'file_put_contents' );
}

if ( $result ) {
	define( 'GWT_CENTRALAUTHED', true );
} 
