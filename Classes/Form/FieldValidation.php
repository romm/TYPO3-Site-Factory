<?php
namespace Romm\SiteFactory\Form;

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

use Romm\SiteFactory\Core\Core;

/**
 * Class containing functions allowing to check if a field is correctly filled,
 * depending on the configuration which has been set in TypoScript.
 *
 * See the following classes for further information:
 * - \Romm\SiteFactory\Core\Configuration\Configuration
 * - \Romm\SiteFactory\Core\Configuration\FieldsConfiguration
 */
class FieldValidation {
	/**
	 * Checks if a given field is correctly filled.
	 *
	 * @param	Fields\AbstractField	$field	The field.
	 * @return	array							An array with 2 indexes:
	 * 											 - fieldLabel:			The translated label of the field.
	 * 											 - validationResult:	List of TYPO3\CMS\Extbase\Error\Result
	 */
	public function validateField(Fields\AbstractField $field) {
		$fieldValidation = array('fieldLabel' => Core::translate($field->getLabel()));

		$validationResult = $field->validate()->getValidationResult();
		$fieldValidation['validationResult'] = $validationResult;

		return $fieldValidation;
	}

	/**
	 * Ajax implementation of the function "validateField". Will display the
	 * result encoded in JSON.
	 *
	 * @param	string	$content	Not used.
	 * @param	array	$params		Parameters.
	 * @return	string	JSON encoded result.
	 */
	public function ajaxValidateField($content, $params) {
		$arguments = $params['arguments'];

		// @todo : Exception ?
		if (!isset($arguments['fieldName']) || !isset($arguments['value']) || !isset($arguments['pageUid'])) return '';

		// @todo : check pageUid
		$field = Fields\Field::getField($arguments['fieldName'], $arguments['pageUid']);
		$field->setValue($arguments['value']);

		$validation = $this->validateField($field);

		$validationResult = Core::convertValidationResultToArray($validation['validationResult']);

		$validation['validationResult'] = $validationResult;

		return json_encode($validation);
	}
}