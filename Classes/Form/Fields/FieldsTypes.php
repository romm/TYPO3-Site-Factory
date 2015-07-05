<?php
namespace Romm\SiteFactory\Form\Fields;

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

use Romm\SiteFactory\Utility\TypoScriptUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Romm\SiteFactory\Core\Configuration\Configuration;

/**
 * @todo
 */
class FieldsTypes {
	/** @var array Cache for the fields types configuration for various pages. */
	private static $fieldsTypes = array();

	/**
	 * Returns an array containing the fields types configuration, based on the
	 * TypoScript configuration of a given page.
	 *
	 * @param $pageUid	int	The id of the page you want the configuration of.
	 * @return array	The fields types configuration as an array : key is the name and value is the configuration.
	 * @throws \Exception
	 */
	public static function getFieldsTypesConfiguration($pageUid) {
		if (!isset(self::$fieldsTypes[$pageUid])) {
			$fieldsTypesConfiguration = TypoScriptUtility::getExtensionConfigurationFromPath('fieldsTypes', $pageUid);
			self::$fieldsTypes[$pageUid] = array();

			foreach($fieldsTypesConfiguration as $fieldTypeName => $fieldTypeConfiguration) {
				if (!isset($fieldTypeConfiguration['class'])) {
					throw new \Exception('The field type "' . $fieldTypeName . '" should have a value for "class"', 1423770370);
				}

				self::$fieldsTypes[$pageUid][$fieldTypeName] = $fieldTypeConfiguration;
			}
		}

		return self::$fieldsTypes[$pageUid];
	}

	/**
	 * Returns the different fields types names, based on the TypoScript
	 * configuration of a given page.
	 *
	 * @param $pageUid	int	The id of the page you want the fields types of.
	 * @return array	The fields types names.
	 */
	public static function getFieldsTypes($pageUid) {
		$fieldsTypesConfiguration = self::getFieldsTypesConfiguration($pageUid);

		return array_keys($fieldsTypesConfiguration);
	}
}