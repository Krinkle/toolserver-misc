<?php

/**
 * index.php :: All-in-One file
 *
 * MoreContributions
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
require_once 'header.php';
?>
		<h3 id="settings">Settings</h3>
		<form name="editbutton" method="post" class="ns colly" action="input.php">
			<fieldset>
				<legend>Settings</legend>
				<input type="submit" name="submit" value="Edit" />

				<table>
				<tr>
					<td>
						<label>Username:</label>
						<input disabled="disabled" value="<?=krEscapeHTML($_POST['username'])?>"/>
						<br />
					</td>
				</tr>
				<tr>
					<td>
						<label>Wiki:</label>
						<input disabled="disabled" value="<?=krEscapeHTML($_POST['wikidb'])?>"/>
						<br />
					</td>
					<td>
						<label>All wikis:</label>
						<input disabled="disabled" value="<?php echo $_POST['allwikis']; ?>" />
						<br />
					</td>
				</tr>
				</table>

				<input type="hidden" name="limit" value="<?=krStripStr($_POST['limit'])?>" />
				<input type="hidden" name="username" value="<?=krEscapeHTML($_POST['username'])?>" />
				<input type="hidden" name="wikidb" value="<?=krEscapeHTML($_POST['wikidb'])?>" />
				<input type="hidden" name="allwikis" value="<?=krStripStr($_POST['allwikis'])?>" />
			</fieldset>
		</form>


		<?php
/**
 *  MySQL Links Connect
 * -------------------------------------------------
 */
