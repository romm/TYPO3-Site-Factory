<?php
namespace Romm\SiteFactory\Form;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use Romm\SiteFactory\Core\Core;

/**
 * Class containing common evaluation functions for form fields' values.
 *
 * Example: "field is empty", "field is an email address", etc.
 */
class FieldEvaluationPresets {

	// @todo ?
	public function backendUserNameDoesNotExist(&$params, &$pObj) {
		$database = Core::getDatabase();

		$userName = Core::getCleanedValueFromTCA('be_users', 'username', $params['fieldValue'], 0, false);
		$userNames = $database->exec_SELECTgetRows(
			'username',
			'be_users',
			'username="' . $userName . '"'
		);

		if (!empty($userNames)) {
			return false;
		}

		return true;
	}


}