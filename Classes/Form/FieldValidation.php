<?php
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

namespace Romm\SiteFactory\Form;

use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Form\Fields\AbstractField;

/**
 * Class containing functions allowing to check if a field is correctly filled,
 * depending on the configuration which has been set in TypoScript.
 *
 * See the following classes for further information:
 * - \Romm\SiteFactory\Core\Configuration\Configuration
 * - \Romm\SiteFactory\Core\Configuration\FieldsConfiguration
 */
class FieldValidation
{

    /**
     * Checks if a given field is correctly filled.
     *
     * @param    AbstractField $field       The field.
     * @return    array  An array with 2 indexes:
     *                   - fieldLabel:       The translated label of the field.
     *                   - validationResult: List of TYPO3\CMS\Extbase\Error\Result
     */
    public function validateField(AbstractField $field)
    {
        $fieldValidation = ['fieldLabel' => Core::translate($field->getLabel())];

        $validationResult = $field->validate()->getValidationResult();
        $fieldValidation['validationResult'] = $validationResult;

        return $fieldValidation;
    }

    /**
     * Ajax implementation of the function "validateField". Will display the
     * result encoded in JSON.
     *
     * @param    string $content Not used.
     * @param    array  $params  Parameters.
     * @return    string    JSON encoded result.
     */
    public function ajaxValidateField($content, $params)
    {
        $arguments = $params['arguments'];

        // @todo : Exception ?
        if (!isset($arguments['fieldName']) || !isset($arguments['value']) || !isset($arguments['pageUid'])) {
            return '';
        }

        // @todo : check pageUid
        $field = Fields\Field::getField($arguments['fieldName'], $arguments['pageUid']);
        $field->setValue($arguments['value']);

        $validation = $this->validateField($field);

        $validationResult = Core::convertValidationResultToArray($validation['validationResult']);

        $validation['validationResult'] = $validationResult;

        return json_encode($validation);
    }
}
