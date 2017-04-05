<?php
/**
 * Magiccart 
 * @category 	Magiccart 
 * @copyright 	Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license 	http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-03-23 16:15:14
 * @@Function:
 */

namespace Magiccart\Shopbrand\Controller\Adminhtml;

abstract class Brand extends \Magiccart\Shopbrand\Controller\Adminhtml\Shopbrand
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magiccart_Shopbrand::shopbrand_brand');
    }
}
