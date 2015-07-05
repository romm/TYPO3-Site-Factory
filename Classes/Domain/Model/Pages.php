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
 * Model for the database "pages" table.
 */
class Pages extends AbstractEntity {
	/**
	 * The title of the page.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * The hidden flag of the page.
	 *
	 * @var int
	 */
	protected $hidden = 0;

	/**
	 * The deleted flag of the page.
	 *
	 * @var int
	 */
	protected $deleted = 0;

	/*********************
	 * SETTERS & GETTERS *
	 *********************/
	/**
	 * @return	string
	 */
	public function getTitle() {
		return $this->title;
	}
	/**
	 * @param	string
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return	int
	 */
	public function getHidden() {
		return $this->hidden;
	}
	/**
	 * @param	int
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	/**
	 * @return	int
	 */
	public function getDeleted() {
		return $this->deleted;
	}
	/**
	 * @param	int
	 */
	public function setDeleted($deleted) {
		$this->deleted = $deleted;
	}

}