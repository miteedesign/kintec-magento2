<?php
namespace Dotdigitalgroup\Email\Block\Recommended\Recentlyviewed;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Block\Recommended\Recentlyviewed
 */
class Interceptor extends \Dotdigitalgroup\Email\Block\Recommended\Recentlyviewed implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\ProductFactory $productFactory, \Magento\Customer\Model\SessionFactory $sessionFactory, \Dotdigitalgroup\Email\Helper\Data $helper, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Dotdigitalgroup\Email\Helper\Recommended $recommended, \Magento\Catalog\Block\Product\Context $context, \Magento\Reports\Block\Product\Viewed $viewed, array $data = array())
    {
        $this->___init();
        parent::__construct($productFactory, $sessionFactory, $helper, $priceHelper, $recommended, $context, $viewed, $data);
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
