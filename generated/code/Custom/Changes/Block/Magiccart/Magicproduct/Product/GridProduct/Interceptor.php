<?php
namespace Custom\Changes\Block\Magiccart\Magicproduct\Product\GridProduct;

/**
 * Interceptor class for @see \Custom\Changes\Block\Magiccart\Magicproduct\Product\GridProduct
 */
class Interceptor extends \Custom\Changes\Block\Magiccart\Magicproduct\Product\GridProduct implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $urlHelper, $objectManager, $productCollectionFactory, $catalogProductVisibility, $data);
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
