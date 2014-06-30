$.fn.placeholder = function () {

	return this.each(function () {
		var placeholder, $input;

		// If the HTML5 placeholder attribute is supported, use it
		if (this.placeholder && document.createElement(this.tagName).placeholder !== undefined) {
			return;
		}

		placeholder = this.getAttribute('placeholder');
		$input = $(this);

		// Show initially, if empty
		if (this.value === '' || this.value === placeholder) {
			$input.addClass('placeholder').val(placeholder);
		}

		$input
			// Show on blur if empty
			.blur(function () {
				if (this.value === '') {
					this.value = placeholder;
					$input.addClass('placeholder');
				} else {
					$input.removeClass('placeholder');
				}
			})

			// Hide on focus
			.focus(function () {
				if ($input.hasClass('placeholder')) {
					this.value = '';
					$input.removeClass('placeholder');
				}
			});

		// Blank on submit -- prevents submitting with unintended value
		if (this.form) {
			$(this.form).submit(function () {
				// $input.trigger('focus'); would be problematic
				// because it actually focuses $input, leading
				// to nasty behavior in mobile browsers
				if ($input.hasClass('placeholder')) {
					$input
						.val('')
						.removeClass('placeholder');
				}
			});
		}

	});
};

// Untill $.fn.placeholder is no longer needed
// We need this one as well:
// A way to get a value (like $.fn.val) which
// returns '' if the value equals the placeholder
$.fn.valler = function (value) {

	// If the HTML5 placeholder attribute is supported, we don't need to do anything and can just
	// Call val() directly
	if (this.placeholder && document.createElement(this.tagName).placeholder !== undefined) {
		return $.fn.val.call(this, value);
	}

	// If no arguments, it's a getter: val()
	if (!arguments.length) {
		if (this.val() === this.attr('placeholder')) {
			return '';
		} else {
			return this.val();
		}

	// Arguments, it's a setter: val('value')
	} else {
		return $.fn.val.call(this, value);
	}


};

// From:
// svn.wikimedia.org/viewvc/mediawiki/trunk/phase3/resources/jquery/jquery.makeCollapsible.js?revision=79075&view=co
// Modifications: Removed calls to mw.*
/**
 * jQuery makeCollapsible
 * @author Krinkle <krinklemail@gmail.com>
 *
 * Dual license:
 * @license CC-BY 3.0 <creativecommons.org/licenses/by/3.0>
 * @license GPL2 <www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 */
