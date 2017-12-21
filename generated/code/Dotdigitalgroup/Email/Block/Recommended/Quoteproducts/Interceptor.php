<?php
namespace Dotdigitalgroup\Email\Block\Recommended\Quoteproducts;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Block\Recommended\Quoteproducts
 */
class Interceptor extends \Dotdigitalgroup\Email\Block\Recommended\Quoteproducts implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Quote\Model\QuoteFactory $quoteFactory, \Dotdigitalgroup\Email\Helper\Data $helper, \Magento\Catalog\Model\ProductFactory $productFactory, \Dotdigitalgroup\Email\Helper\Recommended $recommendedHelper, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Magento\Catalog\Block\Product\Context $context, array $data = array())
    {
        $this->___init();
        parent::__construct($quoteFactory, $helper, $productFactory, $recommendedHelper, $priceHelper, $context, $data);
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
