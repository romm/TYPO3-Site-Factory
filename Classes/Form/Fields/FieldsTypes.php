<?php
namespace Romm\SiteFactory\Form\Fields;

/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Site Factory project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

use Romm\SiteFactory\Utility\TypoScriptUtility;

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
