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
$writeConnection = $resource->getConnection('core_write');

$products = $objectManager->get('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToFilter('type_id', array('eq' => 'configurable'))->addAttributeToFilter('attribute_set_id',4)->addAttributeToSelect('colour');


$attr1 = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('sizechart');

$resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
$writeConnection = $resource->getConnection('core_write');	
$table = $resource->getTableName('catalog_product_entity_text');
 //$query="DELETE FROM `{$table}` WHERE `attribute_id` = '{$attr1->getId()}';" ;
//var_dump($writeConnection->fetchAll("select * from `{$table}` LIMIT 1"));

/*echo $query = "SELECT * FROM `{$table}` `t1` ,`{$table}` `t2` WHERE `t1`.`value_id` > `t2`.`value_id` AND `t1`.`attribute_id` = '{$attr1->getId()}' AND `t1`.`entity_id`=`t2.`entity_id` AND `t1`.`attribute_id`=`t2`.`attribute_id`;" ;
die;
//$query = "SELECT * FROM `{$table}` AS `t1` group";
 var_dump($writeConnection->fetchAll($query));
 die;
$query="DELETE FROM `{$table}` t1 ,`{$table}` t2 WHERE `t1`.`value_id` > `t2`.`value_id` AND `t1`.`attribute_id` = '{$attr1->getId()}' AND `t1`.`entity_id`=`t2.`entity_id` AND `t1`.`attribute_id`=`t2`.`attribute_id`;" ;
 $writeConnection->query($query);
*/
 
$i = 0;
try{
	foreach($products as $product)
	{
		echo $product->getId().',';
		
		
		$swatch = $writeConnection->fetchAll("SELECT * FROM `{$table}` WHERE `attribute_id` = '{$attr1->getId()}' AND `entity_id`='{$product->getId()}';" );
		if(isset($swatch[0])){
			
		
		}else
		{
			$query = "INSERT INTO `{$table}` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('{$attr1->getId()}','0','{$product->getId()}','<img src=\"/media/wysiwyg/cms-images/Shoe-Size-Conversion-Chart.jpg\" />') ";	
		}

		
		/*if($productColor=='grey'){
			$query="UPDATE`{$table}` SET `value` = {$colors[$productColor]} WHERE `attribute_id` = '{$attr1->getId()}' AND `entity_id`='{$product->getId()}';" ;*/
		
		$writeConnection->query($query);
		echo '.';
	}
	
	//var_dump($product->getAttributeSetId());
	//var_dump($product->getTypeId());die;
}
catch(Exception $e)
{
	echo $e->getMessage();
}
die('Done');

//$bootstrap->run($app);
