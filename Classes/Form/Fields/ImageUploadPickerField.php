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
class ImageUploadPickerField extends AbstractField {
	/** @var mixed The field type of the field : text, checkbox, select, etc.. */
	protected $fieldType = AbstractField::FIELD_TYPE_HIDDEN;

	/** @var array Array containing the JavaScript files which will be imported. */
	protected $javaScriptFilesNewAction = array(
		'EXT:site_factory/Resources/Public/Contrib/fine-uploader/fine-uploader.min.js',
		'EXT:site_factory/Resources/Public/JavaScript/Fields/SiteFactory.Field.ImageUploadPicker.js'
	);

	/** @var array Array containing the JavaScript files which will be imported. */
	protected $cssFilesNewAction = array(
		'EXT:site_factory/Resources/Public/Contrib/fine-uploader/fine-uploader-new.min.css',
		'EXT:site_factory/Resources/Public/StyleSheets/site-factory-fine-uploader.css'
	);

	/** @var array Array containing the properties that must be filled for the field. */
	protected $requiredFieldsConfiguration = array();

	/**
	 * Returns a human readable version of the value. Useful for select fields,
	 * for example.
	 * Override this function in your class if you want a custom behavior.
	 *
	 * @return mixed	The value of the field, in a human readable version.
	 */
	public function getDisplayValue() {
		if (substr($this->getValue(), 0, 4) == 'new:')
			$value = substr($this->getValue(), 4, strlen($this->getValue()) - 4);
		else
			$value = $this->value;
		return $value;
	}
}
