<?php
namespace Romm\SiteFactory\Utility;

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
		);

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
