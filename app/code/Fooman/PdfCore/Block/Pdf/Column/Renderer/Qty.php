<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Block\Pdf\Column\Renderer;

class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const XML_PATH_QTY_AS_INT = 'sales_pdf/all/allqtyasint';

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return mixed
     */
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $data = parent::_getValue($row);
        if ($data && $this->_scopeConfig->getValue(
            self::XML_PATH_QTY_AS_INT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $row->getStoreId()
        )
        ) {
            $data = intval($data);
        }
        return $this->escapeHtml($data);
    }
}
