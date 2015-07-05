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
use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Class containing functions called when a site is being duplicated.
 *
 * Available duplication settings:
 *  - path:	Path of the folder created on the server.
 * 			If none given, "user_upload" is used.
 *  - createdRecordName:	Will save the new "sys_filemounts" record's uid at this index.
 * 							It can then be used later (e.g. link this record to a backend user group).
 * 							If none is given, "fileMountUid" is used.
 */
class SysFileMountsProcess extends AbstractDuplicationProcess {
	/**
	 * Default path used to create the folder for the file mount. Can be
	 * overwritten with "settings.path" in the duplication TypoScript
	 * configuration.
	 *
	 * @var string
	 */
	private static $defaultPath = 'user_upload';

	/**
	 * Default name of the index used to store the uid of the created
	 * "sys_filemounts" record.
	 *
	 * @var string
	 */
	private static $defaultCreatedRecordName = 'fileMountUid';

	/**
	 * Will create a file mount on the duplicated page. A directory will also be
	 * created in fileadmin. The site's name will be used for both the file
	 * mount's name and the directory created in fileadmin.
	 */
	public function run() {
		$siteTitle = $this->getField('siteTitle');
		if ($siteTitle) {
			$fileMountUid = $this->manageSysFileMounts($siteTitle->getValue());

			if ($fileMountUid !== false) {
				// Checking if "settings.createdRecordName" can be used, use self::$defaultCreatedRecordName otherwise.
				$createdRecordName = $this->getProcessSettings('createdRecordName');
				$createdRecordName = (!empty($createdRecordName))
					? $createdRecordName
					: self::$defaultCreatedRecordName;

				$this->setDuplicationDataValue($createdRecordName, $fileMountUid);
			}
			else {
				$this->addError('duplication_process.mount_point_creation.error.error_creation', 1431426127);
			}
		}
	}

	/**
	 * @param	string		$siteTitle	The name of the new site, will be used for both the file mount's name and the directory created in fileadmin.
	 * @return	null|int	Returns null if the file mount could not be created, or the uid of the last inserted "sys_filemounts" record.
	 */
	private function manageSysFileMounts($siteTitle) {
		$pathFirstPart = ($this->getProcessSettings('path'))
			? $this->getProcessSettings('path')
			: self::$defaultPath;
		$pathFirstPart = (substr($pathFirstPart, -1, 1) == '/')
			? $pathFirstPart
			: $pathFirstPart . '/';

		$folderPath = $pathFirstPart . GeneralUtility::strtolower($siteTitle);
		$folderPath = Core::formatAccentsInString($folderPath);
		$folderPath = preg_replace('/\s+/', ' ', $folderPath);
		$folderPath = preg_replace('/\s/', '_', $folderPath);
		$folderPath = '/' . $folderPath . '/';

		// @todo: manage warning when overriding a folder?
		GeneralUtility::mkdir_deep(PATH_site . 'fileadmin' . $folderPath);

		/** @var $fileMount \TYPO3\CMS\Extbase\Domain\Model\FileMount */
		$fileMount = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Domain\\Model\\FileMount');
		$fileMount->setPath($folderPath);
		$fileMount->setTitle($siteTitle);
		$fileMount->setIsAbsolutePath(true);
		// @todo: seems it must be on pid=0, check?
//		$fileMount->setPid($this->getDuplicatedPageUid());
		$fileMount->setPid(0);

		/** @var $persistenceManager \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager */
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$persistenceManager->add($fileMount);
		$persistenceManager->persistAll();

		return $fileMount->getUid();
	}
}