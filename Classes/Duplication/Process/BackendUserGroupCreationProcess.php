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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup;
use TYPO3\CMS\Extbase\Domain\Model\FileMount;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;
use TYPO3\CMS\Extbase\Domain\Repository\BackendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\FileMountRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class containing functions called when a site is being duplicated.
 * See function "run" for more information.
 *
 * Available duplication settings:
 *  - modelUid: The uid of the backend user group which will be duplicated.
 *  - sysFileMountUid:    The uid of the file mount which will be linked to the backend user group.
 *                        Can be an integer, or "data:foo" where foo refers to the value of "settings.createdRecordName"
 *                        for the file mount creation process (default value is "fileMountUid").
 *  - createdRecordName:    Will save the new "be_group" record's uid at this index.
 *                            It can then be used later (e.g. link this record to a backend user).
 *                            If none is given, "backendUserGroupUid" is used.
 */
class BackendUserGroupCreationProcess extends AbstractDuplicationProcess
{

    /**
     * Default name of the index used to store the uid of the created
     * "be_groups" record.
     *
     * @var string
     */
    const DEFAULT_CREATED_RECORD_NAME = 'backendUserGroupUid';

    /**
     * Creates a backend user group.
     *
     * See class documentation for more details.
     */
    public function run()
    {
        $backendUserGroupModelUid = $this->getProcessSettings('modelUid');

        // Checking if something was given in "settings.modelUid".
        if (!MathUtility::canBeInterpretedAsInteger($backendUserGroupModelUid)) {
            $this->addError('duplication_process.backend_usergroup_creation.error.must_be_integer', 1431993912);

            return;
        }

        // Checking if the given backend user group is valid.
        /** @var BackendUserGroupRepository $backendUserGroupRepository */
        $backendUserGroupRepository = $this->objectManager->get(BackendUserGroupRepository::class);

        /** @var BackendUserGroup $backendUserGroup */
        $backendUserGroup = $backendUserGroupRepository->findByUid($backendUserGroupModelUid);

        if (!$backendUserGroup) {
            $this->addError(
                'duplication_process.backend_usergroup_creation.error.wrong_uid',
                1431427134,
                ['d' => $backendUserGroupModelUid]
            );

            return;
        }

        // Creating a new instance of backend user, and copying the values from the model one.
        /** @var BackendUserGroup $backendUserGroupClone */
        $backendUserGroupClone = GeneralUtility::makeInstance(BackendUserGroup::class);

        $backendUserGroupClone->setPid($this->getDuplicatedPageUid());
        $backendUserGroupClone->setDatabaseMounts($this->getDuplicatedPageUid());

        $siteTitle = $this->getField('siteTitle');
        $backendUserGroupTitle = ($siteTitle) ?
            $backendUserGroup->getTitle() . ' [' . $siteTitle->getValue() . ']' :
            $backendUserGroup->getTitle();
        $backendUserGroupClone->setTitle($backendUserGroupTitle);

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $persistenceManager->add($backendUserGroupClone);
        $persistenceManager->persistAll();

        $this->addNotice(
            'duplication_process.backend_usergroup_creation.notice.success',
            1431993853,
            ['s' => $backendUserGroupClone->getTitle()]
        );

        // Managing file mount.
        $fileMountUid = $this->getProcessSettings('sysFileMountUid');
        if ($fileMountUid) {
            /** @var FileMountRepository $fileMountRepository */
            $fileMountRepository = $this->objectManager->get(FileMountRepository::class);

            /** @var FileMount $fileMount */
            $fileMount = $fileMountRepository->findByUid($fileMountUid);
            if (!$fileMount) {
                $this->addWarning(
                    'duplication_process.backend_usergroup_creation.warning.wrong_mount_point_uid',
                    1432909348,
                    ['s' => $fileMountUid]
                );
            } else {
                $flag = $this->fixFileMountForBackendUserGroup($backendUserGroupClone, $fileMount);
                if ($flag) {
                    $this->addNotice(
                        'duplication_process.backend_usergroup_creation.notice.filemount_association_success',
                        1431994215,
                        ['s' => $fileMount->getTitle()]
                    );
                }
            }
        }

        // Checking if "settings.createdRecordName" can be used, use self::DEFAULT_CREATED_RECORD_NAME otherwise.
        $backendUserGroupUid = $backendUserGroupClone->getUid();
        $createdRecordName = $this->getProcessSettings('createdRecordName');
        $createdRecordName = (!empty($createdRecordName))
            ? $createdRecordName
            : self::DEFAULT_CREATED_RECORD_NAME;

        $this->setDuplicationDataValue($createdRecordName, $backendUserGroupUid);
    }

    /**
     * Currently, "file_mountpoints" is not bound to the BackendUserGroup
     * model, so we need to bind "the old way".
     *
     * @param    BackendUserGroup $backendUserGroup The backend user group.
     * @param    FileMount        $fileMount        The file mount which will be bound to the given backend user group.
     * @return    boolean|\mysqli_result|object MySQLi result object / DBAL object
     */
    private function fixFileMountForBackendUserGroup(BackendUserGroup $backendUserGroup, FileMount $fileMount)
    {
        return $this->database->exec_UPDATEquery(
            'be_groups',
            'uid=' . $backendUserGroup->getUid(),
            ['file_mountpoints' => $fileMount->getUid()]
        );
    }
}
