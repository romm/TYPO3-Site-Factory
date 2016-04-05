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

namespace Romm\SiteFactory\Duplication\Process;

use Romm\SiteFactory\Core\Core;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Class containing functions called when a site is being duplicated.
 * See function "run" for more information.
 */
class SaveSiteConfigurationProcess extends AbstractDuplicationProcess
{

    /**
     * When a site is being saved, this function will save all the fields values
     * in the DataBase, for further usage.
     */
    public function run()
    {
        $objectManager = Core::getObjectManager();

        /** @var \Romm\SiteFactory\Domain\Repository\SaveRepository $saveRepository */
        $saveRepository = $objectManager->get('Romm\\SiteFactory\\Domain\\Repository\\SaveRepository');

        $saveObject = $saveRepository->findOneByRootPageUid($this->getDuplicatedPageUid());

        $newObject = false;
        if (empty($saveObject)) {
            $newObject = true;
            /** @var \Romm\SiteFactory\Domain\Model\Save $saveObject */
            $saveObject = GeneralUtility::makeInstance('Romm\\SiteFactory\\Domain\\Model\\Save');
            $saveObject->setRootPageUid($this->getDuplicatedPageUid());
        }

        $configuration = $this->getDuplicationData();
        ArrayUtility::mergeRecursiveWithOverrule(
            $configuration,
            ['fieldsValues' => $this->getFieldsValues()]
        );
        $saveObject->setConfiguration(json_encode($configuration));

        if ($newObject) {
            $saveRepository->add($saveObject);
        } else {
            $saveRepository->update($saveObject);
        }
    }
}
