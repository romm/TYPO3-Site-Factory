<?php
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

namespace Romm\SiteFactory\Core;

/**
 * Class managing the cache instances for the extension.
 */
class CacheManager
{

    const CACHE_MAIN = 'cache_site_factory_main';
    const CACHE_PROCESSED = 'cache_site_factory_processed';

    /** @var \TYPO3\CMS\Core\Cache\CacheManager */
    protected static $cacheManager;

    /**
     * Returns a cache instance.
     *
     * @param    string $name Name of the cache. Must be one of the class' constants.
     * @return    \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    public static function getCacheInstance($name)
    {
        if (!self::$cacheManager) {
            self::$cacheManager = Core::getObjectManager()->get('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        }

        $cacheInstance = null;
        switch ($name) {
            case self::CACHE_MAIN:
            case self::CACHE_PROCESSED:
                $cacheInstance = self::$cacheManager->getCache($name);
        }

        return $cacheInstance;
    }
}
