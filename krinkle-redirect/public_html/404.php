<?php

$protocol = ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' );
$append = '#readme';
$delay = 10;
if ( isset( $_SERVER['REQUEST_URI'] ) ) {
	$uri = $_SERVER['REQUEST_URI'];
	$patterns = array(
		'CommonsCreatorLister/' => '#commonscreatorlister',
		'getTopRCpages.php' => '#get-top-rc-pages',
		'getTopRCusers.php' => '#get-top-rc-users',
		'TSUsers.php' => '#tsusers',
		'wikimedia-svn-search/' => '#wikimedia-svn-stats',
		'wikimedia-svn-stats.php' => '#wikimedia-svn-stats',
		'wmfBugZillaPortal/' => '#wmfbugzillaportal',
		'CommonsMoveReview.php' => '#commonsmovereview',
		'SpeedUserPatrol.php' => '#speeduserpatrol',
		'SpeedPagePatrol.php' => '#speedpagepatrol',
		'InterfaceFiles.php' => '#interfacefiles',
		'SingleAuthorTalk.php' => '#singleauthortalk',
		'SiteMatrixChecklist/' => '#sitematrixchecklist',
		'WikiInfo/' => '#wikiinfo',
		'404.php' => '/blob/master/krinkle-redirect/public_html/404.php',
	);
	foreach ( $patterns as $key => $value ) {
		if ( $key[0] !== '/' ) {
			$pattern = '/' . preg_quote( $key, '/' );
			if ( substr( $key, -1 ) === '/' ) {
				// Directory
				// e.g. /\/CommonsCreatorLister(\?|\/|$)/
				$pattern = '/\/' . preg_quote( substr( $key, 0, -1 ), '/' ) . '(\?|\/|$)/';
			} else {
				// File
				// e.g. /\/getTopRCusers\.php(\?|$)/
				$pattern = '/\/' . preg_quote( $key, '/' ) . '(\?|$)/';
			}
		} else {
			$pattern = $value;
		}
		if ( preg_match( $pattern, $uri ) ) {
			$append = $value;
			$delay = 5;
			break;
		}
	}
}

$location = 'https://github.com/Krinkle/ts-krinkle-misc' . $append;

header( $protocol . ' 404 Not Found' );
header( 'Content-Type: text/html; charset=utf-8' );
header( 'Cache-Control: s-maxage=2678400, max-age=2678400' );
header( "Refresh: $delay; url=$location" );

?><!DOCTYPE html>
<meta charset="utf-8">
<title>Toolserver | Krinkle</title>
<style>
html {
	margin: 0; padding: 0;
}
body {
	font-family: sans-serif;
	color: #333;
	max-width: 700px;
	margin: 0 auto;
	padding: 1em 2em;
}
h1 {
	color: #555;
}
</style>
<h1>Toolserver</h1>
<p>The page you were looking for was not found.</p>
<p>Be sure to check out my <a href="<?php echo htmlspecialchars( $location ); ?>">Toolserver archive</a> on GitHub.</p>
<p>Due to the large number of tools I had, I choose not to migrate all of them (many were largely
unused, became obsolete over the years, or no longer functioned properly).</p>
<p>If you're interested in a particular tool, please <a href="https://github.com/Krinkle/ts-krinkle-misc/issues">let me know!</a></p>
<p><em>– Krinkle</em></p>
