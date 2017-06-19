<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PrintOrderPdf
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Pdfshipments extends \Fooman\PdfCustomiser\Controller\Adminhtml\AbstractMassPdf
{
    /**
     * @var \Fooman\PdfCustomiser\Block\ShipmentFactory
     */
    protected $shipmentDocumentFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory            $fileFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                          $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\ShipmentFactory                $shipmentDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\ShipmentFactory $shipmentDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->collectionFactory = $orderCollectionFactory;
        parent::__construct($context, $filter, $fileFactory, $pdfRenderer);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return ($this->_authorization->isAllowed('Magento_Sales::shipment') || $this->_authorization->isAllowed('Magento_Sales::sales_shipment'));
    }

    /**
     * Print selected shipments
     *
     * @param AbstractCollection $collection
     *
     * @return void
     */
    protected function processCollection(AbstractCollection $collection)
    {
        foreach ($collection->getItems() as $order) {
            $shipments = $order->getShipmentsCollection();
            if ($shipments) {
                foreach ($shipments as $shipment) {
                    $document = $this->shipmentDocumentFactory->create(
                        ['data' => ['shipment' => $shipment]]
                    );

                    $this->pdfRenderer->addDocument($document);
                }
            }
        }
    }
}
