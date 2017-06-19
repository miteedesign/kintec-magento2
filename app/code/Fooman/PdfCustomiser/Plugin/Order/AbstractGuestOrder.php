<?php

namespace Fooman\PdfCustomiser\Plugin\Order;

class AbstractGuestOrder extends \Fooman\PdfCustomiser\Plugin\Adminhtml\AbstractPdfPlugin
{

    /**
     * @var \Fooman\PdfCustomiser\Block\OrderFactory
     */
    protected $orderDocumentFactory;

    /**
     * @var \Magento\Sales\Controller\Guest\OrderViewAuthorization
     */
    protected $orderViewAuthorization;

    /**
     * @var \Magento\Sales\Controller\Guest\OrderLoader
     */
    protected $orderLoader;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * AbstractGuestOrder constructor.
     *
     * @param \Fooman\PdfCore\Model\Response\Http\FileFactory        $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory      $resultForwardFactory
     * @param \Fooman\PdfCore\Model\PdfRenderer                      $pdfRenderer
     * @param \Fooman\PdfCustomiser\Block\OrderFactory               $orderDocumentFactory
     * @param \Magento\Sales\Controller\Guest\OrderViewAuthorization $orderViewAuthorization
     * @param \Magento\Sales\Controller\Guest\OrderLoader            $orderLoader
     */
    public function __construct(
        \Fooman\PdfCore\Model\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Fooman\PdfCore\Model\PdfRenderer $pdfRenderer,
        \Fooman\PdfCustomiser\Block\OrderFactory $orderDocumentFactory,
        \Magento\Sales\Controller\Guest\OrderViewAuthorization $orderViewAuthorization,
        \Magento\Sales\Controller\Guest\OrderLoader $orderLoader,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($fileFactory, $resultForwardFactory, $pdfRenderer);

        $this->orderDocumentFactory = $orderDocumentFactory;
        $this->orderViewAuthorization = $orderViewAuthorization;
        $this->orderLoader = $orderLoader;
        $this->registry = $registry;
    }
}