$dbLinkSQL = @mysql_connect($dbServerSQL, $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS1  = @mysql_connect($dbServerS1,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS2  = @mysql_connect($dbServerS2,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS3  = @mysql_connect($dbServerS3,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS4  = @mysql_connect($dbServerS4,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS5  = @mysql_connect($dbServerS5,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS6  = @mysql_connect($dbServerS6,  $s['mysql_user'], $s['mysql_pwd']);
$dbLinkS7  = @mysql_connect($dbServerS7,  $s['mysql_user'], $s['mysql_pwd']);

if ( !$dbLinkSQL && !$dbLinkS1 && !$dbLinkS2 && !$dbLinkS3 && !$dbLinkS4 && !$dbLinkS7 ){
	krDie( 'Error in connecting to database. Please try again later.');
} elseif ( !$dbLinkSQL || !$dbLinkS1 || !$dbLinkS2 || !$dbLinkS3 || !$dbLinkS4 || !$dbLinkS7 ){
	krWarn( 'Connection failed to one or more servers. Some wikis may be hidden from the results.' );
}
krLog('-- MySQL Links done');


/**
 *  Generate query info
 * -------------------------------------------------
 */

if (empty($_POST['username'])) {
	krDie('Username cannot be blank.');
}
if (strlen($_POST['username']) <= 3) {
	krDie('Username must be atleast 3 characters.');
}

// Add extra clauses
if ( substr($_POST['username'], -1, 1) == '*' ) {
	$_POST['username'] = krStrLastReplace('*', '%', $_POST['username']);
	$s['filterclauses'] .= " AND rev_user_text LIKE '".sql_clean($_POST['username'])."' ";
} else {
	$s['filterclauses'] .= " AND rev_user_text = '".sql_clean($_POST['username'])."' ";
}
if( !empty($_POST['summary']) ){
	if( substr($_POST['summary'], -1, 1) == '*' ) {
		$_POST['summary'] = krStrLastReplace('*', '%', $_POST['summary']);
		$s['filterclauses'] .= " AND rev_comment LIKE '".sql_clean($_POST['summary'])."' ";
	} else {
		$s['filterclauses'] .= " AND rev_comment = '".sql_clean($_POST['summary'])."' ";
	}
}
// Optionally add a clause to select a single wiki
$s['selectwikiclause'] = '';
if ($_POST['wikidb'] !== '') {
	$s['selectwikiclause'] = " AND dbname = '".sql_clean($_POST['wikidb'])."' ";;
}


/**
 *  Get wikis
 * -------------------------------------------------
 */
mysql_select_db('toolserver', $dbLinkSQL);
$dbReturn = mysql_query("SELECT * FROM wiki WHERE is_closed = 0".$s['selectwikiclause'], $dbLinkSQL);
$dbResults = mysql_fetch_all($dbReturn);unset($dbReturn);
if (!$dbResults) {
	krDie('Wiki information acquirement failed.');
}


/**
 *  Interate over wikis
 * -------------------------------------------------
 */
$dbActiveLink = false;
$dbAllWikiResults = array();
foreach ($dbResults as $wiki) {
/* $wiki = Array(
    [dbname] => abwiki_p
    [lang] => ab
    [family] => wikipedia
    [domain] => ab.wikipedia.org
    [size] => 518
    [is_meta] => 0
    [is_closed] => 0
    [is_multilang] => 0
    [is_sensitive] => 0
    [root_category] =>
    [server] => 3
    [script_path] => /w/
)*/
	// Get the right server link
	switch ($wiki['server']) {
		case '1':
			$dbActiveLink = $dbLinkS1;
			break;
		case '2':
			$dbActiveLink = $dbLinkS2;
			break;
		case '3':
			$dbActiveLink = $dbLinkS3;
			break;
		case '4':
			$dbActiveLink = $dbLinkS4;
			break;
		case '5':
			$dbActiveLink = $dbLinkS5;
			break;
		case '6':
			$dbActiveLink = $dbLinkS6;
			break;
		case '7':
			$dbActiveLink = $dbLinkS7;
			break;
		default:
			$dbActiveLink = false;
			break;
	}

	// If we got a link, continue
	if ($dbActiveLink) {

		mysql_select_db($wiki['dbname'], $dbActiveLink) or krErrorLine('Database error for '.$wiki['dbname']);
		$dbQuery = " /* LIMIT:10 */
			SELECT
				*,
				'".sql_clean($wiki['dbname'])."' as wiki_dbname,
				'".sql_clean($wiki['domain'])."' as wiki_domain
			FROM revision
			INNER JOIN page
			ON page_id = rev_page
			WHERE rev_deleted = 0
			".$s['filterclauses']."

			ORDER BY rev_timestamp DESC
			LIMIT 0,25
			;
			";

		$dbReturn = mysql_query($dbQuery, $dbActiveLink);
		$dbResult = mysql_fetch_all($dbReturn);
		unset($dbReturn);
		// Check www.mediawiki.org/wiki/Manual:Recentchanges_table for what $dbResult's arrays contain

 		$dbAllWikiResults = array_merge($dbAllWikiResults, $dbResult);
		//krLog("Done with querying ".$wiki['dbname']);

	} else {
		$varname = "dbServerS" . $wiki['server'];
		$sqlhostname = $$varname;
		krErrorLine("Unable to connect to {$wiki['domain']} database ({$sqlhostname} &bull; <a href=\"//status.toolserver.org/\" title=\"Toolserver Status\">more info</a>)");
	}
}


/**
 *  Interate over results
 * -------------------------------------------------
 */
krLog($dbQuery);
krLog(krEscapeHTML(print_r($dbAllWikiResults, true)));

$dbAllWikiResults = iw_multiarray_sort($dbAllWikiResults, 'rev_timestamp', 'DESC');

// as <div>
echo '<div id="krIW_list" class="ns">';

foreach ($dbAllWikiResults as $result) {

	// Title underscore strip
	$result['page_title'] = str_replace("_", " ", $result['page_title']);

	// Diff-size
	$diff = $result['rev_len'];
	$el = 'span';
	$diffEl = "<$el class='mw-plusminus-null'>($diff)</$el>";

	$typeSymbol = "&nbsp;&nbsp;";
	$diffLink = '<a href="//'.$result['wiki_domain'].'/?diff=prev&oldid='.$result['rev_id'].'">diff</a>';

	iw_dateheader2($result['rev_timestamp']);
	echo	'<div class="item">'
	.			'<div first>'
	.				'('.$diffLink.' | <a href="//'.$result['wiki_domain'].'/?curid='.$result['rev_page'].'&action=history">hist</a>) '.$typeSymbol.' '.iw_timefromtimestamp($result['rev_timestamp'])
	.				' <a href="//'.$result['wiki_domain'].'/?curid='.$result['rev_page'].'">'.str_replace("_", " ", $s['namespaces'][$result['page_namespace']].$result['page_title']).'</a>'
	.			'</div>'
	.			'<div user>'
	.				'&nbsp;<small>&middot;&nbsp;<a href="//'.$result['wiki_domain'].'/wiki/User_talk:'.rawurlencode($result['rev_user_text']).'">T</a> &middot; <a href="//'.$result['wiki_domain'].'/wiki/Special:Contributions/'.rawurlencode($result['rev_user_text']).'">C</a>&nbsp;</small>&middot;&nbsp;<a href="//'.$result['wiki_domain'].'/wiki/User:'.rawurlencode($result['rev_user_text']).'" class="mw-userlink">'.$result['rev_user_text'].'</a><small>@'.$result['wiki_domain'].'</small>'
	.			'</div>'
	.			'<div other>'
	.				'<em>'.krEscapeHTML($result['rev_comment']).'</em>'
	.			'</div>'
	.			'<div size>'.$diffEl.'</div>'
	.		'</div>';
}
if (empty($dbAllWikiResults)) {
	echo '<p><em>No changes by this username in the last 30 days.</em></p>';
}
echo '</div><div style="clear: both;"></div>';


/**
 *  MySQL Links Close
 * -------------------------------------------------
 */
if ($dbLinkSQL) { mysql_close($dbLinkSQL); }
if ($dbLinkS1) { mysql_close($dbLinkS1); }
if ($dbLinkS2) { mysql_close($dbLinkS2); }
if ($dbLinkS3) { mysql_close($dbLinkS3); }
if ($dbLinkS4) { mysql_close($dbLinkS4); }
if ($dbLinkS5) { mysql_close($dbLinkS5); }
if ($dbLinkS6) { mysql_close($dbLinkS6); }
if ($dbLinkS7) { mysql_close($dbLinkS7); }
krLog('-- MySQL Links closed');

?>
<p>Queried <?php echo count( $dbResults ); ?> wiki databases. A maximum of 25 edits per wiki is shown.</p>
<?php require_once('footer.php'); ?>
