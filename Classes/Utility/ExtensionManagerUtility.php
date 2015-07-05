<?php
namespace Romm\SiteFactory\Utility;

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
use Romm\SiteFactory\Core\Core;

/**
 * Set of functions for the extension's configuration in the extension manager.
 */
class ExtensionManagerUtility {
	/**
	 * Returns a select containing all the Backend user groups.
	 *
	 * @param	array	$options	Current options of the field.
	 * @return	string				The HTML code containing the <select> tag with filled options.
	 */
	public function getBackendUserGroupsSelect($options) {
		$html = '<select name="' . $options['fieldName'] . '">';

		$backendUserGroups = GeneralUtility::array_merge(
			array(0 => array('uid' => -1, 'title' => '')),
			Core::getDatabase()->exec_SELECTgetRows(
				'uid, title',
				'be_groups',
				'1=1'
			)
		);;

		foreach($backendUserGroups as $group) {
			$selected = ($group['uid'] == $options['fieldValue']) ? ' selected="selected"' : '';
			$uidLabel = ($group['uid'] != -1) ? ' [' . $group['uid'] . ']' : '';

			$html .= '<option value="' . $group['uid'] . '"' . $selected . '>' . $group['title'] . $uidLabel . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Returns a select containing all the Backend users.
	 *
	 * @param	array	$options	Current options of the field.
	 * @return	string				The HTML code containing the <select> tag with filled options.
	 */
	public function getBackendUsersSelect($options) {
		$html = '<select name="' . $options['fieldName'] . '">';

		$backendUserGroups = GeneralUtility::array_merge(
			array(0 => array('uid' => -1, 'title' => '')),
			Core::getDatabase()->exec_SELECTgetRows(
				'uid, username',
				'be_users',
				'1=1'
			)
		);

		foreach($backendUserGroups as $group) {
			$selected = ($group['uid'] == $options['fieldValue']) ? ' selected="selected"' : '';
			$uidLabel = ($group['uid'] != -1) ? ' [' . $group['uid'] . ']' : '';

			$html .= '<option value="' . $group['uid'] . '"' . $selected . '>' . $group['username'] . $uidLabel . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

}