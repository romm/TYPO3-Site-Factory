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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
class SelectField extends AbstractField {
	/** @var mixed The field type of the field : text, checkbox, select, etc.. */
	protected $fieldType = AbstractField::FIELD_TYPE_SELECT;

	/** @var array Array containing the JavaScript files which will be imported. */
	protected $javaScriptFilesNewAction = array(
		'EXT:site_factory/Resources/Public/JavaScript/Fields/SiteFactory.Field.Select.js'
	);

	/** @var array Array containing the default rules for the field. */
	protected $localValidation = array(
		'options' => array(
			'validator'	=>  'Romm\\SiteFactory\\Form\\Validation\\SelectOptionsValidator',
			'error'	=> 'form.field.error.picker_color' // @todo
		)
	);

	/** @var array Array containing the properties that must be filled for the field. */
	protected $requiredFieldsConfiguration = array('options');

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
	public function setOptions($options) {
		$this->options = $options;
		return $this;
	}

	/**
	 * @return array	The options of the field.
	 */
	public function getOptions() {
		if (!is_array($this->options)) {
			$params = array();
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
	 * @return mixed	The value of the field, in a human readable version.
	 */
	public function getDisplayValue() {
		$options = $this->getOptions();
		if (array_key_exists($this->getValue(), $options))
			$value = $options[$this->getValue()];
		else
			$value = $this->value;

		return $value;
	}
}