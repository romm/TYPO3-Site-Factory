<?php
namespace Romm\SiteFactory\ViewHelpers;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * ViewHelper to escape a string.
 */
class AddSlashesViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * Escapes a string.
     *
     * @param    string $value            String to format.
     * @param    bool   $onlyDoubleQuotes If only the double quotes should be escaped.
     * @return    string                        The altered string.
     */
    public function render($value = null, $onlyDoubleQuotes = false)
    {
        if ($value === null) {
            $value = $this->renderChildren();
        }
        if (!is_string($value)) {
            return $value;
        }

        if ($onlyDoubleQuotes) {
            $return = addcslashes($value, '\"');
        } else {
            $return = addslashes($value);
        }

        return $return;
    }
}
