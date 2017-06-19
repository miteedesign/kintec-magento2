<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml;

class AbstractPdfPlugin
{
    /**
     * @var \Fooman\PdfCore\Model\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Fooman\PdfCore\Model\Tcpdf\Pdf
     */
    protected $pdfRenderer;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer|Pdf             $pdfRenderer
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer
    ) {
        $this->fileFactory = $fileFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->pdfRenderer = $pdfRenderer;
    }
}
