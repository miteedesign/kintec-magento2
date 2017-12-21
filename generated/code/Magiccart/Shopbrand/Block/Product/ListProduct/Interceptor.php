<?php
namespace Magiccart\Shopbrand\Block\Product\ListProduct;

/**
 * Interceptor class for @see \Magiccart\Shopbrand\Block\Product\ListProduct
 */
class Interceptor extends \Magiccart\Shopbrand\Block\Product\ListProduct implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Data\Helper\PostHelper $postDataHelper, \Magento\Catalog\Model\Layer\Resolver $layerResolver, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $objectManager, $productCollectionFactory, $catalogProductVisibility, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadedProductCollection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getLoadedProductCollection');
        if (!$pluginInfo) {
            return parent::getLoadedProductCollection();
        } else {
            return $this->___callPlugins('getLoadedProductCollection', func_get_args(), $pluginInfo);
        }
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
