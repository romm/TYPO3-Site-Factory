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
use TYPO3\CMS\Extbase\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\Domain\Model\FileMount;
use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * Class containing functions called when a site is being duplicated.
 * See function "run" for more information.
 *
 * Available duplication settings:
 *  - modelUid: The uid of the backend user which will be duplicated.
 *  - sysFileMountUid:    The uid of the file mount which will be linked to the backend user.
 *                        Can be an integer, or "data:foo" where foo refers to the value of "settings.createdRecordName"
 *                        for the file mount creation process (default value is "fileMountUid").
 *  - createdRecordName:    Will save the new "be_users" record's uid at this index.
 *                            It can then be used later (e.g. link this record to a backend user).
 *                            If none is given, "backendUserUid" is used.
 */
class BackendUserCreationProcess extends AbstractDuplicationProcess
{

    /**
     * Default name of the index used to store the uid of the created
     * "be_users" record.
     *
     * @var string
     */
    const DEFAULT_CREATED_RECORD_NAME = 'backendUserUid';

    /**
     * Creates a backend user.
     *
     * See class documentation for more details.
     */
    public function run()
    {
        $backendUserModelUid = $this->getProcessSettings('modelUid');

        // Checking if something was given in "settings.modelUid".
        if (!MathUtility::canBeInterpretedAsInteger($backendUserModelUid)) {
            $this->addError('duplication_process.backend_user_creation.error.must_be_integer', 1432908303);

            return;
        }

        // Checking if the given backend user is valid.
        /** @var \TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository $backendUserRepository */
        $backendUserRepository = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Domain\\Repository\\BackendUserRepository');

        /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $backendUser */
        $backendUser = $backendUserRepository->findByUid($backendUserModelUid);

        if (!$backendUser) {
            $this->addError(
                'duplication_process.backend_user_creation.error.wrong_uid',
                1432908427,
                ['d' => $backendUserModelUid]
            );

            return;
        }

        // Creating a new instance of backend user, and copying the values from the model one.
        /** @var \TYPO3\CMS\Extbase\Domain\Model\BackendUser $backendUserClone */
        $backendUserClone = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Domain\\Model\\BackendUser');

        $backendUserClone->setPid($this->getDuplicatedPageUid());

        $backendUserName = Core::getCleanedValueFromTCA('be_users', 'username', $backendUser->getUserName(), 0);
        $backendUserClone->setUserName($backendUserName);

        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
        $persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
        $persistenceManager->add($backendUserClone);
        $persistenceManager->persistAll();

        $this->addNotice(
            'duplication_process.backend_user_creation.notice.success',
            1432909010,
            ['s' => $backendUserClone->getUserName()]
        );

        // Managing base mount.
        $duplicatedPageUid = $this->getDuplicatedPageUid();
        if ($duplicatedPageUid) {
            $this->fixDataBaseMountPointForBackendUser($backendUserClone, $this->getDuplicatedPageUid());
        }

        // Managing file mount.
        $fileMountUid = $this->getProcessSettings('sysFileMountUid');
        if ($fileMountUid) {
            /** @var \TYPO3\CMS\Extbase\Domain\Repository\FileMountRepository $fileMountRepository */
            $fileMountRepository = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Domain\\Repository\\FileMountRepository');

            /** @var \TYPO3\CMS\Extbase\Domain\Model\FileMount $fileMount */
            $fileMount = $fileMountRepository->findByUid($fileMountUid);
            if (!$fileMount) {
                $this->addWarning(
                    'duplication_process.backend_user_creation.warning.wrong_mount_point_uid',
                    1432909510,
                    ['s' => $fileMountUid]
                );
            } else {
                $flag = $this->fixFileMountForBackendUser($backendUserClone, $fileMount);
                if ($flag) {
                    $this->addNotice(
                        'duplication_process.backend_user_creation.notice.filemount_association_success',
                        1432909851,
                        ['s' => $fileMount->getTitle()]
                    );
                }
            }
        }

        // Checking if "settings.createdRecordName" can be used, use self::DEFAULT_CREATED_RECORD_NAME otherwise.
        $backendUserUid = $backendUserClone->getUid();
        $createdRecordName = $this->getProcessSettings('createdRecordName');
        $createdRecordName = (!empty($createdRecordName))
            ? $createdRecordName
            : self::DEFAULT_CREATED_RECORD_NAME;

        $this->setDuplicationDataValue($createdRecordName, $backendUserUid);
    }

    /**
     * Currently, "file_mountpoints" is not bound to the BackendUser model, so
     * we need to bind "the old way".
     *
     * @param    BackendUser $backendUser The backend user.
     * @param    FileMount   $fileMount   The file mount which will be bound to the given backend user.
     * @return    boolean|\mysqli_result|object MySQLi result object / DBAL object
     */
    private function fixFileMountForBackendUser(BackendUser $backendUser, FileMount $fileMount)
    {
        return $this->database->exec_UPDATEquery(
            'be_users',
            'uid=' . $backendUser->getUid(),
            ['file_mountpoints' => $fileMount->getUid()]
        );
    }

    /**
     * Currently, "db_mountpoints" is not bound to the BackendUser model, so
     * we need to bind "the old way".
     *
     * @param    BackendUser $backendUser The backend user.
     * @param    int         $uid         The uid of the root page.
     * @return    boolean|\mysqli_result|object MySQLi result object / DBAL object
     */
    private function fixDataBaseMountPointForBackendUser(BackendUser $backendUser, $uid)
    {
        return $this->database->exec_UPDATEquery(
            'be_users',
            'uid=' . $backendUser->getUid(),
            ['db_mountpoints' => intval($uid)]
        );
    }
}
