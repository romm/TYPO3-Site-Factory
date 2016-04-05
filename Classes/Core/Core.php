<?php
namespace Romm\SiteFactory\Core;

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

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Error;
use Romm\SiteFactory\Form\FieldsConfigurationPresets;

/**
 * Core class of the extension, containing common functions that can be used
 * everywhere.
 */
class Core
{

    const EXTENSION_KEY = 'site_factory';

    /**
     * The path to the folder where the extension will be able to manipulate
     * temporary files.
     */
    const PROCESSED_FOLDER_PATH = 'uploads/tx_sitefactory/_processed_/';

    /** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
    private static $objectManager;

    /**
     * Translation handler.
     *
     * @param    string $index     The index to the LLL reference.
     * @param    array  $arguments Arguments passed over to vsprintf.
     * @return    string                The translated string.
     */
    public static function translate($index, $arguments = null)
    {
        $result = LocalizationUtility::translate($index, self::EXTENSION_KEY, $arguments);
        if ($result == '' && $index != '') {
            $result = $index;
        }

        return $result;
    }

    /**
     * Will remove accents from a string.
     * Example: "à" will become "a".
     *
     * @param    $text    string    The string you want to format.
     * @return    string    The formatted string.
     */
    public static function formatAccentsInString($text)
    {
        $translationTable = get_html_translation_table(HTML_ENTITIES);
        $search = [];
        $replace = [];
        foreach ($translationTable as $literal => $entity) {
            if (ord($literal) >= 192) {
                $replace[] = substr($entity, 1, 1);
                $search[] = $literal;
            }
        }

        return str_replace($search, $replace, $text);
    }

    /**
     * Converts an object based validation result to an array (can be used to
     * be converted for JSON usage).
     *
     * @param    Error\Result|Error\Result[] $validationResult The validation result you want to convert.
     * @return    array                        The converted result.
     */
    static public function convertValidationResultToArray($validationResult)
    {
        $validationResultArray = [
            'errors'   => [],
            'warnings' => [],
            'notices'  => []
        ];
        foreach ($validationResultArray as $validationName => $validationValue) {
            $validationResultArray[$validationName] = [];
            if (!is_array($validationResult)) {
                $validationResult = [$validationResult];
            }
            foreach ($validationResult as $validatorResult) {
                /** @var \TYPO3\CMS\Extbase\Error\Message[] $values */
                $values = ObjectAccess::getProperty($validatorResult, $validationName);
                foreach ($values as $value) {
                    $validationResultArray[$validationName][] = $value->render();
                }
            }
        }

        return $validationResultArray;
    }

    /**
     * Will manage the sorting of an array, depending on the value at the index
     * "position" (by default). Values can be:
     *  - "first": The field will be at the beginning of the array.
     *  - "last": The field will be at the end of the array.
     *  - "after:[key]": The field will be placed right after the desired key.
     *  - "before:[key]": The field will be placed right before the desired key.
     *
     * Example:
     * $myArray = array(
     *        'Apple'        => array(
     *            'position'    => 'first'
     *        ),
     *        'Pear'        => array(
     *            'position'    => 'last'
     *        ),
     *        'Banana'    => array(
     *            'foo'        => 'bar',
     *            'position'    => 'after:Potato'
     *        ),
     *        'Potato'    => array(
     *            'position'    => 'before:Banana'
     *        )
     * );
     *
     * After calling the function below, the keys will be sorted like that:
     * Apple, Potato, Banana, Pear
     *
     * @param array  $fieldsConfiguration The configuration array, constructed like the example above.
     * @param string $positionKey         If you want to use another key for the position value.
     * @return array    The sorted array.
     */
    public static function sortArrayByPositionValue($fieldsConfiguration, $positionKey = 'position')
    {
        // @todo: comments
        $sortingKeysAssociation = [];
        $currentFields = $fieldsConfiguration;
        $cachedFields = [];
        $lastFields = [];

        while (!empty($currentFields)) {
            $remainingFields = [];

            foreach ($currentFields as $fieldName => $configuration) {
                if (isset($configuration[$positionKey])) {
                    $positionType = $configuration[$positionKey];
                    if (substr($configuration[$positionKey], 0, 6) == 'after:') {
                        $positionType = 'after';
                    }
                    if (substr($configuration[$positionKey], 0, 7) == 'before:') {
                        $positionType = 'before';
                    }

                    switch ($positionType) {
                        case 'first':
                            for ($i = count($sortingKeysAssociation) - 1; $i >= 0; $i--) {
                                $sortingKeysAssociation[$i + 1] = $sortingKeysAssociation[$i];
                            }
                            $sortingKeysAssociation[0] = $fieldName;
                            break;
                        case 'after':
                            $position = str_replace('after:', '', $configuration[$positionKey]);
                            if (in_array($position, $sortingKeysAssociation)) {
                                $key = array_search($position, $sortingKeysAssociation);
                                for ($i = count($sortingKeysAssociation) - 1; $i > $key; $i--) {
                                    $sortingKeysAssociation[$i + 1] = $sortingKeysAssociation[$i];
                                }
                                $sortingKeysAssociation[$key + 1] = $fieldName;
                            } else {
                                $remainingFields[$fieldName] = $configuration;
                            }

                            break;
                        case 'before':
                            $position = str_replace('before:', '', $configuration[$positionKey]);
                            if (in_array($position, $sortingKeysAssociation)) {
                                $key = array_search($position, $sortingKeysAssociation);
                                for ($i = count($sortingKeysAssociation) - 1; $i >= $key; $i--) {
                                    $sortingKeysAssociation[$i + 1] = $sortingKeysAssociation[$i];
                                }
                                $sortingKeysAssociation[$key] = $fieldName;
                            } else {
                                $remainingFields[$fieldName] = $configuration;
                            }

                            break;
                        case 'last':
                            $lastFields[] = $fieldName;
                            break;
                        default:
                            $sortingKeysAssociation[] = $fieldName;
                    }
                } else {
                    $sortingKeysAssociation[] = $fieldName;
                }
            }

            if (!empty($remainingFields)) {
                $currentFields = $remainingFields;

                // If we get the same array as the last loop, something is stuck, so we delete the position for the first key. It will prevent infinite loop.
                $diff = array_diff_assoc($currentFields, $cachedFields);
                if (empty($diff)) {
                    unset($currentFields[array_keys($currentFields)[0]][$positionKey]);
                }
                $cachedFields = $currentFields;
            } else {
                $currentFields = [];
            }
        }

        if (!empty($lastFields)) {
            foreach ($lastFields as $fieldName) {
                $sortingKeysAssociation[] = $fieldName;
            }
        }

        $finalSortedFieldsConfiguration = [];
        foreach ($sortingKeysAssociation as $fieldName) {
            $finalSortedFieldsConfiguration[$fieldName] = $fieldsConfiguration[$fieldName];
        }

        return $finalSortedFieldsConfiguration;
    }

