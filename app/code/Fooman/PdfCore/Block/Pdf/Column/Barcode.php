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

class Barcode extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 28;
    const DEFAULT_TITLE = '';

    const COLUMN_TYPE = 'fooman_barcode';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->paramKeyHelper = $paramKeyHelper;
        parent::__construct($context, $data);
    }

    public function getGetter()
    {
        return [$this, 'getBarcode'];
    }

    public function getBarcode($row)
    {
        $sku = $this->getSku($row);
        $barcodeParams = [
            $this->escapeHtml($sku),
            $this->getBarcodeType(),
            //the parameters below refer to x, y, width, and height of the barcode respectively
            '', '', '35', '13'
        ];
        return sprintf('<tcpdf method="write1DBarcode" %s />', $this->paramKeyHelper->getEncodedParams($barcodeParams));
    }

    public function getSku($row)
    {
        $product = $this->productRepository->getById($row->getProductId(), false, $row->getStoreId());
        return $product->getData('sku');
    }

    public function getBarcodeType()
    {
        return $this->_scopeConfig->getValue(
            \Fooman\PdfCore\Block\Pdf\PdfAbstract::XML_PATH_BARCODE_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
