<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractGuestShipment extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $shipmentDocumentFactory;

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
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory       $shipmentDocumentFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface    $shipmentRepository
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Controller\Guest\OrderViewAuthorization $orderViewAuthorization,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
    }
}
