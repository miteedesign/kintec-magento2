<?php

namespace Unirgy\RapidFlow\Model;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Rule as CatalogModelRule;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CatalogRule
 *
 * @method \Unirgy\RapidFlow\Model\ResourceModel\CatalogRule getResource()
 * @method string getFromDate()
 * @package Unirgy\RapidFlow\Model
 */
class CatalogRule extends CatalogModelRule
{
    protected $_multiProductIds = [];

    public function getMatchingMultiProductIds($pIds)
    {
        $pIdsHash = $pIds;
        if (is_array($pIds)) {
            sort($pIds);
            $pIdsHash = implode(',', $pIds);
        }
        $pIdsHash = md5($pIdsHash);
        if (!isset($this->_multiProductIds[$pIdsHash])) {
            $this->_multiProductIds[$pIdsHash] = [];
            $this->setCollectedAttributes([]);
            $websiteIds = $this->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', $websiteIds);
            }

            if ($websiteIds) {
                $productCollection = $this->_productCollectionFactory->create();

                $productCollection->addWebsiteFilter($websiteIds)->addIdFilter($pIds);
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->_modelResourceModelIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateMultiProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product' => $this->_productFactory->create(),
                        'pids_hash' => $pIdsHash,
                    ]
                );
            }
        }

        return $this->_multiProductIds[$pIdsHash];
    }

    public function callbackValidateMultiProduct($args)
    {
        /** @var Product $product */
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_multiProductIds[$args['pids_hash']][] = $product->getId();
        }
    }

    public function applyAllNoIndex()
    {
        $this->_getResource()->applyAllRulesForDateRange();
        $this->_invalidateCache();
    }

    public function applyAllByPids($pIds)
    {
        $this->_getResource()->applyAllRulesForDateRange(null, null, $pIds);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Unirgy\RapidFlow\Model\ResourceModel\CatalogRule');
        $this->setIdFieldName('rule_id');
    }
}
