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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use Romm\SiteFactory\Form\Validation\AbstractValidator;
use Romm\SiteFactory\Core\Core;

/**
 * A class allowing to manage the fields configuration.
 *
 * See $allowedFieldTypes, $requiredFieldsConfiguration and $translatedFields
 * for further information.
 */
abstract class AbstractField extends AbstractEntity implements FieldInterface {
	/** Different field types. */
	const FIELD_TYPE_TEXT		= 'text';
	const FIELD_TYPE_SELECT		= 'select';
	const FIELD_TYPE_CHECKBOX	= 'checkbox';
	const FIELD_TYPE_RADIO		= 'radio';
	const FIELD_TYPE_TEXTAREA	= 'textarea';
	const FIELD_TYPE_HIDDEN		= 'hidden';

	/** @var array Array containing the JavaScript files which will be imported during the action "New". */
	protected $javaScriptFilesNewAction = array();

	/** @var array Array containing the CSS files which will be imported during the action "New". */
	protected $cssFilesNewAction = array();

	/**
	 * Array containing paths to partials that should be displayed only one
	 * time. For example, if you want a JavaScript's HTML template, it should
	 * be imported once, even if two fields of the same type are created.
	 *
	 * @var array
	 * @fill
	 */
	protected $partialsHeader = array();

	/** @var array Array containing the properties that must be filled for the field. */
	protected $requiredFieldsConfiguration = array();

	/** @var array Properties that should always be filled for a field. */
	private $defaultRequiredFieldsProperties = array('label');

	/** @var array Merge between the field's required properties and the default required one. */
	private $finalRequiredFieldsProperties = array();

	/**
	 * The type of the field.
	 *
	 * @var string
	 * @fill
	 */
	protected $type;

	/** @var mixed The field type of the field : text, checkbox, select, etc.. */
	protected $fieldType = self::FIELD_TYPE_TEXT;

	/**
	 * The name of the field.
	 *
	 * @var string
	 * @fill
	 */
	protected $name;

	/** @var bool Used to know if a value has been set. Useful because Fluid thinks an empty string is a correct value. */
	private $hasValue = false;

	/** @var mixed The value of the field. */
	protected $value;

	/**
	 * The default value of the field, mainly useful for the form.
	 *
	 * @var mixed
	 * @fill
	 */
	protected $defaultValue;

	/**
	 * The label of the field, mainly useful for the form.
	 *
	 * @var string
	 * @fill
	 */
	protected $label;

	/**
	 * The hint of the field, mainly useful for the form.
	 *
	 * @var string
	 * @fill
	 */
	protected $hint = '';

	/** @var array Array containing the default validators of the field. */
	protected $localValidation = array();

	/**
	 * Settings of the field.
	 *
	 * @var array
	 * @fill
	 */
	protected $settings = array();

	/**
	 * The validators of the field. A validator must have a "validator" index
	 * containing a reference to a class extending: \Romm\SiteFactory\Form\Validation\AbstractValidator
	 * It may also have an "error" index containing the label of the error if
	 * the validator does not match the value.
	 *
	 * @var array
	 * @fill
	 */
	protected $validation = array();

	/**
	 * The results of the validation process.
	 *
	 * @var \TYPO3\CMS\Extbase\Error\Result[]
	 */
	protected $validationResult = array();

	/**
	 * The results of the validation process, merged in one result.
	 *
	 * This variable is in fact an array containing one result: the key is the
	 * MD5 value of the validation result at the time the function is called.
	 * This is made to increase performances when the function is called several
	 * times.
	 *
	 * @var \TYPO3\CMS\Extbase\Error\Result[]
	 */
	protected $mergedValidationResult = array();

	/**
	 * Array containing md5 values of arrays that have already been checked by
	 * the method "checkValidationConfiguration".
	 *
	 * @var array
	 */
	protected $validationChecked = array();

	/**
	 * If true, the field won't be active if the current site is being edited.
	 *
	 * @var bool
	 * @fill
	 */
	protected $hideInSiteModification = false;

