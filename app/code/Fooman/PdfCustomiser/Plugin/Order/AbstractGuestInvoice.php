<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractGuestInvoice extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\InvoiceFactory
     */
    protected $invoiceDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\Guest\OrderViewAuthorization
     */
    protected $orderViewAuthorization;

    /**
     * @var \Magento\Sales\Controller\Guest\OrderLoader
     */
    protected $orderLoader;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

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
        \Magento\Sales\Controller\Guest\OrderViewAuthorization $orderViewAuthorization,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
    }
}
