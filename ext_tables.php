<?php
if (!defined('TYPO3_MODE'))
	throw new \Exception('Access denied.');

call_user_func(
	function($extensionKey) {
		if (TYPO3_MODE === 'BE') {
			// Registering module.
			\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
				'Romm.' . $extensionKey,
				'tools',
				'Administration',
				'',
				array(
					'Administration'	=> 'index,new,submit,processCopy,help',
					'Duplication'		=> 'ajaxProcessDuplication'
				),
				array(
					'access'	=> 'user,group',
					'icon'		=> 'EXT:' . $extensionKey . '/ext_icon.png',
					'labels'	=> 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_sitefactory.xlf',
				)
			);
		}

		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extensionKey, 'Configuration/TypoScript/Default', 'Site Factory - Default settings');
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extensionKey, 'Configuration/TypoScript/FieldsExample', 'Site Factory - Fields example');

		// Extending locallang files.
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf'] = array(
			'EXT:' . $extensionKey . '/Resources/Private/Language/FineUploader.xlf',
			'EXT:' . $extensionKey . '/Resources/Private/Language/FieldsLocallang.xlf'
		);
	},
	$_EXTKEY
);
