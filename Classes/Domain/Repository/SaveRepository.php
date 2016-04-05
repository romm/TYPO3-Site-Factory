<?php
namespace Romm\SiteFactory\Domain\Repository;

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

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use Romm\SiteFactory\Core\Core;
use Romm\SiteFactory\Domain\Model\Save;

/**
 * The repository for the "Save" model.
 *
 * @method Save|null findOneByRootPageUid(int $pageUid)
 */
class SaveRepository extends Repository
{

    /**
     * Returns the last record for a given root page uid.
     *
     * @param    int $rootPageUid The root page uid.
     * @return array|QueryResultInterface
     */
    public function findLastByRootPageUid($rootPageUid)
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('rootPageUid', intval($rootPageUid))
            )
        )
            ->setOrderings(['date' => QueryInterface::ORDER_DESCENDING])
            ->setLimit(1);

        $result = $query->execute();
        $result = ($result) ?
            $result[0] :
            null;

        return $result;
    }

    /**
     * Returns all records grouped by "root_page_uid".
     *
     * @return array|QueryResultInterface
     */
    public function findAllByDistinctRootPageUid()
    {
        /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
        $query = $this->createQuery();

        return $query
            ->statement('SELECT * '
                . 'FROM (SELECT * FROM tx_sitefactory_domain_model_save ORDER BY tx_sitefactory_domain_model_save.date ' . QueryInterface::ORDER_DESCENDING . ') AS save '
                . 'JOIN pages ON pages.uid=save.root_page_uid '
                . 'WHERE pages.deleted=0 '
                . 'GROUP BY root_page_uid '
            )
            ->execute();
    }

    /**
     * Creates a new "Save" record and add it to current persistence.
     *
     * @param    Save $save The save instance.
     */
    public function createSave(Save $save)
    {
        $this->add($save);
        $this->persistenceManager->persistAll();
    }

    /**
     * Will set the page linked to the "Save" records (based on their attribute
     * "rootPageUid").
     *
     * @param    array|Save $records An array containing "Save" models, or a single "Save" model.
     * @return    array|null                Returns the array sent with modified "page" attribute, or null if the parameter was empty.
     */
    public function attachPage($records)
    {
        if (!$records) {
            return null;
        }

        $objectManager = Core::getObjectManager();
        /** @var \Romm\SiteFactory\Domain\Repository\PagesRepository $pagesRepository */
        $pagesRepository = $objectManager->get('Romm\\SiteFactory\\Domain\\Repository\\PagesRepository');

        if (is_array($records) || $records instanceof QueryResult) {
            /** @var \Romm\SiteFactory\Domain\Model\Save[] $records */
            foreach ($records as $key => $record) {
                $page = $pagesRepository->findByUidWithoutCondition($record->getRootPageUid());
                $records[$key]->setPage($page[0]);
            }
        } elseif ($records instanceof Save) {
            /** @var \Romm\SiteFactory\Domain\Model\Pages $page */
            $page = $pagesRepository->findByUidWithoutCondition($records->getRootPageUid());
            $records->setPage($page[0]);
        }

        return $records;
    }
}
