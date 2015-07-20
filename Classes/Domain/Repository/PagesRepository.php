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
