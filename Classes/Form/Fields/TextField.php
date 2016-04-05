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

use Romm\SiteFactory\Core\Core;

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
class TextField extends AbstractField
{

    /** @var mixed The field type of the field : text, checkbox, select, etc.. */
    protected $fieldType = AbstractField::FIELD_TYPE_TEXT;

    /** @var array Array containing the JavaScript files which will be imported. */
    protected $javaScriptFilesNewAction = [
        'EXT:site_factory/Resources/Public/JavaScript/Fields/SiteFactory.Field.Text.js'
    ];

    /** @var array Array containing the properties that must be filled for the field. */
    protected $requiredFieldsConfiguration = [];

    /**
     * The placeholder of the field, mainly useful for the form.
     *
     * @var string
     * @fill
     */
    protected $placeholder = '';

    /**
     * Sets the default value of the field.
     *
     * @param string $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = Core::translate((string)$defaultValue);

        return $this;
    }

    /**
     * Sets the placeholder of the field.
     *
     * @param string $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = Core::translate((string)$placeholder);

        return $this;
    }

    /**
     * @return string    The placeholder of the field.
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }
}
