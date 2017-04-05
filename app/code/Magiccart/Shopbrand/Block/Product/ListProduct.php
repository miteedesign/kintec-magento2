<?php
/**
 * Magiccart 
 * @category    Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license     http://www.magiccart.net/license-agreement.html
 * @Author: DOng NGuyen<nguyen@dvn.com>
 * @@Create Date: 2016-01-05 10:40:51
 * @@Modify Date: 2016-03-24 19:57:32
 * @@Function:
 */

namespace Magiccart\Shopbrand\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * Product collection model
     *
     * @var Magento\Catalog\Model\Resource\Product\Collection
     */
    protected $_productCollection;

    /**
     * Catalog Layer
     *
     * @var Magento\Catalog\Model\Layer\Resolver
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_objectManager;
    
    /**
     * Initialize
     *
     * @param Magento\Catalog\Block\Product\Context $context
     * @param Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository,
     * @param Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param Magento\Framework\Url\Helper\Data $urlHelper
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context, 
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper, 
        \Magento\Catalog\Model\Layer\Resolver $layerResolver, 
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,

        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
            array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;

        $this->_objectManager = $objectManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function getType()
    {
        $type = $this->getRequest()->getParam('type');
        if(!$type){
            $type = $this->getActive(); // get form setData in Block
        }
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
            $type = $this->getType();
            $fn = 'get' . ucfirst($type) . 'Products';
            $collection = $this->{$fn}();
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }


    public function getBestsellerProducts(){

        $timePeriod = 365;
        $date = date('Y-m-d H:i:s');
        $newdate = strtotime ( '-'.$timePeriod.' day' , strtotime ( $date ) ) ;
        $newdate = date ( 'Y-m-j' , $newdate ); 

        // set Limit
        $limit = 6;

        // $resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        // $tablePrefix = ''; //$resources->getTablePrefix();

        // $sql = "SELECT max(qo) AS des_qty,`product_id`,`parent_item_id`
        //     FROM ( SELECT sum(`qty_ordered`) AS qo,`product_id`,created_at,store_id,`parent_item_id` FROM {$tablePrefix}sales_flat_order_item GROUP BY `product_id` )
        //     AS t1 where parent_item_id is null
        //     AND created_at between '{$newdate}' AND '{$date}'
        //     GROUP BY `product_id` ORDER BY des_qty DESC LIMIT {$limit}";

        // // Note: remove limit if filter follow category
        // $connection= $resources->getConnection();
        // $rows = $connection->fetchAll($sql);
        // $producIds = array();
        // foreach ($rows as $row) { $producIds[] = $row['product_id'];}

        // $collection = $this->_productCollectionFactory->create();
        //         $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        //         $collection = $this->_addProductAttributesAndPrices(
        //             $collection
        //         )->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));


        $collection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
        $producIds = array();
        foreach ($collection as $product) {
            $producIds[] = $product->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));

        return $collection;
        
    }

    public function getFeaturedProducts()
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
        $collection->addAttributeToFilter('featured', '1')
                    ->addStoreFilter()
                    ->addAttributeToSelect('*')
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents()
                    ->setPageSize(10);

        return $collection;

    }

    public function getLatestProducts(){

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()
        ->addAttributeToSort('entity_id', 'desc')
        ->setPageSize(
            10
        )->setCurPage(
            1
        );

        return $collection; 
    }

    public function getMostviewedProducts(){
 
        $collection = $this->_objectManager->get('\Magento\Reports\Model\ResourceModel\Report\Product\Viewed\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
        $producIds = array();
        foreach ($collection as $product) {
            $producIds[] = $product->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));
        return $collection;

    }

    public function getNewProducts() {

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'news_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort(
            'news_from_date',
            'desc'
        )->setPageSize(
            10
        )->setCurPage(
            1
        );

        return $collection;
    }

    public function getRandomProducts() {

        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter();

        $collection->getSelect()->order('rand()');


        // getNumProduct
        $collection->setPageSize(
            10
        )->setCurPage(
            1
        );
        return $collection;
    }

    public function getRecentlyProducts() {

        // \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory

    }

    public function getSaleProducts(){

        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'special_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort(
            'special_to_date',
            'desc'
        )->setPageSize(
            10
        )->setCurPage(
            1
        );

        return $collection;

    }

    public function getSpecialProducts() {


        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'special_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            [
                ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
                ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
        )->addAttributeToSort(
            'special_to_date',
            'desc'
        )->setPageSize(
            10
        )->setCurPage(
            1
        );

        return $collection;
    }

}
