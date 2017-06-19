<?php
/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCore
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fooman\PdfCore\Model\Config\Source;

use \Magento\Catalog\Ui\Component\Listing\Attribute\AbstractRepository;
use \Magento\Framework\Data\OptionSourceInterface;

class ProductAttributes extends AbstractRepository implements OptionSourceInterface
{

    protected $excludedAttributes = ['sku', 'category_id'];

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * ProductAttributes constructor.
     *
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder             $searchCriteriaBuilder
     * @param \Magento\Framework\Escaper                               $escaper
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        parent::__construct($productAttributeRepository, $searchCriteriaBuilder);
        $this->searchCriteria = $searchCriteria;
        $this->escaper = $escaper;
    }

    /**
     * supply dropdown choices for custom product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getAttributeList() as $attribute) {
            if (!in_array($attribute->getAttributeCode(), $this->excludedAttributes)
                && in_array(
                    $attribute->getFrontendInput(),
                    [
                        'select',
                        'text',
                        'textarea',
                        'date',
                        'price'
                    ]
                )
            ) {
                $options[] = [
                    'value' => 'product/' . $attribute->getAttributeCode(),
                    'label' => $this->getLabel($attribute)
                ];
            }
        }
        return $options;
    }

    public function getAttributeList()
    {
        return $this->productAttributeRepository->getList($this->searchCriteria)->getItems();
    }

    protected function getLabel($attribute)
    {
        $label = ($attribute->getFrontendLabel() ? $attribute->getFrontendLabel() : $attribute->getAttributeCode());
        return $this->escaper->escapeJsQuote($label);
    }

    protected function buildSearchCriteria()
    {
        return $this->searchCriteriaBuilder->create();
    }
}
