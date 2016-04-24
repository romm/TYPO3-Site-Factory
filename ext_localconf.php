<?php
if (!defined('TYPO3_MODE')) {
    throw new \Exception('Access denied.');
}

call_user_func(
    function ($extensionKey) {
        if (TYPO3_MODE == 'BE') {
            \Romm\SiteFactory\Utility\AjaxDispatcherUtility::activateAjaxDispatcher();

            // Registering the caches for this extension.
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_' . $extensionKey . '_main'] = [
                'backend'  => 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend',
                'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
                'groups'   => ['all', 'system', 'pages']
            ];

            // Registering the caches for this extension.
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_' . $extensionKey . '_processed'] = [
                'backend'  => 'TYPO3\\CMS\\Core\\Cache\\Backend\\SimpleFileBackend',
                'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
                'options'  => [
                    'cacheDirectory' => Romm\SiteFactory\Core\Core::getProcessedFolderPath()
                ]
            ];

            // Including main TypoScript files.
            $includeTypoScriptSyntax = '<INCLUDE_TYPOSCRIPT: source="FILE:%s">';
            $typoScriptFiles = [
                'EXT:' . $extensionKey . '/Configuration/TypoScript/Default/DefaultConfiguration.ts',
                'EXT:' . $extensionKey . '/Configuration/TypoScript/Default/FieldsTypesConfiguration.ts',
                'EXT:' . $extensionKey . '/Configuration/TypoScript/Default/FieldsConfiguration.ts',
                'EXT:' . $extensionKey . '/Configuration/TypoScript/Default/DuplicationConfiguration.ts'
            ];
            foreach ($typoScriptFiles as $filePath) {
                \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(sprintf($includeTypoScriptSyntax, $filePath));
            }
        }
    },
    $_EXTKEY
);
