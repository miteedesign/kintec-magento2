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

class Image extends \Fooman\PdfCore\Block\Pdf\Column implements \Fooman\PdfCore\Block\Pdf\ColumnInterface
{
    const DEFAULT_WIDTH = 18;
    const COLUMN_TYPE = 'fooman_image';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $file;

    /**
     * @var \Fooman\PdfCore\Helper\ParamKey
     */
    protected $paramKeyHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context         $context
     * @param \Magento\Catalog\Model\ResourceModel\Product    $productResource
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Filesystem\Io\File           $file
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Filesystem\Io\File $file,
        \Fooman\PdfCore\Helper\ParamKey $paramKeyHelper,
        array $data = []
    ) {
        $this->productResource = $productResource;
        $this->productRepository = $productRepository;
        $this->paramKeyHelper = $paramKeyHelper;
        $this->file = $file;
        parent::__construct($context, $data);
    }

    public function getGetter()
    {
        return [$this, 'getImage'];
    }

    /**
     * @param $row
     *
     * @return string
     */
    public function getImage($row)
    {
        $imagePath = $this->getImagePath($row);
        if ($imagePath) {
            $params = [
                $imagePath,
                null,
                null,
                null,
                '15mm',
                null,
                null,
                null,
                true
            ];
            return sprintf(
                '<tcpdf method="Image" %s /><p style="font-size:14px;">&nbsp;</p>',
                $this->paramKeyHelper->getEncodedParams($params)
            );
        } else {
            return '';
        }
    }

    /**
     * @param $row
     *
     * @return bool|string
     */
    public function getImagePath($row)
    {
        $product = $this->productRepository->getById($row->getProductId(), false, $row->getStoreId());
        $attribute = $this->productResource->getAttribute('image');
        $imagePath = $attribute->getFrontend()->getValue($product);
        if ($imagePath) {
            $fullPath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath('/catalog/product' . $imagePath);
            if ($this->file->fileExists($fullPath)) {
                return $fullPath;
            }
        }
        return false;
    }
}
