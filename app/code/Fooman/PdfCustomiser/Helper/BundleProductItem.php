<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCustomiser\Helper;

class BundleProductItem
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Escaper                        $escaper
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->escaper = $escaper;
    }

    /**
     * @param  string $value
     * @return string
     */
    protected function getFormattedPrice($value)
    {
        return $this->priceCurrency->format($value, false, null);
    }

    /**
     * @param  \Magento\Sales\Api\Data\OrderItemInterface  $item
     * @return boolean
     */
    public function isItemBundleProduct(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        return ($item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE);
    }

    /**
     * @param  \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return string
     */
    public function getBundleProductExtrasContent(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        $html = '';
        $productOptions = $item->getProductOptions();
        if (!$productOptions) {
            return $html;
        }
        if (!isset($productOptions['bundle_options'])) {
            return $html;
        }
        foreach ($productOptions['bundle_options'] as $bundleOption) {
            $html .= $this->escaper->escapeHtml($bundleOption['label']) . '<br/>';
            foreach ($bundleOption['value'] as $value) {
                $html .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . __('Title:') . '</b> ' . $this->escaper->escapeHtml($value['title']) . '<br/>';
                $html .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . __('Qty:') . '</b> ' . $this->escaper->escapeHtml($value['qty']) . '<br/>';
                $html .= '<b>&nbsp;&nbsp;&nbsp;&nbsp;' . __('Value:') . '</b> ' . $this->escaper->escapeHtml($this->getFormattedPrice($value['price'])) . '<br/>';
            }
            $html .= '<br/>';
        }
        return $html;
    }
}
