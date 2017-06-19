<?php

namespace Fooman\PdfCustomiser\Block;

class Totals extends \Fooman\PdfCore\Block\Pdf\Block
{
    protected $_template = 'Fooman_PdfCustomiser::pdf/totals.phtml';
    protected $totalsHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fooman\PdfCustomiser\Helper\Totals              $totalsHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fooman\PdfCustomiser\Helper\Totals $totalsHelper,
        array $data = []
    ) {
        $this->totalsHelper = $totalsHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal[]
     */
    public function getTotals()
    {
        return $this->totalsHelper->getTotalsList();
    }

    /**
     * @param \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total
     *
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
     */
    public function prepareTotal(\Magento\Sales\Model\Order\Pdf\Total\DefaultTotal $total)
    {
        $total->setOrder($this->getOrder())->setSource($this->getSalesObject());
        return $total;
    }
}
