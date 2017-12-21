<?php
namespace Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport\Download;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport\Download
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport\Download implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Magento\Framework\Component\ComponentRegistrar $componentRegistrar, \Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\Filesystem $filesystem, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, \Magento\Backend\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Mirasvit\Seo\Model\RedirectFactory $redirectFactory)
    {
        $this->___init();
        parent::__construct($fileFactory, $resultRawFactory, $componentRegistrar, $resource, $filesystem, $fileUploaderFactory, $context, $storeManager, $redirectFactory);
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
