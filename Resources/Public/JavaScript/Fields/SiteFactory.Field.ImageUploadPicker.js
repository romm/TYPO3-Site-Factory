SiteFactory.FineUploaderDefaultSettings = function() {
	// For further Site Factory usage.
	this.element = null;
	this.formElement = null;
	this.fieldName = '';

	// Validation settings for files.
	this.validation = {
		allowedExtensions: ['jpeg', 'jpg', 'gif', 'png'],
		itemLimit: 1,
		sizeLimit: 409600000 // 400 kB = 400 * 1024 bytes
	};

	// Custom classes added to element on certain events.
	this.classes = {
		fail:		'alert alert-danger counter-errors',
		success:	'alert alert-info'
	};

	// Id of the HTML element containing the Fine Uploader template.
	this.template = 'qq-template-validation';

	// TYPO3 request handler when a new file is added.
	this.request = {
		endpoint:       TYPO3.settings.ajaxUrls['ajaxDispatcher'],
		paramsInBody:   false,
		params: {
			ajaxID:		'ajaxDispatcher',
			request: {
				function: 'Romm\\SiteFactory\\Utility\\FileUtility->ajaxMoveUploadedFileToSiteFactoryFolder'
			}
		}
	};

	// Request managing the already existing files for a field.
	this.session = {
		endpoint:       TYPO3.settings.ajaxUrls['ajaxDispatcher'],
		params: {
			ajaxID:		'ajaxDispatcher',
			request: {
				function: 'Romm\\SiteFactory\\Utility\\FileUtility->getExistingFiles'
			},
			fieldSettings: null
		}
	};

	// Setting up the delete functionality.
	this.deleteFile = {
		enabled:		true,
		forceConfirm:	true,
		endpoint:		TYPO3.settings.ajaxUrls['ajaxDispatcher'] + '&dummy='
	};

	// Paths to the thumbnails.
	this.thumbnails = {
		placeholders: {
			waitingPath: '',
			notAvailablePath: ''
		}
	};

	// Initializing the messages object which are filled in the Fluid template.
	this.messages =  {};

	// Callback functions customization.
	this.callbacks = {
		// When a file has been uploaded.
		onComplete: function(id, name, response) {
			// Changing the value of the form element to the path of the file.
			var formElement = window[this._options.formId];
			var fieldName = this._options.fieldName;
			var fieldElement = formElement.getFieldByName(fieldName);
			fieldElement.input.val('new:' + response['tmpFilePath']);
		},

		// When a delete request is sent.
		onSubmitDelete: function (id) {
			this.setDeleteFileParams(
				{
					fileName:	this.getUuid(id),
					request: {
						function: 'Romm\\SiteFactory\\Utility\\FileUtility->deleteFile'
					}
				},
				id
			);
		},

		// When a delete request has been done.
		onDeleteComplete: function () {
			// Emptying the value of the form element.
			var formElement = window[this._options.formId];
			var fieldName = this._options.fieldName;
			var fieldElement = formElement.getFieldByName(fieldName);
			fieldElement.input.val('');
		}
	};
};