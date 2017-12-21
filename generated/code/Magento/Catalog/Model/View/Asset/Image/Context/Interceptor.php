<?php
namespace Magento\Catalog\Model\View\Asset\Image\Context;

/**
 * Interceptor class for @see \Magento\Catalog\Model\View\Asset\Image\Context
 */
class Interceptor extends \Magento\Catalog\Model\View\Asset\Image\Context implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\Product\Media\ConfigInterface $mediaConfig, \Magento\Framework\Filesystem $filesystem)
    {
        $this->___init();
        parent::__construct($mediaConfig, $filesystem);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getPath');
        if (!$pluginInfo) {
            return parent::getPath();
        } else {
            return $this->___callPlugins('getPath', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getBaseUrl');
        if (!$pluginInfo) {
            return parent::getBaseUrl();
        } else {
            return $this->___callPlugins('getBaseUrl', func_get_args(), $pluginInfo);
        }
    }
}
