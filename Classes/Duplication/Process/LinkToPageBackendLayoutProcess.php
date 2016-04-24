<?php
/**
 * NOT IMPLEMENTED YET
 */

//namespace Romm\SiteFactory\Duplication\Process;
//
///***************************************************************
// *  Copyright notice
// *
// *  (c) 2015 Romain CANON <romain.canon@exl-group.com>
// *  All rights reserved
// *
// *  This script is part of the TYPO3 project. The TYPO3 project is
// *  free software; you can redistribute it and/or modify
// *  it under the terms of the GNU General Public License as published by
// *  the Free Software Foundation; either version 3 of the License, or
// *  (at your option) any later version.
// *
// *  The GNU General Public License can be found at
// *  http://www.gnu.org/copyleft/gpl.html.
// *
// *  This script is distributed in the hope that it will be useful,
// *  but WITHOUT ANY WARRANTY; without even the implied warranty of
// *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// *  GNU General Public License for more details.
// *
// *  This copyright notice MUST APPEAR in all copies of the script!
// ***************************************************************/
//
//use Romm\SiteFactory\Duplication\AbstractDuplicationProcess;
//
///**
// * Class containing functions called when a site is being duplicated.
// */
//class LinkToPageBackendLayoutProcess extends AbstractDuplicationProcess {
//    public function ajaxRun(&$params) {
//        $data = array();
//        if (isset($params['fieldsValues']['pageBackendLayout'])) {
//            $data['backend_layout']    = $params['fieldsValues']['pageBackendLayout'];
//        }
//        if (isset($params['fieldsValues']['subPagesBackendLayout'])) {
//            $data['backend_layout_next_level']    = $params['fieldsValues']['subPagesBackendLayout'];
//        }
//
//        if (!empty($data)) {
//            $this->database->exec_UPDATEquery(
//                'pages',
//                'uid=' . intval($params['duplicationData']['duplicatedPageUid']),
//                $data
//            );
//        }
//    }
//}
