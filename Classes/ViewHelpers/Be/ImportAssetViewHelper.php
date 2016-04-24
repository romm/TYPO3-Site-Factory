<?php
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

namespace Romm\SiteFactory\ViewHelpers\Be;

use TYPO3\CMS\Fluid\ViewHelpers\Be\AbstractBackendViewHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * ViewHelper to include CSS or JavaScript assets.
 */
class ImportAssetViewHelper extends AbstractBackendViewHelper
{

    /**
     * Includes the given CSS or JavaScript files.
     *
     * @param    array $cssFiles CSS files.
     * @param    array $jsFiles  JavaScript files.
     */
    public function render($cssFiles = [], $jsFiles = [])
    {
        $pageRenderer = (version_compare(TYPO3_version, '7.0', '<'))
            ? $this->getDocInstance()->getPageRenderer()
            : $this->getPageRenderer();

        foreach ($cssFiles as $value) {
            $path = $this->getFileRealPath($value);
            $pageRenderer->addCssFile($path);
        }

        foreach ($jsFiles as $value) {
            $path = $this->getFileRealPath($value);
            $pageRenderer->addJsLibrary($path, $path);
        }
    }

    /**
     * Returns a file path correct value by finding the 'EXT:xxx' values.
     *
     * @param    string $path The path to the file.
     * @return    string            The correct path;
     */
    private function getFileRealPath($path)
    {
        if (preg_match('/^EXT:([^\/]*)\/(.*)$/', $path, $res)) {
            $extRelPath = ExtensionManagementUtility::extRelPath($res[1]);
            $path = str_replace('EXT:' . $res[1] . '/', $extRelPath, $path);
        }

        return $path;
    }

}
