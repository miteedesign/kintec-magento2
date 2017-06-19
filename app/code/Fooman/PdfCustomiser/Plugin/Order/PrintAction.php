<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends AbstractOrder
{
    /**
     * @param \Magento\Sales\Controller\Order\PrintAction $subject
     * @param \Closure                                                 $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Order\PrintAction $subject,
        \Closure $proceed
    ) {
        $orderId = (int)$subject->getRequest()->getParam('order_id');

        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
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

        }
        return $this->resultForwardFactory->create()->forward('noroute');

    }
}
