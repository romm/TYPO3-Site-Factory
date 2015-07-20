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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Romm\SiteFactory\Core\Core;

/**
 * A class allowing to manage the fields configuration.
 */
class Field {

	/**
	 * Storage for the fields configuration on several pages.
	 *
	 * @var array
	 */
	private static $fieldsConfiguration = array();

	/**
	 * Storage for the fields on several pages.
	 *
	 * @var array
	 */
	private static $field = array();

	/**
	 * Returns all the fields of a given page.
	 *
	 * @param $pageUid						int		The id of the page you want the fields of.
	 * @param $checkHideInSiteModification	bool	If true, it will check if the field has the key "hideInSiteModification"; if it does, it will not be sent.
	 * @return AbstractField[]				Array containing fields.
	 * @throws \Exception
	 */
	public static function getFields($pageUid, $checkHideInSiteModification = false) {
		$fieldsConfiguration = self::getFieldsConfiguration($pageUid);
		$fields = array();

		foreach($fieldsConfiguration as $fieldName => $fieldConfiguration) {
			$field = self::getField($fieldName, $pageUid);

			// If $checkHideInSiteModification is true, we add the field only if it must be activated in a site modification.
			if (!$checkHideInSiteModification || ($checkHideInSiteModification && !$field->getHideInSiteModification())) {
				$fields[$fieldName] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Returns a specific field of a given page.
	 *
	 * @param $fieldName	string	The name of the field.
	 * @param $pageUid		int		The id of the page you want the field of.
	 * @return AbstractField		The field.
	 * @throws \Exception
	 */
	public static function getField($fieldName, $pageUid) {
		if (!isset(self::$field[$pageUid][$fieldName])) {
			$fieldsConfiguration = self::getFieldsConfiguration($pageUid);

			// Checking if the field exists.
			if (!isset($fieldsConfiguration[$fieldName])) {
				throw new \Exception('The field "' . $fieldName . '" does not exist.', 1423772429);
			}

			$fieldConfiguration = $fieldsConfiguration[$fieldName];

			// The field must have a type.
			if (!isset($fieldConfiguration['type'])) {
				throw new \Exception('The field "' . $fieldName . '" should have a value for "type".', 1423770475);
			}

			// Checking if the type of the field exists.
			$fieldType = $fieldConfiguration['type'];
			if (!in_array($fieldType, FieldsTypes::getFieldsTypes($pageUid))) {
				throw new \Exception("[Field: $fieldName] The field type {$fieldConfiguration['type']} is not allowed. Try one of the following: " . implode(', ', FieldsTypes::getFieldsTypes($pageUid)) . '.', 1423770969);
			}

			$fieldsTypesConfiguration = FieldsTypes::getFieldsTypesConfiguration($pageUid);
			$field = GeneralUtility::makeInstance($fieldsTypesConfiguration[$fieldType]['class'], $fieldName, $fieldConfiguration['type']);
			if (!$field instanceof AbstractField) {
				throw new \Exception('The class ' . $fieldsTypesConfiguration[$fieldType]['class'] . ' must extend \Romm\SiteFactory\Form\Fields\AbstractField.', 1423771432);
			}

			$field->fillConfiguration($fieldConfiguration);

			self::$field[$pageUid][$fieldName] = $field;
		}

		return self::$field[$pageUid][$fieldName];
	}

	/**
	 * Returns the fields configuration of a given page.
	 *
	 * @param	$pageUid	int		The id of the page you want the fields of.
	 * @return	array				The fields configuration.
	 */
	private static function getFieldsConfiguration($pageUid) {
		if (!isset(self::$fieldsConfiguration[$pageUid]))
			self::$fieldsConfiguration[$pageUid] = Core::sortArrayByPositionValue(TypoScriptUtility::getExtensionConfigurationFromPath('fields', $pageUid));

		return self::$fieldsConfiguration[$pageUid];
	}
}
