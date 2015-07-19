<?php
namespace Romm\SiteFactory\Utility;

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

use Romm\SiteFactory\Core\CacheManager;
use Romm\SiteFactory\Core\Core;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Handles the TypoScript configuration's construction of the extension.
 */
class TypoScriptUtility {
	const EXTENSION_CONFIGURATION_PATH	= 'module.tx_sitefactory';

	/**
	 * Storage for the pages' configuration.
	 *
	 * @var \TYPO3\CMS\Core\TypoScript\TemplateService[]
	 */
	private static $pageConfiguration = array();

	/**
	 * Storage for the pages' TypoScript configuration arrays.
	 *
	 * @var	array
	 */
	private static $pageTypoScriptConfiguration = array();

	/**
	 * Storage for the pages' TypoScript constants arrays.
	 *
	 * @var	array
	 */
	private static $pageTypoScriptConstants = array();

	/**
	 * Calls the function "getConfigurationFromPath", but uses the Site Factory
	 * configuration path as root path.
	 *
	 * @param	string			$path		The path to the configuration value.
	 * @param 	int|null|bool	$pageUid	The uid of the page you want the TypoScript configuration from. If "null" is given, only the static configuration is returned.
	 * @param	string			$delimiter	The delimiter for the path. Default is ".".
	 * @return	mixed|null
	 */
	public static function getExtensionConfigurationFromPath($path, $pageUid = null, $delimiter = '.') {
		return self::getConfigurationFromPath(self::EXTENSION_CONFIGURATION_PATH . '.' . $path, $pageUid, $delimiter);
	}

	/**
	 * Returns the TypoScript configuration value at a the given path.
	 * Example: config.tx_myext.some_conf
	 *
	 * @param	string			$path		The path to the configuration value.
	 * @param 	int|null|bool	$pageUid	The uid of the page you want the TypoScript configuration from. If "null" is given, only the static configuration is returned.
	 * @param	string			$delimiter	The delimiter for the path. Default is ".".
	 * @return	mixed|null
	 */
	public static function getConfigurationFromPath($path, $pageUid = null, $delimiter = '.') {
		$result = null;
		$cacheIdentifier = md5($path . (string)$pageUid);

		$cacheInstance = CacheManager::getCacheInstance(CacheManager::CACHE_MAIN);
		if ($cacheInstance)
			if ($cacheInstance->has($cacheIdentifier))
				$result = $cacheInstance->get($cacheIdentifier);
			elseif (ArrayUtility::isValidPath(self::getTypoScriptConfiguration($pageUid), $path, $delimiter)) {
				$result = ArrayUtility::getValueByPath(self::getTypoScriptConfiguration($pageUid), $path, $delimiter);
				$cacheInstance->set($cacheIdentifier, $result);
			}


		return $result;
	}

	/**
	 * Returns the TypoScript configuration, including the static configuration
	 * from files (see function "getExtensionConfiguration").
	 *
	 * As this function does not save the configuration in cache, we advise not
	 * to call it, and prefer using the function "getConfigurationFromPath"
	 * instead, which has its own caching system.
	 * It can still be useful to get the whole TypoScript configuration, so the
	 * function remains public, but use with caution!
	 *
	 * @param 	int|null|bool	$pageUid	The uid of the page you want the TypoScript configuration from. If "null" is given, only the static configuration is returned.
	 * @return	array						The configuration.
	 */
	public static function getTypoScriptConfiguration($pageUid = null) {
		if (!array_key_exists($pageUid, self::$pageTypoScriptConfiguration)) {
			$configuration = self::generateConfiguration($pageUid);

			/** @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService */
			$typoScriptService = Core::getObjectManager()->get('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
			self::$pageTypoScriptConfiguration[$pageUid] = $typoScriptService->convertTypoScriptArrayToPlainArray($configuration->setup);
		}

		return self::$pageTypoScriptConfiguration[$pageUid];
	}

	/**
	 * Returns the TypoScript constants at a given path.
	 *
	 * @param 	int|null|bool	$pageUid	The uid of the page you want the TypoScript constants from. If "null" is given, only the static constants is returned.
	 * @return	array
	 */
	public static function getTypoScriptConstants($pageUid = null) {
		if (!array_key_exists($pageUid, self::$pageTypoScriptConstants)) {
			$configuration = self::generateConfiguration($pageUid);
			/** @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService */
			$typoScriptService = Core::getObjectManager()->get('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');
			self::$pageTypoScriptConstants[$pageUid] = $typoScriptService->convertTypoScriptArrayToPlainArray($configuration->setup_constants);
		}

		return self::$pageTypoScriptConstants[$pageUid];
	}

	/**
	 * Generates a TemplateService from a given page uid, by running through
	 * the pages root line.
	 * @param 	int|null|bool	$pageUid	The uid of the page you want the TypoScript configuration from. If "null" is given, only the static configuration is returned.
	 * @return	\TYPO3\CMS\Core\TypoScript\TemplateService
	 */
	private static function generateConfiguration($pageUid = null) {
		if (!array_key_exists($pageUid, self::$pageConfiguration)) {
			$objectManager = Core::getObjectManager();

			$rootLine = null;
			if ($pageUid && MathUtility::canBeInterpretedAsInteger($pageUid) && $pageUid > 0) {
				/** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
				$pageRepository = $objectManager->get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
				$rootLine = $pageRepository->getRootLine($pageUid);
			}

			/** @var \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService $templateService */
			$templateService = $objectManager->get('TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');

			$templateService->tt_track = 0;
			$templateService->init();
			if ($rootLine !== null) {
				$templateService->runThroughTemplates($rootLine);
			}
			$templateService->generateConfig();
			$templateService->generateConfig_constants();

			self::$pageConfiguration[$pageUid] = $templateService;
		}

		return self::$pageConfiguration[$pageUid];
	}
}
