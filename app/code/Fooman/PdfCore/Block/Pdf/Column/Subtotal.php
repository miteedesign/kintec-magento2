<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Block\Pdf\Column;

class Subtotal extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Subtotal';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        if ($this->getUseOrderCurrency()) {
            $property = \Magento\Sales\Api\Data\OrderItemInterface::ROW_TOTAL_INCL_TAX;
        } else {
            $property = \Magento\Sales\Api\Data\OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX;
        }
        return $this->convertInterfaceConstantToGetter($property);
    }
}
