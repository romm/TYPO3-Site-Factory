<?php
namespace Romm\SiteFactory\Utility;

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

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\Utility\EidUtility;

// TODO: explain arguments: serialized: true

/**
 * Allows to easily dispatch an Ajax request.
 *
 * Two types of use can be done with it:
 *  -    It can be a call to a class function with some arguments (just like a
 *        user function). Please notice that your function should have two
 *        parameters:
 *            1. $content
 *            2. $configuration
 *  -    If you want to be in a controller context, you may also send some mvc
 *        arguments: plugin name, controller, action, etc.
 *
 * USAGE:
 *
 *  1 - Add this file in your extension at this path:
 *
 *        my_extension/Classes/Utility/AjaxDispatcherUtility.php
 *
 * ------------------------------
 *
 *  2 - Paste the following configuration in your ext_localconf.php. Notice that
 *        you can customize the name of the dispatcher, and whether the dispatcher
 *        should be called in a Frontend/Backend context.
 *        See function documentation for more information.
 *
 *        \MyVendor\MyExtension\Utility\AjaxDispatcherUtility::activateAjaxDispatcher();
 *
 * ------------------------------
 *
 *  3 - Call this class with Ajax. Example with jQuery below:
 *
 *        // Example of a simple class function call.
 *        var request = {
 *            // Attention: "id"=the id of the rootPage from which you want your
 *            // TypoScriptFrontendController ($GLOBALS['TSFE']) to be based on.
 *             id: 1,
 *            function: 'MyVendor\\MyCustomClass\\MyFunction->foo',
 *            arguments: {
 *                foo: 'bar'
 *            }
 *        };
 *
 *        // Example of a controller call.
 *        var request = {
 *            // Attention: "id"=the id of the rootPage from which you want your
 *            // TypoScriptFrontendController ($GLOBALS['TSFE']) to be based on.
 *             id: 1,
 *             mvc: {
 *                 vendor:            'MyVendor',
 *                 extensionName:        'MyExtension',
 *                 pluginName:        'MyPlugin',
 *                 controller:        'MyController',
 *                 action:            'foo'
 *             },
 *             arguments: {
 *                 foo: 'bar'
 *             }
 *         };
 *
 *         jQuery.ajax({
 *             url:        'index.php', // If called in a Backend context, replace with "ajax.php".
 *             type:        'GET',
 *             dataType:    'html',
 *             data: {
 *                 eID:        'ajaxDispatcher', // If called in a Backend context, "eID" must be replaced with "ajaxID".
 *                 request:    request
 *             },
 *             success: function(result) {
 *                // Your actions here.
 *             }
 *         });
 */
class AjaxDispatcherUtility
{

    /** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
    private $objectManager;

    /**
     * The name of this class' instance. Default value is "ajaxDispatcher".
     *
     * @var string
     */
    public static $instanceName = null;

    /**
     * Main function of the class, will run the function call process.
     *
     * See class documentation for more information.
     */
    public function run()
    {
        $content = null;

        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        // Bootstrap initialization.
        Bootstrap::getInstance()
            ->initializeTypo3DbGlobal()
            ->applyAdditionalConfigurationSettings()
            ->initializeBackendUser();

        // Gets the Ajax call parameters.
        $arguments = GeneralUtility::_GP('request');

        // Checking if the real arguments are serialized.
        if (is_array($arguments) && array_key_exists('arguments', $arguments)) {
            if (array_key_exists('serialized', $arguments['arguments']) && array_key_exists('value', $arguments['arguments'])) {
                $arguments['arguments'] = GeneralUtility::explodeUrl2Array($arguments['arguments']['value'], true);
            }
        }

        $id = (isset($arguments['id'])) ? $arguments['id'] : 0;

        // Initializing TypoScript Frontend Controller.
        if (TYPO3_MODE == 'FE') {
            // Creating global time tracker.
            $GLOBALS['TT'] = $this->objectManager->get('TYPO3\\CMS\\Core\\TimeTracker\\TimeTracker');

            $this->initializeTSFE($id);
        } else {
            Bootstrap::getInstance()->loadExtensionTables();
        }

        // If the argument "mvc" is sent, then we should be able to call a controller.
        if (isset($arguments['mvc'])) {
            $content = $this->callExtbaseController($arguments);
        }

        // If the argument "function" is sent, we call a user function.
        if (isset($arguments['function'])) {
            $content = $this->callUserFunction($arguments);
        }

        // If the argument "function" is sent, we call a user function.
        if (isset($arguments['typoScriptLib'])) {
            $content = $this->callTypoScriptLibrary($arguments, $id);
        }

        if (TYPO3_MODE == 'FE') {
            $content = $this->manageInternalObjects($content);
        }

        // Display the final content on screen.
        echo $content;
        die();
    }

