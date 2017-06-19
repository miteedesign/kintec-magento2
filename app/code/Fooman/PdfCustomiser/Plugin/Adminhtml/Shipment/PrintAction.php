<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Shipment;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends AbstractShipment
{
    /**
     * @param \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\PrintAction $subject
     * @param \Closure                                                                  $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Shipment\AbstractShipment\PrintAction $subject,
        \Closure $proceed
    ) {
        $shipmentId = $subject->getRequest()->getParam('shipment_id');

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
        }
        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
