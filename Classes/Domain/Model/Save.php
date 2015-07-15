<?php
namespace Romm\SiteFactory\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Save model, containing information about the sites that have been duplicated
 * or modified.
 *
 * When a site is created/modified, the submitted form's data is saved in JSON
 * format in the property "configuration".
 */
class Save extends AbstractEntity {
	/**
	 * The uid of the root page of the duplicated site.
	 *
	 * @var int
	 */
	protected $rootPageUid = 0;

	/**
	 * Page
	 *
	 * @var Pages
	 */
	protected $page = NULL;

	/**
	 * The date of the the creation/modification.
	 *
	 * @var int
	 */
	protected $date = 0;

	/**
	 * The submitted form's data in JSON format.
	 *
	 * @var string
	 */
	protected $configuration = '';

	/**
	 * Construction function.
	 *
	 * @return \Romm\SiteFactory\Domain\Model\Save
	 */
	public function __construct() {
		// Setting date to actual time.
		$this->setDate(time());
	}

	/*********************
	 * SETTERS & GETTERS *
	 *********************/
	/**
	 * @param	int
	 */
	public function setRootPageUid($rootPageUid) {
		$this->rootPageUid = $rootPageUid;
	}
	/**
	 * @return	int
	 */
	public function getRootPageUid() {
		return $this->rootPageUid;
	}

	/**
	 * @param	Pages
	 */
	public function setPage(Pages $page) {
		$this->page = $page;
	}
	/**
	 * @return	Pages
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * @param	int
	 */
	public function setDate($date) {
		$this->date = $date;
	}
	/**
	 * @return	int
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * @param	string
	 */
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
	}
	/**
	 * @return	array
	 */
	public function getConfiguration() {
		$configuration = (is_array($this->configuration))
			? $this->configuration
			: json_decode($this->configuration, true);
		return $configuration;
	}

}
