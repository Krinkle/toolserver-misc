<?php
/**
 * SiteMatrix Checklist
 * Created on January 2, 2012
 *
 * @author Krinkle <krinklemail@gmail.com>, 2012–2014
 * @license http://krinkle.mit-license.org/
 */

/**
 * Configuration
 * -------------------------------------------------
 */
// BaseTool
require_once '/home/krinkle/common/InitTool.php';
// Localization (todo)
require_once KR_TSINT_START_INC;

$toolConfig = array(
	'displayTitle'	=> 'SiteMatrix Checklist',
	'simplePath'	=> '/SiteMatrixChecklist/',
	'revisionId'	=> '0.2.0',
	'revisionDate'	=> '2012-03-08',
	'styles'		=> array(
		'main.css',
	),
	'scripts'		=> array(
		'//bits.wikimedia.org/en.wikipedia.org/load.php?debug=false&modules=startup&only=scripts',
	),
);

$Tool = BaseTool::newFromArray( $toolConfig );

$Tool->doHtmlHead();
$Tool->doStartBodyWrapper();

/**
 * Settings
 * -------------------------------------------------
 */
$Params = array(
	'list' => $kgReq->getVal( 'list' ),

);

# Defines $lists
require_once __DIR__ . '/lists.php';

$toolSettings = array(
	'lists' => $lists,
	'activeList' => isset( $Params['list'] ) && isset( $lists[$Params['list']] ) ? $lists[$Params['list']] : null,
);


/**
 * Gather data
 * -------------------------------------------------
 */
$wikiData = kfGetWikiDataFromDBName( 'metawiki_p' );
$apiQuery = array(
	'action' => 'sitematrix',
);
$apiData = kfQueryWMFAPI( $wikiData, $apiQuery );

if ( $toolSettings['activeList'] && isset( $toolSettings['activeList']['progress-external'] ) ) {
	$externalProgressRaw = file_get_contents( $toolSettings['activeList']['progress-external'] );
	$lines = explode( "\n", $externalProgressRaw );
	foreach ( $lines as $line ) {
		if ( strlen( $line ) > 0 && $line[0] === '*' ) {
			// '* aawiki | Krinkle | done' --> array( ' aawiki ', ' Krinkle', ' done' )
			$lineParts = explode( '|', substr( $line, 1 ), 3 );
			if ( count( $lineParts ) === 3 ) {
				$toolSettings['activeList']['progress'][trim($lineParts[0])] = array(
					'user' => trim($lineParts[1]),
					'status' => trim($lineParts[2]),
				);
			}
		}
	}
}

/**
 * Output
 * -------------------------------------------------
 */

## Navigation
$navItems = array();
foreach( $toolSettings['lists'] as $listKey => $listData ) {
	$navItems[] =
		kfTag( $listKey, 'a', array( 'href' => $Tool->generatePermalink(array('list' => $listKey)) ) )
		. ' ('
		. kfTag( 'info', 'a', array( 'href' => $listData['info'], 'target' => '_bank', 'class' => 'external' ) )
		. ')';
}
$nav = 'Lists: ' . implode( ' &bullet; ', $navItems ) . '<hr/>';
$Tool->addHtml( $nav );

## Table

$siteMatrix = $apiData['sitematrix'];
// Wikis are grouped per language code
// The last group is 'specials'
/*
	"286": {
		"code": "zu",
		"name": "isiZulu",
		"site": [
			{
				"url": "http://zu.wikipedia.org",
				"code": "wiki"
			},
			{
				"url": "http://zu.wiktionary.org",
				"code": "wiktionary"
			},
			{
				"url": "http://zu.wikibooks.org",
				"code": "wikibooks"
			}
		]
	},
	"specials": [
		{
			"url": "http://advisory.wikimedia.org",
			"code": "advisory"
		},
*/
$tableHTML = '<table class="wikitable mw-sortable" style="width: 100%;">';
$tableHTML .= '<thead><tr><th>Lang</th><th>Project</th><th>Location</th>'
	. (
	$toolSettings['activeList']
		? '<th>Status</th><th>Sign</th>'
		: ''
	) . '</tr></thead>';
