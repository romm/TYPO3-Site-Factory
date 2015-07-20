<?php
namespace Romm\SiteFactory\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Romain CANON <romain.canon@exl-group.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Error\Error;
use Romm\SiteFactory\Core\CacheManager;
use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Controller managing the duplication of sites.
 */
class DuplicationController extends AbstractController {

	/**
	 * Ajax implementation of the function "processDuplication". Will display
	 * the result in JSON.
	 *
	 * See "processDuplication" function for more details.
	 *
	 * @return bool
	 */
	public function ajaxProcessDuplicationAction() {
		// @todo: check if token is valid
		$cacheToken = $this->request->getArgument('duplicationToken');

		// @todo: check if index is valid
		$index = $this->request->getArgument('index');

		$result = $this->processDuplication($cacheToken, $index, true);

		// Printing result for JavaScript.
		echo json_encode($result);
		return true;
	}

	/**
	 * @todo: rewrite function doc
	 * @param	$cacheToken	string	The token of the cache file to get the current state of the duplication.
	 * @param	$index		string	The index of the process which will be executed (e.g. "pagesDuplication" or "treeUidAssociation").
	 * @param	$checkAjax	bool	If true, will call the function "checkAjaxCall" of the current process class.
	 * @return	array				The result of the function, may contain these keys :
	 * 								 - "success":		"False" if error(s) occurred, "true" otherwise.
	 *								 - "result":		The result of the execution function. Contains useful data for further duplication process steps.
	 *								 - "errorMessage":	If error(s) occurred, will contain an error message. If the current user is admin, it will get a detailed message.
	 */
	private function processDuplication($cacheToken, $index, $checkAjax = false) {
		// Getting configuration in cache file.
		$cache = CacheManager::getCacheInstance(CacheManager::CACHE_PROCESSED);
		$cacheData = $cache->get($cacheToken);
		$cacheData = json_decode($cacheData, true);

		/** @var \TYPO3\CMS\Extbase\Error\Result $result */
		$result = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Error\\Result');

		try {
			if (isset($cacheData['duplicationData']['modelPageUid']) && MathUtility::canBeInterpretedAsInteger($cacheData['duplicationData']['modelPageUid']) && $cacheData['duplicationData']['modelPageUid'] > 0) {
				$duplicationConfiguration = AbstractDuplicationProcess::getCleanedDuplicationConfiguration($cacheData['duplicationData']['modelPageUid']);

				if (isset($duplicationConfiguration[$index])) {
					if (isset($duplicationConfiguration[$index]['class'])) {
						$class = $duplicationConfiguration[$index]['class'];
						$settings = (array_key_exists('settings', $duplicationConfiguration[$index]))
							? (is_array($duplicationConfiguration[$index]['settings']))
								? $duplicationConfiguration[$index]['settings']
								: array()
							: array();

						// Calling the function of the current process step.
						/** @var AbstractDuplicationProcess $class */
						$class = GeneralUtility::makeInstance($class, $cacheData['duplicationData'], $settings, $cacheData['fieldsValues']);
						if ($class instanceof AbstractDuplicationProcess) {
							// @todo : else
							if (!$checkAjax || ($checkAjax && $class->checkAjaxCall())) {
								$class->run();
								$fieldsValues = $class->getFieldsValues();
								$result->merge($class->getResult());

								// Saving modified data in cache.
								$configuration = array(
									'duplicationData'	=> $class->getDuplicationData(),
									'fieldsValues'		=> $fieldsValues
								);
								$cache->set($cacheToken, json_encode($configuration));
							}
						}
						else
							throw new \Exception('The class "' . $class . '" must extend "\Romm\SiteFactory\Duplication\AbstractDuplicationProcess".', 1422887215);
					}
					else
						throw new \Exception('The class is not set for the duplication configuration named "' . $index . '".', 1422885526);
				}
				else
					throw new \Exception('Trying to get the duplication configuration named "' . $index . '" but it does not exist.', 1422885438);
			}
			else
				throw new \Exception('The duplication data must contain a valid index for "modelPageUid".', 1422885697);
		}
		catch(\Exception $exception) {
			/** @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser */
			$backendUser = $GLOBALS['BE_USER'];

			// Setting up error message. If the user is admin, it gets a detailed message.
			if ($backendUser->isAdmin())
				$errorMessage = Core::translate('duplication_process.process_error_detailed') . ' ' . $exception->getMessage();
			else
				$errorMessage = Core::translate('duplication_process.process_error_single');

			$result->addError(new Error($errorMessage, 1431985617));
		}

		return Core::convertValidationResultToArray($result);
	}
}
