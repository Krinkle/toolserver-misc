<?php
/**
 * index.php :: Main front-end
 *
 * Commons Creator Lister
 * Created on December 4th, 2010
 *
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */
/*
TODO:
- i18n
- pagination for toolserver-frontend
- "more..." for JSON-format (introduce an offset-parameter and a json-more=1 parameter, if the latter is set only tablerows are returned, which the json-application can append to the table
- Support for {{Information and {{Painting that don't end in "=\n}}". Fallback to regular "\n}}"
*/

/**
 *  Configuration
 * -------------------------------------------------
 */
if ( $_GET['format'] == 'json' ) {
	$c['hide_html'] = true;
}
require_once 'header.php';

if ( empty( $settings['wikidb'] ) || empty( $settings['transclude-namespace'] ) || empty( $settings['transclude-name'] ) ) {
	krDie( 'Invalid or missing parameters. <a href="input.php">Edit settings</a>' );
}



/**
 *  Database connection
 * -------------------------------------------------
 */
$toolserver_mycnf = parse_ini_file( '/home/' . get_current_user() . '/.my.cnf' );
$dbHost = krStripStr( str_replace( '_', '-', $settings['wikidb'] ) ) . '.rrdb.toolserver.org';
krLog( 'dbHost: ' . $dbHost );
$dbConnect = mysql_connect( $dbHost, $toolserver_mycnf['user'], $toolserver_mycnf['password'] );
if ( !$dbConnect ) {
	die( 'dbConnect: ERROR: <br />' . mysql_error() );
} else {
	krLog( 'dbConnect: OK' );
}
$dbSelect = mysql_select_db( sql_clean( $settings['wikidb'] ), $dbConnect );
if ( !$dbSelect ) {
	die( 'dbSelect: ERROR; <br />' . mysql_error() );
} else {
	krLog( 'dbSelect: OK' );
}


/**
 *  Query
 * -------------------------------------------------
 */
$dbQuery = "
SELECT
	tl_namespace,
	tl_title,
	page_namespace,
	page_title,
	page_is_redirect,
	page_id,
	page_touched

FROM templatelinks

JOIN page ON page.page_id=templatelinks.tl_from

WHERE tl_namespace=" . sql_clean( $settings['transclude-namespace'] ) . "
AND tl_title='" . sql_clean( str_replace( ' ', '_', ucfirst( $settings['transclude-name'] ) ) ) . "'
AND page_namespace=6
AND page_is_redirect=0

ORDER BY page_touched desc
LIMIT 100
;";
krLog( $dbQuery );

$dbResult = mysql_query( $dbQuery, $dbConnect ); unset( $dbQuery );

if ( !!$dbResult ) {

	$dbResult = mysql_fetch_all( $dbResult );

	// Log a sample
	krLog( print_r( $dbResult[3], true ) );

} else {
	krDie( 'dbQuery: ERROR; <br />' . mysql_error() );
}



/**
 *  Output
 * -------------------------------------------------
 */
// Start of Table
if ( $_GET['format'] !== 'json' ) {

	$table_code =  '<table id="works-table" class="wikitable sortable">'
	.					'<caption>Files calling "' . krEscapeHTML( namespacename( $settings['transclude-namespace'] ) . ':' .$settings['transclude-name'] ) . '" on <code>' . krEscapeHTML( $settings['wikidb'] ) . '</code></caption>'
	.					'<tbody><tr><th thumb>Thumbnail</th><th title>Title</th><th e>E</th></tr>';

} else {

	$table_code .= '<p style="text-align:right">debug: CommonsCreatorLister by [https://wiki.toolserver.org/view/User:Krinkle ~krinkle] (r21)<br /><small>Query executed at ' . date( 'r' ) . '</small></p> __NOEDITSECTION__ {{TOCright}}
{{Worklist start}}';

	// Holds titles to pages that we could not extract a template (like {{Artwork}}) from
	$malformed_filepages = array();

}

