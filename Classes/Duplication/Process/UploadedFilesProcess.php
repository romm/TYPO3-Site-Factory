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

use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;
use Romm\SiteFactory\Form\Fields\AbstractField;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Domain\Model\FileMount;
use TYPO3\CMS\Extbase\Domain\Repository\FileMountRepository;

/**
 * Class containing functions called when a site is being duplicated.
 * See function "run" for more information.
 */
class UploadedFilesProcess extends AbstractDuplicationProcess
{

    /**
     * Gets all the fields which contains files, and upload them to the given
     * file mount.
     */
    public function run()
    {
        /** @var AbstractField[] $filesFields */
        $filesFields = [];
        foreach ($this->getFields() as $field) {
            if ($field->getSettings('moveToFileMount') && $field->getValue() != '') {
                if (substr($field->getValue(), 0, 4) == 'new:') {
                    $field->setValue(substr($field->getValue(), 4, strlen($field->getValue()) - 4));
                    $filesFields[] = $field;
                }
            }
        }

        if (!empty($filesFields)) {
            $fileMountUid = $this->getDuplicationData('fileMountUid');

            if ($fileMountUid) {
                /** @var FileMountRepository $fileMountRepository */
                $fileMountRepository = $this->objectManager->get(FileMountRepository::class);

                /** @var FileMount $fileMount */
                $fileMount = $fileMountRepository->findByUid($fileMountUid);
                if ($fileMount) {
                    $filesMoved = [];

                    /** @var ResourceFactory $resourceFactory */
                    $resourceFactory = $this->objectManager->get(ResourceFactory::class);
                    $storage = $resourceFactory->getDefaultStorage();

                    /** @var Folder $folder */
                    $folderPath = substr($fileMount->getPath(), 1, strlen($fileMount->getPath()));
                    $folder = $this->objectManager->get(Folder::class, $storage, $folderPath, 'SiteFactory');

                    /** @var LocalDriver $driver */
                    $driver = $resourceFactory->getDriverObject($storage->getDriverType(), $storage->getConfiguration());
                    $driver->processConfiguration();

                    foreach ($filesFields as $field) {
                        $name = $field->getName();
                        $path = $field->getValue();
                        $fileExtension = substr(strrchr($path, '.'), 1);
                        $identifier = $folderPath . $name . '.' . $fileExtension;

                        if (file_exists($path)) {
                            /** @var File $file */
                            if ($driver->fileExists($identifier)) {
                                $file = $storage->getFile($identifier);
                                $storage->replaceFile($file, $path);

                                /** @var ProcessedFileRepository $processedFileRepository */
                                $processedFileRepository = $this->objectManager->get(ProcessedFileRepository::class);
                                /** @var ProcessedFile[] $processedFiles */
                                $processedFiles = $processedFileRepository->findAllByOriginalFile($file);

                                foreach ($processedFiles as $processedFile) {
                                    $processedFile->delete();
                                }
                            } else {
                                $file = $storage->addFile($path, $folder, $name . '.' . $fileExtension, 'replace');
                            }

                            $this->getField($field->getName())->setValue($driver->getPublicUrl($identifier));
                            $filesMoved[$name] = $file->getName();
                        }
                    }

                    if (!empty($filesMoved)) {
                        $this->addNotice(
                            'duplication_process.uploaded_files.notice.success',
                            1435421057,
                            [$folder->getPublicUrl(), '"' . implode('", ', $filesMoved) . '"']
                        );
                    }
                }
            }
        }
    }
}
