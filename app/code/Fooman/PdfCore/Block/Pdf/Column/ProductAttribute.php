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

class ProductAttribute extends \Fooman\PdfCore\Block\Pdf\Column
{
    const DEFAULT_WIDTH = 20;
    const DEFAULT_TITLE = '';
    const COLUMN_TYPE = 'default';

    public function getTitle()
    {
        //We have set a custom title
        if (strlen(parent::getTitle()) >= 1) {
            return parent::getTitle();
        }

        //convert the product attribute code as best as we can into a title
        return __(ucwords(str_replace('_', ' ', $this->getIndex())));
    }

    public function getGetter()
    {
        return [$this, 'getProductAttribute'];
    }

    public function getProductAttribute($row)
    {
        $method = 'getProduct' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getIndex())));
        if ($row->getOrderItem()) {
            return $row->getOrderItem()->$method();
        }
        return $row->$method();
    }
}
