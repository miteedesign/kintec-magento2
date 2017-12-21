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
error_reporting(E_ALL);
ini_set('display_errors', 1);
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Http');
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');

$registry = $objectManager->get('\Magento\Framework\Registry');
$registry->register('isSecureArea', true);
$writeConnection = $resource->getConnection('core_write');
$query = "SELECT email, COUNT(*) c FROM customer_entity GROUP BY email HAVING c > 1;";
$table = $resource->getTableName('customer_entity');
$duplicate = $writeConnection->fetchAll($query);
foreach($duplicate as $customer){
	
	$query="SELECT * FROM {$table} WHERE website_id=0 AND store_id=2 AND email='{$customer['email']}'; " ;
	$data = $writeConnection->fetchAll($query);
	/*echo "DELETE FROM {$table} WHERE entity_id={$data[0]['entity_id']}";
	die
	//$writeConnection->query("DELETE FROM {$table} WHERE entity_id={$data[0]['entity_id']}");*/
	$cust = $objectManager->create('\Magento\Customer\Model\Customer')->load($data[0]['entity_id']);
	
	$cust->delete();
	echo 'd';

}
$writeConnection->query('update customer_entity set website_id=1,store_id=1 where website_id=0 and store_id=2;');

die('done');	




die;

//$bootstrap->run($app);
