<?php
/**
 * Application entry point
 *
 * Example - run a particular store or website:
 * --------------------------------------------
 * require __DIR__ . '/app/bootstrap.php';
 * $params = $_SERVER;
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'website2';
 * $params[\Magento\Store\Model\StoreManager::PARAM_RUN_TYPE] = 'website';
 * $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
 * \/** @var \Magento\Framework\App\Http $app *\/
 * $app = $bootstrap->createApplication('Magento\Framework\App\Http');
 * $bootstrap->run($app);
 * --------------------------------------------
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
$attr = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('colour');
$writeConnection = $resource->getConnection('core_write');
//$query = "UPDATE `eav_attribute_label` SET  `value`='Colour' WHERE `attribute_id`={$attr->getId()} AND `store_id`=1";
//$query = "INSERT INTO `eav_attribute_label`(`attribute_id`, `store_id`, `value`) VALUES (211,1,'Colour')";
//$writeConnection->query($query);
$data = $writeConnection->fetchAll("SELECT * FROM `eav_attribute_label` WHERE `attribute_id`={$attr->getId()}");
print_r($data);
echo 'done';
//update catalog_eav_attribute set used_in_product_listing=1 where attribute_id=211;

//INSERT INTO `eav_attribute_label`(`attribute_id`, `store_id`, `value`) VALUES (211,1,'Colour')
//$bootstrap->run($app);
