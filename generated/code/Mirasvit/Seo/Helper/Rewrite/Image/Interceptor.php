<?php
namespace Mirasvit\Seo\Helper\Rewrite\Image;

/**
 * Interceptor class for @see \Mirasvit\Seo\Helper\Rewrite\Image
 */
class Interceptor extends \Mirasvit\Seo\Helper\Rewrite\Image implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\Seo\Model\Config $config, \Mirasvit\Seo\Helper\Parse $parse, \Magento\Framework\App\Helper\Context $context, \Magento\Catalog\Model\Product\ImageFactory $productImageFactory, \Magento\Framework\View\Asset\Repository $assetRepo, \Magento\Framework\View\ConfigInterface $viewConfig)
    {
        $this->___init();
        parent::__construct($config, $parse, $context, $productImageFactory, $assetRepo, $viewConfig);
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
