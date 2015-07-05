<?php
namespace Romm\SiteFactory\Form\Validation;

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
 * Custom validator for the Site Factory.
 */
class HexadecimalValidator extends AbstractValidator {

	/**
	 * Checks if the field value matches a hexadecimal value.
	 *
	 * @param	\Romm\SiteFactory\Form\Fields\AbstractField	$field	The field.
	 */
	protected function isValid($field) {
		if (!preg_match('/^#[0123456789ABCDEF]{6}$/', strtoupper($field->getValue()))) {
			$this->addError(
				$this->translateErrorMessage(
					'fields.validation.wrong_hexadecimal_value',
					Core::getExtensionKey(),
					array('s' => $field->getValue())
				),
				1430127326
			);
		}
	}
}