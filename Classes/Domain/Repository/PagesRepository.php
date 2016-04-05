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

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * The repository for the "Pages" model.
 */
class PagesRepository extends Repository {

	/**
	 * Finds a page without taking care of the deleted/hidden flags and the
	 * storage page.
	 *
	 * @param	int	$uid	The uid of the page you want to find.
	 * @return	array|QueryResultInterface
	 */
	public function findByUidWithoutCondition($uid) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(false);
		$query->getQuerySettings()->setIgnoreEnableFields(true);

		$query->matching(
			$query->equals('uid', $uid)
		);

		return $query->execute();
	}

}
