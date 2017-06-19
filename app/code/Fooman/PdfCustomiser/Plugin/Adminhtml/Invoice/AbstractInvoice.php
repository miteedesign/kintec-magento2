<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Invoice;

class AbstractInvoice extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
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
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\InvoiceFactory        $invoiceDocumentFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface     $invoiceRepositoryInterface
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepositoryInterface
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->invoiceRepository = $invoiceRepositoryInterface;
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
    }
}
