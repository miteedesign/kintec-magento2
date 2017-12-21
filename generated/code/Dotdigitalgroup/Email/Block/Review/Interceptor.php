<?php
namespace Dotdigitalgroup\Email\Block\Review;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Block\Review
 */
class Interceptor extends \Dotdigitalgroup\Email\Block\Review implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection, \Magento\Review\Model\ReviewFactory $reviewFactory, \Magento\Sales\Model\OrderFactory $orderFactory, \Dotdigitalgroup\Email\Helper\Data $helper, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Magento\Catalog\Block\Product\Context $context, array $data = array())
    {
        $this->___init();
        parent::__construct($productCollection, $reviewFactory, $orderFactory, $helper, $priceHelper, $context, $data);
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
