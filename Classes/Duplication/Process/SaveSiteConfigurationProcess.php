<?php
namespace Romm\SiteFactory\Duplication\Process;

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

use Romm\SiteFactory\Core\Core;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;

/**
 * @todo: comment
 */
class SaveSiteConfigurationProcess extends AbstractDuplicationProcess {
	public function run() {
		$objectManager = Core::getObjectManager();

		/** @var $saveRepository \Romm\SiteFactory\Domain\Repository\SaveRepository */
		$saveRepository = $objectManager->get('Romm\\SiteFactory\\Domain\\Repository\\SaveRepository');

		$saveObject = $saveRepository->findOneByRootPageUid($this->getDuplicatedPageUid());

		$newObject = false;
		if (empty($saveObject)) {
			$newObject = true;
			/** @var $saveObject \Romm\SiteFactory\Domain\Model\Save */
			$saveObject = GeneralUtility::makeInstance('Romm\\SiteFactory\\Domain\\Model\\Save');
			$saveObject->setRootPageUid($this->getDuplicatedPageUid());
		}

		$configuration = $this->getDuplicationData();
		ArrayUtility::mergeRecursiveWithOverrule(
			$configuration,
			array('fieldsValues' => $this->getFieldsValues())
		);
		$saveObject->setConfiguration(json_encode($configuration));

		if ($newObject)
			$saveRepository->add($saveObject);
		else
			$saveRepository->update($saveObject);
	}
}
