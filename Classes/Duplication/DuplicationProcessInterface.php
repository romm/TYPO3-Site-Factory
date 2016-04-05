<?php
namespace Romm\SiteFactory\Duplication;

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

/**
 * Class containing functions called when a site is being duplicated.
 */
interface DuplicationProcessInterface
{

    /**
     * Do the process of your duplication step in this function.
     */
    public function run();
}
