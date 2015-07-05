"use strict";

/** @namespace SiteFactory */

SiteFactory.Copy = {
	duplicationToken:			null,
	duplicationConfiguration:	null,
	unknownErrorMessage:		null,

	// Some dummy HTML code used to show the status of different tasks.
	statusPendingHtml:		null,
	statusProcessingHtml:	null,
	statusOkHtml:			null,
	statusErrorHtml:		null,
	statusWarningHtml:		null,
	statusNoticeHtml:		null,

	/**
	 * Used to store information about duplication processes. After a process is
	 * called, an index is created/modified: value is TRUE if the process
	 * encountered no error, or "warnings" if at least one warning was returned.
	 *
	 * @type	{Object.<number, string|boolean>}
	 */
	duplicationCompleted: {},

	/**
	 * Initialization function.
	 */
	initialize: function () {
		$(document).ready(function() {
			// Getting dummy HTML codes.
			SiteFactory.Copy.statusPendingHtml		= $('.site-duplication .dummy-status-pending').html();
			SiteFactory.Copy.statusProcessingHtml	= $('.site-duplication .dummy-status-processing').html();
			SiteFactory.Copy.statusOkHtml			= $('.site-duplication .dummy-status-ok').html();
			SiteFactory.Copy.statusErrorHtml		= $('.site-duplication .dummy-status-error').html();
			SiteFactory.Copy.statusWarningHtml		= $('.site-duplication .dummy-status-warning').html();
			SiteFactory.Copy.statusNoticeHtml		= $('.site-duplication .dummy-status-notice').html();

			// Running the first process, the next ones will follow.
			SiteFactory.Copy.processDuplicationRecursive(0);

			// Detecting a click on the "retry" buttons for the processes.
			$('.site-duplication-process .retry').on('click', function() {
				var processIndex = $(this).attr('data-key');
				var processIndexKey = SiteFactory.Copy.getProcessIndexKey(processIndex);
				if (processIndexKey !== null) {
					// If the process has already been called, and warnings were returned, we run this process only once again.
					if (SiteFactory.Copy.duplicationCompleted[processIndex] == 'warnings')
						SiteFactory.Copy.processDuplication(processIndexKey);
					// If the process had errors, the we run it again, and the next ones will follow.
					else if (SiteFactory.Copy.duplicationCompleted[processIndex] !== true)
						SiteFactory.Copy.processDuplicationRecursive(processIndexKey);
				}
			})
		});
	},

	/**
	 * Gets the key in the global duplication's configuration var for a given
	 * process.
	 *
	 * @param	{int}		processIndex	The index of the process.
	 * @returns	{int|null}
	 */
	getProcessIndexKey: function(processIndex) {
		for (var i = 0; i < this.duplicationConfiguration.length; i++)
			if (processIndex == this.duplicationConfiguration[i])
				return i;
		return null;
	},

	/**
	 * Will call function "processDuplication" for every process. Stops if an
	 * error is returned in the current process.
	 *
	 * @param	{int}	processIndexKey	The key for the process index in the global duplication configuration var.
	 */
	processDuplicationRecursive: function(processIndexKey) {
		this.processDuplication(processIndexKey).pipe(function(doContinue) {
			if (doContinue) {
				processIndexKey++;
				if (processIndexKey in SiteFactory.Copy.duplicationConfiguration) {
					SiteFactory.Copy.processDuplicationRecursive(processIndexKey);
				}
			}
		}).fail(function() {
			// @todo
		});
	},

	/**
	 * Entire duplication process for a given index.
	 *
	 * @param	{int}	processIndexKey	The key for the process index in the global duplication configuration var.
	 * @returns	{*}
	 */
	processDuplication: function(processIndexKey) {
		var	processIndex = this.duplicationConfiguration[processIndexKey],
			d = $.Deferred();

		// If the element has already been processed, we break.
		if (this.duplicationCompleted[processIndex] === true)
			d.resolve(true);
		else {
			var duplicationProcessElement = $('.site-duplication-process');

			// Managing some elements' visibility.
			var statusElement = duplicationProcessElement.find('.' + processIndex + ' .status');
			statusElement.html(this.statusProcessingHtml);
			var resultElem = duplicationProcessElement.find('.' + processIndex + ' .result-text-container');
			resultElem.hide();
			var retryElement = duplicationProcessElement.find('.' + processIndex + ' .retry');
			retryElement.hide();

			$('.site-duplication .title-normal').show();
			$('.site-duplication .title-error').hide();

			// Managing timer.
			var timePassed = -1;
			var timeElement = $(duplicationProcessElement).find('.' + processIndex + ' .time');
			timeElement.show();
			var timerFunction = function() {
				timePassed += 1;
				timeElement.html('[' + timePassed.toString().toHHMMSS() + ']');
			};
			var timer = setInterval(
				timerFunction,
				1000
			);

			// Ajax callback functions.
			var ajaxFunctions = {
				// Success callback function.
				success:	function(result) {
					var message = '';

					if (result['errors'].length == 0) {
						// No error occurred.
						statusElement.html(SiteFactory.Copy.statusOkHtml);
						SiteFactory.Copy.duplicationCompleted[processIndex] = true;

						// Showing warnings and notices.
						if (result['warnings'].length > 0) {
							SiteFactory.Copy.duplicationCompleted[processIndex] = 'warnings';
							retryElement.show();
							statusElement.html(SiteFactory.Copy.statusWarningHtml);
							message += SiteFactory.Copy.convertMessagesArrayToHtmlList(result['warnings'], 'text-warning');
						}
						if (result['notices'].length > 0) {
							if (result['warnings'].length == 0) statusElement.html(SiteFactory.Copy.statusNoticeHtml);
							message += SiteFactory.Copy.convertMessagesArrayToHtmlList(result['notices'], 'text-info');
						}

						// Managing progress bar.
						SiteFactory.Copy.updateProgressBar();
					}
					else {
						// At least an error occurred.
						retryElement.show();
						statusElement.html(SiteFactory.Copy.statusErrorHtml);
						$('.site-duplication .title-normal').hide();
						$('.site-duplication .title-error').show();

						message = SiteFactory.Copy.convertMessagesArrayToHtmlList(result['errors'], 'text-danger');
					}

					if (message != '') {
						resultElem.find('.result-list').html(message);
						resultElem.show();
					}
				},
				// Error callback function.
				error:	function(xhr, status, error) {
					retryElement.show();
					statusElement.html(SiteFactory.Copy.statusErrorHtml);
					$('.site-duplication .title-normal').hide();
					$('.site-duplication .title-error').show();

					var errorMessage = SiteFactory.Copy.convertMessagesArrayToHtmlList({0: SiteFactory.Copy.unknownErrorMessage}, 'text-danger');
					resultElem.find('.result-list').html(errorMessage);
					resultElem.show();
				},
				// Complete callback function.
				complete:	function() {
					clearInterval(timer);
				}
			};

			this.ajaxProcess(
				{
					mvc:		{
						vendor:			'Romm',
						extensionName:	'SiteFactory',
						pluginName:		'Administration',
						controller:		'Duplication',
						action:			'ajaxProcessDuplication'
					},
					arguments:	{
						duplicationToken:	SiteFactory.Copy.duplicationToken,
						index:				processIndex
					}
				},
				'json',
				ajaxFunctions['success'],
				ajaxFunctions['error'],
				ajaxFunctions['complete']
			).done(
				function(result) {
					d.resolve(result.errors.length == 0);
				}
			).fail(d.reject);
		}

		return d.promise();
	},

	/**
	 * Updates the progress bar element, depending on how many processes have
	 * run and did not return errors.
	 */
	updateProgressBar: function() {
		var processedNumber = 0;
		var maxNumber = $(this.duplicationConfiguration).length;
		$.each(this.duplicationConfiguration, function(index, processIndex) {
			if (SiteFactory.Copy.duplicationCompleted[processIndex] === true || SiteFactory.Copy.duplicationCompleted[processIndex] == 'warnings')
				processedNumber++;
		});

		var percent = processedNumber / maxNumber * 100;

		if (processedNumber == maxNumber) {
			$('.site-duplication .title-normal').hide();
			$('.site-duplication .title-error').hide();
			$('.site-duplication .title-success').show();
		}


		var progressBar = $('.site-duplication .progress .progress-bar');
		progressBar.attr('aria-valuenow', percent);
		progressBar.css('width', progressBar.attr('aria-valuenow') + '%');
	},

	/**
	 * Converts an array of messages to a HTML <li> list.
	 *
	 * @param	{Object}	message			The messages array.
	 * @param	{string}	elementClass	The class added to the <li>.
	 * @returns	{string}	The HTML result.
	 */
	convertMessagesArrayToHtmlList: function(message, elementClass) {
		var finalMessage = '';
		for (var key in message) {
			if (message.hasOwnProperty(key)) {
				var element = document.createElement('li');
				if (typeof elementClass !== 'undefined') element.className = elementClass;
				element.innerHTML = message[key];
				finalMessage += element.outerHTML;
			}
		}
		return finalMessage;
	},

	/**
	 * Ajax process function.
	 *
	 * @param	{Object}	request						The request parameters.
	 * @param	{string}	dataType					The data type of the Ajax call.
	 * @param	{function}	callBackSuccessFunction		Function called after a success.
	 * @param	{function}	callBackErrorFunction		Function called if an error occurred.
	 * @param	{function}	callBackCompleteFunction	Function called after the Ajax call.
	 * @returns	{*}
	 */
	ajaxProcess: function(request, dataType, callBackSuccessFunction, callBackErrorFunction, callBackCompleteFunction) {
		return $.ajax({
			async:		true,
			url:		TYPO3.settings.ajaxUrls['ajaxDispatcher'],
			type:		'GET',
			dataType:	dataType,
			data: {
				request:	request
			},
			success: function(result) {
				if (callBackSuccessFunction !== undefined)
					callBackSuccessFunction(result);
			},
			error: function(xhr, status, error) {
				if (callBackErrorFunction !== undefined)
					callBackErrorFunction(xhr, status, error);
			},
			complete: function() {
				if (callBackCompleteFunction !== undefined)
					callBackCompleteFunction();
			}
		});
	}
};