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

$products = $objectManager->get('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToFilter('type_id', array('eq' => 'configurable'))->addAttributeToFilter('attribute_set_id',4)->addAttributeToSelect('*');


$attr1 = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('meta_title'); //text

$attr2 = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('meta_keyword'); //text area

$attr3 = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('meta_description'); // stored in text by type text area

$brandAttr = $objectManager->get('\Magento\Catalog\Model\Product')->getResource()->getAttribute('brand');

$resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
$writeConnection = $resource->getConnection('core_write');	
$table = $resource->getTableName('catalog_product_entity_text');
$table1 = $resource->getTableName('catalog_product_entity_varchar');

 
$i = 0;

	foreach($products as $product)
	{
		try{
		//echo $product->getId().',';

		$brand = $brandAttr->getSource()->getOptionText($product->getBrand());
		$model = addslashes($product->getName());
		$brand = $brand ? addslashes($brand) : '';

		
		$title = "{$model}, {$brand}. | Kintec";
		$keyword = "{$model}, {$brand}";
		$description = "Buy {$model} online at Kintec Footwear + Orthotics. Huge selection of shoes and accessories. Free shipping on orders over $99.";



		/** Meta Title **/
		$val1 = $writeConnection->fetchAll("SELECT * FROM `{$table1}` WHERE `attribute_id`='{$attr1->getId()}' AND `entity_id`='{$product->getId()}';" );
		if(isset($val1[0])){
			$query="UPDATE `{$table1}` SET `value` = '{$title}' WHERE `attribute_id` = '{$attr1->getId()}' AND `entity_id`='{$product->getId()}';" ;
		
		}else
		{
			$query = "INSERT INTO `{$table1}` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('{$attr1->getId()}','0','{$product->getId()}','{$title}') ";	
		}
		$writeConnection->query($query);

		/** Meta keyword **/
		if(true || is_null($product->getMetaKeyword()) || $product->getMetaKeyword()==''){
			$val1 = $writeConnection->fetchAll("SELECT * FROM `{$table}` WHERE `attribute_id` = '{$attr2->getId()}' AND `entity_id`='{$product->getId()}';" );
			if(isset($val1[0])){
				$query="UPDATE `{$table}` SET `value` = '{$keyword}' WHERE `attribute_id` = '{$attr2->getId()}' AND `entity_id`='{$product->getId()}';" ;
			
			}else
			{
				$query = "INSERT INTO `{$table}` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('{$attr2->getId()}','0','{$product->getId()}','{$keyword}') ";	
			}
			$writeConnection->query($query);
		}

		/** Meta Description **/
		$val1 = $writeConnection->fetchAll("SELECT * FROM `{$table1}` WHERE `attribute_id` = '{$attr3->getId()}' AND `entity_id`='{$product->getId()}';" );
		if(isset($val1[0])){
			$query="UPDATE `{$table1}` SET `value` = '{$description}' WHERE `attribute_id` = '{$attr3->getId()}' AND `entity_id`='{$product->getId()}';" ;
		
		}else
		{
			$query = "INSERT INTO `{$table1}` (`attribute_id`,`store_id`,`entity_id`,`value`) VALUES ('{$attr3->getId()}','0','{$product->getId()}','{$description}') ";	
		}
		$writeConnection->query($query);





		
		/*if($productColor=='grey'){
			$query="UPDATE`{$table}` SET `value` = {$colors[$productColor]} WHERE `attribute_id` = '{$attr1->getId()}' AND `entity_id`='{$product->getId()}';" ;*/
		
		$writeConnection->query($query);
		echo '.';
		}
		catch(Exception $e)
		{
			echo $e->getMessage();die;
			echo '*';
			continue;
		}
	}
	//var_dump($product->getAttributeSetId());
	//var_dump($product->getTypeId());die;

die('<br>Done');

//$bootstrap->run($app);
