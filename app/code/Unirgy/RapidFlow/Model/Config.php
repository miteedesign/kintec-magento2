<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model;

use Magento\Framework\App\Cache\Proxy;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\Base;
use Magento\Framework\App\Config\Element;
use Unirgy\RapidFlow\Helper\Data;
use Unirgy\RapidFlow\Model\Config\Reader as RfReader;

/**
 * Class Config
 * @package Unirgy\RapidFlow\Model
 */
class Config extends Base
{
    /**
     * Cache tag and cache id
     */
    const CACHE_TAG = 'config_urapidflow';

    const CACHE_ID = 'config_urapidflow';

    /**
     * @var array
     */
    protected $_rowTypeColumns = [];

    /**
     * @var Proxy
     */
    protected $_cacheProxy;

    /**
     * @var
     */
    protected $_useCache;

    /**
     * @var StateInterface
     */
    protected $_cacheState;

    /**
     * @var RfReader
     */
    protected $_urfConfigReader;

    /**
     * Config constructor.
     * @param Proxy $cache
     * @param StateInterface $cacheState
     * @param RfReader $reader
     * @param null $sourceData
     */
    public function __construct(
        Proxy $cache,
        StateInterface $cacheState,
        RfReader $reader,
        $sourceData = null
    ) {
        $this->_cacheProxy = $cache;
        $this->_cacheState = $cacheState;
        $this->_urfConfigReader = $reader;

        $this->setCacheId(self::CACHE_ID);
        $this->setCacheTags([self::CACHE_TAG]);
        $this->setCacheChecksum(null);

        parent::__construct($sourceData);
        $this->_construct();
    }

    /**
     * @return Proxy
     */
    public function getCache()
    {
        return $this->_cacheProxy;
    }

    /**
     * @return $this
     */
    protected function _construct()
    {
        if ($this->_useCache()) {
            if ($this->loadCache()) {
                return $this;
            }
        }

        $config = $this->_urfConfigReader->read();

//        $mergeConfig = $this->_baseConfig;
//
//        $config = $this->_scopeConfig;
//        $modules = $this->_helper->getModuleList();
//
//        // check if local modules are disabled
//        $disableLocalModules = (string)$config->getValue('global/disable_local_modules');
//        $disableLocalModules = !empty($disableLocalModules) && (('true' === $disableLocalModules) || ('1' === $disableLocalModules));
//
//        $configFile = $this->_moduleDirReader->getModuleDir('etc', 'Unirgy_RapidFlow') . DIRECTORY_SEPARATOR . 'urapidflow.xml';
//
//        if ($mergeConfig->loadFile($configFile)) {
//            $config->extend($mergeConfig, true);
//        }
//
//        foreach ($modules as $modName => $module) {
//            if ($module->is('active')) {
//                if (($disableLocalModules && ('local' === (string)$module->codePool)) || $modName == 'Unirgy\RapidFlow') {
//                    continue;
//                }
//
//                $configFile = $config->getModuleDir('etc', $modName) . DIRECTORY_SEPARATOR . 'urapidflow.xml';
//
//                if ($mergeConfig->loadFile($configFile)) {
//                    $config->extend($mergeConfig, true);
//                }
//            }
//        }
//
        $this->setXml($config->descend('urapidflow'));

        if ($this->_useCache()) {
            $this->saveCache();
        }
        return $this;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getDataTypes()
    {
        return $this->getNode('data_types')->children();
    }

    /**
     * @param null $dataType
     * @return array|\SimpleXMLElement
     */
    public function getRowTypes($dataType = null)
    {
        $nodes = $this->getNode('row_types')->children();
        if (!$dataType) {
            return $nodes;
        }
        $rowTypes = [];
        /** @var Element $node */
        foreach ($nodes as $k => $node) {
            $restrictMagentoVersion = $node->descend('restrictions/magento_version');
            if ($restrictMagentoVersion && version_compare(Data::getVersion(), (string)$restrictMagentoVersion, '<')) {
                continue;
            }
            if ($dataType != (string)$node->data_type) {
                continue;
            }
            $rowTypes[$k] = $node;
        }
        return $rowTypes;
    }

    /**
     * @param $profileType
     * @param $dataType
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function getProfileTabs($profileType, $dataType)
    {
        $tabs = $this->getNode("data_types/$dataType/profile/$profileType/tabs");
        if (!$tabs) {
            throw new \Exception(__("Invalid data type '%1' or profile type '%2'", $dataType, $profileType));
        }
        return $tabs->children();
    }

    /**
     * Maintain file columns cache
     *
     * @param string $rowType
     * @return array
     */
    public function getRowTypeColumns($rowType)
    {
        if (empty($this->_rowTypeColumns[$rowType])) {
            $node = $this->getNode("row_types/$rowType/columns");
            if (!$node) {
                var_dump($rowType);
                exit;
            }
            $columnsConfig = $node->asArray();
            if (!$columnsConfig) {
                return false;
            }
            uasort($columnsConfig, [$this, '_sortColumnsCb']);
            $this->_rowTypeColumns[$rowType] = $columnsConfig;
        }
        return $this->_rowTypeColumns[$rowType];
    }

    /**
     * Sort columns by 'col' member
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortColumnsCb($a, $b)
    {
        return empty($a['col']) || empty($b['col']) || $a['col'] == $b['col'] ? 0 : ($a['col'] < $b['col'] ? -1 : 1);
    }

    /**
     * @return bool
     */
    protected function _useCache()
    {
        if (null === $this->_useCache) {
            $this->_useCache = $this->_cacheState->isEnabled(self::CACHE_TAG);
        }

        return $this->_useCache;
    }
}
