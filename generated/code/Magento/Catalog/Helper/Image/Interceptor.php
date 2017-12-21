<?php
namespace Magento\Catalog\Helper\Image;

/**
 * Interceptor class for @see \Magento\Catalog\Helper\Image
 */
class Interceptor extends \Magento\Catalog\Helper\Image implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Catalog\Model\Product\ImageFactory $productImageFactory, \Magento\Framework\View\Asset\Repository $assetRepo, \Magento\Framework\View\ConfigInterface $viewConfig, \Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholderFactory = null)
    {
        $this->___init();
        parent::__construct($context, $productImageFactory, $assetRepo, $viewConfig, $placeholderFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function init($product, $imageId, $attributes = array())
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'init');
        if (!$pluginInfo) {
            return parent::init($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('init', func_get_args(), $pluginInfo);
        }
    }
}
