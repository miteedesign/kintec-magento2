<?php

/**
 *
 * Manually Trigger indexer http://mgupdate.kintec.net/reindex.php
 * Purpose: trigger reindex process as needed
 * [0] => design_config_grid
 * [1] => customer_grid
 * [2] => catalog_product_flat
 * [3] => catalog_category_flat
 * [4] => catalog_category_product
 * [5] => catalog_product_category
 * [6] => catalog_product_price
 * [7] => catalog_product_attribute
 * [8] => catalogrule_rule
 * [9] => catalogrule_product
 * [10] => cataloginventory_stock
 *[11] => catalogsearch_fulltext
 */

try {
    require __DIR__ . '/../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Http');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
$writeConnection = $resource->getConnection('core_write');

$obj = $bootstrap->getObjectManager();

$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$indexer = $obj->get('Magento\Indexer\Model\Indexer\CollectionFactory')->create();
$ids = $indexer->getAllIds();

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalog_product_flat');
$indexer->reindexAll('catalog_product_flat'); // this reindexes all

echo 'Finished re-indexing catalog_product_flat<br>';

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalog_category_flat');
$indexer->reindexAll('catalog_category_flat'); // this reindexes all

echo 'Finished re-indexing catalog_category_flat<br>';

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalog_category_product');
$indexer->reindexAll('catalog_category_product'); // this reindexes all

echo 'Finished re-indexing catalog_category_product<br>';

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalog_product_category');
$indexer->reindexAll('catalog_product_category'); // this reindexes all

echo 'Finished re-indexing catalog_product_category<br>';

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalog_product_attribute');
$indexer->reindexAll('catalog_product_attribute'); // this reindexes all

echo 'Finished re-indexing catalog_product_attribute<br>';

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('cataloginventory_stock');
$indexer->reindexAll('cataloginventory_stock'); // this reindexes all

echo 'Finished re-indexing cataloginventory_stock<br>';

$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalogsearch_fulltext');
$indexer->reindexAll('catalogsearch_fulltext'); // this reindexes all

echo 'Finished re-indexing catalogsearch_fulltext<br>';

/*
$indexer = $obj->get('Magento\Framework\Indexer\IndexerRegistry')->get('catalog_product_price');
$indexer->reindexAll('catalog_product_price'); // this reindexes all

echo 'Finished re-indexing catalog_product_price<br>';*/

echo 'Successfully Reindexed All';

?>