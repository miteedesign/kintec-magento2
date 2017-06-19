<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PrintOrderPdf
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Creditmemo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Pdfcreditmemos extends \Fooman\PdfCustomiser\Controller\Adminhtml\AbstractMassPdf
{
    protected $redirectUrl = 'sales/order_creditmemo/index';

    /**
     * @var \Fooman\PdfCustomiser\Block\CreditmemoFactory
     */
    protected $creditmemoDocumentFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                                $context
     * @param \Magento\Ui\Component\MassAction\Filter                            $filter
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory                    $fileFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                                  $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\CreditmemoFactory                      $creditmemoDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\CreditmemoFactory $creditmemoDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory
    ) {
        $this->creditmemoDocumentFactory = $creditmemoDocumentFactory;
        $this->collectionFactory = $creditmemoCollectionFactory;
        parent::__construct($context, $filter, $fileFactory, $pdfRenderer);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_creditmemo');
    }

    /**
     * Print selected creditmemos
     *
     * @param AbstractCollection $collection
     *
     * @return void
     */
    protected function processCollection(AbstractCollection $collection)
    {
        foreach ($collection->getItems() as $creditmemo) {
            $document = $this->creditmemoDocumentFactory->create(
                ['data' => ['creditmemo' => $creditmemo]]
            );

            $this->pdfRenderer->addDocument($document);
        }
    }
}
