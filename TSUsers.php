<?php
/**
 * TSUsers.php :: All-in-One file
 *
 * TS Users
 * Created on January 31, 2011
 *
 * Copyright 2011 Krinkle <krinklemail@gmail.com>
 *
 * This file is released in the public domain.
 */
// @TODO: Get additional data from LDAP (see sandbox2.php)
// @TODO: Move getting and parsing of .about.me into a function
// @TODO: Switch main loop from foreach/glob-items to foreach/LDAP-results
//		 getting .about.me where posible

/**
 * Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

$c['revID'] = '0.0.4';
$c['revDate'] = '2011-02-01';
$c['title'] = 'TS Users';
$c['baseurl'] = $c['tshome'] .'/TSUsers.php';

/**
 * Functions
 * -------------------------------------------------
 */
function tsu_clean( $str ) {
	return preg_replace("!@!",'<sup>[at]</sup>', $str );
}

/**
 * Settings
 * -------------------------------------------------
 */
$s = array();
$s['homeDir'] = '/home';
$s['aboutFilename'] = '.about.me';
$s['ignoreList'] = array(
	'didi',
	'ppeople2007', // has aboutFile values in wrong keys (ie. email in firstlang, url in email etc.)
	'edwardzyang', // '' idem
	'luca', // '' idem
	'stable', // '' idem
);

/**
 * Parameters
 * -------------------------------------------------
 */
$params['example'] = array_key_exists( 'example', $_GET );

// Permalink
$s['permalink'] = generatePermalink( $params );

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?></title>
	<link rel="stylesheet" href="main.css">
	<?php krLoadjQuery(); ?>
	<script src="main.js"></script>
	<style>
	#page-wrap { width: 1280px; }
	</style>
</head>
<body>
	<div id="page-wrap">
		<h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<p style="float: left;"><small><em>Version <?=$c['revID']?> as uploaded on <?=$c['revDate']?></em><?php /* if($s['permalink']) echo ' | <a href="'.$s['permalink'].'">Permalink to this page</a>'; */ //  no need for permalinks for now... ?></small></p>
		<p style="float: right;"><strong>For users:</strong> put an <code>.about.me</code> like <a href="<?=$c['baseurl']?>?example=1">this</a> into your home directory and make it readable to appear.</p>
		<hr>
<?php
if ( $params['example'] ) :
$exampleHTML = '<h3 id="example">Example</h3><p><strong><a href="' . $c['baseurl'] . '">&laquo; back</a></strong></p>';
// https://wiki.toolserver.org/w/index.php?title=.about.me&oldid=5290&action=raw

$john_doe = <<<HTML
<pre>
# ircname is your nick in the irc-channel #wikimedia-toolserver on freenode -- leave it empty if you're not on IRC.
ircname = John_Doe

# wikiname is your username in your default wikimedia project.
wikiname = John Doe

# homewiki is the domain name of your default wikimedia project.
homewiki = commons.wikimedia.org

# firstlang is the language you speak best -- shouldn't be empty ;-)
firstlang = Dutch

# secondlang is another language you understand more or less, may be empty.
secondlang = English, German

# just give your email-adress here if you want to publish it, leave empty otherwise.
email = johndoe@wikimedia.invalid

# if you've got certian rights such as ts-root or wiki-admin, or other memberships that may be interesting,
# put it here. Else, leave it empty.
status = query-service, dewiki bureaucrat, commonswiki sysop, MediaWiki developer, Wikimedia DE member, WMF employee

## that's it :)
</pre>
HTML;
$exampleHTML .= $john_doe;

echo $exampleHTML;


else :
/**
 * Prepare and begin output
 * -------------------------------------------------
 */
$accHTML = '<h3 id="accounts">Accounts</h3><table class="wikitable v-top sortable">';
$debugCode = '';
$fields = array(
	'ircname' => array(
		'head' => '<a target="_blank" title="join #wikimedia-toolserver on irc.freenoe.net" href="irc://irc.freenode.net/wikimedia-toolserver">IRC nick</a>',
		'html' => true,
		'clean' => true,
	),
	'wikiname' => array(
		'html' => false,
		'clean' => true,
	),
	'homewiki' => array(
		'html' => false,
		'clean' => true,
	),
	'firstlang' => array(
		'head' => 'Primary language',
		'html' => true,
		'clean' => true,
	),
	'secondlang' => array(
		'head' => 'Second language',
		'html' => true,
		'clean' => true,
	),
	'email' => array(
		'html' => false,
		'clean' => true,
	),
	'status' => array(
		'head' => 'Role',
		'html' => true,
		'clean' => false,
	),
);
// Headings
$accHTML .= '<tr><th>username</th>';
foreach ( $fields as $fieldname => $fPrefs ) {
	$accHTML .= '<th>' . ( !array_key_exists( 'head', $fPrefs ) ? $fieldname : $fPrefs['head'] ) . '</th>';
}
$accHTML .= '</tr>';


