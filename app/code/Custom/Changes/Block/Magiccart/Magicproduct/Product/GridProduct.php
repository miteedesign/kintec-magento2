<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-04-26 23:16:50
 * @@Function:
 */

namespace Custom\Changes\Block\Magiccart\Magicproduct\Product;

class GridProduct extends \Magiccart\Magicproduct\Block\Product\GridProduct
{



	public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
		
        $renderer = $this->getDetailsRenderer($product->getTypeId());
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    public function getDetailsRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }


    public function getFeaturedoneProducts()
    {

		
        // // cach 1 
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // //$data= array();
        // //$this->_objectManager->create($this->_instanceName, $data);
        // $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

        // // Cach 2
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $manager */
        // $manager = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        // $collection = $manager->create();
        
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addAttributeToFilter('featuredone', '1')
                    ->addStoreFilter()
                    ->addAttributeToSelect('*')
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->setPageSize($this->_limit)->setCurPage(1);;

        return $collection;

    }

    public function getFeaturedtwoProducts()
    {

        // // cach 1 
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // //$data= array();
        // //$this->_objectManager->create($this->_instanceName, $data);
        // $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection');

        // // Cach 2
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $manager */
        // $manager = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        // $collection = $manager->create();
        
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addAttributeToFilter('featuredtwo', '1')
                    ->addStoreFilter()
                    ->addAttributeToSelect('*')
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->setPageSize($this->_limit)->setCurPage(1);;

        return $collection;

    }



}
