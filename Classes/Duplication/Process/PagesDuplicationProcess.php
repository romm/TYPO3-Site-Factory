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
use TYPO3\CMS\Backend\Tree\Pagetree\Commands;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class containing functions called when a site is being duplicated.
 */
class PagesDuplicationProcess extends AbstractDuplicationProcess {
	/**
	 * Will duplicate the model site's page and its sub-pages in the configured
	 * destination.
	 */
	public function run() {
		$modelPageUid = $this->getModelPageUid();
		if (!$modelPageUid) return;

		$copyDestination = intval($this->getDuplicationData('copyDestination'));

		// Testing if the values and $copyDestination is valid.
		$testCopyDestination = $this->database->exec_SELECTgetSingleRow('uid', 'pages', 'deleted=0 AND uid=' . intval($copyDestination));
		if ($testCopyDestination === false) {
			$this->addError(
				'duplication_process.pages_duplication.error.wrong_destination_uid',
				1431372959,
				array('d' => $copyDestination)
			);
			return;
		}

		// Calling duplication process.
		$duplicatedPageUid = $this->copyNodeToDestination($modelPageUid, $copyDestination);
		$this->setDuplicationDataValue('duplicatedPageUid', $duplicatedPageUid);

		// Updating the new page's title with the given one.
		$siteTitle = $this->getField('siteTitle');
		if ($siteTitle) {
			$this->database->exec_UPDATEquery(
				'pages',
				'uid=' . $duplicatedPageUid,
				array('title' => $siteTitle->getValue())
			);
		}
	}

	/**
	 * Copies the source node directly as the first child of the destination
	 * node and returns the created node.
	 *
	 * @param	integer	$nodeUid			The node which will be duplicated.
	 * @param	integer	$destinationUid		The uid of the new node's parent.
	 * @return	integer	The uid of the new node's first page.
	 */
	private function copyNodeToDestination($nodeUid, $destinationUid) {
		$beUserSave = $GLOBALS['BE_USER'];

		$GLOBALS['BE_USER']->uc['copyLevels'] = 100;
		$GLOBALS['BE_USER']->workspace = 0;

		$nodeData = new \stdClass();
		$nodeData->serializeClassName = 'TYPO3\\CMS\\Backend\\Tree\\Pagetree\\PagetreeNode';
		$nodeData->id = $nodeUid;
		$nodeData->type = 'pages';

		/** @var $node \TYPO3\CMS\Backend\Tree\Pagetree\PagetreeNode */
		$node = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Tree\\Pagetree\\PagetreeNode', (array)$nodeData);

		$duplicatedPageUid = Commands::copyNode($node, $destinationUid);

		$GLOBALS['BE_USER'] = $beUserSave;

		return $duplicatedPageUid;
	}
}