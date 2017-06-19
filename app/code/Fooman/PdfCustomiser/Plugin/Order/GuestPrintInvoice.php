<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class GuestPrintInvoice extends AbstractGuestInvoice
{
    /**
     * @param \Magento\Sales\Controller\AbstractController\PrintInvoice $subject
     * @param \Closure                                                  $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\AbstractController\PrintInvoice $subject,
        \Closure $proceed
    ) {

        $orderLoaded = $this->orderLoader->load($subject->getRequest());
        if ($orderLoaded === true) {
            $order = $this->registry->registry('current_order');
            if ($this->orderViewAuthorization->canView($order)) {

                $invoiceId = (int)$subject->getRequest()->getParam('invoice_id');
                $orderId = (int)$subject->getRequest()->getParam('order_id');

                if ($invoiceId) {
                    $invoice = $this->invoiceRepository->get($invoiceId);

                    if ($invoice) {
                        $document = $this->invoiceDocumentFactory->create(
                            ['data' => ['invoice' => $invoice]]
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
                    $invoices = $order->getInvoiceCollection();
                    if ($invoices) {
                        foreach ($invoices as $invoice) {
                            $document = $this->invoiceDocumentFactory->create(
                                ['data' => ['invoice' => $invoice]]
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
