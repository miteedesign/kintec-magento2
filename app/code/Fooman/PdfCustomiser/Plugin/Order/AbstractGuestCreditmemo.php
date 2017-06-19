<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractGuestCreditmemo extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
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
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory     $creditmemoDocumentFactory
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface  $creditmemoRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Controller\Guest\OrderViewAuthorization $orderViewAuthorization,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
    }
}
