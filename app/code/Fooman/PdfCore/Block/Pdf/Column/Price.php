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

use \Magento\Tax\Model\Config as TaxConfig;
use \Magento\Sales\Api\Data\OrderItemInterface;

class Price extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 12;
    const DEFAULT_TITLE = 'Price';
    const COLUMN_TYPE = 'fooman_currency';

    public function getGetter()
    {
        return [$this, 'getPrice'];
    }

    public function getPrice($row)
    {
        $priceTaxDisplay = $this->_scopeConfig->getValue(
            TaxConfig::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $row->getStoreId()
        );

        if ($priceTaxDisplay == TaxConfig::DISPLAY_TYPE_EXCLUDING_TAX
            || $priceTaxDisplay == TaxConfig::DISPLAY_TYPE_BOTH
        ) {
            if ($this->getUseOrderCurrency()) {
                $property = OrderItemInterface::PRICE;
            } else {
                $property = OrderItemInterface::BASE_PRICE;
            }
        } else {
            if ($this->getUseOrderCurrency()) {
                $property = OrderItemInterface::PRICE_INCL_TAX;
            } else {
                $property = OrderItemInterface::BASE_PRICE_INCL_TAX;
            }
        }

        $method = $this->convertInterfaceConstantToGetter($property);
        return call_user_func([$row, $method]);
    }
}
