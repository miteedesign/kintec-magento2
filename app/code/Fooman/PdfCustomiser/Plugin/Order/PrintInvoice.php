<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintInvoice extends AbstractInvoice
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintInvoice $subject
     * @param \Closure                                     $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintInvoice $subject,
        \Closure $proceed
    ) {
        $invoiceId = (int)$subject->getRequest()->getParam('invoice_id');
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            if ($invoice) {
                if ($this->orderViewAuthorization->canView($invoice->getOrder())) {
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
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                if ($this->orderViewAuthorization->canView($order)) {
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
