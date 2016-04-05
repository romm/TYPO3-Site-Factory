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
class SelectOptionsValidator extends AbstractValidator
{

    /**
     * Checks if the select field current value is in its available options. If
     * not, an error is thrown.
     *
     * @param    \Romm\SiteFactory\Form\Fields\SelectField $field The select field.
     */
    protected function isValid($field)
    {
        if (!array_key_exists($field->getValue(), $field->getOptions())) {
            $this->addError(
                $this->translateErrorMessage(
                    'fields.validation.select.wrong_option_value',
                    Core::getExtensionKey(),
                    ['s' => $field->getValue()]
                ),
                1430127401
            );
        }
    }
}
