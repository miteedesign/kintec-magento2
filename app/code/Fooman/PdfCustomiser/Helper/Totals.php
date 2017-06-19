<?php

namespace Fooman\PdfCustomiser\Helper;

class Totals extends \Magento\Sales\Model\Order\Pdf\AbstractPdf
{
    /**
     * not required but "implemented" due to abstract class requirement
     */
    public function getPdf()
    {
        //noop
    }

    /**
     * make the totals list accessible
     *
     * @return \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal[]
     */
    public function getTotalsList()
    {
        return $this->_getTotalsList();
    }
}
