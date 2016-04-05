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

use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;
use Romm\SiteFactory\Utility\ConstantManagerUtility;

/**
 * Class containing functions called when a site is being duplicated.
 * See function "run" for more information.
 */
class BackendConstantsAssignationProcess extends AbstractDuplicationProcess
{

    /**
     * Manages the constants by setting the new values put in the fields, and
     * linking the pages of the model site with the duplicated one's.
     */
    public function run()
    {
        ConstantManagerUtility::manageTemplateConstants(
            $this->getModelPageUid(),
            $this->getDuplicatedPageUid(),
            $this->getFieldsValues(),
            $this->getDuplicationData('pagesUidAssociation')
        );
    }
}
