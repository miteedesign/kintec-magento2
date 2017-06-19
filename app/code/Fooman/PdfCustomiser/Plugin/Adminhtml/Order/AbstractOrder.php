<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Order;

class AbstractOrder extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $orderDocumentFactory;

    /**
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\OrderFactory          $orderDocumentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface    $orderRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->orderRepository = $orderRepository;
        $this->orderDocumentFactory = $orderDocumentFactory;
    }
}
