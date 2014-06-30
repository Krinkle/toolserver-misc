<?php
/**
 * InterfaceFiles.php :: All-in-One file
 *
 * InterfaceFiles
 * Created on January 29th, 2011
 *
 * Copyright 2011 Krinkle <krinklemail@gmail.com>
 *
 * This file is released in the public domain=
 */

/**
 * Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

$c['revID'] = '0.0.1';
$c['revDate'] = '2011-01-29';
$c['title'] = 'InterfaceFiles';
$c['baseurl'] = $c['tshome'] .'/InterfaceFiles.php';

/**
 * Parameters
 * -------------------------------------------------
 */
$params['wiki'] = CacheAndDefault( getParamVar( 'wiki' ), 'commonswiki_p' );
$params['hideprotected'] = getParamBool( 'hideprotected' );

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
</head>
<body>
	<div id="page-wrap">

		<h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<small><em>Version <?=$c['revID']?> as uploaded on <?=$c['revDate']?></em><?php if($s['permalink']) echo ' | <a href="'.$s['permalink'].'">Permalink to this page</a>';?></small>
		<hr />
		<?php if ( $params['hideprotected'] ) {
			echo '<a href="' . generatePermalink( array_merge( $params, array( 'hideprotected' => false ) ) ) . '">Show protected files</a>';
		} else {
			echo '<a href="' . generatePermalink( array_merge( $params, array( 'hideprotected' => true ) ) ) . '">Hide protected files</a>';
		}
		?>

<?php
/**
 * Query customizations
 * -------------------------------------------------
 */
$whereClauses = array();
if ( $params['hideprotected'] ) {
	$whereClauses[] = "AND pr_page is NULL";
}


/**
 * Output
 * -------------------------------------------------
 */
connectRRServerByDBName( $params['wiki'] );
$wikiData = getWikiData( $params['wiki'] );

$dbQuery = " /* LIMIT:5 */ /* InterfaceFiles( {$params['wiki']} ) */
	SELECT
		messagepage.page_title as message_title,
		il_to,
		pr_page

	FROM page as messagepage
		JOIN imagelinks
			ON il_from = messagepage.page_id
		LEFT JOIN page as imagepage
			ON imagepage.page_title = il_to
			AND imagepage.page_namespace=6
		LEFT JOIN page_restrictions
			ON pr_page = imagepage.page_id

	WHERE messagepage.page_namespace=8

	" . implode( '  ', $whereClauses )  . "

	ORDER BY il_to ASC

	LIMIT 1000
	;
";

$dbReturn = mysql_query( $dbQuery, $dbConnect );

if ( !$dbReturn ) {
	die('db error' . mysql_error() );
}
$dbResult = mysql_fetch_all( $dbReturn );
krLog( krDump( $dbResult, KR_RETURN ) );

echo '<table class="wikitable v-top sortable">';
echo '<tr>
		<th class="unsortable">(thumb)</th>
		<th class="unsortable">image_title</th>
		<th class="unsortable">message_title</th>
		<th>locally protected ?</th>
	</tr>';

// rows
$convertedData = array();

foreach ( $dbResult as $row ) {
	$convertedData[$row['il_to']]['protected'] = is_null( $row['pr_page'] ) ? 'false' : 'true';
	$convertedData[$row['il_to']]['pages'][] = $row['message_title'];
}
foreach ( $convertedData as $image => $data ) {
	echo '<tr>';

	echo '<td><img src="' . $wikiData['url'] . '/wiki/Special:FilePath?width=100&file=' . rawurlencode( $image  )  . '" title="" alt=""/></td>';
	echo '<td>' . krCreateLink( getWikiLink( $wikiData, 'File:' . $image  ), $image ) . '</td>';
	echo '<td><div><ul>';
		foreach( $data['pages'] as $i => $page ) {
			if ( $i == 5 ) {
				echo '</ul></div>';
				echo '<div class="mw-collapsible mw-collapsed"><ul>';
			}
			echo '<li>' . krCreateLink( getWikiLink( $wikiData, 'MediaWiki:' . $page ), $page ) . '</li>';
		}
	echo '</ul></div></td>';
	echo '<td>' . $data['protected'] . '</td>';

	echo '</tr>';
}


echo '</table><p><em>Limited to 1000 imagelinks.</em></p>';


/**
 * Finish
 * -------------------------------------------------
 */
mysql_close( $dbConnect );
krLogFlush( KR_FLUSHLOG, KR_ESCAPEHTML, '<pre>', '</pre>' );
krLoadBottomCSS();
?>
<script src="sortable.js"></script>
<script>
jQuery( '.mw-collapsible' ).makeCollapsible();
</script>
</body>
</html>
