<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PrintOrderPdf
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Fooman\PdfCustomiser\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

abstract class AbstractMassPdf extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    protected $redirectUrl = 'sales/order/index';

    /**
     * @param AbstractCollection $collection
     *
     * @return mixed
     */
    abstract protected function processCollection(AbstractCollection $collection);

    /**
     * @var \Fooman\PdfCore\Model\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Fooman\PdfCore\Model\Tcpdf\Pdf
     */
    protected $pdfRenderer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Magento\Backend\App\Action\Context             $context
     * @param \Magento\Ui\Component\MassAction\Filter         $filter
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer               $pdfRenderer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer
    ) {
        $this->fileFactory = $fileFactory;
        $this->pdfRenderer = $pdfRenderer;
        parent::__construct($context, $filter);
    }

    /**
     * Print selected orders
     *
     * @param AbstractCollection $collection
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $this->processCollection($collection);

        if ($this->pdfRenderer->hasPrintContent()) {
            return $this->fileFactory->create(
                $this->getFileName(),
                $this->pdfRenderer->getPdfAsString(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }
        throw new \Magento\Framework\Exception\NotFoundException(__('Nothing to print'));
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return $this->pdfRenderer->getFileName();
    }
}
