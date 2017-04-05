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
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;


class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * Default direction
     *
     * @var string
     */
    protected $_direction = 'desc';
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param ToolbarModel $toolbarModel
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param ProductList $productListHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magiccart\Shopbrand\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_catalogConfig = $catalogConfig;
        $this->_toolbarModel = $toolbarModel;
        $this->urlEncoder = $urlEncoder;
        $this->_productListHelper = $productListHelper;
        $this->_postDataHelper = $postDataHelper;
        parent::__construct($context,$catalogSession,$catalogConfig,$toolbarModel,$urlEncoder,$productListHelper,$postDataHelper,$data);
    }
    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        /*if ($this->getCurrentOrder()) {
            $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
        }
        */
        if ($this->getCurrentOrder()) {


            // Costruisco la custom query
            switch ($this->getCurrentOrder()) {

                case 'created_at':

                    if ( $this->getCurrentDirection() == 'desc' ) {

                        $this->_collection
                            ->getSelect()
                            ->order('e.created_at DESC');


                    } elseif ( $this->getCurrentDirection() == 'asc' ) {

                        $this->_collection
                            ->getSelect()
                            ->order('e.created_at ASC');

                    }

                    break;

                default:

                    $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
                    break;

            }

        }
        return $this;
    }
    /**
     * Get order field
     *
     * @return null|string
     */
    protected function getOrderField()
    {
        if ($this->_orderField === null) {
            $this->_orderField = $this->_productListHelper->getDefaultSortField();
        }
        return 'created_at';
    }

        /**
     * Load Available Orders
     *
     * @return $this
     */
        /*
    private function loadAvailableOrders()
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = $this->_catalogConfig->getAttributeUsedForSortByArray();
        }

        $this->_availableOrder['created_at'] = 'New';
        return $this;
    }
    */
}