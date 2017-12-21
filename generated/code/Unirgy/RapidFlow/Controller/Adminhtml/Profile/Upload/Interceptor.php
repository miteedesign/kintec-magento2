<?php
namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile\Upload;

/**
 * Interceptor class for @see \Unirgy\RapidFlow\Controller\Adminhtml\Profile\Upload
 */
class Interceptor extends \Unirgy\RapidFlow\Controller\Adminhtml\Profile\Upload implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\RapidFlow\Model\Profile $profile, \Magento\Catalog\Helper\Data $catalogHelper, \Unirgy\RapidFlow\Model\ResourceModel\Profile $resource, \Magento\Framework\App\Filesystem\DirectoryList $directoryList, \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory)
    {
        $this->___init();
        parent::__construct($context, $profile, $catalogHelper, $resource, $directoryList, $writeFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