    /**
     * Checks if an uid is the root page of a model site.
     *
     * @param $uid    int    The uid.
     * @return bool        True if the uid is the root page of a model site.
     */
    public static function checkUidIsModelSite($uid)
    {
        $modelSitesUidList = array_keys(FieldsConfigurationPresets::getModelSitesList());

        return in_array(intval($uid), $modelSitesUidList);
    }

    /**
     * Checks if an uid is the root page of a model site.
     *
     * @param $uid    int    The uid.
     * @return bool        True if the uid is the root page of a model site.
     * @throws \Exception
     */
    public static function checkUidIsSavedSite($uid)
    {
        $objectManager = self::getObjectManager();

        /** @var \Romm\SiteFactory\Domain\Repository\SaveRepository $saveRepository */
        $saveRepository = $objectManager->get('Romm\\SiteFactory\\Domain\\Repository\\SaveRepository');
        $save = $saveRepository->findLastByRootPageUid($uid);

        if (!$save) {
            throw new \Exception('The uid "' . $uid . '" does not match an editable site.', 1423831279);
        }

        return true;
    }

    /**
     * Cleans a value depending on its TCA configuration.
     *
     * @param $table          string    The table of the field.
     * @param $field          string    The field.
     * @param $value          string    The value that should be cleaned.
     * @param $pid            int        The pid of the content.
     * @param $checkUnique    bool    If set to true, will get a unique value.
     * @return string    The cleaned value.
     */
    public static function getCleanedValueFromTCA($table, $field, $value, $pid, $checkUnique = true)
    {
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');

        $res = ['value' => null];
        $PP = [$table, $value, '', '', $pid, ''];
        $config = $GLOBALS['TCA'][$table]['columns'][$field]['config'];
        $fieldsEval = (isset($config['eval'])) ? GeneralUtility::trimExplode(',', $config['eval']) : [];
        if ($checkUnique) {
            if (($key = array_search('unique', $fieldsEval)) === false) {
                $fieldsEval[$key][] = 'unique';
            }
        } else {
            if (($key = array_search('unique', $fieldsEval)) !== false) {
                unset($fieldsEval[$key]);
            }
        }
        $config['eval'] = implode(',', $fieldsEval);
        $result = $dataHandler->checkValue_input($res, $value, $config, $PP, $field);

        return $result['value'];
    }

    /**
     * Return the extension configuration.
     *
     * @param    string $configurationName If null, returns the whole configuration. Otherwise, returns the asked configuration.
     * @return    array
     */
    public static function getExtensionConfiguration($configurationName = null)
    {
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = self::getObjectManager()->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ConfigurationUtility');
        $configuration = $configurationUtility->getCurrentConfiguration(self::EXTENSION_KEY);
        $result = ($configurationName) ?
            $configuration[$configurationName]['value'] :
            $configuration;

        return $result;
    }

    /**
     * Get the current page renderer, and loads jQuery in the templates.
     */
    public static function loadJquery()
    {
        /** @var \TYPO3\CMS\Backend\Template\DocumentTemplate $documentTemplate */
        $documentTemplate = self::getDocumentTemplate();
        $pageRenderer = $documentTemplate->getPageRenderer();
        $pageRenderer->loadJquery(PageRenderer::JQUERY_VERSION_LATEST, 'local', $pageRenderer::JQUERY_NAMESPACE_DEFAULT_NOCONFLICT);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController|\TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public static function getDocumentTemplate()
    {
        if (self::isEnvironmentInFrontendMode()) {
            return $GLOBALS['TSFE'];
        } else {
            return $GLOBALS['TBE_TEMPLATE'];
        }
    }

    /**
     * @return bool
     */
    public static function isEnvironmentInFrontendMode()
    {
        /** @var \TYPO3\CMS\Extbase\Service\EnvironmentService $environmentService */
        $environmentService = self::getObjectManager()->get('TYPO3\\CMS\\Extbase\\Service\\EnvironmentService');

        return $environmentService->isEnvironmentInFrontendMode();
    }

    /**
     * @return    string    The extension key.
     */
    public static function getExtensionKey()
    {
        return self::EXTENSION_KEY;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    public static function getObjectManager()
    {
        if (!self::$objectManager) {
            self::$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        }

        return self::$objectManager;
    }

    /**
     * Returns the path to the folder where the extension will be able to
     * manipulate temporary files.
     *
     * @return string
     */
    public static function getProcessedFolderPath()
    {
        return self::PROCESSED_FOLDER_PATH;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    public static function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }

}
