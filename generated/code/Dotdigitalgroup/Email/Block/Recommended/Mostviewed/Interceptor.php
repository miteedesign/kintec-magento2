<?php
namespace Dotdigitalgroup\Email\Block\Recommended\Mostviewed;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Block\Recommended\Mostviewed
 */
class Interceptor extends \Dotdigitalgroup\Email\Block\Recommended\Mostviewed implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Dotdigitalgroup\Email\Helper\Data $helper, \Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\ProductFactory $productFactory, \Dotdigitalgroup\Email\Helper\Recommended $recommended, \Magento\Catalog\Model\CategoryFactory $categtoryFactory, \Magento\Framework\App\ResourceConnection $resourceConnection, \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportProductCollection, array $data = array())
    {
        $this->___init();
        parent::__construct($helper, $context, $priceHelper, $productCollectionFactory, $productFactory, $recommended, $categtoryFactory, $resourceConnection, $reportProductCollection, $data);
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
