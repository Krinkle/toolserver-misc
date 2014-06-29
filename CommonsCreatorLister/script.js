/**
 * script.js :: Get data for Commons
 * @revision: 22 (2014-06-29)
 *
 * Created on December 4th, 2010
 * @stats [[File:Krinkle_CommonsCreatorLister.js]]
 *
 * @package CommonsCreatorLister
 * @author Krinkle <krinklemail@gmail.com>, 2010–2014
 * @license http://krinkle.mit-license.org/
 */
jQuery( function ( $ ) {

	// Only load on Creator: ... /works pages when viewing or purging the page
	if ( wgCanonicalNamespace == 'Creator'
	  && wgTitle.indexOf( '/works' ) == wgTitle.length - '/works'.length
	  && ( wgAction == 'view' || wgAction == 'purge' ) ) {

		// Clean up layout
		appendCSS( '#ca-edit,#ca-history{display:none}');
		$( '#ca-nstab-creator' ).removeClass( 'new' );

		// Get page name of base page
		var wgBasePageName = wgTitle.substr( 0, wgTitle.indexOf( '/works' ) );

		// Show message while we wait for the toolserver
		$( '#bodyContent' ).html( '<p style="text-align:center"><img src="//upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif" width="32" height="32" alt="" title="Loading" /><br /><br />Loading...</p>' );

		// Get data from toolserver
		$.getJSON( '//toolserver.org/~krinkle/CommonsCreatorLister/?wikidb=commonswiki_p&transclude-namespace=100&transclude-name=' + encodeURIComponent( wgBasePageName ) + '&wgPageName=' + encodeURIComponent( wgPageName ) + '&wgUserLanguage=' + encodeURIComponent( wgUserLanguage ) + '&format=json&callback=?', function ( response ) {

			// Show message while we wait for DOM to render
			$( '#bodyContent' ).html( '<p style="text-align:center"><img src="//upload.wikimedia.org/wikipedia/commons/d/de/Ajax-loader.gif" width="32" height="32" alt="" title="Building page" /><br /><br />Building page...</p>' );

			$( '#bodyContent' ).html( response.table );

			// Currently the tool has a hardcoded limit of 100 during initial testing
			// If it performs well and is optimized it can be raised
			// IDEA: Create an AJAX-more button on the bottom that will load more table rows with an offset of 100, 200, 300 etc.
			if ( response.rows > 99 ) {
				// Show message if it was limited
				$( '#bodyContent' ).append( '<p><em>Results limited to 100 rows</em></p>' );
			}

		} );
	}
} );
