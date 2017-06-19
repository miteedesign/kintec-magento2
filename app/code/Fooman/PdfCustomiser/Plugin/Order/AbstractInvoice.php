<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractInvoice extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\InvoiceFactory
     */
    protected $invoiceDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
     */
    protected $orderViewAuthorization;

    /**
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\InvoiceFactory        $invoiceDocumentFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface     $invoiceRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
    }
}
