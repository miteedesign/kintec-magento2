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
$result = $writeConnection->fetchAll('SELECT `eao`.`option_id`,`eaov`.`value` FROM `eav_attribute_option` AS `eao` JOIN `eav_attribute_option_value` `eaov` ON `eaov`.`option_id`=`eao`.`option_id` WHERE `eao`.`attribute_id`=211 ');
$colors = [
 'red'=>'#FF0000',
 'pink'=>'#FF69B4',
 'orange'=>'#FF4500',
 'black'=>'#000000',
 'purple'=>'#FF00FF',
 'gray'=>'#808080',
 'grey'=>'#808080',
 'green'=>'#32CD32',
 'blue'=>'#0000FF',
 'white'=>'#FFFFFF',
 'silver'=>'#C0C0C0',
 'yellow'=>'#FFFF00',
 'brown'=>'#8B4513',
 'maroon'=>'#800000',
 'violet'=>'#8a2be2',
 'chocolate'=>'#d2691e',
 'orchid'=>'#9932cc',
 'gold'=>'#ffd700',
];


foreach($result as $row){
	$found = false;
	foreach($colors as $key=>$color){
		$color = strtolower($color);
		if(strpos(strtolower($row['value']),$key)!==false){
			$swatch = $writeConnection->fetchAll("SELECT * FROM `eav_attribute_option_swatch` WHERE `option_id`={$row['option_id']} ");
			if(isset($swatch[0])){
				$writeConnection->query("UPDATE `eav_attribute_option_swatch` SET value='{$color}',type=1 WHERE `option_id`={$row['option_id']} ");
			}
			else
			{
				$writeConnection->query("INSERT INTO `eav_attribute_option_swatch`(option_id,store_id,type,value) VALUES('{$row['option_id']}',0,1,'$color') ");
			}
			$found = true;
			echo 'Found : '.$row['value'].'='.$color.'<br>';
		}

	}

	if(!$found)
	{
		$swatch = $writeConnection->fetchOne("SELECT * FROM `eav_attribute_option_swatch` WHERE `option_id`={$row['option_id']} ");
		if(isset($swatch[0])){
			$writeConnection->query("UPDATE `eav_attribute_option_swatch` SET value='#ffffff',type=1 WHERE `option_id`={$row['option_id']} ");
		}else
			{
				$writeConnection->query("INSERT INTO `eav_attribute_option_swatch`(option_id,store_id,type,value) VALUES('{$row['option_id']}',0,1,'#ffffff') ");
			}
		echo 'Not Found : '.$row['value'].'<br>';
	}
}
echo 'done';
//update catalog_eav_attribute set used_in_product_listing=1 where attribute_id=211;

//INSERT INTO `eav_attribute_label`(`attribute_id`, `store_id`, `value`) VALUES (211,1,'Colour')
//$bootstrap->run($app);
