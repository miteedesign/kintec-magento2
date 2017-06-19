<?php

namespace Fooman\PdfCustomiser\Plugin\Adminhtml\Creditmemo;

use Magento\Framework\App\Filesystem\DirectoryList;

class PrintAction extends AbstractCreditmemo
{
    /**
     * @param \Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\PrintAction $subject
     * @param \Closure                                                                      $proceed
     *
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Sales\Controller\Adminhtml\Creditmemo\AbstractCreditmemo\PrintAction $subject,
        \Closure $proceed
    ) {
        $creditmemoId = $subject->getRequest()->getParam('creditmemo_id');

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
        }
        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
