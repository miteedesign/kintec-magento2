<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Creditmemo;

class AbstractCreditmemo extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    protected $creditmemoDocumentFactory;

    /**
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory     $creditmemoDocumentFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface  $creditmemoRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
    }
}
