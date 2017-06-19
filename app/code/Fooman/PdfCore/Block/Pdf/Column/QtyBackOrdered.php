<?php
namespace Fooman\PdfCore\Block\Pdf\Column;

class QtyBackOrdered extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Qty (back ordered)';
    const COLUMN_TYPE = 'fooman_qtyBackOrdered';

    public function getGetter()
    {
        return [$this, 'getQtyBackOrdered'];
    }

    public function getQtyBackOrdered($row)
    {
        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            return $row->getQtyBackordered();
        } else {
            return $row->getOrderItem()->getQtyBackordered();
        }
    }
}
