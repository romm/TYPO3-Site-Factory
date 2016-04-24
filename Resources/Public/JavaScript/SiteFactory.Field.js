"use strict";

/** @namespace SiteFactory */

SiteFactory.Field = function(formElement, fieldElement) {
	this.formElement		= formElement;
	this.element			= fieldElement;
	this.name				= fieldElement.attr('data-name');
	this.type				= fieldElement.attr('data-type');
	this.fieldType			= fieldElement.attr('data-fieldtype');
	this.label				= fieldElement.attr('data-label');
	this.input				= jQuery(fieldElement).find('.field-container :input');
	this.parentElement		= jQuery(fieldElement).parents('.form-group');
	this.errorTooltip		= this.parentElement.find('.factory-tooltip-error');
	this.errors				= [];

	var localField = this;

	/**
	 * This function will evaluate the given field, based on the validation
	 * defined in its configuration. The evaluation is done via Ajax, and tells
	 * if the field is correctly filled or not.
	 *
	 * If the field does not match the validation, error messages are returned.
	 */
	this.validate = function() {
		var element = this.input;
		var value = element.val();

		var loading = element.parents('.form-group').find('.form-evaluation-loading');
		loading.show();

		var glyphicons = element.parents('.form-group').find('.form-infos .glyphicon');
		glyphicons.hide();

		// Ajax callback functions.
		var ajaxFunctions = {
			// Success callback function.
			success: function(result) {
				loading.hide();
				result = jQuery.parseJSON(result);
				var parentElement = jQuery(element).parents('.form-group');
				var errorTooltip = parentElement.find('.factory-tooltip-error');

				// The field has at least one error.
				if (jQuery(result['validationResult']['errors']).length > 0) {
					localField.errors = [];
					localField.addErrors(result['validationResult']['errors']).showErrors();
				}
				else {
					// No error occurred.
					localField.resetErrors();
					parentElement.removeClass('has-error');
					parentElement.addClass('has-success');
					errorTooltip.hide();
				}
			},
			// Error callback function.
			error: function(xhr, status, error) {},
			// Complete callback function.
			complete: function() {
				if (typeof localField.formElement.menu != 'undefined')
					localField.formElement.menu.refreshErrorsMenu();
			}
		};

		jQuery.ajax({
			async:		'true',
			url:		SiteFactory.ajaxUrl,
			type:		'GET',
			dataType:	'html',
			data: {
				request: {
					function: 'Romm\\SiteFactory\\Form\\FieldValidation->ajaxValidateField',
					arguments: {
						fieldName:	localField.name,
						value: 		value,
						pageUid:	localField.formElement.modelSiteId
					}
				}
			},
			success: function(result) {
				ajaxFunctions['success'](result);
			},
			error: function(xhr, status, error) {
				ajaxFunctions['error'](xhr, status, error);
			},
			complete: function() {
				ajaxFunctions['complete']();
			}
		});
	};

	/**
	* Adds an error message to the field.
	*
	* @param	{string}	error
	* @returns	SiteFactory.Field
	*/
	this.addError = function(error) {
		this.errors.push(error);
		return this;
	};

	/**
	* Adds several error messages to the field.
	*
	* @param	{[]}	errors
	* @returns	SiteFactory.Field
	*/
	this.addErrors = function(errors) {
		for (var i= 0; i < errors.length; i++)
			this.addError(errors[i].toString());
		return this;
	};

	/**
	 * Will empty the array that contains the errors of the field.
	 */
	this.resetErrors = function() {
		this.errors = [];
	};

	/**
	* Returns the field's errors array.
	*
	* @returns {[]}
	*/
	this.getErrors = function() {
		return this.errors;
	};

	/**
	* Handles errors display.
	*
	* @returns	{SiteFactory.Field}
	*/
	this.showErrors = function() {
		this.parentElement.removeClass('has-success');
		this.parentElement.addClass('has-error');
		this.errorTooltip.removeClass('factory-tooltip-disabled');
		this.errorTooltip.attr('data-original-title', this.getErrorMessageTooltip());
		this.errorTooltip.show();
		return this;
	};

	/**
	* Returns a line-break message.
	*
	* @returns	{string}
	*/
	this.getErrorMessageTooltip = function() {
		var errorMessage = '';
		for (var i= 0; i < this.errors.length; i++) {
			if (i > 0)
				errorMessage += '\n\r\n\r';
			errorMessage += this.errors[i];
		}

		return errorMessage;
	};

	this.findElement = function(selector) {
		return jQuery(this.element).find(selector);
	};
};