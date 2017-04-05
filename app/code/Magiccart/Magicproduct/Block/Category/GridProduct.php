<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-04-26 23:49:31
 * @@Function:
 */

namespace Magiccart\Magicproduct\Block\Category;

class GridProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    public function getTypeFilter() // getTypeFilter
    {
        $type = $this->getRequest()->getParam('type');
        if(!$type) $type = $this->getActivated(); // get form setData in Block
        return $type;
    }

    public function getWidgetCfg($cfg=null)
    {
        $info = $this->getRequest()->getParam('info');
        if($info){
            if(isset($info[$cfg])) return $info[$cfg];
            return $info;          
        }else {
            $info = $this->getCfg();
            if(isset($info[$cfg])) return $info[$cfg];
            return $info;
        }
    }

    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $type = $this->getTypeFilter();
            $category = $this->categoryRepository->get($type);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $registry = $objectManager->get('Magento\Framework\Registry');
            $registry->register('current_category', $category, true); //register current category
            // $category = $registry->registry('current_category'); //get current category
            // $registry->unregister('current_category');  //unregister current category
        }
        return parent::_getProductCollection();
    }

}
