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

namespace Romm\SiteFactory\Form\Fields;

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
class ImageUploadPickerField extends AbstractField
{

    /** @var mixed The field type of the field : text, checkbox, select, etc.. */
    protected $fieldType = AbstractField::FIELD_TYPE_HIDDEN;

    /** @var array Array containing the JavaScript files which will be imported. */
    protected $javaScriptFilesNewAction = [
        'EXT:site_factory/Resources/Public/Contrib/fine-uploader/fine-uploader.min.js',
        'EXT:site_factory/Resources/Public/JavaScript/Fields/SiteFactory.Field.ImageUploadPicker.js'
    ];

    /** @var array Array containing the JavaScript files which will be imported. */
    protected $cssFilesNewAction = [
        'EXT:site_factory/Resources/Public/Contrib/fine-uploader/fine-uploader-new.min.css',
        'EXT:site_factory/Resources/Public/StyleSheets/site-factory-fine-uploader.css'
    ];

    /** @var array Array containing the properties that must be filled for the field. */
    protected $requiredFieldsConfiguration = [];

    /**
     * Returns a human readable version of the value. Useful for select fields,
     * for example.
     * Override this function in your class if you want a custom behavior.
     *
     * @return mixed    The value of the field, in a human readable version.
     */
    public function getDisplayValue()
    {
        if (substr($this->getValue(), 0, 4) == 'new:') {
            $value = substr($this->getValue(), 4, strlen($this->getValue()) - 4);
        } else {
            $value = $this->value;
        }

        return $value;
    }
}
