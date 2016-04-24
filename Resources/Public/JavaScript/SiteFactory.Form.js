"use strict";

/** @namespace SiteFactory */

/**
 * @typedef {SiteFactory.Field} SiteFactoryField
 */
SiteFactory.Form = {
	/**
	 * Repository containing all instances of forms.
	 */
	repository: [],

	GetAllInstances: function() {
		return this.repository;
	},

	GetInstanceByName: function(name) {
		for (var i = 0; i < this.repository.length; i++) {
			var formInstance = this.repository[i];

			if (formInstance.element === name) {
				return formInstance;
			}
		}

		return null;
	},

	Instance: function(element) {
		SiteFactory.Form.repository.push(this);

		this.element					= element;
		this.fields						= {};
		this.modelSiteId				= 0;
		/**
		 * @type {SiteFactory.Menu}
		 */
		this.menu						= null;
		var localForm = this;

		this.repository					= [];

		/**
		 * Gets all form fields.
		 *
		 * @return	{Object<string, SiteFactory.Field>}}
		 */
		this.getFields = function() {
			if (Object.keys(this.fields).length == 0) {
				jQuery('.form-field').each(function() {
					var field = new SiteFactory.Field(localForm, jQuery(this));
					localForm.fields[field.name] = field;
				});
			}

			return this.fields;
		};

		/**
		 * Gets all fields, filtered on the given type (text, select, color_picker,
		 * etc.).
		 *
		 * @param	{string}	type	The type you want to filter on.
		 * @return	{Object<String, SiteFactory.Field>}
		 */
		this.getFieldsByType = function(type) {
			var fields = this.getFields(),
				typedFields = {};

			for(var index in fields)
				if (fields.hasOwnProperty(index))
					if (fields[index].type == type)
						typedFields[index] = fields[index];

			return typedFields;
		};

		/**
		 * Gets all fields, filtered on the given field type (text, select, etc.).
		 *
		 * @param	{string}	type	The type you want to filter on.
		 * @return	{Object<String, SiteFactory.Field>}
		 */
		this.getFieldsByFieldType = function(type) {
			var fields = this.getFields(),
				typedFields = {};

			for(var index in fields)
				if (fields.hasOwnProperty(index))
					if (fields[index].fieldType == type)
						typedFields[index] = fields[index];

			return typedFields;
		};

		/**
		 * Gets a field from its name.
		 *
		 * @param	{string}	name	The name of the field.
		 * @return	{SiteFactory.Field}
		 */
		this.getFieldByName = function(name) {
			var fields = this.getFields();

			for(var index in fields)
				if (fields.hasOwnProperty(index))
					if (fields[index].name == name)
						return this.fields[index];

			return null;
		};

		/**
		 * This function is called when clicking on a button/link (should come from the
		 * header). It will submit the main site creation form.
		 *
		 * @param	{string}	action	The name of the controller's action.
		 * @return	{boolean}
		 */
		this.submit = function(action) {
			this.findElement('input[field-name="action"]').attr('value', action);
			jQuery('#' + this.element).parents('form').submit();

			return false;
		};

		this.findElement = function(selector) {
			return jQuery('#' + this.element).find(selector);
		};

		this.refreshSiteNameHeader = function(val) {
			jQuery('.static-site-name span.content').html(val);
		};

		jQuery(document).ready(function() {
			var menu = jQuery('.static-menu[data-site-factory-form="' + localForm.element + '"]');
			if (typeof menu != 'undefined')
				localForm.menu = new SiteFactory.Menu(localForm, menu);

			// Reloading page after changing model site.
			var modelSiteField = localForm.getFieldByName('modelSite');
			if (modelSiteField) {
				var modelSiteSelect = modelSiteField.input;
				localForm.modelSiteId = modelSiteSelect.val();
				jQuery(modelSiteSelect).on(
					'change',
					{form: localForm},
					function(event) {
						localForm.modelSiteId = modelSiteSelect.val();
						event.data.form.findElement('input[data-name="changeModelSiteId"]').val(localForm.modelSiteId);
						event.data.form.submit('new');
					}
				);
			}
			else {
				var modifySiteElement = localForm.findElement('input[data-name="modifySiteId"]');

				if (modifySiteElement.length > 0)
					localForm.modelSiteId = modifySiteElement.val();
			}

			if (typeof refreshForm !== 'undefined') {
				var fields = localForm.getFields();
				for(var index in fields)
					if (fields.hasOwnProperty(index))
						localForm.fields[index].validate();
			}

			// Refreshing the static site's name in the header, depending on the input events.
			var siteNameField = localForm.getFieldByName('siteTitle');
			if (siteNameField) {
				localForm.refreshSiteNameHeader(siteNameField.input.val());

				siteNameField.input.on('change keyup', function() {
					localForm.refreshSiteNameHeader(jQuery(this).val());
				});
			}
		});

		return this;
	}
};