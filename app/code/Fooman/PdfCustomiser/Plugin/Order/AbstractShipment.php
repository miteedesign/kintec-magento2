<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractShipment extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $shipmentDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface
     */
    protected $orderViewAuthorization;

    /**
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory       $shipmentDocumentFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface    $shipmentRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Controller\AbstractController\OrderViewAuthorizationInterface $orderViewAuthorization,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
    }
}
