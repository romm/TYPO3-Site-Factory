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

use Romm\SiteFactory\Core\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Set of functions to manipulate files.
 * Used with the JavaScript library FineUploader.
 *
 * @todo make me become a service
 */
class FileUtility {
	const FILE_KEY	= 'qqfile';

	/**
	 * Processing files handling.
	 */
	public function ajaxMoveUploadedFileToSiteFactoryFolder() {
		$file = (isset($_FILES[self::FILE_KEY])) ?
			$_FILES[self::FILE_KEY] :
			null;
		if ($file) {
			$fileExtension = strtolower(substr(strrchr($file['name'], '.'), 1));
			$tmpFileName = md5(uniqid(rand(), true)) . '.' . $fileExtension;
			$tmpFilePath = PATH_site . Core::getProcessedFolderPath() . $tmpFileName;

			move_uploaded_file($file['tmp_name'], $tmpFilePath);

			echo json_encode(array(
				'tmpFilePath' 	=> $tmpFilePath,
				'newUuid'		=> $tmpFileName,
				'success'		=> true
			));
		}
	}

	/**
	 * Deletes a specific file from the processing folder.
	 */
	public function deleteFile() {
		$fileName = GeneralUtility::_GP('fileName');
		$filePath = PATH_site . Core::getProcessedFolderPath() . $fileName;
		if (file_exists($filePath)) {
			unlink($filePath);
		}
	}

	/**
	 * Handles the existing files of a Fine Uploader form.
	 * The values are stored in the GET/POST var at the index "fieldValue".
	 *
	 * @return string
	 */
	public function getExistingFiles() {
		$files = array();

		$fieldValue = GeneralUtility::_GP('fieldValue');
		if ($fieldValue != '') {
			$imagePath = GeneralUtility::getFileAbsFileName($fieldValue);
			$imageName = PathUtility::basename($imagePath);
			$imageDirectoryPath = PathUtility::dirname($imagePath);
			$imageDirectoryPath = PathUtility::getRelativePath(PATH_site, $imageDirectoryPath);
			$imageUrl = GeneralUtility::locationHeaderUrl('/' . $imageDirectoryPath . $imageName);

			if (file_exists($imagePath))
				$files[] = array(
					'name'			=> $imageName,
					'uuid'			=> $imageUrl,
					'thumbnailUrl'	=> $imageUrl
				);
		}

		return json_encode($files);
	}
}
