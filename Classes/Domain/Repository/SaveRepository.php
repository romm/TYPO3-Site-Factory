<?php
namespace Romm\SiteFactory\Domain\Repository;

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
class SaveRepository extends Repository {

	/**
	 * Returns the last record for a given root page uid.
	 *
	 * @param	int	$rootPageUid	The root page uid.
	 * @return array|QueryResultInterface
	 */
	public function findLastByRootPageUid($rootPageUid) {
		/** @var $query \TYPO3\CMS\Extbase\Persistence\Generic\Query */
		$query = $this->createQuery();

		$query->matching(
				$query->logicalAnd(
					$query->equals('rootPageUid', intval($rootPageUid))
				)
			)
			->setOrderings(array('date' => QueryInterface::ORDER_DESCENDING))
			->setLimit(1);

		$result = $query->execute();
		$result = ($result) ?
			$result[0] :
			NULL;

		return $result;
	}

	/**
	 * Returns all records grouped by "root_page_uid".
	 *
	 * @return array|QueryResultInterface
	 */
	public function findAllByDistinctRootPageUid() {
		/** @var $query \TYPO3\CMS\Extbase\Persistence\Generic\Query */
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
	 * @param	Save	$save	The save instance.
	 */
	public function createSave(Save $save) {
		$this->add($save);
		$this->persistenceManager->persistAll();
	}

	/**
	 * Will set the page linked to the "Save" records (based on their attribute
	 * "rootPageUid").
	 *
	 * @param	array|Save	$records	An array containing "Save" models, or a single "Save" model.
	 * @return	array|null				Returns the array sent with modified "page" attribute, or null if the parameter was empty.
	 */
	public function attachPage($records) {
		if (!$records) return NULL;

		$objectManager = Core::getObjectManager();
		/** @var $pagesRepository \Romm\SiteFactory\Domain\Repository\PagesRepository */
		$pagesRepository = $objectManager->get('Romm\\SiteFactory\\Domain\\Repository\\PagesRepository');

		if (is_array($records) || $records instanceof QueryResult) {
			/** @var $records \Romm\SiteFactory\Domain\Model\Save[] */
			foreach($records as $key => $record) {
				$page = $pagesRepository->findByUidWithoutCondition($record->getRootPageUid());
				$records[$key]->setPage($page[0]);
			}
		}
		elseif($records instanceof Save) {
			/** @var $page \Romm\SiteFactory\Domain\Model\Pages */
			$page = $pagesRepository->findByUidWithoutCondition($records->getRootPageUid());
			$records->setPage($page[0]);
		}

		return $records;
	}
}