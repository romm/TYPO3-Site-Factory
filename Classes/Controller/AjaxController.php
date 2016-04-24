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

namespace Romm\SiteFactory\Controller;

use Romm\SiteFactory\Utility\AjaxInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;

class AjaxController extends ActionController
{
    public function dispatchAction()
    {
        $requestArguments = GeneralUtility::_GP('request');
        $result = [];

        if (null !== $requestArguments
            && is_array($requestArguments)
        ) {
            if (true === isset($requestArguments['function'])) {
                $result = $this->dispatchUserFunction($requestArguments);
            } elseif (true === isset($requestArguments['mvc'])
                && is_array($requestArguments['mvc'])
            ) {
                $result = $this->dispatchControllerAction($requestArguments);
            }
        }

        if (is_array($result)) {
            $result = json_encode($result);
        }

        return $result;
    }

    public function dispatchUserFunction($requestArguments)
    {
        $result = [];
        list($className) = GeneralUtility::trimExplode('->', $requestArguments['function']);

        if (class_exists($className)
            && in_array(AjaxInterface::class, class_implements($className))
        ) {
            $parameters = (true === isset($requestArguments['arguments']))
                ? $requestArguments['arguments']
                : [];

            $result = GeneralUtility::callUserFunction($requestArguments['function'], $parameters, $this);
        }

        return $result;
    }

    public function dispatchControllerAction($requestArguments) {
        $result = [];
        $extensionName = (true === isset($requestArguments['mvc']['extensionName']))
            ? $requestArguments['mvc']['extensionName']
            : null;
        $controllerName = (true === isset($requestArguments['mvc']['controller']))
            ? $requestArguments['mvc']['controller']
            : null;
        $vendorName = (true === isset($requestArguments['mvc']['vendor']))
            ? $requestArguments['mvc']['vendor']
            : null;
        $actionName = (true === isset($requestArguments['mvc']['action']))
            ? $requestArguments['mvc']['action']
            : null;
        $arguments = (true === isset($requestArguments['arguments']))
            ? $requestArguments['arguments']
            : [];

        if ($extensionName && $vendorName && $controllerName && $actionName) {
            /** @var Request $request */
            $request = GeneralUtility::makeInstance(Request::class);
            $request->setControllerExtensionName($extensionName);
            $request->setControllerVendorName($vendorName);
            $request->setControllerName($controllerName);
            $request->setControllerActionName($actionName);
            $request->setArguments($arguments);

            /** @var Response $response */
            $response = GeneralUtility::makeInstance(Response::class);

            /** @var Dispatcher $dispatcher */
            $dispatcher = GeneralUtility::makeInstance(Dispatcher::class);
            $dispatcher->dispatch($request, $response);

            $result = $response->getContent();
        }

        return $result;
    }
}