    /**
     * Run an Extbase call. See documentation for more information.
     *
     * @param    array $arguments Array containing the request arguments.
     * @return    string    The result of the Extbase request.
     */
    private function callExtbaseController($arguments)
    {
        $mvcArguments = [
            'extensionName'               => '',
            'pluginName'                  => '',
            'vendorName'                  => '',
            'controller'                  => '',
            'switchableControllerActions' => ''
        ];
        ArrayUtility::mergeRecursiveWithOverrule($mvcArguments, $arguments['mvc']);

        // If we're in a Backend context, the plugin name is a plugin signature. We need to find it.
        $pluginName = $mvcArguments['pluginName'];
        if (TYPO3_MODE == 'BE') {
            if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$mvcArguments['extensionName']]['modules'])) {
                foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$mvcArguments['extensionName']]['modules'] as $pluginKey => $pluginConfiguration) {
                    if (preg_match('#^[^_]+_' . $mvcArguments['extensionName'] . $mvcArguments['pluginName'] . '$#', $pluginKey)) {
                        $pluginName = $pluginKey;
                        break;
                    }
                }
            }
        }

        $bootstrapConfiguration = [
            'extensionName'               => $mvcArguments['extensionName'],
            'pluginName'                  => $pluginName,
            'vendorName'                  => $mvcArguments['vendor'],
            'controller'                  => $mvcArguments['controller'],
            'switchableControllerActions' => [
                $mvcArguments['controller'] => [$mvcArguments['action']]
            ]
        ];

        // Add the arguments to the PHP global $_GET var.
        $pluginKey = strtolower('tx_' . $mvcArguments['extensionName'] . '_' . $pluginName);
        $this->addRequestArgumentsToGlobal([$pluginKey => $arguments['arguments']]);

        // Calling the controller by running an Extbase Bootstrap with the correct configuration.
        /** @var \TYPO3\CMS\Extbase\Core\Bootstrap $bootstrap */
        $bootstrap = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Core\\Bootstrap');
        $result = $bootstrap->run('', $bootstrapConfiguration);

        return $result;
    }

    /**
     * Run a user function call. See documentation for more information.
     *
     * @param    array $arguments Array containing the request arguments.
     * @return    string    The result of the user function.
     */
    private function callUserFunction($arguments)
    {
        $configuration = [
            'userFunc'             => $arguments['function'],
            '_typoScriptNodeValue' => 'USER'
        ];

        return $this->callContentObject($configuration, $arguments['arguments']);
    }

    /**
     * Calls a defined TypoScript library.
     *
     * @param    array $arguments Array containing the request arguments.
     * @param    int   $id        The page uid.
     * @return    string    The result of the TypoScript library.
     * @throws \Exception
     */
    private function callTypoScriptLibrary($arguments, $id)
    {
        $pageConfiguration = self::getPageConfiguration($id);
        if (ArrayUtility::isValidPath($pageConfiguration, $arguments['typoScriptLib'], '.')) {
            $typoScriptLib = ArrayUtility::getValueByPath($pageConfiguration, $arguments['typoScriptLib'], '.');

            if (!array_key_exists('_typoScriptNodeValue', $typoScriptLib)) {
                throw new \Exception('The TypoScript libraty "' . $arguments['typoScriptLib'] . '" does not have a Content Object type.', 1429113764);
            }

            $result = $this->callContentObject($typoScriptLib, $arguments['arguments']);

            return $result;
        } else {
            throw new \Exception('TypoScript library "' . $arguments['typoScriptLib'] . '" was not found.', 1429113004);
        }
    }

    /**
     * Calls a TypoScript content object.
     *
     * @param    array $configuration The configuration of the object.
     * @param    array $arguments     The arguments passed to the content object.
     * @return    string    The result of the content object.
     */
    private function callContentObject($configuration, $arguments = [])
    {
        /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = $this->objectManager->get('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
        /** @var \TYPO3\CMS\Frontend\ContentObject\AbstractContentObject $contentObject */
        $contentObject = $contentObjectRenderer->getContentObject($configuration['_typoScriptNodeValue']);

        // Add the arguments to the PHP global $_GET var.
        $this->addRequestArgumentsToGlobal($arguments);
        $configuration['arguments'] = $arguments;

        $result = $contentObject->render($configuration);

        return $result;
    }

    /**
     * Initializes the $GLOBALS['TSFE'] var, useful everywhere when in a
     * Frontend context.
     *
     * @param int $id The id of the rootPage from which you want the controller to be based on.
     */
    private function initializeTSFE($id)
    {
        if (TYPO3_MODE == 'FE') {
            $frontendController = $this->getFrontendController($id);
            EidUtility::initLanguage();
            EidUtility::initTCA();

            // No Cache for Ajax stuff.
            $frontendController->set_no_cache();

            $frontendController->initFEuser();
            $frontendController->checkAlternativeIdMethods();
            $frontendController->determineId();
            $frontendController->initTemplate();
            $frontendController->getPageAndRootline();
            $frontendController->getConfigArray();
            $frontendController->connectToDB();
            $frontendController->settingLanguage();
        }
    }

    /**
     * @param    int $id The id of the rootPage from which you want the controller to be based on.
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    private function getFrontendController($id = 0)
    {
        if (!$GLOBALS['TSFE']) {
            global $TYPO3_CONF_VARS;
            $GLOBALS['TSFE'] = $this->objectManager->get('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', $TYPO3_CONF_VARS, $id, 0);
        }

        return $GLOBALS['TSFE'];
    }

    /**
     * Adds an array to the PHP global $_GET var.
     *
     * @param    array $arguments The arguments you want to add.
     */
    private function addRequestArgumentsToGlobal($arguments)
    {
        GeneralUtility::_GETset($arguments);
    }

    /**
     * Returns the TypoScript configuration of a given page as an array.
     *
     * @param    int $uid The uid of the page you want the TypoScript configuration from. If none given, the full configuration is returned.
     * @return    array    The page configuration.
     */
    public static function getPageConfiguration($uid = 0)
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

        /** @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService */
        $typoScriptService = $objectManager->get('TYPO3\\CMS\\Extbase\\Service\\TypoScriptService');

        if ($uid && MathUtility::canBeInterpretedAsInteger($uid) && $uid > 0) {
            /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageRepository */
            $pageRepository = $objectManager->get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
            $rootLine = $pageRepository->getRootLine($uid);

            /** @var \TYPO3\CMS\Core\TypoScript\TemplateService $templateService */
            $templateService = $objectManager->get('TYPO3\\CMS\\Core\\TypoScript\\TemplateService');
            $templateService->tt_track = 0;
            $templateService->init();
            $templateService->runThroughTemplates($rootLine);
            $templateService->generateConfig();

            $fullConfiguration = $typoScriptService->convertTypoScriptArrayToPlainArray($templateService->setup);
        } else {
            /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager */
            $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');

            $fullConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

            if (is_array($fullConfiguration) && !empty($fullConfiguration)) {
                $fullConfiguration = $typoScriptService->convertTypoScriptArrayToPlainArray($fullConfiguration);
            }
        }

        return $fullConfiguration;
    }

    /**
     * Tricky function which will manage the internal objects (USER_INT), which
     * are defined as token string. The function will analyze the content, find
     * possible internal objects tokens and convert them to the real content.
     *
     * @param    string $content The current content.
     * @return    string    The final content.
     */
    private function manageInternalObjects($content)
    {
        if (TYPO3_MODE == 'FE') {
            $frontendController = $this->getFrontendController();

            if ($frontendController->config['INTincScript']) {
                $frontendController->content = $content;
                $frontendController->INTincScript();
                $content = $frontendController->content;
            }
        }

        return $content;
    }

    /**
     * Activates the Ajax Dispatcher.
     *
     * Should be called from "ext_localconf.php".
     *
     * @param bool   $frontend True if the dispatcher should be activated in a Frontend context.
     * @param bool   $backend  True if the dispatcher should be activated in a Backend context.
     * @param string $name     The name of the dispatcher, used to access it in your JavaScript.
     * @throws \Exception
     */
    public static function activateAjaxDispatcher($frontend = true, $backend = true, $name = 'ajaxDispatcher')
    {
        global $TYPO3_CONF_VARS;

        if (self::$instanceName != null && $name != self::$instanceName) {
            throw new \Exception('Trying to activate the Ajax Dispatcher with the name "' . $name . '", but it has already been activated with the name "' . self::$instanceName . '".', 1428569386);
        }

        self::$instanceName = $name;

        // Registering a hook before the normal process rendering of a page.
        // This is a trick that prevents the "require" used in "typo3/sysext/cms/tslib/index_ts.php"
        // not to fire because the class has already been included with the
        // static function "activateAjaxDispatcher".
        $TYPO3_CONF_VARS['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'][] = __CLASS__ . '->runFrontendAjaxDispatcher';

        if (TYPO3_MODE == 'BE' && $backend) {
            ExtensionManagementUtility::registerAjaxHandler($name, __CLASS__ . '->run');
        }
        if (TYPO3_MODE == 'FE' && $frontend) {
            $TYPO3_CONF_VARS['FE']['eID_include'][$name] = __FILE__;
        }
    }

    /**
     * Will check if the AjaxDispatcher is called, by looking in the
     */
    public function runFrontendAjaxDispatcher()
    {
        if (GeneralUtility::_GP('eID') != null && GeneralUtility::_GP('eID') == AjaxDispatcherUtility::$instanceName) {
            /** @var AjaxDispatcherUtility $ajaxDispatcher */
            $ajaxDispatcher = new AjaxDispatcherUtility();
            $ajaxDispatcher->run();
        }
    }
}
