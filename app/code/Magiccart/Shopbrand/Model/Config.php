<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-11 23:15:05
 * @@Modify Date: 2016-03-23 16:10:15
 * @@Function:
 */

namespace Magiccart\Shopbrand\Model;

class Config extends \Magento\Catalog\Model\Config
{
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Eav\Model\Entity\TypeFactory $entityTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $entityTypeCollectionFactory,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $configFactory,
        \Magento\Catalog\Model\Product\TypeFactory $productTypeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_configFactory = $configFactory;
        $this->_productTypeFactory = $productTypeFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->_setCollectionFactory = $setCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;

        parent::__construct($cache, $entityTypeFactory, $entityTypeCollectionFactory, $cacheState, $universalFactory,$scopeConfig,$configFactory,$productTypeFactory,$groupCollectionFactory,$setCollectionFactory,$storeManager,$eavConfig);
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = ['position' => __('Position'),'created_at'=>'Newest'];
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }

}
