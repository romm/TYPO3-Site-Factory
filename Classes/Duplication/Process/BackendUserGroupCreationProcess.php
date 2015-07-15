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
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup;
use TYPO3\CMS\Extbase\Domain\Model\FileMount;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Class containing functions called when a site is being duplicated.
 *
 * Available duplication settings:
 *  - modelUid: The uid of the backend user group which will be duplicated.
 *  - sysFileMountUid:	The uid of the file mount which will be linked to the backend user group.
 * 						Can be an integer, or "data:foo" where foo refers to the value of "settings.createdRecordName"
 * 						for the file mount creation process (default value is "fileMountUid").
 *  - createdRecordName:	Will save the new "be_group" record's uid at this index.
 * 							It can then be used later (e.g. link this record to a backend user).
 * 							If none is given, "backendUserGroupUid" is used.
 */
class BackendUserGroupCreationProcess extends AbstractDuplicationProcess {
	/**
	 * Default name of the index used to store the uid of the created
	 * "be_groups" record.
	 *
	 * @var string
	 */
	const DEFAULT_CREATED_RECORD_NAME = 'backendUserGroupUid';

	/**
	 * Creates a backend user group.
	 *
	 * See class documentation for more details.
	 */
	public function run() {
		$backendUserGroupModelUid = $this->getProcessSettings('modelUid');

		// Checking if something was given in "settings.modelUid".
		if (!MathUtility::canBeInterpretedAsInteger($backendUserGroupModelUid)) {
			$this->addError('duplication_process.backend_usergroup_creation.error.must_be_integer', 1431993912);
			return;
		}

		// Checking if the given backend user group is valid.
		/** @var $backendUserGroupRepository \TYPO3\CMS\Extbase\Domain\Repository\BackendUserGroupRepository */
		$backendUserGroupRepository = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Domain\\Repository\\BackendUserGroupRepository');

		/** @var $backendUserGroup \TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup */
		$backendUserGroup = $backendUserGroupRepository->findByUid($backendUserGroupModelUid);

		if (!$backendUserGroup) {
			$this->addError(
				'duplication_process.backend_usergroup_creation.error.wrong_uid',
				1431427134,
				array('d' => $backendUserGroupModelUid)
			);
			return;
		}

		// Creating a new instance of backend user, and copying the values from the model one.
		/** @var $backendUserGroupClone \TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup */
		$backendUserGroupClone = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Domain\\Model\\BackendUserGroup');

		$backendUserGroupClone->setPid($this->getDuplicatedPageUid());
		$backendUserGroupClone->setDatabaseMounts($this->getDuplicatedPageUid());

		$siteTitle = $this->getField('siteTitle');
		$backendUserGroupTitle = ($siteTitle) ?
			$backendUserGroup->getTitle() . ' [' . $siteTitle->getValue() . ']' :
			$backendUserGroup->getTitle();
		$backendUserGroupClone->setTitle($backendUserGroupTitle);

		/** @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$persistenceManager->add($backendUserGroupClone);
		$persistenceManager->persistAll();

		$this->addNotice(
			'duplication_process.backend_usergroup_creation.notice.success',
			1431993853,
			array('s' => $backendUserGroupClone->getTitle())
		);

		// Managing file mount.
		$fileMountUid = $this->getProcessSettings('sysFileMountUid');
		if ($fileMountUid) {
			/** @var $fileMountRepository \TYPO3\CMS\Extbase\Domain\Repository\FileMountRepository */
			$fileMountRepository = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Domain\\Repository\\FileMountRepository');

			/** @var $fileMount \TYPO3\CMS\Extbase\Domain\Model\FileMount */
			$fileMount = $fileMountRepository->findByUid($fileMountUid);
			if (!$fileMount) {
				$this->addWarning(
					'duplication_process.backend_usergroup_creation.warning.wrong_mount_point_uid',
					1432909348,
					array('s' => $fileMountUid)
				);
			}
			else {
				$flag = $this->fixFileMountForBackendUserGroup($backendUserGroupClone, $fileMount);
				if ($flag) {
					$this->addNotice(
						'duplication_process.backend_usergroup_creation.notice.filemount_association_success',
						1431994215,
						array('s' => $fileMount->getTitle())
					);
				}
			}
		}

		// Checking if "settings.createdRecordName" can be used, use self::DEFAULT_CREATED_RECORD_NAME otherwise.
		$backendUserGroupUid = $backendUserGroupClone->getUid();
		$createdRecordName = $this->getProcessSettings('createdRecordName');
		$createdRecordName = (!empty($createdRecordName))
			? $createdRecordName
			: self::DEFAULT_CREATED_RECORD_NAME;


		$this->setDuplicationDataValue($createdRecordName, $backendUserGroupUid);
	}

	/**
	 * Currently, "file_mountpoints" is not mapped to the BackendUserGroup
	 * model, so we need to map "the old way".
	 *
	 * @param	BackendUserGroup	$backendUserGroup	The backend user group.
	 * @param	FileMount			$fileMount			The file mount which will be mapped to the given backend user group.
	 * @return	boolean|\mysqli_result|object MySQLi result object / DBAL object
	 */
	private function fixFileMountForBackendUserGroup(BackendUserGroup $backendUserGroup, FileMount $fileMount) {
		return $this->database->exec_UPDATEquery(
			'be_groups',
			'uid=' . $backendUserGroup->getUid(),
			array('file_mountpoints' => $fileMount->getUid())
		);
	}
}