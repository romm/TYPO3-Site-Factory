"use strict";

/** @namespace SiteFactory */

/***************************************************************************
 * Managing fadeout elements. These are tall texts that are partially hidden.
 * When clicking on the "plus" button, it will show the entire text.
 ***************************************************************************/
SiteFactory.FadeOut = {
	initialize: function() {
		jQuery(document).ready(function() {
			// Initial process.
			SiteFactory.FadeOut.checkFadeoutElementsHeight();

			// Processing on window's resizing.
			window.onresize = function() {
				SiteFactory.FadeOut.checkFadeoutElementsHeight();
			};

			/**
			 * When clicking on a fadeout toggle link, we toggle the fadeout element's
			 * displaying.
			 */
			jQuery('.fadeout-area .fadeout-toggle').on('click', function(e) {
				e.preventDefault();

				SiteFactory.FadeOut.toggleFadeOut(jQuery(this));
			});
		});
	},

	/**
	 * This function will check if a fadeout element's height is smaller than its
	 * "data-height" attribute. If so, the fadeout is set.
	 */
	checkFadeoutElementsHeight: function() {
		jQuery('.fadeout-container').each(function() {
			var fadeoutArea = jQuery('.fadeout-toggle[href="#' + jQuery(this).attr('id') + '"]').parents('.fadeout-area');

			if (jQuery(this).hasClass('fadeout-extended')) {
				jQuery(this).css('height', 'initial');
			}

			// Fadeout is set.
			if (jQuery(this).attr('data-height') < jQuery(this).find('.fadeout-content').height()) {
				if (!jQuery(this).hasClass('fadeout-extended')) {
					jQuery(this).css('height', jQuery(this).attr('data-height'));

					fadeoutArea.find('.fadeout-background').show();
					SiteFactory.FadeOut.toggleFadeOutLabel(fadeoutArea.find('.fadeout-toggle'), false);
				}
			}
			// Fadeout is unset.
			else {
				jQuery(this).css('height', 'initial');
				jQuery(this).removeClass('fadeout-extended');

				fadeoutArea.hide();
			}
		})
	},

	/**
	 * This function will toggle the fadeout link's label and icon depending
	 * on if it's extended or not.
	 */
	toggleFadeOutLabel: function(elem, extend) {
		var label = elem.find('.fadeout-toggle-label');
		var icon = elem.find('.fadeout-toggle-icon');

		// Click on the "plus".
		if (extend) {
			label.html(elem.attr('data-hide-label'));
			icon.removeClass('glyphicon-plus');
			icon.addClass('glyphicon-minus');
		}
		// Click on the "minus".
		else {
			icon.removeClass('glyphicon-minus');
			icon.addClass('glyphicon-plus');
			if (elem.attr('data-show-label')) {
				label.html(elem.attr('data-show-label'));
			}
		}
	},

	toggleFadeOut: function(elem) {
		// Cancel process if the fadeout is toggling.
		if (elem.hasClass('toggling')) return;
		elem.addClass('toggling');

		var target = jQuery(elem.attr('href'));
		var height = target.find('.fadeout-content').height();

		// Process fadeout extending.
		if (!target.hasClass('fadeout-extended')) {
			target.addClass('fadeout-extended');

			// Hiding fadeout's background.
			elem.parents('.fadeout-area').addClass('fadeout-disabled');

			// Toggling label.
			if (elem.attr('data-hide-label')) {
				if (!elem.attr('data-show-label'))
					elem.attr('data-show-label', elem.find('.fadeout-toggle-label').html());

				SiteFactory.FadeOut.toggleFadeOutLabel(elem, true);
			}
		}
		// Cancel fadeout extending.
		else {
			height = target.attr('data-height');
			target.removeClass('fadeout-extended');

			// Show fadeout extending.
			elem.parents('.fadeout-area').removeClass('fadeout-disabled');

			// Toggling label.
			SiteFactory.FadeOut.toggleFadeOutLabel(elem, false);
		}

		// Processing animation.
		target.animate(
			{ height: height },
			500,
			function() { elem.removeClass('toggling'); }
		);
	}
};

SiteFactory.FadeOut.initialize();