// Table rows
foreach ( $dbResult as $result ) {

	$title = explode( '.', $result['page_title'] );
	array_pop( $title );
	$title = implode( '.', $title );
	$title = str_replace( '_', ' ', $title );

	$url = 'http://commons.wikimedia.org/?title=File:' . rawurlencode( $result['page_title'] );

	if ( $_GET['format'] !== 'json' ) {

		$table_code .=
		 	'<tr class="work-entry">'
		 	.	'<td thumb><img src="' . krEscapeHTML( commons_thumb_url( $result['page_title'], '100' ) ) . '" alt="" width="100" /></td>'
			.	'<td title><a target="_blank" href="' . krEscapeHTML( $url ) . '">' . krEscapeHTML( $title ) . '</a></td>'
			.	'<td class="work-entry-e"><a target="_blank" href="' . krEscapeHTML( $url ) . '&amp;action=edit">+/-</a></td>'
			.'</tr>';

	} else {

		$wikitext = file_get_contents( $url . '&action=raw' );

		// Below is a stack of if-elses
		// attempting to cut out the needed template
		// First we check for =\n}} to avoid matching the }} that closes for example {{mld}} or {{en|}}
		// .. which matches "other versions=\n}}"
		// If none of those are, we check for </gallery>\n}}
		// and finally we check to any }} on a new line which supposedly closes the Artwork template

		// Cut out only {{Artwork|...=\n}}
		$pattern = '/\{\{' . preg_quote('artwork') . '(.*)=\s*\n' . preg_quote('}}') . '/is';
		preg_match_all( $pattern, $wikitext, $output );
		if ( !empty( $output[0] ) && !empty( $output[0][0] ) ) {
			// Replace "{{Artwork" with "{{Artwork/layout/table for creator" and add Filename=
			$output = str_ireplace( '{{Artwork', '{{Artwork/layout/table for creator' . "\n|Filename=" . $result['page_title'], $output[0][0] );

		} else {

			// Cut out only {{Painting|...=\n}}
			$pattern = '/\{\{' . preg_quote('painting') . '(.*)=\s*\n' . preg_quote('}}') . '/is';
			preg_match_all( $pattern, $wikitext, $output );
			if ( !empty( $output[0] ) && !empty( $output[0][0] ) ) {
				// Replace "{{Painting" with "{{Artwork/layout/table for creator" and add Filename=
				$output = str_ireplace( '{{Painting', '{{Artwork/layout/table for creator' . "\n|Filename=" . $result['page_title'], $output[0][0] );

			} else {

				// Cut out only {{Information|...=\n}}
				$pattern = '/\{\{' . preg_quote('information') . '(.*)=\s*\n' . preg_quote('}}') . '/is';
				preg_match_all( $pattern, $wikitext, $output );
				if ( !empty( $output[0] ) && !empty( $output[0][0] ) ) {
					// Replace "{{Information" with "{{Artwork/layout/table for creator" and add Filename=
					$output = str_ireplace( '{{Information', '{{Artwork/layout/table for creator' . "\n|Filename=" . $result['page_title'], $output[0][0] );

				// If no usable template detected, empty string fallback
				// + add to bottom of page for reference
				} else {

					// Cut out only {{Artwork|...</gallery>\n}}
					$pattern = '/\{\{' . preg_quote('artwork') . '(.*?)<\/gallery\>\n}}/is';
					preg_match_all( $pattern, $wikitext, $output );
					if ( !empty( $output[0] ) && !empty( $output[0][0] ) ) {
						// Replace "{{Artwork" with "{{Artwork/layout/table for creator" and add Filename=
						$output = str_ireplace( '{{Artwork', '{{Artwork/layout/table for creator' . "\n|Filename=" . $result['page_title'], $output[0][0] );

					// If no usable template detected, empty string fallback
					// + add to bottom of page for reference
					} else {

						// Cut out only {{Artwork|...}}
						$pattern = '/\{\{' . preg_quote('artwork') . '(.*?)\n' . preg_quote('}}') . '/is';
						preg_match_all( $pattern, $wikitext, $output );
						if ( !empty( $output[0] ) && !empty( $output[0][0] ) ) {
							// Replace "{{Artwork" with "{{Artwork/layout/table for creator" and add Filename=
							$output = str_ireplace( '{{Artwork', '{{Artwork/layout/table for creator' . "\n|Filename=" . $result['page_title'], $output[0][0] );

						// If no usable template detected, empty string fallback
						// + add to bottom of page for reference
						} else {
							$output = '';
							$malformed_filepages[] = $result['page_title'];
						}
					}
				}
			}
		}

		$table_code .= "\n" . $output . "\n";

	}

}

// End of table
if ( $_GET['format'] !== 'json' ) {

	$table_code .= '</tbody></table>';

} else {

	$table_code .= '{{Worklist end}}
<references />'; // Just in case there were some <ref> tags. Could be stripped, but since <references /> simply returns empty if there are none we might as well make use of it

	// Create a heading for additional files that couldn't be parsed correctly
	// These files have either no Artwork, Painting or Information template or were otherwise different than 'normal'
	if ( !empty( $malformed_filepages ) ) {
	$table_code .= "\n== More files ==\n<table style='width:100%' class='wikitable plainlinks'><tr><th>Thumbnail</th><th>Title</th><th>E&nbsp;&nbsp;</th></tr>";

	foreach ( $malformed_filepages as $filepage ) {
		$table_code .=
			'<tr>'
		 	.	'<td>[[File:' . $filepage . '|100px]]</td>'
		 	.	'<td>[[:File:' . $filepage . ']]</td>'
			.	'<td>[http://commons.wikimedia.org/?title=File:' . rawurlencode( $filepage ) . '&action=edit +/-]</td>'
			.'</tr>';
	}
	$table_code .= '
</table>';

	}

}

// Return to front-end
if ( $_GET['format'] == 'json' ) {

	header( 'Content-Type: text/javascript' );

	// Parse wikitext
	$postdata = http_build_query(
	    array(
	        'action' => 'parse',
	        'text' => $table_code,
	        'title' => !empty( $_GET['wgPageName'] ) ? $_GET['wgPageName'] : $settings['transclude-name'],
	        'format' => 'php',
	        'uselang' => !empty( $_GET['wgUserLanguage'] ) ? $_GET['wgUserLanguage'] : 'en'
	    )
	);
	$opts = array('http' =>
	    array(
	        'method'  => 'POST',
	        'header'  => 'Content-type: application/x-www-form-urlencoded',
	        'content' => $postdata
	    )
	);
	$context  = stream_context_create( $opts );
	$result = file_get_contents( 'http://commons.wikimedia.org/w/api.php', false, $context );
	$result = unserialize( $result );
	$result = $result['parse']['text']['*'];

	// Echo javascript callback
	$data = array( 'table' => $result, 'rows' => count( $dbResult ) );
	echo $_GET['callback'] . '(' . json_encode( $data ) . ');';

	die;

} else {

	echo '<h3 id="works">Works</h3>' . $table_code;

}

require_once 'footer.php';
