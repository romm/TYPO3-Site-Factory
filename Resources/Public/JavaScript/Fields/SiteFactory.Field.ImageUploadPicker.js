SiteFactory.FineUploaderDefaultSettings = function() {
	this.element = null;
	this.formElement = null;
	this.fieldName = '';
	this.template = 'qq-template-validation';
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
	this.thumbnails = {
		placeholders: {
			waitingPath: '/typo3conf/ext/site_factory/Resources/Public/Contrib/fine-uploader/placeholders/waiting-generic.png',
			notAvailablePath: '/typo3conf/ext/site_factory/Resources/Public/Contrib/fine-uploader/placeholders/not_available-generic.png'
		}
	};
	this.validation = {
		allowedExtensions: ['jpeg', 'jpg', 'gif', 'png'],
		itemLimit: 1,
		sizeLimit: 409600 // 400 kB = 400 * 1024 bytes
	};
	this.classes = {
		fail: 'alert alert-danger counter-errors'
	};
	this.messages =  {};
	this.callbacks = {
		onComplete: function(id, name, response) {
			var formElement = window[this._options.formId];
			var fieldName = this._options.fieldName;
			var fieldElement = formElement.getFieldByName(fieldName);
			fieldElement.input.val('new:' + response['tmpFilePath']);
		}
	};
};