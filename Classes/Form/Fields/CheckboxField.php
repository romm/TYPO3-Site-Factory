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

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
class CheckboxField extends AbstractField {
	/** @var array Array containing the properties that must be filled for the field. */
	protected $requiredFieldsConfiguration = array();

	/** @var mixed The field type of the field : text, checkbox, select, etc.. */
	protected $fieldType = AbstractField::FIELD_TYPE_CHECKBOX;

	/**
	 * Returns a human readable version of the value. Useful for select fields,
	 * for example.
	 * Override this function in your class if you want a custom behavior.
	 *
	 * @return mixed	The value of the field, in a human readable version.
	 */
	public function getDisplayValue() {
		if ($this->value)
			$value = '&#10003;';
		else
			$value = $this->value;

		return $value;
	}
}