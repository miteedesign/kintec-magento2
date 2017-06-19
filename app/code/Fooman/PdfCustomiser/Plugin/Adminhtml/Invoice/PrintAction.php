<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Invoice;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends AbstractInvoice
{
    /**
     * @param \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\PrintAction $subject
     * @param \Closure                                                                $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\PrintAction $subject,
        \Closure $proceed
    ) {
        $invoiceId = $subject->getRequest()->getParam('invoice_id');

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
        }
        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
