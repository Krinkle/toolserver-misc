<?php
/**
 * download.php :: Serves a .html file to the client with a static form prefilled from $_POST
 *
 * @package GetWatchlistTokens
 * Created on January 11th, 2011
 *
 * Copyright © 2011 Krinkle <krinklemail@gmail.com>
 *
 * This is released in the public domain by the author
 */

/**
 * Configuration
 * -------------------------------------------------
 */
require_once( '../common.inc.php' );

ob_clean();
ob_start();

$c['revID'] = '0.3.1';
$c['revDate'] = '2011-01-13';
$c['title'] = 'GetWatchlistTokens';
$c['baseurl'] = '../MoreWatchlists/GetWatchlistTokens.php';

?><!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<meta charset="utf-8">

	<title>Krinkle | <?=$c['title']?></title>

	<link rel="stylesheet" href="../main.css" type="text/css" media="all" />
</head>
<body>
	<div id="page-wrap">
		<h1><a href="../"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<small><em>Version <?=$c['revID']?> as uploaded on <?=$c['revDate']?></em></small>
		<hr />
		<?php krMsg( 'This page has been cached from <a href="' . $c['baseurl'] . '">krinkle-tools</a> on ' . date( $c['fulldatefmt'] ) ); ?>
		<h3 id="textarea"><a href="./">MoreWatchlists</a> Template</h3>
		<form class="colly" action="./" method="post" name="mwtform">
			<fieldset>
				<legend>Settings</legend>

				<label for="owner">Owner</label>
				<input type="type" readonly="readonly" name="owner" value="<?php echo krEscapeHTML( postParamVar( 'mwt-owner' ) ); ?>" />
				<br />

				<label for="wikidata_raw">Raw watchlist tokens</label>
				<textarea readonly="readonly" cols="70" rows="14" name="wikidata_raw" id="wikidata_raw"><?php

					echo krEscapeHTML( postParamVar( 'mwt-wikidata_raw' ) );

				?></textarea>
				<br />

				<label for="hidebots">Hide bots</label>
				<input type="checkbox" id="hidebots" name="hidebots" value="on" <?php echo postParamCheck( 'mwt-hidebots' ) ? 'checked="checked"' : ''; ?> />
				<br />

				<label for="hideown">Hide own</label>
				<input type="checkbox" id="hideown" name="hideown" value="on" <?php echo postParamCheck( 'mwt-hideown' ) ? 'checked="checked"' : ''; ?> />
				<br />

				<label></label>
				<input type="hidden" name="go" value="Go" />
				<input type="submit" nof name="load" value="Continue to MoreWatchlists &rarr;" />
				<br />
			</fieldset>
		</form>
	</div>
</body>
</html>
<?php

// Force download of this file
$size = ob_get_length();
header( 'Content-Type: text/html' );
header( 'Content-Length: ' . $size );
header( 'Content-Disposition: attachment; filename="Krinkle_MoreWatchlists_Template_(' . date( 'Y-m-d_His' )  . ').html"' );
header( 'Content-Transfer-Encoding: binary' );
header( 'Accept-Ranges: bytes' );

/* The three lines below basically make the
download non-cacheable */
header( 'Cache-control: private' );
header( 'Pragma: private' );
header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );

ob_end_flush();
