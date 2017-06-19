<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractCreditmemo extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    protected $creditmemoDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
     */
    protected $orderViewAuthorization;

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
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
    }
}
