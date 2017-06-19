<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Shipment;

class AbstractShipment extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
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
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory   $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                 $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory       $shipmentDocumentFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface    $shipmentRepositoryInterface
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->shipmentRepository = $shipmentRepositoryInterface;
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
    }
}
