<?php
namespace Romm\SiteFactory\Core;

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

/**
 * Class managing the cache instances for the extension.
 */
class CacheManager {
	const CACHE_MAIN		= 'cache_site_factory_main';
	const CACHE_PROCESSED	= 'cache_site_factory_processed';

	/** @var \TYPO3\CMS\Core\Cache\CacheManager */
	protected static $cacheManager;

	/**
	 * Returns a cache instance.
	 *
	 * @param	string	$name	Name of the cache. Must be one of the class' constants.
	 * @return	\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
	 */
	public static function getCacheInstance($name) {
		if (!self::$cacheManager)
			self::$cacheManager = Core::getObjectManager()->get('TYPO3\\CMS\\Core\\Cache\\CacheManager');

		$cacheInstance = null;
		switch ($name) {
			case self::CACHE_MAIN:
			case self::CACHE_PROCESSED:
				$cacheInstance = self::$cacheManager->getCache($name);
		}

		return $cacheInstance;
	}
}