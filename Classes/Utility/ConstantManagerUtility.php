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

namespace Romm\SiteFactory\Utility;

use Romm\SiteFactory\Core\Core;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class containing main functions to get/set a page's constants that match this
 * extension's configurations.
 *
 * In the "constants" field of a page, the configuration MUST be surrounded by
 * the two delimiters. See $constantsDelimiterBegin and $constantsDelimiterEnd
 * below.
 */
class ConstantManagerUtility
{

    const WARNING_NO_UPDATE_MESSAGE = 'WARNING: please do not modify the values below, unless you know exactly what you are doing.';
    const CODE_DELIMITER_BEGIN = '# SITE FACTORY BEGIN #';
    const CODE_DELIMITER_END = '# SITE FACTORY END #';

    /**
     * Updates the template of the given page marked with
     * "site_factory_template" by setting the correct values of the pages and
     * the fields.
     *
     * @param    int   $modelPageUid       The model page uid.
     * @param    int   $pageUid            The page uid.
     * @param    array $fieldValues        The fields values.
     * @param    array $pageUidAssociation Association of uid for the given page.
     */
    public static function manageTemplateConstants($modelPageUid, $pageUid, array $fieldValues, array $pageUidAssociation)
    {
        $constantsString = self::CODE_DELIMITER_BEGIN . CRLF;
        $constantsString .= '# ' . self::WARNING_NO_UPDATE_MESSAGE . CRLF;
        $constantsString .= self::getTemplateConstantsValuesString($modelPageUid, $fieldValues);
        $constantsString .= self::getTemplateConstantsUidAssociationString($modelPageUid, $pageUidAssociation);
        $constantsString .= self::CODE_DELIMITER_END . CRLF;

        self::createPageTemplateIfNone($pageUid);
        $template = self::getPageTemplate($pageUid);

        $currentConstantsString = $template['constants'];
        $currentConstantsString = preg_replace('/' . self::CODE_DELIMITER_BEGIN . '.*' . self::CODE_DELIMITER_END . '/s', '', $currentConstantsString);
        $currentConstantsString .= CRLF . $constantsString;

        Core::getDatabase()->exec_UPDATEquery(
            'sys_template',
            'uid=' . $template['uid'],
            [
                'constants' => $currentConstantsString
            ]
        );
    }

    /**
     * Returns the values contained in the constants for a given list of fields.
     *
     * @param    int   $pageUid     The page uid.
     * @param    array $fieldsNames Name of the fields from which you want the values.
     * @return    array
     */
    public static function getTemplateConstantsValues($pageUid, array $fieldsNames)
    {
        $constantsFound = [];
        $pagesPaths = TypoScriptUtility::getExtensionConfigurationFromPath('constantsPaths.configurationPaths');

        if (!is_array($pagesPaths)) {
        } // @todo: exception
        else {
            $constants = TypoScriptUtility::getTypoScriptConstants($pageUid);
            foreach ($pagesPaths as $path) {
                foreach ($fieldsNames as $fieldName) {
                    $fullPath = $path . '.' . $fieldName;
                    if (ArrayUtility::isValidPath($constants, $fullPath, '.')) {
                        $constantsFound[$fieldName] = ArrayUtility::getValueByPath($constants, $fullPath, '.');
                    }
                }
            }
        }

        return $constantsFound;
    }

    /**
     * Returns a string containing all the constants configuration for the
     * fields values.
     *
     * @param    int   $pageUid     The page uid.
     * @param    array $fieldValues The values of the fields.
     * @return    string
     */
    private static function getTemplateConstantsValuesString($pageUid, array $fieldValues)
    {
        $constantsString = '';
        $pagesPaths = TypoScriptUtility::getExtensionConfigurationFromPath('constantsPaths.configurationPaths');

        if (!is_array($pagesPaths)) {
        } // @todo: exception
        else {
            $constants = TypoScriptUtility::getTypoScriptConstants($pageUid);

            foreach ($pagesPaths as $path) {
                foreach ($fieldValues as $key => $value) {
                    $fullPath = $path . '.' . $key;
                    if (ArrayUtility::isValidPath($constants, $fullPath, '.')) {
                        $constantsString .= $fullPath . ' = ' . $value . CRLF;
                    }
                }
            }
        }

        return $constantsString;
    }

    /**
     * Returns a string containing all the constants configuration for the
     * pages.
     *
     * @param    int   $pageUid            The page uid.
     * @param    array $pageUidAssociation Association of uid for the given page.
     * @return    string
     */
    private static function getTemplateConstantsUidAssociationString($pageUid, array $pageUidAssociation)
    {
        $constantsString = '';
        $pagesPaths = TypoScriptUtility::getExtensionConfigurationFromPath('constantsPaths.pagesPaths');

        if (!is_array($pagesPaths)) {
        } // @todo: exception
        else {
            $constants = TypoScriptUtility::getTypoScriptConstants($pageUid);

            foreach ($pagesPaths as $path) {

                if (ArrayUtility::isValidPath($constants, $path, '.')) {
                    $pagesValues = ArrayUtility::getValueByPath($constants, $path, '.');
                    if (is_array($pagesValues)) {
                        foreach ($pagesValues as $pageName => $pageValue) {
                            if (array_key_exists($pageValue, $pageUidAssociation)) {
                                $constantsString .= $path . '.' . $pageName . ' = ' . $pageUidAssociation[$pageValue] . CRLF;
                            }
                        }
                    }
                }
            }
        }

        return $constantsString;
    }

    /**
     * If no template exists for the given page, a new one is created.
     *
     * @param    int $pageUid The page uid.
     */
    public static function createPageTemplateIfNone($pageUid)
    {
        if (self::getPageTemplate($pageUid) === false) {
            Core::getDatabase()->exec_INSERTquery(
                'sys_template',
                [
                    'pid'    => intval($pageUid),
                    'title'  => 'Site Factory Template',
                    'tstamp' => time(),
                    'crdate' => time(),
                ]
            );
        }
    }

    /**
     * Returns the sys_template record marked with "site_factory_template" for
     * the given page.
     *
     * @param    int $pageUid The page uid.
     * @return    array|false                Array of result, false if no template was found.
     */
    private static function getPageTemplate($pageUid)
    {
        /** @var \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService $templateService */
        $templateService = Core::getObjectManager()->get('TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');
        $template = $templateService->ext_getFirstTemplate($pageUid);

        return $template;
    }

}
