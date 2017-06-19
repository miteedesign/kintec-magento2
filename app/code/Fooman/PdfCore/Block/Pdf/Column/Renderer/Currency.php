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

class Currency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency
{

    protected $priceCurrency;

    /**
     * Currency constructor.
     *
     * @param \Magento\Backend\Block\Context                    $context
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \Magento\Directory\Model\Currency\DefaultLocator  $currencyLocator
     * @param \Magento\Directory\Model\CurrencyFactory          $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface       $localeCurrency
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency\DefaultLocator $currencyLocator,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $storeManager, $currencyLocator, $currencyFactory, $localeCurrency, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $this->_getValue($row);
        if ($value) {
            return $this->priceCurrency->format($value, null, null, null, $this->_getCurrencyCode($row));
        }
        return $this->getColumn()->getDefault();
    }
}
