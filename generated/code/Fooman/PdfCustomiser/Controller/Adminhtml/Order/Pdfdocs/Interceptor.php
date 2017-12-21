<?php
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Order\Pdfdocs;

/**
 * Interceptor class for @see \Fooman\PdfCustomiser\Controller\Adminhtml\Order\Pdfdocs
 */
class Interceptor extends \Fooman\PdfCustomiser\Controller\Adminhtml\Order\Pdfdocs implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory, \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer, \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory, \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory, \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory, \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory, \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory)
    {
        $this->___init();
        parent::__construct($context, $filter, $fileFactory, $pdfRenderer, $orderDocumentFactory, $invoiceDocumentFactory, $shipmentDocumentFactory, $creditmemoDocumentFactory, $orderCollectionFactory);
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
