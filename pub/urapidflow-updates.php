<?php
/**
 * Created by pp
 * @project magento202
 * default file path /magento202/app/code/Unirgy/RapidFlow/misc/rf.php
 */
 
use Magento\Framework\ObjectManagerInterface;

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
 
$params = $_SERVER;
$params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'admin'; // change this to appropriate store if needed.
$params[\Magento\Store\Model\Store::CUSTOM_ENTRY_POINT_PARAM] = true;
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params); // bootstrap
 
/** @var \Magento\Framework\App\Http $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Http');
 
// configure environment
$om = $bootstrap->getObjectManager();
$areaList = $om->get('Magento\Framework\App\AreaList');
$areaCode = $areaList->getCodeByFrontName('admin');
/** @var \Magento\Framework\App\State $state */
$state = $om->get('Magento\Framework\App\State');
$state->setAreaCode($areaCode);
/** @var \Magento\Framework\ObjectManager\ConfigLoaderInterface $configLoader */
$configLoader = $om->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
$om->configure($configLoader->load($areaCode));
 
function rfImportUpdates(ObjectManagerInterface $om)
{
    runRfProfile($om, "3");
}
 
/**
 * Function to
 * @param ObjectManagerInterface $om
 * @param string|int $profile
 */
function runRfProfile(ObjectManagerInterface $om, $profile)
{
    /** @var \Unirgy\RapidFlow\Helper\Data $helper */
    $helper = $om->get('\Unirgy\RapidFlow\Helper\Data');
    $helper->run($profile);
}
 
rfImportUpdates($om);

?> 