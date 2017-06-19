<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PrintOrderPdf
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Pdfdocs extends \Fooman\PdfCustomiser\Controller\Adminhtml\AbstractMassPdf
{
    /**
     * @var \Fooman\PdfCustomiser\Block\InvoiceFactory
     */
    protected $invoiceDocumentFactory;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $shipmentDocumentFactory;

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    protected $creditmemoDocumentFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory            $fileFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                          $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\InvoiceFactory                 $invoiceDocumentFactory
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory                $shipmentDocumentFactory
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory              $creditmemoDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory,
        \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->orderDocumentFactory = $orderDocumentFactory;
        $this->collectionFactory = $orderCollectionFactory;
        parent::__construct($context, $filter, $fileFactory, $pdfRenderer);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_order')
            && $this->_authorization->isAllowed('Magento_Sales::sales_invoice')
            && ($this->_authorization->isAllowed('Magento_Sales::shipment') || $this->_authorization->isAllowed('Magento_Sales::sales_shipment'))
            && $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Print selected orders, invoices, creditmemo and shipments for selected orders
     *
     * @param AbstractCollection $collection
     *
     * @return void
     */
    protected function processCollection(AbstractCollection $collection)
    {
        foreach ($collection->getItems() as $order) {
            $document = $this->orderDocumentFactory->create(
                ['data' => ['order' => $order]]
            );

            $this->pdfRenderer->addDocument($document);

            $invoices = $order->getInvoiceCollection();
            if ($invoices) {
                foreach ($invoices as $invoice) {
                    $document = $this->invoiceDocumentFactory->create(
                        ['data' => ['invoice' => $invoice]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }

            $shipments = $order->getShipmentsCollection();
            if ($shipments) {
                foreach ($shipments as $shipment) {
                    $document = $this->shipmentDocumentFactory->create(
                        ['data' => ['shipment' => $shipment]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }

            $creditmemos = $order->getCreditmemosCollection();
            if ($creditmemos) {
                foreach ($creditmemos as $creditmemo) {
                    $document = $this->creditmemoDocumentFactory->create(
                        ['data' => ['creditmemo' => $creditmemo]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }
        }
    }
}