$.fn.makeCollapsible=function(){return this.each(function(){var $that=$(this).addClass('mw-collapsible'),that=this,collapsetext=$(this).attr('data-collapsetext'),expandtext=$(this).attr('data-expandtext'),toggleElement=function($collapsible,action,$defaultToggle){if(!$collapsible.jquery){return;}
if(action!='expand'&&action!='collapse'){return;}
if(typeof $defaultToggle!=='undefined'&&!$defaultToggle.jquery){return;}
if(action=='collapse'){if($collapsible.is('table')){if($defaultToggle.jquery){$collapsible.find('>tbody>tr').not($defaultToggle.parent().parent()).stop(true,true).fadeOut();}else{$collapsible.find('>tbody>tr').stop(true,true).fadeOut();}}else if($collapsible.is('ul')||$collapsible.is('ol')){if($defaultToggle.jquery){$collapsible.find('> li').not($defaultToggle.parent()).stop(true,true).slideUp();}else{$collapsible.find('> li').stop(true,true).slideUp();}}else{var $collapsibleContent=$collapsible.find('> .mw-collapsible-content');if($collapsibleContent.size()){$collapsibleContent.slideUp();}else{if($collapsible.is('tr')||$collapsible.is('td')||$collapsible.is('th')){$collapsible.fadeOut();}else{$collapsible.slideUp();}}}}else{if($collapsible.is('table')){if($defaultToggle.jquery){$collapsible.find('>tbody>tr').not($defaultToggle.parent().parent()).stop(true,true).fadeIn();}else{$collapsible.find('>tbody>tr').stop(true,true).fadeIn();}}else if($collapsible.is('ul')||$collapsible.is('ol')){if($defaultToggle.jquery){$collapsible.find('> li').not($defaultToggle.parent()).stop(true,true).slideDown();}else{$collapsible.find('> li').stop(true,true).slideDown();}}else{var $collapsibleContent=$collapsible.find('> .mw-collapsible-content');if($collapsibleContent.size()){$collapsibleContent.slideDown();}else{if($collapsible.is('tr')||$collapsible.is('td')||$collapsible.is('th')){$collapsible.fadeIn();}else{$collapsible.slideDown();}}}}},toggleLinkDefault=function(that,e){var $that=$(that),$collapsible=$that.closest('.mw-collapsible.mw-made-collapsible').toggleClass('mw-collapsed');e.preventDefault();if(!$that.hasClass('mw-collapsible-toggle-collapsed')){$that.removeClass('mw-collapsible-toggle-expanded').addClass('mw-collapsible-toggle-collapsed');if($that.find('> a').size()){$that.find('> a').text(expandtext);}else{$that.text(expandtext);}
toggleElement($collapsible,'collapse',$that);}else{$that.removeClass('mw-collapsible-toggle-collapsed').addClass('mw-collapsible-toggle-expanded');if($that.find('> a').size()){$that.find('> a').text(collapsetext);}else{$that.text(collapsetext);}
toggleElement($collapsible,'expand',$that);}
return;},toggleLinkPremade=function(that,e){var $that=$(that),$collapsible=$that.closest('.mw-collapsible.mw-made-collapsible').toggleClass('mw-collapsed');e.preventDefault();if(!$that.hasClass('mw-collapsible-toggle-collapsed')){$that.removeClass('mw-collapsible-toggle-expanded').addClass('mw-collapsible-toggle-collapsed');toggleElement($collapsible,'collapse',$that);}else{$that.removeClass('mw-collapsible-toggle-collapsed').addClass('mw-collapsible-toggle-expanded');toggleElement($collapsible,'expand',$that);}
return;},toggleLinkCustom=function($that,e,$collapsible){if(e){e.preventDefault();}
var action=$collapsible.hasClass('mw-collapsed')?'expand':'collapse';$collapsible.toggleClass('mw-collapsed');toggleElement($collapsible,action,$that)};if(!collapsetext||collapsetext===''){collapsetext='Collapse';}
if(!expandtext||expandtext===''){expandtext='Expand';}
var $toggleLink=$('<a href="#">').text(collapsetext).wrap('<span class="mw-collapsible-toggle">').parent().prepend('&nbsp;[').append(']&nbsp;').bind('click.mw-collapse',function(e){toggleLinkDefault(this,e);});if($that.hasClass('mw-made-collapsible')){return;}else{$that.addClass('mw-made-collapsible');}
if($that.attr('id').indexOf('mw-customcollapsible-')===0){var thatId=$that.attr('id'),$customTogglers=$('.'+thatId.replace('mw-customcollapsible','mw-customtoggle'));if($customTogglers.size()){$customTogglers.bind('click.mw-collapse',function(e){toggleLinkCustom($(this),e,$that);});}
if($that.hasClass('mw-collapsed')){$that.removeClass('mw-collapsed');toggleLinkCustom($customTogglers,null,$that)}}else{if($that.is('table')){var $firstRowCells=$('tr:first th, tr:first td',that),$toggle=$firstRowCells.find('> .mw-collapsible-toggle');if(!$toggle.size()){$firstRowCells.eq(-1).prepend($toggleLink);}else{$toggleLink=$toggle.unbind('click.mw-collapse').bind('click.mw-collapse',function(e){toggleLinkPremade(this,e);});}}else if($that.is('ul')||$that.is('ol')){var $firstItem=$('li:first',$that),$toggle=$firstItem.find('> .mw-collapsible-toggle');if(!$toggle.size()){if($firstItem.attr('value')==''||$firstItem.attr('value')=='-1'){$firstItem.attr('value','1');}
$that.prepend($toggleLink.wrap('<li class="mw-collapsible-toggle-li">').parent());}else{$toggleLink=$toggle.unbind('click.mw-collapse').bind('click.mw-collapse',function(e){toggleLinkPremade(this,e);});}}else{if(!$that.find('> .mw-collapsible-content').size()){$that.wrapInner('<div class="mw-collapsible-content">');}
var $toggle=$that.find('> .mw-collapsible-toggle');if(!$toggle.size()){$that.prepend($toggleLink);}else{$toggleLink=$toggle.unbind('click.mw-collapse').bind('click.mw-collapse',function(e){toggleLinkPremade(this,e);});}}}
if($that.hasClass('mw-collapsed')&&$that.attr('id').indexOf('mw-customcollapsible-')!==0){$that.removeClass('mw-collapsed');$toggleLink.click();}});};

$(function () {
	// For JavaScript specific CSS
	$('body').addClass('JS');

	// Emulate placeholder if not supported by browser
	if (document.createElement('input').placeholder === undefined) {
		$('input[placeholder]').placeholder();
	}

	// Collapser
	$('.collapseWrapToggle').live('click', function (e) {

		e.preventDefault();
		if ($(this).parent().hasClass('collapsed')) {
			$(this)
				.html('Hide revisions &uarr;')
				.parent()
					.addClass('expanded')
					.removeClass('collapsed')
				.find('.collapseInner')
					.slideDown();
		} else {
			$(this)
				.html('Show revisions &darr;')
				.parent()
					.addClass('collapsed')
					.removeClass('expanded')
				.find('.collapseInner')
					.slideUp();
		}

	});
});