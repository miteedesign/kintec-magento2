<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintShipment extends AbstractShipment
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintShipment $subject
     * @param \Closure                                      $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintShipment $subject,
        \Closure $proceed
    ) {
        $shipmentId = (int)$subject->getRequest()->getParam('shipment_id');
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if ($shipment) {
                if ($this->orderViewAuthorization->canView($shipment->getOrder())) {
                    $document = $this->shipmentDocumentFactory->create(
                        ['data' => ['shipment' => $shipment]]
                    );

                    $this->pdfRenderer->addDocument($document);

                    return $this->fileFactory->create(
                        $this->pdfRenderer->getFileName(),
                        $this->pdfRenderer->getPdfAsString(),
                        DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                }
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                if ($this->orderViewAuthorization->canView($order)) {
                    $shipments = $order->getShipmentsCollection();
                    if ($shipments) {
                        foreach ($shipments as $shipment) {
                            $document = $this->shipmentDocumentFactory->create(
                                ['data' => ['shipment' => $shipment]]
                            );

                            $this->pdfRenderer->addDocument($document);
                        }
                        return $this->fileFactory->create(
                            $this->pdfRenderer->getFileName(),
                            $this->pdfRenderer->getPdfAsString(),
                            DirectoryList::VAR_DIR,
                            'application/pdf'
                        );
                    }
                }
            }
        }
        return $this->resultForwardFactory->create()->forward('noroute');

    }
}
