<?php
namespace Romm\SiteFactory\Form\Validation;

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
