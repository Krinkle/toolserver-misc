/*
 * Tool functions
 */

jQuery.extend({
	escapeRE : function( str ) {
		return str.replace ( /([\\{}()|.?*+^$\[\]])/g, "\\$1" );
	}
});

function getParamValue( param, url ) {
	url = url ? url : document.location.href;
	// Get last match, stop at hash
	var re = new RegExp( '[^#]*[&?]' + $.escapeRE( param ) + '=([^&#]*)' );
	var m = re.exec( url );
	if ( m && m.length > 1 ) {
		return decodeURIComponent( m[1] );
	}
	return null;
}

/**
 * Load all internal links with AJAX
 *
 * @author Original code written by Krinkle <krinklemail@gmail.com>
 * on December 24, 2010.
 * @source http://krinkle-tools.grizzdesign.nl/mw-allajax.js
 * @version 0.3.0 (2010-12-24)
 * @license Released in the public domain by Krinkle.
 */
jQuery( document ).ready( function(){

	// Add animator icon
	$( '#p-search' ).after( '<img id="mw-allajax-loader" style="display:none" src="' + wgServer + wgScriptPath + '/skins/common/images/ajax-loader.gif' + '" />' );

	// Add loader-wrapper
	$( '#content' ).wrap( '<div id="ajax-content">' );

	// Watch all anchor tags on the page
	$( 'a' ).live( 'click', function(e){

		var	$that = $(this),
			that = this;

		// If the href attribute is not a valid link, do nothing
		if ( !this.href || $that.attr('href') == '' || $that.attr('href').indexOf('#') != -1
			 || $that.attr('href').indexOf('javascript:') != -1 ) {
			return;
		}
		// If the link doesn't start with wgServer, do nothing (we can't load in external content, not allowed)
		if ( this.href.indexOf( wgServer + wgScript ) !== 0 ) {
			return;
		}
		// Some url patterns require extra javascript, since we can only load in raw pages, we exclude the following
		if ( this.href.indexOf( 'action=edit' ) != -1 // Edit toolbar
			|| this.href.indexOf( 'redlink=1' ) != -1 // 404 error is ignored by javascript
			|| this.href.indexOf( 'Special:Preferences' ) != -1 // javascript tabs on Preferences
			|| this.href.indexOf( 'action=purge' ) != -1 // Purge.. ofcourse
			) {
			return;
		}
		//

		// Prevent the default action (ie. browser refreshing page to differrent url)
		e.preventDefault();

		// Get the ajax icon
		var $loaderIcon = $( '#mw-allajax-loader' );

		// Show it
		$loaderIcon.fadeIn();

		// AJAX load() the new page into the memory
		$( '#ajax-content' ).load( $that.attr('href') + ' #content', function(data){ // Replace #bodyContent

			/* Deal with the title */

			// Extract it
			var title = $( '#firstHeading' ).text();

			// Not empty ?
			if ( title && title != '' ) {
				// If it's the main page, don't put "Main Page" in the title
				if ( title == 'Main Page' ) {
					document.title = wgSiteName;
					$( 'body' ).addClass( 'page-Main_Page' );

				// In other cases, but it in the title
				} else {
					document.title = title + ' - ' + wgSiteName;
					$( 'body' ).removeClass( 'page-Main_Page' );
				}
			// Else leave it open
			} else {
				document.title = wgSiteName;
				$( 'body' ).removeClass( 'page-Main_Page' );
			}

			/* Update actual content */

			// Update tabs on the left side (#p-namespaces, #p-variants)
			$( '#left-navigation' ).html(
				$(data).find( '#left-navigation' ).html()
			);
			// Update only the view and cactions elements in #right-navigation (ie. not #p-search)
			$( '#p-views' ).html(
				$(data).find( '#p-views' ).html()
			);
			$( '#p-cactions' ).html(
				$(data).find( '#p-cactions' ).html()
			);
			// We're done, hide the loader
			$loaderIcon.fadeOut();
		} );

	} );
} );
