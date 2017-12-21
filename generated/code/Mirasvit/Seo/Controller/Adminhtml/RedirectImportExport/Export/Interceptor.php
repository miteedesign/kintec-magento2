<?php
namespace Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport\Export;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport\Export
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport\Export implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\ResourceConnection $resource, \Magento\Framework\Filesystem $filesystem, \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory, \Magento\Backend\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Mirasvit\Seo\Model\RedirectFactory $redirectFactory)
    {
        $this->___init();
        parent::__construct($resource, $filesystem, $fileUploaderFactory, $context, $storeManager, $fileFactory, $redirectFactory);
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
