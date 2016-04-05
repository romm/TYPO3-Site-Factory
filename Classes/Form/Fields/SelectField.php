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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
class SelectField extends AbstractField
{

    /** @var mixed The field type of the field : text, checkbox, select, etc.. */
    protected $fieldType = AbstractField::FIELD_TYPE_SELECT;

    /** @var array Array containing the JavaScript files which will be imported. */
    protected $javaScriptFilesNewAction = [
        'EXT:site_factory/Resources/Public/JavaScript/Fields/SiteFactory.Field.Select.js'
    ];

    /** @var array Array containing the default rules for the field. */
    protected $localValidation = [
        'options' => [
            'validator' => 'Romm\\SiteFactory\\Form\\Validation\\SelectOptionsValidator',
            'error'     => 'form.field.error.picker_color' // @todo
        ]
    ];

    /** @var array Array containing the properties that must be filled for the field. */
    protected $requiredFieldsConfiguration = ['options'];

    /**
     * The options of the field, mainly useful for the form.
     *
     * @var
     * @fill
     */
    protected $options;

    /**
     * Sets the options of the field.
     *
     * @param array|string $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array    The options of the field.
     */
    public function getOptions()
    {
        if (!is_array($this->options)) {
            $params = [];
            $options = GeneralUtility::callUserFunction($this->options, $params, $this);
            if ($options) {
                $this->options = (array)$options;
            }
        }

        return $this->options;
    }

    /**
     * Returns a human readable version of the value. Useful for select fields,
     * for example.
     * Override this function in your class if you want a custom behavior.
     *
     * @return mixed    The value of the field, in a human readable version.
     */
    public function getDisplayValue()
    {
        $options = $this->getOptions();
        if (array_key_exists($this->getValue(), $options)) {
            $value = $options[$this->getValue()];
        } else {
            $value = $this->value;
        }

        return $value;
    }
}
