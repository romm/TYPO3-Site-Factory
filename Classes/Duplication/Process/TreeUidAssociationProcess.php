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

use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Class containing functions called when a site is being duplicated.
 */
class TreeUidAssociationProcess extends AbstractDuplicationProcess {
	/**
	 * After a node duplication, by knowing the source node's uid, and the
	 * duplicated node's uid, this function will be able to associate every old
	 * page of the node's tree with the new duplicated node's uid.
	 */
	public function run() {
		$modelPageUid = $this->getModelPageUid();
		if (!$modelPageUid) return;
		$duplicatedPageUid = $this->getDuplicatedPageUid();
		if (!$duplicatedPageUid) return;

		// Processing the pages uid association.
		$treeUidAssociation = $this->getTreeUidAssociationRecursive($modelPageUid, $duplicatedPageUid);

		$this->addNotice(
			'duplication_process.pages_association.notice.amount_pages',
			1431985778,
			array('d' => count($treeUidAssociation))
		);

		$this->setDuplicationDataValue('pagesUidAssociation', $treeUidAssociation);
	}

	/**
	 * Recursive function for processing the function "getTreeUidAssociation".
	 * Will check the sub pages of the old and new page.
	 *
	 * @param	integer	$oldUid	The uid of the model page.
	 * @param	integer	$newUid	The uid of the duplicated page.
	 * @return	array	An array containing association between the pages.
	 */
	private function getTreeUidAssociationRecursive($oldUid, $newUid) {
		$uidAssociation = array($oldUid => $newUid);

		$oldChildren = $this->database->exec_SELECTgetRows(
			'uid',
			'pages',
			'deleted=0 AND pid=' . $oldUid,
			'',
			'sorting ASC'
		);

		$newChildren = $this->database->exec_SELECTgetRows(
			'uid',
			'pages',
			'deleted=0 AND pid=' . $newUid,
			'',
			'sorting ASC'
		);

		if (array_keys($oldChildren) == array_keys($newChildren)) {
			foreach($oldChildren as $key => $oldChildUid) {
				$childrenAssociation = $this->getTreeUidAssociationRecursive($oldChildUid['uid'], $newChildren[$key]['uid']);

				foreach($childrenAssociation as $childrenAssociationOldUid => $childrenAssociationNewUid) {
					$uidAssociation[$childrenAssociationOldUid] = $childrenAssociationNewUid;
				}
			}
		}

		return $uidAssociation;
	}
}
