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

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
class CheckboxField extends AbstractField
{

    /** @var array Array containing the properties that must be filled for the field. */
    protected $requiredFieldsConfiguration = [];

    /** @var mixed The field type of the field : text, checkbox, select, etc.. */
    protected $fieldType = AbstractField::FIELD_TYPE_CHECKBOX;

    /**
     * Returns a human readable version of the value. Useful for select fields,
     * for example.
     * Override this function in your class if you want a custom behavior.
     *
     * @return mixed    The value of the field, in a human readable version.
     */
    public function getDisplayValue()
    {
        if ($this->value) {
            $value = '&#10003;';
        } else {
            $value = $this->value;
        }

        return $value;
    }
}
