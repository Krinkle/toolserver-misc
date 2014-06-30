<?php
/**
 * CommonsMoveReview.php :: All-in-One file
 * Created on February 5th, 2011
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 * Configuration
 * -------------------------------------------------
 */
require_once 'CommonStuff.php';

$c['is_submit'] = false;
$c['revID'] = '0.2.1';
$c['revDate'] = '2011-02-06';
$c['title'] = 'CommonsMoveReview';
$c['baseurl'] = $c['tshome'] .'/CommonsMoveReview.php';

/**
 * Settings
 * -------------------------------------------------
 */
$s = array();


/**
 * Parameters
 * -------------------------------------------------
 */
$params['wikidb'] = getParamVar( 'wikidb' );
$params['category'] = getParamVar( 'category' );
$params['nav'] = getParamVar( 'nav' );
$params['data'] = getParamVar( 'data' );

// Check if all the required paramters are not-empty and in the correct type
if (	!empty( $params['wikidb'] ) && is_string( $params['wikidb'] )
	&&	!empty( $params['category'] ) && is_string( $params['category'] )
	) {
	$c['is_submit'] = true;
}

// Permalink
$s['permalink'] = generatePermalink( $params );

?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Krinkle | <?=$c['title']?></title>
	<link rel="stylesheet" href="main.css">
	<style>
	/* Layout (global css override) */
	body								{ overflow-x:hidden }
	#page-wrap							{ min-width:800px; width:100%; padding:0; margin:0 }
	form.colly input[type="text"],
	form.colly input[type="password"]	{ width:560px }
	/* CommonsMoveReview (local) */
	#nav-wrap							{ margin:0 auto; width:560px; text-align:center }
	#nav-prev, #nav-next				{ padding:10px; font-size:larger; float:left }
	#nav-next							{ float:right }
	#comparetable						{ width:100% }
	.title								{ font-weight:bold; text-align:center }
	textarea.raw						{ width:100%; height:350px; padding:5px 0px }
	iframe.render						{ width:100%; height:960px }
	img.thumblocal						{ float:left }
	img.thumbcommons					{ float:right }
	</style>
</head>
<body>
	<div id="page-wrap">
		<h1><a href="<?=$c['tshome']?>"><small>Krinkle</small></a> | <a href="<?=$c['baseurl']?>"><?=$c['title']?></a></h1>
		<small><em>Version <?=$c['revID']?> as uploaded on <?=$c['revDate']?></em><?php if($s['permalink']) echo ' | <a href="'.$s['permalink'].'">Permalink to this page</a>';?></small>
		<hr />

		<form class="colly ns" action="<?=$c['baseurl']?>#nav-wrap" method="get">
		<fieldset>

			<label for="wikidb">Source wiki:</label>
			<?php echo krGetAllWikiSelect( 'wikidb', $params['wikidb'], array( 'commonswiki_p' ) ); ?>
			<br />

			<label for="category">Source category:</label>
			<input type="text" name="category" id="category" value="<?php echo krEscapeHTML( $params['category'] ); ?>" />
			<span>Without "Category:"-prefix</span>

			<label></label>
			<input type="submit" nof value="Go" />
			<br />

		</fieldset>
		</form>

<?php
/**
 * Get the data
 * -------------------------------------------------
 */
if ( $c['is_submit'] ) :

	$wdataSource = getWikiData( $params['wikidb'] );
	$wdataCommons = getWikiData( 'commons' );

	// Request base
	$apiRequest = array(
		'action' => 'query',
		'list' => 'categorymembers',
		'cmtitle' => $params['category'],
		'cmlimit' => 1,	// overridden if nav is prev or next
						// stays 1 if nav is neither (ie. 'page 1')
		'cmprop' => 'title',
		'cmtype' => 'file',
	);
	// Finetune request based on our navigational status
	if ( $params['nav'] == 'next' && !empty( $params['data'] ) ) {
		$apiRequest['cmcontinue'] = $params['data'];
		$apiRequest['cmlimit'] = 2;
		$apiRequest['cmdir'] = 'asc';
	} elseif ( $params['nav'] == 'prev' && !empty( $params['data'] ) ) {
		$apiRequest['cmcontinue'] = $params['data'];
		$apiRequest['cmlimit'] = 2;
		$apiRequest['cmdir'] = 'desc';
	}

	$apiReturn = getAPIData( $wdataSource, $apiRequest );
	//krDump($apiReturn, true, '<pre>', '</pre>');
	$s['currFile'] = array();
	$continue = '';
	if ( @$apiReturn['query']['categorymembers'] ) {
		if ( $params['nav'] == 'next' ) {
			$s['currFile'] = array_pop( $apiReturn['query']['categorymembers'] );
		} else {
			$s['currFile'] = array_pop( $apiReturn['query']['categorymembers'] );
		}
	}
	if ( true /* @$apiReturn['query-continue']['categorymembers']['cmcontinue'] */ ) {
		// We can't use the continue since it only works forwards, not backwards
		// When using it for backwards (cmcontinue=cmcontinue&cmdir=desc) the result
		// will start with itself, that can be solved by setting limit to 2 and array_pop()
		// but if we then want to fo forward again we get the same file again
		// (two next>-clicks required) to actually get to 'next' from a 'prev'
		// A much easier way is to set make up the continue ourselfs:
		$continue = str_replace('File:', '', $s['currFile']['title'].'|');
		// And use limit=2 and grab the second one. This way navigation goes
		// perfect in both ways and never goes out of sync
		// (not even if the 'next' file gets deleted, uncategorized or sortkey changed
		// between visiting 'page 1' and going to 'page 2'. Since we're using the
		// current file as origin rather than a different one).
	}

/**
 * Build the table
 * -------------------------------------------------
 */
?>
	<p id="nav-wrap">
		<a id="nav-prev" href="<?php echo generatePermalink( array_merge( $params, array( 'nav' => 'prev', 'data' => $continue ) ) ); ?>#nav-wrap">&laquo; prev</a>
		&nbsp;
		<a id="nav-next" href="<?php echo generatePermalink( array_merge( $params, array( 'nav' => 'next', 'data' => $continue ) ) ); ?>#nav-wrap">next &raquo;</a>
	</p>
	<table class="ns v-top" id="comparetable">
	<tr>
		<th><?php echo $wdataSource['localdomain']; ?></th>
		<th>&nbsp;</th>
		<th><?php echo $wdataCommons['localdomain']; ?></th>
	</tr>
	<tr>
		<td><iframe class="render" src="<?php echo getWikiLink( $wdataSource, $s['currFile']['title'] ); ?>"></iframe></td>
		<td></td>
		<td><iframe class="render" src="<?php echo getWikiLink( $wdataCommons, $s['currFile']['title'] ); ?>"></iframe></td>
	</tr><?php /*
	<tr>
		<td><textarea class="raw"><?php echo krEscapeHTML( file_get_contents( getWikiLink( $wdataSource, $s['currFile']['title'], array( 'action' => 'raw' ) ) ) ); ?></textarea></td>
		<td></td>
		<td><textarea class="raw"><?php echo krEscapeHTML( file_get_contents( getWikiLink( $wdataCommons, $s['currFile']['title'], array( 'action' => 'raw' ) ) ) ); ?></textarea></td>
	</tr>
	*/ ?>
	</table>
<?php endif; ?>

	</div>
<?php
/**
 * Finish
 * -------------------------------------------------
 */
krLogFlush( KR_FLUSHLOG, KR_ESCAPEHTML, '<pre>', '</pre>');
?>
</body>
</html>
