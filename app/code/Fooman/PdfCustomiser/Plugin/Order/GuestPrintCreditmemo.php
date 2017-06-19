<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class GuestPrintCreditmemo extends AbstractGuestCreditmemo
{
    /**
     * @param \Magento\Sales\Controller\Guest\PrintCreditmemo $subject
     * @param \Closure                                        $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Guest\PrintCreditmemo $subject,
        \Closure $proceed
    ) {

        $orderLoaded = $this->orderLoader->load($subject->getRequest());
        if ($orderLoaded === true) {
            $order = $this->registry->registry('current_order');
            if ($this->orderViewAuthorization->canView($order)) {

                $creditmemoId = (int)$subject->getRequest()->getParam('creditmemo_id');
                $orderId = (int)$subject->getRequest()->getParam('order_id');

                if ($creditmemoId) {
                    $creditmemo = $this->creditmemoRepository->get($creditmemoId);
                    if ($creditmemo) {
                        $document = $this->creditmemoDocumentFactory->create(
                            ['data' => ['creditmemo' => $creditmemo]]
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
                    $creditmemos = $order->getCreditmemosCollection();
                    if ($creditmemos) {
                        foreach ($creditmemos as $creditmemo) {
                            $document = $this->creditmemoDocumentFactory->create(
                                ['data' => ['creditmemo' => $creditmemo]]
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
