<?php

namespace Dotdigitalgroup\Email\Block;

class Order extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var
     */
    protected $_quote;
    /**
     * @var \Dotdigitalgroup\Email\Helper\Data
     */
    public $helper;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $priceHelper;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;
    /**
     * @var
     */
    protected $_reviewHelper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * Order constructor.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Dotdigitalgroup\Email\Helper\Data $helper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Dotdigitalgroup\Email\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        $this->_productCollection = $productCollection;
        $this->_reviewFactory = $reviewFactory;
        $this->_orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->priceHelper = $priceHelper;

        parent::__construct($context, $data);
    }

    /**
     * Current Order.
     *
     * @return $this|bool|mixed
     */
    public function getOrder()
    {
        $orderId = $this->_coreRegistry->registry('order_id');
        $order = $this->_coreRegistry->registry('current_order');
        if (!$orderId) {
            $orderId = $this->getRequest()->getParam('order_id');
            if (!$orderId) {
                return false;
            }
            $this->_coreRegistry->unregister('order_id'); // additional measure
            $this->_coreRegistry->register('order_id', $orderId);
        }
        if (!$order) {
            if (!$orderId) {
                return false;
            }
            $order = $this->_orderFactory->create()->load($orderId);
            $this->_coreRegistry->unregister('current_order'); // additional measure
            $this->_coreRegistry->register('current_order', $order);
        }

        return $order;
    }

    /**
     * @param string $mode
     *
     * @return mixed|string
     */
    public function getMode($mode = 'list')
    {
        if ($this->getOrder()) {
            $website = $this->_storeManager->getStore(
                $this->getOrder()->getStoreId()
            )
                ->getWebsite();
            $mode = $this->helper->getReviewDisplayType($website);
        }

        return $mode;
    }

    /**
     * Filter items for review. If a customer has already placed a review for a product then exclude the product.
     *
     * @param array $items
     * @param int   $websiteId
     *
     * @return mixed
     */
    public function filterItemsForReview($items, $websiteId)
    {
        if (!count($items)) {
            return false;
        }

        $order = $this->getOrder();

        //if customer is guest then no need to filter any items
        if ($order->getCustomerIsGuest()) {
            return $items;
        }

        if (!$this->helper->isNewProductOnly($websiteId)) {
            return $items;
        }

        $customerId = $order->getCustomerId();

        foreach ($items as $key => $item) {
            $productId = $item->getProduct()->getId();

            $collection = $this->_reviewFactory->create()->getCollection()
                ->addCustomerFilter($customerId)
                ->addStoreFilter($order->getStoreId())
                ->addFieldToFilter('main_table.entity_pk_value', $productId);

            //remove item if customer has already placed review on this item
            if ($collection->getSize()) {
                unset($items[$key]);
            }
        }

        return $items;
    }

    /**
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function getItems()
    {
        $order = $this->getOrder();
        $items = $order->getAllVisibleItems();
        $productIds = [];
        //get the product ids for the collection
        foreach ($items as $item) {
            $productIds[] = $item->getProductId();
        }
        $items = $this->_productCollection
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $productIds]);

        return $items;
    }

    /**
     * @param $productId
     *
     * @return string
     */
    public function getReviewItemUrl($productId)
    {
        return $this->_urlBuilder->getUrl('review/product/list', ['id' => $productId]);
    }
}