/**
 * Parse files and create table rows
 * -------------------------------------------------
 */
$homeDirs = glob( $s['homeDir'] . '/*', GLOB_ONLYDIR );
foreach ( $homeDirs as $curDir ) {
	// We only want real directories
	if ( is_link( $curDir ) ) {
		continue;
	}
	$aboutFile = "{$curDir}/{$s['aboutFilename']}";
	$username = basename( $curDir );
	// Check if everything is ready to go
	if (	!file_exists( $aboutFile ) // may not exist
		||	in_array( $username, $s['ignoreList'] ) // may be ignored
		||	!is_readable( $aboutFile ) // may be chmodded wrong
		) {
		continue;
	}
	$lines = file( $aboutFile );
	$data = array();
	foreach ( $lines as $curLine ) {
		$curLine = trim( $curLine );
		// Ignore empty lines and # comments
		if ( $curLine == '' || $curLine{0} == '#' ) {
			continue;
		}
		$split = explode( '=', $curLine, 2 );
		// Multiline values are not allowed
		// If this line did not contain a = ignore it
		if ( count( $split ) < 2 ) {
			continue;
		}
		list( $key, $value ) = $split;
		// Save it
		$data[trim($key)] = trim( $value );
	}
	// @TODO: Make this nicer
	$data['email'] = $username . '@toolserver.org';
	// Process fields
	if ( !empty( $data['homewiki'] ) ) {
		$data['domain'] = $data['homewiki_raw'] = $data['homewiki'];
		if ( !preg_match( '/.*\.org/', $data['homewiki'] ) ) {
			$data['domain'] .= '.org';
		}
		if ( !empty( $data['wikiname'] ) ) {
			$data['wikiname'] =
				'<a class="external" title="User:' . krEscapeHTML( $data['wikiname'] . ' at ' . $data['homewiki'] ) . '" '
				. 'href="//' . krEscapeHTML( $data['domain'] ) . '/wiki/User:' . krEscapeHTML( rawurlencode( $data['wikiname'] ) ) . '">'
					. krEscapeHTML( $data['wikiname'] )
				. '</a>';
		}
		$data['homewiki'] =
			'<a class="external" href="//' . krEscapeHTML( $data['domain'] ) . '" '
			. 'title="' . krEscapeHTML( $data['homewiki'] ) . '">'
				. krEscapeHTML(  $data['homewiki'] )
			. '</a>';
	}

	if ( krDebug() ) {
		$debugCode[$username] = $data;
	}
	$accHTML .= "\n" . '<tr><th><a href="/~' .$username . '" title="/~' .$username . '">' .$username. '</a></th>';
	foreach ( $fields as $fieldname => $fPrefs ) {
		$val = @$data[$fieldname];
		if ( $fPrefs['clean'] ) {
			$val = tsu_clean( $val );
		}
		if ( $fPrefs['html'] ) {
			$val = krEscapeHTML( $val );
		}
		$accHTML .= '<td>' . $val . '</td>' . "\n";
	}
	$accHTML .= '</tr>';
}
$accHTML .= '</table>';
echo $accHTML;
if ( krDebug() ) {
	//var_dump( $debugCode );
	var_dump( krDump( $debugCode, KR_ECHO, '<pre>', '</pre>', KR_NOESCAPEHTML ) );
}

endif; // end of if example or real

/**
 * Finish
 * -------------------------------------------------
 */
krLogFlush( KR_FLUSHLOG, KR_ESCAPEHTML, '<pre>', '</pre>');
krLoadBottomCSS();
?>
<h3 id="hideme">Hide me!</h3>
<p>If you prefer not to be listed here, please <a target="_blank" href="<?php
$p = array(
	'wpSubject' => '[KrinkleTools | TS Users] Hide me from user list',
	'wpText' => 'Hi Krinkle,

Please hide me from the public user list.
My Toolserver shell username: TOOLSERVER_SHELL_USERNAME_HERE

Thanks,
'

);

echo '//commons.wikimedia.org/wiki/Special:EmailUser/Krinkle?' . http_build_query( $p );

?>" title="Click here to request to opt-out from the user list">click here</a> to opt-out from this overview.</p>
<script src="sortable.js"></script>
</body>
</html>
