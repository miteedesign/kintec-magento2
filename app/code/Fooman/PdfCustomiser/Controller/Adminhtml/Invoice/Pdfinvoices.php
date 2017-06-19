<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PrintOrderPdf
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\PdfCustomiser\Controller\Adminhtml\Invoice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Pdfinvoices extends \Fooman\PdfCustomiser\Controller\Adminhtml\AbstractMassPdf
{
    protected $redirectUrl = 'sales/order_invoice/index';

    /**
     * @var \Fooman\PdfCustomiser\Block\InvoiceFactory
     */
    protected $invoiceDocumentFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                                $context
     * @param \Magento\Ui\Component\MassAction\Filter                            $filter
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory                    $fileFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                                  $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\InvoiceFactory                         $invoiceDocumentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\InvoiceFactory $invoiceDocumentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
    ) {
        $this->invoiceDocumentFactory = $invoiceDocumentFactory;
        $this->collectionFactory = $invoiceCollectionFactory;
        parent::__construct($context, $filter, $fileFactory, $pdfRenderer);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::sales_invoice');
    }

    /**
     * Print selected invoices
     *
     * @param AbstractCollection $collection
     *
     * @return void
     */
    protected function processCollection(AbstractCollection $collection)
    {
        foreach ($collection->getItems() as $invoice) {
            $document = $this->invoiceDocumentFactory->create(
                ['data' => ['invoice' => $invoice]]
            );

            $this->pdfRenderer->addDocument($document);
        }
    }
}
