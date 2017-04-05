<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-02-29 11:55:40
 * @@Function:
 */

namespace Magiccart\Magicmenu\Controller\Adminhtml;

abstract class Menu extends \Magiccart\Magicmenu\Controller\Adminhtml\Magicmenu
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magiccart_Magicmenu::magicmenu_menu');
    }
}
