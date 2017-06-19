<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class GuestPrintAction extends AbstractGuestOrder
{
    /**
     * @param \Magento\Sales\Controller\Guest\PrintAction $subject
     * @param \Closure                                    $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Guest\PrintAction $subject,
        \Closure $proceed
    ) {

        $orderLoaded = $this->orderLoader->load($subject->getRequest());
        if ($orderLoaded === true) {
            $order = $this->registry->registry('current_order');
            if ($this->orderViewAuthorization->canView($order)) {
                $document = $this->orderDocumentFactory->create(
                    ['data' => ['order' => $order]]
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
