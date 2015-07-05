<?php
namespace Romm\SiteFactory\Duplication;

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

use Romm\SiteFactory\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Warning;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Romm\SiteFactory\Core\Configuration\Configuration;
use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Form\Fields\Field;

/**
 * Class containing functions called when a site is being duplicated.
 */
abstract class AbstractDuplicationProcess implements DuplicationProcessInterface {
	/** @var array */
	private static $duplicationConfiguration = array();

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
	protected $objectManager;

	/** @var \TYPO3\CMS\Core\Database\DatabaseConnection */
	protected $database;

	/**
	 * The configuration of the extension.
	 *
	 * @var array
	 */
	protected $extensionConfiguration;

	/**
	 * The data of the duplication process. This array will be filled up during
	 * the several processes.
	 *
	 * @var array
	 */
	private $data = array();

	/** @var array */
	private $settings = array();

	/** @var \Romm\SiteFactory\Form\Fields\AbstractField[] */
	private $fields;

	/**
	 * @var \TYPO3\CMS\Extbase\Error\Result
	 */
	protected $result;

	/** @var null|int */
	private $duplicatedPageUid = null;

	/** @var null|int */
	private $modelPageUid = null;

	/**
	 * Construction function.
	 *
	 * @param array $duplicationData
	 * @param array $duplicationSettings
	 * @param array $fieldsValues
	 */
	public function __construct(array $duplicationData = array(), array $duplicationSettings = array(), $fieldsValues = array()) {
		$this->objectManager = Core::getObjectManager();
		$this->database = Core::getDatabase();
		$this->extensionConfiguration = Core::getExtensionConfiguration();
		$this->result = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Error\\Result');

		$this->setDuplicationData($duplicationData);
		$this->setDuplicationSettings($duplicationSettings);
		$this->initializeFields($fieldsValues);
	}

	/**
	 * Do the process of your duplication step in this function.
	 *
	 * @return bool
	 */
	public function run() {}

	/**
	 * Will initialize all the fields, based on the uid of the model page. Will
	 * fill the values of these fields with the duplication data.
	 *
	 * @param	array	$fieldsValues	The fields values in an array: name => value.
	 * @return	$this
	 * @throws	\Exception
	 */
	private function initializeFields(array $fieldsValues) {
		$id = $this->getModelPageUid();
		if ($id) {
			$this->fields = Field::getFields($id); // @todo Check site modification?

			if (is_array($fieldsValues) && !empty($fieldsValues)) {
				foreach($fieldsValues as $fieldName => $fieldValue) {
					if (array_key_exists($fieldName, $this->fields)) {
						$this->fields[$fieldName]->setValue($fieldValue);
					}
				}
			}
		}

		return $this;
	}

	public function getFieldsValues() {
		$fieldsValues = array();
		foreach ($this->getFields() as $field)
			$fieldsValues[$field->getName()] = $field->getValue();
		return $fieldsValues;
	}

	/**
	 * @return \Romm\SiteFactory\Form\Fields\AbstractField[]
	 */
	protected function getFields() {
		return $this->fields;
	}

	/**
	 * Returns an existing field, or null if it does not exist.
	 *
	 * @param	string	$fieldName	The field's name.
	 * @return	\Romm\SiteFactory\Form\Fields\AbstractField|null
	 */
	protected function getField($fieldName) {
		if (array_key_exists($fieldName, $this->fields)) {
			return $this->fields[$fieldName];
		}
		return null;
	}

	/**
	 * Set the data of the duplication process.
	 *
	 * @param	array	$data	The data of the duplication process.
	 * @return	$this
	 */
	private function setDuplicationData(array $data) {
		$this->data = $data;
		return $this;
	}

	/**
	 * Set the value within the data of the duplication process, at the given
	 * path.
	 *
	 * @param	string	$path		The path within the data.
	 * @param	mixed	$value		The value to set at given path in data.
	 * @param	string	$delimiter	The delimiter for path, default "/".
	 * @return	$this
	 */
	protected function setDuplicationDataValue($path, $value, $delimiter = '/') {
		$this->data = ArrayUtility::setValueByPath($this->data, $path, $value, $delimiter);
		return $this;
	}

