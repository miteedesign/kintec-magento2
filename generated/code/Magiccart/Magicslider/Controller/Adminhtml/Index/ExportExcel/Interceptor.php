<?php
namespace Magiccart\Magicslider\Controller\Adminhtml\Index\ExportExcel;

/**
 * Interceptor class for @see \Magiccart\Magicslider\Controller\Adminhtml\Index\ExportExcel
 */
class Interceptor extends \Magiccart\Magicslider\Controller\Adminhtml\Index\ExportExcel implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magiccart\Magicslider\Model\MagicsliderFactory $magicsliderFactory, \Magiccart\Magicslider\Model\ResourceModel\Magicslider\CollectionFactory $magicsliderCollectionFactory, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, \Magento\Backend\Helper\Js $jsHelper)
    {
        $this->___init();
        parent::__construct($context, $magicsliderFactory, $magicsliderCollectionFactory, $coreRegistry, $fileFactory, $resultPageFactory, $resultLayoutFactory, $resultForwardFactory, $jsHelper);
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
