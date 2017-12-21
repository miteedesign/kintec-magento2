<?php
namespace Magento\Catalog\Model\View\Asset\Image;

/**
 * Interceptor class for @see \Magento\Catalog\Model\View\Asset\Image
 */
class Interceptor extends \Magento\Catalog\Model\View\Asset\Image implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\Product\Media\ConfigInterface $mediaConfig, \Magento\Framework\View\Asset\ContextInterface $context, \Magento\Framework\Encryption\EncryptorInterface $encryptor, $filePath, array $miscParams = array())
    {
        $this->___init();
        parent::__construct($mediaConfig, $context, $encryptor, $filePath, $miscParams);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getUrl');
        if (!$pluginInfo) {
            return parent::getUrl();
        } else {
            return $this->___callPlugins('getUrl', func_get_args(), $pluginInfo);
        }
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
}