	/**
	 * Constructor of the class, must contain the parameters "name" and "type".
	 *
	 * @param $name	string	The name of the field.
	 * @param $type	string	The type of the field.
	 */
	public function __construct($name, $type) {
		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * Will fill field's existing properties with the given configuration.
	 *
	 * @param $configuration	array	The configuration.
	 * @throws \Exception
	 */
	public function fillConfiguration($configuration) {
		/** @var \TYPO3\CMS\Extbase\Reflection\ClassReflection $propertyReflection */
		$propertyReflection = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', get_class($this));

		// Setting properties.
		foreach($configuration as $configurationName => $configurationValue) {
			if ($propertyReflection->hasProperty($configurationName)) {
				$property = $propertyReflection->getProperty($configurationName);
				if ($property->isTaggedWith('fill')) {
					$methodName = 'set' . GeneralUtility::underscoredToUpperCamelCase($configurationName);
					if ($propertyReflection->hasMethod($methodName))
						$this->$methodName($configurationValue);
					else
						$this->$configurationName = $configurationValue;
				}
			}
		}

		// Checking required properties.
		$requiredFieldProperties = $this->getRequiredFieldProperties();
		foreach($requiredFieldProperties as $configurationName) {
			if (!$this->$configurationName) {
				throw new \Exception('The field "' . $this->name . '" must have a value for "' . $configurationName . '".', 1423775355);
			}
		}
	}

	/**
	 * Processes the validation of the field, using on the validators set in the
	 * field's configuration.
	 *
	 * @return	$this
	 */
	public function validate() {
		$validation = $this->getValidation();

		foreach($validation as $validatorName => $validatorConfiguration) {
			/** @var \Romm\SiteFactory\Form\Validation\AbstractValidator $validator */
			$validator = $validatorConfiguration['validator'];
			$this->validationResult[$validatorName] = $validator->validate($this);
		}

		return $this;
	}

	/**
	 * @return	array	List of JavaScript files used for the action "New".
	 */
	public function getJavaScriptFilesNewAction() {
		return $this->javaScriptFilesNewAction;
	}

	/**
	 * @return	array	List of CSS files used for the action "New".
	 */
	public function getCssFilesNewAction() {
		return $this->cssFilesNewAction;
	}

	/**
	 * @return	array
	 */
	public function getPartialsHeader() {
		return $this->partialsHeader;
	}

	/**
	 * Will merge the field's required configuration and the default required one.
	 *
	 * @return array	The merged field's required configuration.
	 */
	private function getRequiredFieldProperties() {
		if (empty($this->finalRequiredFieldsProperties)) {
			$this->finalRequiredFieldsProperties = ArrayUtility::arrayMergeRecursiveOverrule($this->requiredFieldsConfiguration, $this->defaultRequiredFieldsProperties);

			// Deleting all non string values.
			foreach($this->finalRequiredFieldsProperties as $key => $configuration) {
				if (!is_string($configuration))
					unset($this->finalRequiredFieldsProperties[$key]);
			}
		}
		return $this->finalRequiredFieldsProperties;
	}

	/**
	 * @return string	The type of the field.
	 */
	public function getType() {
		return GeneralUtility::underscoredToUpperCamelCase($this->type);
	}

	/**
	 * @return string	The type of the field.
	 */
	public function getLowerCaseType() {
		return $this->type;
	}

	/**
	 * @return string	The field type of the field.
	 * @throws \Exception
	 */
	public function getFieldType() {
		switch($this->fieldType) {
			case self::FIELD_TYPE_TEXT:
			case self::FIELD_TYPE_SELECT:
			case self::FIELD_TYPE_CHECKBOX:
			case self::FIELD_TYPE_RADIO:
			case self::FIELD_TYPE_TEXTAREA:
			case self::FIELD_TYPE_HIDDEN:
				break;
			default:
				throw new \Exception('The property of $fieldType for the class ' . __CLASS__ . ' is not valid.', 1425061903);
		}

		return GeneralUtility::underscoredToUpperCamelCase($this->fieldType);
	}

	/**
	 * @return string	The field type of the field.
	 */
	public function getLowerCaseFieldType() {
		return GeneralUtility::camelCaseToLowerCaseUnderscored($this->fieldType);
	}

	/**
	 * @return string	The name of the field.
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the value of the field.
	 *
	 * @param mixed $value
	 * @return $this
	 */
	public function setValue($value) {
		if (!empty($value))
			$this->hasValue = true;
		$this->value = $value;
		return $this;
	}

	/**
	 * @return mixed	The value of the field.
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Returns a human readable version of the value. Useful for select fields,
	 * for example.
	 * Override this function in your class if you want a custom behavior.
	 *
	 * @return mixed	The value of the field, in a human readable version.
	 */
	public function getDisplayValue() {
		return $this->value;
	}

	/**
	 * @return bool	True if the field has a value.
	 */
	public function getHasValue() {
		return $this->hasValue;
	}

	/**
	 * Sets the default value of the field.
	 *
	 * @param string $defaultValue
	 * @return $this
	 */
	public function setDefaultValue($defaultValue) {
		$this->defaultValue = (string)$defaultValue;
		return $this;
	}

	/**
	 * @return mixed	The default value of the field.
	 */
	public function getDefaultValue() {
		return $this->defaultValue;
	}

	/**
	 * Sets the label of the field.
	 *
	 * @param string $label
	 * @return $this
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @return mixed	The label of the field.
	 */
	public function getLabel() {
		return Core::translate((string)$this->label);
	}

	/**
	 * Sets the hint of the field.
	 *
	 * @param string $hint
	 * @return $this
	 */
	public function setHint($hint) {
		$this->hint = Core::translate((string)$hint);
		return $this;
	}

	/**
	 * @return mixed	The hint of the field.
	 */
	public function getHint() {
		return $this->hint;
	}

	/**
	 * Sets the settings array of the field.
	 *
	 * @param	array	$settings	Array containing the settings.
	 * @return	$this
	 */
	public function setSettings($settings) {
		$settings = (is_array($settings)) ? $settings : array();
		$this->settings = $settings ;

		return $this;
	}

	/**
	 * @param	string	$path		The path within the settings, if none given, the full settings will be returned.
	 * @param	string	$delimiter	The delimiter for path, default ".".
	 * @return mixed	The settings of the field.
	 */
	public function getSettings($path = null, $delimiter = '.') {
		if ($path) {
			$setting = (\TYPO3\CMS\Core\Utility\ArrayUtility::isValidPath($this->settings, $path, $delimiter))
				? \TYPO3\CMS\Core\Utility\ArrayUtility::getValueByPath($this->settings, $path, $delimiter)
				: null;

			return $setting;
		}
		return $this->settings;
	}

	/**
	 * @return mixed	The settings of the field, converted to JSON.
	 */
	public function getSettingsJson() {
		return json_encode($this->settings);
	}

	/**
	 * Sets the validation array of the field.
	 *
	 * @param	array	$validation	Array containing the validators.
	 * @return	$this
	 */
	public function setValidation(array $validation) {
		$this->checkValidationArrayConfiguration($validation);

		$this->validation = $validation;
		return $this;
	}

	/**
	 * First checks if all the validation are correctly filled.
	 *
	 * @return	array	The validation of the field.
	 */
	public function getValidation() {
		$this->checkValidationArrayConfiguration($this->localValidation);
		$this->checkValidationArrayConfiguration($this->validation);

		return ArrayUtility::arrayMergeRecursiveOverrule($this->localValidation, $this->validation);
	}

	/**
	 * The validation result of the field.
	 *
	 * @return	\TYPO3\CMS\Extbase\Error\Result[]	The validation result of the field.
	 */
	public function getValidationResult() {
		return $this->validationResult;
	}

	/**
	 * Returns all the validation results merged in one result.
	 *
	 * @return	\TYPO3\CMS\Extbase\Error\Result
	 */
	public function getMergedValidationResult() {
		$hash = md5(serialize($this->validationResult));
		if (!array_key_exists($hash, $this->mergedValidationResult)) {
			unset($this->mergedValidationResult);

			/** @var \TYPO3\CMS\Extbase\Error\Result $result */
			$result = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Error\\Result');
			foreach($this->validationResult as $validationResult) {
				$result->merge($validationResult);
			}

			$this->mergedValidationResult[$hash] = $result;
		}

		return $this->mergedValidationResult[$hash];
	}

	/**
	 * Checks every validator in the given array.
	 *
	 * @param	array	$validation	The array containing all the validators.
	 */
	private function checkValidationArrayConfiguration(array &$validation) {
		foreach($validation as $validatorName => &$validatorConfiguration) {
			$this->checkValidationConfiguration($validatorName, $validatorConfiguration);
		}
	}

	/**
	 * @param	string	$validatorName			The name of the validator.
	 * @param	array	$validatorConfiguration	The configuration of the validator, must contain a correct value for the "validator" key.
	 * @throws	\Exception
	 */
	private function checkValidationConfiguration($validatorName, &$validatorConfiguration) {
		$hash = array_key_exists('checkHash', $validatorConfiguration) ? $validatorConfiguration['checkHash'] : null;
		if (in_array($hash, $this->validationChecked)) return;

		// The rule must have a "validator" property.
		if (!isset($validatorConfiguration['validator'])) {
			throw new \Exception('The validation "' . $validatorName . '" of the field "' . $this->name . '" must have a value for "validator".', 1423781095);
		}

		// Check if the validator is correct.
		if (!class_exists($validatorConfiguration['validator'])) {
			throw new \Exception('The validator of the validation "' . $validatorName . '" for the field "' . $this->name . '" is not a correct validator (current value: "' . $validatorConfiguration['validator'] . '"").', 1423781479);
		}

		$validator = GeneralUtility::makeInstance($validatorConfiguration['validator']);
		if (!$validator instanceof AbstractValidator) {
			throw new \Exception('The validator of the validation "' . $validatorName . '" for the field "' . $this->name . ' must extend \Romm\SiteFactory\Form\Validation\AbstractValidator ', 1429466899);
		}

		// Overwrite the validator name with the created class.
		$validatorConfiguration['validator'] = $validator;

		// Adding the validator to the list of checked validations.
		$hash = md5(serialize(array($validatorName => $validatorConfiguration)));
		$validatorConfiguration['checkHash'] = $hash;
		$this->validationChecked[] = $hash;
	}

	/**
	 * Sets the "hideInSiteModification" property of the field.
	 *
	 * @param bool $hideInSiteModification
	 * @return $this
	 */
	public function setHideInSiteModification($hideInSiteModification) {
		$this->hideInSiteModification = $hideInSiteModification;
		return $this;
	}

	/**
	 * @return bool	The "hideInSiteModification" property of the field.
	 */
	public function getHideInSiteModification() {
		return (bool)$this->hideInSiteModification;
	}
}