$tableHTML .= '</body>';
$tableHTML .= "\n";
$closedWikis = 0;
$wikisDone = 0;
$openWikisDone = 0;
foreach ( $siteMatrix as $groupKey => $groupData ) {

	// Skip count property
	if ( $groupKey !== 'count' ) {

		if ( $groupKey === 'specials' ) {
			$siteArray = $groupData;
			$tableHTML .=	'<tr class="sortbottom" id="special-projects"><th colspan="5">special projects (' . $groupKey . ')</th></tr>';

		} else {
			$siteArray = $groupData['site'];
			$tableHTML .=	'<tr class="sortbottom" id="' . $groupData['code'] . '-projects"><th colspan="5">' . $groupData['code'] . ' projects (' . $groupData['name']  .  ')</th></tr>';
		}

		// Loop over them
		foreach ( $siteArray as $site ) {

			$priv = array_key_exists( 'private', $site ) ? ' <strong>(private)</strong>' : '';
			$langCode = $groupKey === 'specials' ? $site['code'] : $groupData['code'];
			$projectName = $groupKey === 'specials' ? $groupKey : $site['code'];
			// WMF supports https everywhere, turn canonical url (provided by the API)
			// into a protocol-relative url so that the reader will follow this url
			// relative to the url this document is viewed from
			// (https://toolserver.org or http://toolserver.org)
			$protocolRelativeUrl = str_replace( 'http://', '//', $site['url'] );
			$hostname = str_replace( 'http://', '', str_replace( 'https://', '', $site['url'] ) );

			$tableHTML .=	'<tr>';
			$tableHTML .= 		'<td>' . $langCode . '</td>';
			$tableHTML .= 		'<td>' . $projectName . '</td>';
			$tableHTML .= 		'<td><a target="_blank" href="' . htmlspecialchars( $protocolRelativeUrl ) . '">'
									. htmlspecialchars( $hostname )
									. '</a>' . $priv
								. '</td>';

			// Status
			if ( $toolSettings['activeList'] ) {
				$progressData = isset( $toolSettings['activeList']['progress'][$site['dbname']] )
					? $toolSettings['activeList']['progress'][$site['dbname']]
					: null;

				$classes = array();
				$label = array();
				$sign = '';
				if ( array_key_exists( 'closed', $site ) ) {
					$closedWikis += 1;

					$classes[] = 'smc-closed';
					$label[] = 'closed';
				}
				if ( $progressData !== null ) {
					$wikisDone += 1;
					if ( !array_key_exists( 'closed', $site ) ) {
						$openWikisDone += 1;
					}

					switch ( $progressData['status'] ) {
						case 'done':
							$label[] = 'OK';
							$classes[] = 'smc-stat-ok';
							break;

						case 'progress':
							$label[] = 'PROGRESS';
							$classes[] = 'smc-stat-later';
							break;

						case 'note':
							$label[] = '(<a href="'.htmlspecialchars($toolSettings['activeList']['talk']).'" target="_blank">note</a>)';
							$classes[] = 'smc-stat-later';
							break;

						default:
							$label[] = '?';
							break;
					}
					$sign = $progressData['user'];
				}
				if ( !sizeof( $label ) ) {
					$label[] = 'unchecked';
				}

				$tableHTML .= 		'<td class="' . implode( ' ', $classes ) . '">' . implode( ' / ', $label ) . '</td>';
				$tableHTML .= 		'<td class="comment">' . htmlspecialchars( substr( $sign, 0, 100 ) ) . '</td>';
			}
			$tableHTML .= 	'</tr>';
			$tableHTML .= 	"\n";
		}

	}
}

$tableHTML .= '</tbody>';
$tableHTML .= '</table>';

$openWikis = intval( $siteMatrix['count'] ) - $closedWikis;

$stats = array();
$stats[] = ( $openWikis + $closedWikis ) .' wikis';
$stats[] = $openWikis . ' open wikis';

if ( $toolSettings['activeList'] ) {
	$stats[] = $wikisDone . ' wikis done';
	$stats[] = $openWikisDone . ' open wikis done';
	$stats[] = round( ($openWikisDone/$openWikis)*100, 2, PHP_ROUND_HALF_DOWN ) . '% of open wikis done';
}

$tableHTML .= '<em>' . implode( '; ' , $stats ) . '.</em>';

$Tool->addHtml( $tableHTML );

/**
 * Close up
 * -------------------------------------------------
 */
$Tool->addOut( '<script>
mw.loader.using(["jquery.tablesorter", "mediawiki.util"], function () {
	$(".mw-sortable").tablesorter();
});
</script>' );
$Tool->flushMainOutput();

