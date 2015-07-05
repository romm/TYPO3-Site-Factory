<?php
namespace Romm\SiteFactory\ViewHelpers;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;

/**
 * View helper which allows you to include a CSS File.
 */
class CssViewHelper extends AbstractBackendViewHelper {

	/**
	 * Computes a JavaScript tag and renders it.
	 *
	 * @param	string	$name			The file to include.
	 * @param	string	$extKey			The extension, where the file is located.
	 * @param	string	$pathInsideExt	The path to the file relative to the ext-folder.
	 * @return	string					The link.
	 */
	public function render($name = NULL, $extKey = NULL, $pathInsideExt = 'Resources/Public/Css/') {
		if ($extKey === NULL)
			$extKey = $this->controllerContext->getRequest()->getControllerExtensionKey();

		if (TYPO3_MODE === 'FE') {
			$extPath = ExtensionManagementUtility::extPath($extKey);
			$extRelPath = substr($extPath, strlen(PATH_site));
		} else
			$extRelPath = ExtensionManagementUtility::extRelPath($extKey);

		$pageRenderer = $this->getDocInstance()->getPageRenderer();
		$pageRenderer->addCssFile($extRelPath . $pathInsideExt . $name);
	}

}
