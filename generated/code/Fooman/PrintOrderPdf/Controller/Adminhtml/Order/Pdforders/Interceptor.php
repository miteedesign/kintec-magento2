<?php
namespace Fooman\PrintOrderPdf\Controller\Adminhtml\Order\Pdforders;

/**
 * Interceptor class for @see \Fooman\PrintOrderPdf\Controller\Adminhtml\Order\Pdforders
 */
class Interceptor extends \Fooman\PrintOrderPdf\Controller\Adminhtml\Order\Pdforders implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Fooman\PrintOrderPdf\Model\Pdf\OrderFactory $orderPdfFactory, \Magento\Framework\Stdlib\DateTime\DateTime $date)
    {
        $this->___init();
        parent::__construct($context, $filter, $collectionFactory, $fileFactory, $orderPdfFactory, $date);
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
