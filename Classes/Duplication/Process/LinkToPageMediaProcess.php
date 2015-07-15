<?php
namespace Romm\SiteFactory\Duplication\Process;

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
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;
use Romm\SiteFactory\Utility\ConstantManagerUtility;

/**
 * Class containing functions called when a site is being duplicated.
 */
class LinkToPageMediaProcess extends AbstractDuplicationProcess {
	public function ajaxRun(&$params) {
		/** @var $objectManager \TYPO3\CMS\Extbase\Object\ObjectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		/** @var $typoScriptParser \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser */
		$typoScriptParser = $objectManager->get('TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser');

		// Getting the constants of the duplicated page.
		$constantsSiteFactory = ConstantManagerUtility::getPageTemplateConstants($params['duplicationData']['duplicatedPageUid'], false);

		$typoScriptParser->parse($constantsSiteFactory);
		$pagesAssociations = $typoScriptParser->getVal('siteFactory.pages', $typoScriptParser->setup);

		if (!empty($pagesAssociations)) {
			$pagesAssociations = $pagesAssociations[1];
			// TODO!
			foreach($params['fieldsValues'] as $fieldName => $fieldConfiguration) {
				$fieldCustomConfiguration = array();
				try {
					$fieldCustomConfiguration = ArrayUtility::getValueByPath($fieldConfiguration, 'config.linkToPageMedia');
				} catch(\Exception $e) {}

				if (!empty($fieldCustomConfiguration)) {
					if(!isset($fieldCustomConfiguration['page'])) {
						throw new \Exception('The field "' . $fieldName . '" must contain the value "page" for the configuration "linkToPageMedia".', 1422616542);
					}

					$targetedPage = null;
					if (isset($pagesAssociations[$fieldCustomConfiguration['page']])) {
						$targetedPage = $pagesAssociations[$fieldCustomConfiguration['page']];
					}

					if ($targetedPage) {
						$res = $this->database->exec_DELETEquery(
							'sys_file_reference',
							'uid_foreign=' . $targetedPage .
							' AND tablenames="pages"' .
							' AND fieldname="media"' .
							' AND table_local="sys_file"'
						);

						$data = array(
							'pid'			=> $targetedPage,
							'tstamp'		=> time(),
							'crdate'		=> time(),
							'cruser_id'		=> '1', // @TODO !
							'uid_local'		=> $fieldConfiguration['value'],
							'uid_foreign'	=> $targetedPage,
							'tablenames'	=> 'pages',
							'fieldname'		=> 'media',
							'table_local'	=> 'sys_file'
						);

						$res = $this->database->exec_INSERTquery(
							'sys_file_reference',
							$data
						);
					}
				}
			}
		}
	}
}
