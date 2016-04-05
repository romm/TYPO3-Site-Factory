<?php
namespace Romm\SiteFactory\Controller;

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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Romm\SiteFactory\Core\Core;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Controller managing the duplication of sites.
 */
class AbstractController extends ActionController {

	/**
	 * Is called before any action.
	 */
	public function initializeAction() {
		Core::loadJquery();
	}

	/**
	 * @param ViewInterface $view
	 */
	protected function initializeView(ViewInterface $view) {
		$this->view->assign('pathSite', $_SERVER['SERVER_NAME']);
	}
}
