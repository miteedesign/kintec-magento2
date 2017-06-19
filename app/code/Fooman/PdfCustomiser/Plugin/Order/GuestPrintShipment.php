<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class GuestPrintShipment extends AbstractGuestShipment
{
    /**
     * @param \Magento\Sales\Controller\Guest\PrintShipment $subject
     * @param \Closure                                      $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Guest\PrintShipment $subject,
        \Closure $proceed
    ) {

        $orderLoaded = $this->orderLoader->load($subject->getRequest());
        if ($orderLoaded === true) {
            $order = $this->registry->registry('current_order');
            if ($this->orderViewAuthorization->canView($order)) {

                $shipmentId = (int)$subject->getRequest()->getParam('shipment_id');
                $orderId = (int)$subject->getRequest()->getParam('order_id');

                if ($shipmentId) {
                    $shipment = $this->shipmentRepository->get($shipmentId);
                    if ($shipment) {
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
                } elseif ($orderId) {
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
