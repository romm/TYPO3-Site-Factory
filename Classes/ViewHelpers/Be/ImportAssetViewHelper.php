<?php
namespace Romm\SiteFactory\ViewHelpers\Be;

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

use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * ViewHelper to include CSS or JavaScript assets.
 */
class ImportAssetViewHelper extends AbstractBackendViewHelper {

	/**
	 * Includes the given CSS or JavaScript files.
	 *
	 * @param	array	$cssFiles	CSS files.
	 * @param	array	$jsFiles	JavaScript files.
	 */
	public function render($cssFiles = array(), $jsFiles = array()) {
		$doc = $this->getDocInstance();
		$pageRenderer = $doc->getPageRenderer();

		foreach ($cssFiles as $value) {
			$path = $this->getFileRealPath($value);
			$pageRenderer->addCssFile($path);
		}

		foreach ($jsFiles as $value) {
			$path = $this->getFileRealPath($value);
			$pageRenderer->addJsFile($path);
		}
	}

	/**
	 * Returns a file path correct value by finding the 'EXT:xxx' values.
	 *
	 * @param	string	$path	The path to the file.
	 * @return	string			The correct path;
	 */
	private function getFileRealPath($path) {
		if (preg_match('/^EXT:([^\/]*)\/(.*)$/', $path, $res)) {
			$extRelPath = ExtensionManagementUtility::extRelPath($res[1]);
			$path = str_replace('EXT:' . $res[1] . '/', $extRelPath, $path);
		}

		return $path;
	}

}
