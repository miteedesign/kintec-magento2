<?php
namespace Dotdigitalgroup\Email\Block\Recommended\Bestsellers;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Block\Recommended\Bestsellers
 */
class Interceptor extends \Dotdigitalgroup\Email\Block\Recommended\Bestsellers implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Dotdigitalgroup\Email\Helper\Data $helper, \Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Dotdigitalgroup\Email\Helper\Recommended $recommended, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Catalog\Block\Product\Context $context, \Magento\CatalogInventory\Model\StockFactory $stockFactory, \Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory $productSoldFactory, array $data = array())
    {
        $this->___init();
        parent::__construct($helper, $resource, $priceHelper, $recommended, $categoryFactory, $context, $stockFactory, $productFactory, $productSoldFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($product, $imageId, $attributes = array())
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getImage');
        if (!$pluginInfo) {
            return parent::getImage($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('getImage', func_get_args(), $pluginInfo);
        }
    }
}
