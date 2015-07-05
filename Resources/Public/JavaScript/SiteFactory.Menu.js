"use strict";

/** @namespace SiteFactory */

/**
 * @typedef	{SiteFactory.Form}	SiteFactoryForm
 * @param	{SiteFactoryForm}	formElement
 * @param	{Object}			menuElement
 */
SiteFactory.Menu = function(formElement, menuElement) {
	/**
	 * Contains the form bound to this menu.
	 *
	 * @type {SiteFactoryForm}
	 */
	this.formElement	= formElement;

	this.element		= menuElement;

	var localMenu = this;

	this.refreshErrorsMenu = function () {
		var errorsCounter = 0;
		var fields = this.formElement.getFields();
		var errorsContainer = this.findElement('.counter-errors .errors-container');
		errorsContainer.removeClass('has-error');
		errorsContainer.html('');

		// Looping on fields.
		for (var key in fields) {
			if (fields.hasOwnProperty(key)) {
				// If the current field has at least one error.
				if (fields[key].getErrors().length > 0) {
					errorsContainer.addClass('has-error');
					this.findElement('.counter-errors').removeClass('hidden');

					// We list all the errors.
					var containerElem = document.createElement('div');
					var errorTitleElem = document.createElement('strong');

					errorTitleElem.appendChild(document.createTextNode(fields[key].label));
					containerElem.appendChild(errorTitleElem);

					var errorsListElem = document.createElement('ul');

					for (var i = 0; i < fields[key].getErrors().length; i++) {
						errorsCounter++;
						var errorElem = document.createElement('li');
						errorElem.appendChild(document.createTextNode(fields[key].getErrors()[i]));

						errorsListElem.appendChild(errorElem);
					}

					containerElem.appendChild(errorsListElem);

					errorsContainer.append(containerElem);
				}
			}
		}


		// Managing the messages in the static menu.
		var errorElements = this.findElement('.has-error');
		var submitButton = this.findElement('.submit-button button');
		if (errorElements.length == 0) {
			this.findElement('.info-success').removeClass('hidden');
			this.findElement('.counter-errors').addClass('hidden');
			submitButton.removeClass('btn-default');
			submitButton.removeClass('btn-danger');
			submitButton.addClass('btn-success');
		}
		else {
			this.findElement('.info-success').addClass('hidden');
			submitButton.removeClass('btn-default');
			submitButton.removeClass('btn-success');
			submitButton.addClass('btn-danger');

			// Label (errors count).
			var counterLabelElem = this.findElement('.counter-errors .counter-label');
			var counterLabel = (errorsCounter == 1)
				? counterLabelElem.attr('data-default-label-single')
				: counterLabelElem.attr('data-default-label-multiple');
			counterLabel = counterLabel.replace('%s', errorsCounter);
			counterLabelElem.html(counterLabel);
		}
	};

	$(document).ready(function() {
		var menu = localMenu.findElement('.fixed-menu > *');

		/*
		 * This functions take care of the right menu's responsive behaviour. When
		 * the windows is smaller than 768px, the menu disappears and is accessible
		 * via a "menu button".
		 */
		localMenu.findElement('.menu-btn-sm').click(function() {
			var glyph = $(this).find('.glyphicon');
			menu.toggle(100, function() {
				if (menu.is(':visible') == true) {
					glyph.removeClass('glyphicon-chevron-left');
					glyph.addClass('glyphicon-chevron-right');
				}
				else {
					glyph.removeClass('glyphicon-chevron-right');
					glyph.addClass('glyphicon-chevron-left');
				}
			});
		});

		$(window).resize(function() {
			if ($(this).width() >= 768) {
				var menuButton = localMenu.findElement('.menu-btn-sm');
				var glyph = menuButton.find('.glyphicon');

				menu.show();
				glyph.removeClass('glyphicon-chevron-left');
				glyph.addClass('glyphicon-chevron-right');
			}
		});
	});

	this.findElement = function(selector) {
		return $(this.element).find(selector);
	};
};