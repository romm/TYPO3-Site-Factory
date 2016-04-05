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
class NotEmptyValidator extends AbstractValidator {

	/**
	 * Checks if the field value is not empty.
	 *
	 * @param	\Romm\SiteFactory\Form\Fields\AbstractField	$field	The field.
	 */
	protected function isValid($field) {
		$value = $field->getValue();
		if (
			$value === NULL ||
			$value === '' ||
			(is_array($value) && empty($value))
		) {
			$this->addError(
				$this->translateErrorMessage(
					'fields.validation.empty_value',
					Core::getExtensionKey()
				),
				1431103424
			);
		}
	}
}