	/**
	 * Get the value within the data of the duplication process, at the given
	 * path.
	 *
	 * @param	string	$path		The path within the data, if none given, the full data will be returned.
	 * @param	string	$delimiter	The delimiter for path, default "/".
	 * @return	mixed	The data of the process at the given path.
	 */
	public function getDuplicationData($path = null, $delimiter = '/') {
		if ($path) {
			return (ArrayUtility::isValidPath($this->data, $path, $delimiter))
				? ArrayUtility::getValueByPath($this->data, $path, $delimiter)
				: null;
		}
		return $this->data;
	}

	/**
	 * Set the settings of the duplication process.
	 *
	 * @param	array	$settings	The settings of the duplication process.
	 * @return	$this
	 */
	private function setDuplicationSettings(array $settings) {
		$this->settings = $settings;
		return $this;
	}

	/**
	 * Get the value within the settings of the duplication process, at the
	 * given path.
	 * If $path begins with "data:", the function will search for the
	 * duplication data at the given index (string after "data:"), and return
	 * the value, if found.
	 *
	 * @param	string	$path		The path within the settings, if none given, the full settings will be returned.
	 * @param	string	$delimiter	The delimiter for path, default ".".
	 * @return	mixed	The settings of the process at the given path, or the duplication data value (see function documentation).
	 */
	public function getProcessSettings($path = null, $delimiter = '.') {
		if ($path) {
			$setting = (ArrayUtility::isValidPath($this->settings, $path, $delimiter))
				? ArrayUtility::getValueByPath($this->settings, $path, $delimiter)
				: null;

			if ($setting) {
				if (strpos($setting, 'data:') !== false) {
					$settingDataKey = substr($setting, 5);
					if ($this->getDuplicationData($settingDataKey))
						$setting = $this->getDuplicationData($settingDataKey);
				}
			}

			return $setting;
		}
		return $this->settings;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Error\Result
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * This function will, at its first call, process a clean of the process
	 * configuration.
	 * The configuration will be stored in cache to increase performances for
	 * future calls.
	 *
	 * @param $pageUid						int		The uid of the page where to get the configuration.
	 * @param $onlyUsedInSiteModification	bool	Set to true if you only want the configuration which will be used during a modification, not a creation.
	 * @return array The cleaned configuration.
	 */
	public static function getCleanedDuplicationConfiguration($pageUid, $onlyUsedInSiteModification = false) {
		// As the result stored in cache may differ with $onlyUsedInSiteModification, we need to store the data in a different cache file.
		$siteModificationToken = ($onlyUsedInSiteModification) ? '1' : '0';

		if (!self::$duplicationConfiguration[$pageUid][$siteModificationToken]) {
			$duplicationConfiguration = Core::sortArrayByPositionValue(TypoScriptUtility::getExtensionConfigurationFromPath('duplication', $pageUid));

			$cleanedDuplicationConfiguration = array();

			// For HTML convention causes, we replace the lower-camel-case indexes with lower-case-underscored ones.
			foreach($duplicationConfiguration as $key => $configuration) {
				if ($onlyUsedInSiteModification) {
					if (!$configuration['usedInSiteModification']) continue;
				}

				$newKey = GeneralUtility::camelCaseToLowerCaseUnderscored($key);
				$newKey = str_replace('_', '-', $newKey);
				$cleanedDuplicationConfiguration[$newKey] = $configuration;
			}

			self::$duplicationConfiguration[$pageUid][$siteModificationToken] = $cleanedDuplicationConfiguration;
		}

		return self::$duplicationConfiguration[$pageUid][$siteModificationToken];
	}

	/**
	 * Adds an error to the process result.
	 *
	 * @param	string	$message	The message, can be a locallang reference.
	 * @param	int		$code		A unique code for this error.
	 * @param	array	$arguments	Array of arguments to be replaced in the message.
	 * @param	string	$title		The title for the message.
	 */
	protected function addError($message, $code, array $arguments = array(), $title = '') {
		$this->addMessage('error', $message, $code, $arguments, $title);
	}

	/**
	 * Adds a warning to the process result.
	 *
	 * @param	string	$message	The message, can be a locallang reference.
	 * @param	int		$code		A unique code for this warning.
	 * @param	array	$arguments	Array of arguments to be replaced in the message.
	 * @param	string	$title		The title for the message.
	 */
	protected function addWarning($message, $code, array $arguments = array(), $title = '') {
		$this->addMessage('warning', $message, $code, $arguments, $title);
	}

	/**
	 * Adds a notice to the process result.
	 *
	 * @param	string	$message	The message, can be a locallang reference.
	 * @param	int		$code		A unique code for this notice.
	 * @param	array	$arguments	Array of arguments to be replaced in the message.
	 * @param	string	$title		The title for the message.
	 */
	protected function addNotice($message, $code, array $arguments = array(), $title = '') {
		$this->addMessage('notice', $message, $code, $arguments, $title);
	}

	/**
	 * Adds a message (error, warning or notice) to the process result.
	 *
	 * @param	string	$type		The type of the message. Can only be one of the following: error, warning or notice.
	 * @param	string	$message	The message, can be a locallang reference.
	 * @param	int		$code		A unique code for this notice.
	 * @param	array	$arguments	Array of arguments to be replaced in the message.
	 * @param	string	$title		The title for the message.
	 */
	private function addMessage($type, $message, $code, array $arguments = array(), $title = '') {
		if (!in_array(strtolower($type), array('error', 'warning', 'notice'))) return;

		$function = 'add' . ucfirst($type);
		$type = 'TYPO3\\CMS\\Extbase\\Error\\' . ucfirst($type);

		$this->result->$function(
			new $type(
				Core::translate(
					$message
				),
				$code,
				$arguments,
				$title
			)
		);
	}

	/**
	 * Checks if the current request is called via Ajax.
	 *
	 * @return bool
	 */
	public function checkAjaxCall() {
		if ($GLOBALS['ajaxObj'] instanceof \TYPO3\CMS\Core\Http\AjaxRequestHandler) {
			return true;
		}
		return false;
	}

	/**
	 * This function is only here to help getting the model page's uid and
	 * prevents doing basic checks everytime.
	 *
	 * @return	int|null	The model page uid, or null if the uid is wrong.
	 */
	protected function getModelPageUid() {
		if (!$this->modelPageUid) {
			$modelPageUid = intval($this->getDuplicationData('modelPageUid'));
			$flag = true;

			if (!$modelPageUid)
				$flag = false;
			else {
				$testModelPageUid = $this->database->exec_SELECTgetSingleRow('uid', 'pages', 'deleted=0 AND uid=' . $modelPageUid);
				if ($testModelPageUid === false)
					$flag = false;
			}

			if (!$flag)
				$modelPageUid = null;

			$this->modelPageUid = $modelPageUid;
		}

		if (!$this->modelPageUid) {
			$this->addError(
				'duplication_process.error.model_page.wrong_uid',
				1431372959,
				array('d' => $this->getDuplicationData('modelPageUid'))
			);
		}

		return $this->modelPageUid;
	}

	/**
	 * This function is only here to help getting the duplicated page's uid and
	 * prevents doing basic checks everytime.
	 *
	 * @return	int|null	The duplicated page uid, or null if the uid is wrong.
	 */
	protected function getDuplicatedPageUid() {
		if (!$this->duplicatedPageUid) {
			$duplicatedPageUid = intval($this->getDuplicationData('duplicatedPageUid'));
			$flag = true;

			if (!$duplicatedPageUid)
				$flag = false;
			else {
				$testDuplicatedPageUid = $this->database->exec_SELECTgetSingleRow('uid', 'pages', 'deleted=0 AND uid=' . $duplicatedPageUid);
				if ($testDuplicatedPageUid === false)
					$flag = false;
			}

			if (!$flag)
				$duplicatedPageUid = null;

			$this->duplicatedPageUid = $duplicatedPageUid;
		}

		if (!$this->duplicatedPageUid) {
			$this->addError(
				'duplication_process.error.duplicated_page.wrong_uid',
				1431373710,
				array('d' => $this->getDuplicationData('duplicatedPageUid'))
			);
		}

		return $this->duplicatedPageUid;
	}
}