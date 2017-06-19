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

class TaxPercentage extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Tax %';

    public function getGetter()
    {
        return [$this, 'getTaxPercent'];
    }

    public function getTaxPercent($row)
    {
        if ($row instanceof \Magento\Sales\Api\Data\OrderItemInterface) {
            return sprintf('%f %%', $row->getTaxPercent());
        } else {
            return sprintf('%f %%', $row->getOrderItem()->getTaxPercent());
        }
    }

}
