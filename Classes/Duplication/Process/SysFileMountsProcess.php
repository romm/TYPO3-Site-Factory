<?php
namespace Romm\SiteFactory\Duplication\Process;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Class containing functions called when a site is being duplicated.
 * See function "run" for more information.
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

		/** @var \TYPO3\CMS\Extbase\Domain\Model\FileMount $fileMount */
		$fileMount = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Domain\\Model\\FileMount');
		$fileMount->setPath($folderPath);
		$fileMount->setTitle($siteTitle);
		$fileMount->setIsAbsolutePath(true);
		// @todo: seems it must be on pid=0, check?
		$fileMount->setPid(0);

		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
		$persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$persistenceManager->add($fileMount);
		$persistenceManager->persistAll();

		return $fileMount->getUid();
	}
}
