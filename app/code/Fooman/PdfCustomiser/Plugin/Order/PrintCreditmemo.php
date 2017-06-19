<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintCreditmemo extends AbstractCreditmemo
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintCreditmemo $subject
     * @param \Closure                                                     $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintCreditmemo $subject,
        \Closure $proceed
    ) {
        $creditmemoId = (int)$subject->getRequest()->getParam('creditmemo_id');
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            if ($creditmemo) {
                if ($this->orderViewAuthorization->canView($creditmemo->getOrder())) {
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
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                if ($this->orderViewAuthorization->canView($order)) {
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
