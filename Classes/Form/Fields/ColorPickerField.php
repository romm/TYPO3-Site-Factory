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
class ColorPickerField extends TextField {
	/** @var mixed The field type of the field : text, checkbox, select, etc.. */
	protected $fieldType = AbstractField::FIELD_TYPE_TEXT;

	/** @var array Array containing the JavaScript files which will be imported. */
	protected $javaScriptFilesNewAction = array(
		'EXT:site_factory/Resources/Public/JavaScript/Fields/SiteFactory.Field.ColorPicker.js'
	);

	/** @var array Array containing the default rules for the field. */
	protected $localValidation = array(
		'hexadecimal' => array(
			'validator'	=>  'Romm\\SiteFactory\\Form\\Validation\\HexadecimalValidator',
			'error'	=> 'form.field.error.picker_color'
		)
	);
}