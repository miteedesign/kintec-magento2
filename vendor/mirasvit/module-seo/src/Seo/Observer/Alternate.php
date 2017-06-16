<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   1.0.58
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Seo\Model\Config as Config;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewrite;
use Magento\Directory\Helper\Data as Data;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Alternate implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Mirasvit\Seo\Helper\CheckPage
     */
    protected $checkPage;

    /**
     * @param \Mirasvit\Seo\Helper\Data                                       $seoData
     * @param \Magento\Framework\View\Element\Template\Context                $context
     * @param \Magento\Framework\ObjectManagerInterface                       $objectManager
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Catalog\Model\Layer\Resolver                           $layerResolver
     * @param \Mirasvit\Seo\Model\Config                                      $config
     * @param \Magento\Cms\Model\Page                                         $page
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory         $pageCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection                       $resource
     * @param \Magento\Catalog\Helper\Category                                $catalogCategory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory           $urlRewriteFactory
     * @param \Magento\Catalog\Model\CategoryFactory                          $categoryFactory
     * @param UrlFinderInterface                                              $urlFinder
     * @param \Mirasvit\Seo\Helper\CheckPage                                  $checkPage
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\Seo\Helper\Data $seoData,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Cms\Model\Page $page,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        UrlFinderInterface $urlFinder,
        \Mirasvit\Seo\Helper\CheckPage $checkPage
    ) {
        $this->seoData = $seoData;
        $this->context = $context;
        $this->objectManager = $objectManager;
        $this->request = $context->getRequest();
        $this->registry = $registry;
        $this->layerResolver = $layerResolver;
        $this->config = $config;
        $this->page = $page;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->resource = $resource;
        $this->escaper = $context->getEscaper();
        $this->catalogCategory = $catalogCategory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->categoryFactory = $categoryFactory;
        $this->urlFinder = $urlFinder;
        $this->checkPage = $checkPage;
    }

    /**
     * @var array
     */
    protected $stores = [];

    /**
     * @var array
     */
    protected $storesBaseUrlsCountValues = [];

    /**
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) 
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setupAlternateTag()
    {
        if (!$this->config->isAlternateHreflangEnabled($this->context
                ->getStoreManager()
                ->getStore()
                ->getStoreId()) || !$this->request) {
            return false;
        }

        $storeUrls = $this->getStoresCurrentUrl();
        if (!$storeUrls || $this->checkPage->isFilterPage()) {
            return false;
        }

        if ($this->request->getControllerName() == 'product'
            && ($product = $this->registry->registry('current_product'))) {
            $storeUrls = $this->getAlternateProductUrl($storeUrls, $product->getId());
        }

        if ($this->request->getControllerName() == 'category'
            && $this->registry->registry('current_category')) {
            $storeUrls = $this->getAlternateCategoryUrl($storeUrls);
        }

        if ($this->request->getModuleName() == 'cms'
            && ($cmsPageId = $this->page->getPageId())
            && $this->request->getActionName() != 'noRoute') {
            $storeUrls = $this->getAlternateCmsUrl($storeUrls, $cmsPageId);
        }

        $this->addLinkAlternate($storeUrls);
    }

    /**
     * Get alternate urls for cms.
     *
     * @param array $storeUrls
     * @param int   $cmsPageId
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) 
     *
     */
    public function getAlternateCmsUrl($storeUrls, $cmsPageId)
    {
        $alternateGroupInstalled = false;
        $cmsStoresIds = $this->page->getStoreId();

        //check if alternate_group exist in cms_page table
        if (($pageObject = $this->pageCollectionFactory->create()->getItemById($cmsPageId))
            && is_object($pageObject) && ($pageObjectData = $pageObject->getData())
            && array_key_exists('alternate_group', $pageObjectData)) {
            $alternateGroupInstalled = true;
        }

        if (!$alternateGroupInstalled) {
            return $storeUrls;
        }

        $cmsCollection = $this->pageCollectionFactory->create()
            ->addFieldToSelect('alternate_group')
            ->addFieldToFilter('page_id', ['eq' => $cmsPageId])
            ->getFirstItem();

        if (($alternateGroup = $cmsCollection->getAlternateGroup()) && $cmsStoresIds[0] != 0) {
            $cmsCollection = $this->pageCollectionFactory->create()
                ->addFieldToSelect(['alternate_group', 'identifier'])
                ->addFieldToFilter('alternate_group', ['eq' => $alternateGroup])
                ->addFieldToFilter('is_active', true);
            $table = $this->resource->getTableName('cms_page_store');
            $cmsCollection->getSelect()
                ->join(
                    [
                        'storeTable' => $table],
                    'main_table.page_id = storeTable.page_id',
                    ['store_id' => 'storeTable.store_id']
                );
            $cmsPages = $cmsCollection->getData();
            if (count($cmsPages) > 0) {
                foreach ($cmsPages as $page) {
                    $pageIdentifier = $page['identifier'];
                    $fullAction = $this->request->getFullActionName();
                    $baseStoreUrl = $this->stores[$page['store_id']]->getBaseUrl();
                    $storeUrls[$page['store_id']] = ($fullAction == 'cms_index_index') ? $baseStoreUrl
                        : $baseStoreUrl.$pageIdentifier;
                }
            }
        }

        return $storeUrls;
    }

    /**
     * Get alternate urls for product.
     *
     * @param array $storeUrls
     * @param int   $productId
     *
     * @return array
     */
    public function getAlternateProductUrl($storeUrls, $productId)
    {
        foreach ($this->stores as $storeId => $store) {
            $idPath = $this->request->getPathInfo();

            if ($idPath && strpos($idPath, $productId)) {
                $rewriteObject = $this->urlFinder->findOneByData([
                                        UrlRewrite::TARGET_PATH => trim($idPath, '/'),
                                        UrlRewrite::STORE_ID => $storeId,
                                    ]);
                if ($rewriteObject && ($requestPath = $rewriteObject->getRequestPath())) {
                    $storeUrls[$storeId] = $store->getBaseUrl().$requestPath.$this->getUrlAddition($store);
                }
            }
        }

        return $storeUrls;
    }

    /**
     * Get alternate urls for category.
     *
     * @param array $storeUrls
     *
     * @return array
     */
    public function getAlternateCategoryUrl($storeUrls)
    {
        foreach ($this->stores as $storeId => $store) {
            $currentUrl = $this->context->getUrlBuilder()->getCurrentUrl();
            $category = $this->categoryCollectionFactory->create()
                ->setStoreId($store->getId())
                ->addFieldToFilter('is_active', ['eq' => '1'])
                ->addFieldToFilter('entity_id', ['eq' => $this->registry->registry('current_category')->getId()])
                ->getFirstItem();

            if ($category->hasData() && ($currentCategory = $this->categoryFactory
                    ->create()
                    ->setStoreId($store->getId())
                    ->load($category->getEntityId()))
                ) {
                $storeBaseUrl = $store->getBaseUrl();
                $currentCategoryUrl = $currentCategory->getUrl();
                // correct suffix for every store can't be added, because magento works incorrect,
                // maybe after magento fix (if need)
                if (strpos($currentCategoryUrl, $storeBaseUrl) === false) {
                    //create correct category way for every store, need if category use different path
                    $slashStoreBaseUrlCount = substr_count($storeBaseUrl, '/');
                    $currentCategoryUrlExploded = explode('/', $currentCategoryUrl);
                    $currentCategoryUrl = $storeBaseUrl.implode(
                        '/',
                        array_slice($currentCategoryUrlExploded, $slashStoreBaseUrlCount)
                    );
                }

                $urlAddition = $this->getUrlAddition($store);

                $preparedUrlAdditionCurrent = $this->getUrlAdditionalParsed(strstr($currentUrl, '?'));
                $preparedUrlAdditionStore = $this->getUrlAdditionalParsed($urlAddition);
                $urlAdditionCategory = $this->getPreparedUrlAdditional(
                    $preparedUrlAdditionCurrent,
                    $preparedUrlAdditionStore
                );
                // if store use different attributes name will be added after use seo filter (if need)
                if ($this->config->isHreflangCutCategoryAdditionalData()) {
                    $storeUrls[$storeId] = $currentCategoryUrl;
                } else {
                    $storeUrls[$storeId] = $currentCategoryUrl . $urlAdditionCategory;
                }
            }
        }

        return $storeUrls;
    }

    /**
     * @param string $store
     * @return string
     */
    protected function getUrlAddition($store)
    {
        $urlAddition = (isset($this->storesBaseUrlsCountValues[$store->getBaseUrl()])
                        && $this->storesBaseUrlsCountValues[$store->getBaseUrl()] > 1) ?
                        strstr(htmlspecialchars_decode($store->getCurrentUrl(false)), '?') : '';

        return $urlAddition;
    }

    /**
     * Get stores urls.
     *
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getStoresCurrentUrl()
    {
        $currentStoreGroup = $this->context->getStoreManager()->getStore()->getGroupId();
        $storesNumberInGroup = 0;
        $storeUrls = [];
        $storesBaseUrls = [];

        foreach ($this->context->getStoreManager()->getStores() as $store) {
            if ($store->getIsActive()
                && $store->getGroupId() == $currentStoreGroup
                && $this->config->isAlternateHreflangEnabled($store)) {
                    //we works only with stores which have the same store group
                    $this->stores[$store->getId()] = $store;
                    $currentUrl = $store->getCurrentUrl(false);
                    $storesBaseUrls[$store->getId()] = $store->getBaseUrl();
                    $storeUrls[$store->getId()] = new \Magento\Framework\DataObject(
                        [
                            'store_base_url' => $store->getBaseUrl(),
                            'current_url' => $currentUrl,
                            'store_code' => $store->getCode()
                        ]
                    );

                    ++$storesNumberInGroup;
            }
        }

        $isSimilarLinks = (count($storesBaseUrls) - count(array_unique($storesBaseUrls)) > 0) ? true : false;

        if (count($storeUrls) > 1) {
            foreach ($storeUrls as $storeId => $storeData) {
                $storeUrls[$storeId] = $this->_storeUrlPrepare(
                    $storesBaseUrls,
                    $storeData->getStoreBaseUrl(),
                    $storeData->getCurrentUrl(),
                    $storeData->getStoreCode(),
                    $isSimilarLinks
                );
            }
        }

        $this->storesBaseUrlsCountValues = array_count_values($storesBaseUrls);
        //array with quantity of identical Base Urls

        if ($storesNumberInGroup > 1 && count($storeUrls) > 1) { //if a current store is multilanguage
            return $storeUrls;
        }

        return false;
    }

    /**
     * Prepare store current url.
     *
     * @param string $storesBaseUrls
     * @param string $storeBaseUrl
     * @param string $currentUrl
     * @param string $storeCode
     * @param bool $isSimilarLinks
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _storeUrlPrepare($storesBaseUrls, $storeBaseUrl, $currentUrl, $storeCode, $isSimilarLinks)
    {
        if (strpos($currentUrl, $storeBaseUrl) == false) {
            $currentUrl = str_replace($storesBaseUrls, $storeBaseUrl, $currentUrl); // fix bug with incorrect base urls
        }

        $currentUrl = str_replace('&amp;', '&', $currentUrl);
        $currentUrl = preg_replace('/SID=(.*?)(&|$)/', '', $currentUrl);

        //cut get params for AMASTY_XLANDING if "Cut category additional data for alternate url" enabled
        if ($this->config->isHreflangCutCategoryAdditionalData()
            && $this->seoData->getFullActionCode() == Config::AMASTY_XLANDING) {
            $currentUrl = strtok($currentUrl, '?');
        }

        $deleteStoreQuery = (substr_count($storeBaseUrl, '/') > 3) ? true : false;

        if (strpos($currentUrl, '___store=' . $storeCode) === false
            || (!$deleteStoreQuery && $isSimilarLinks)) {
                return $currentUrl;
        }

        if (strpos($currentUrl, '?___store=' . $storeCode) !== false
            && strpos($currentUrl, '&') === false) {
                $currentUrl = str_replace('?___store=' . $storeCode, '', $currentUrl);
        } elseif (strpos($currentUrl, '?___store=' . $storeCode) !== false
            && strpos($currentUrl, '&') !== false) {
                $currentUrl = str_replace('?___store=' . $storeCode . '&', '?', $currentUrl);
        } elseif (strpos($currentUrl, '&___store=' . $storeCode) !== false) {
                $currentUrl = str_replace('&___store=' . $storeCode, '', $currentUrl);
        }

        return $currentUrl;
    }

    /**
     * Create alternate.
     *
     * @param array $storeUrls
     * @return void
     */
    public function addLinkAlternate($storeUrls)
    {
        $pageConfig = $this->context->getPageConfig();
        $type = 'alternate';
        $addLocaleCodeAutomatical = $this->config->isHreflangLocaleCodeAddAutomatical();
        foreach ($storeUrls as $storeId => $url) {
            $storeCode = $this->stores[$storeId]->getConfig(Data::XML_PATH_DEFAULT_LOCALE);
            $hreflang = ($hreflang = $this->config->getHreflangLocaleCode($storeId)) ?
                            substr($storeCode, 0, 2).'-'.strtoupper($hreflang) :
                            (($addLocaleCodeAutomatical) ? str_replace('_', '-', $storeCode) :
                                substr($storeCode, 0, 2));

            $pageConfig->addRemotePageAsset(
                html_entity_decode($url),
                $type,
                ['attributes' => ['rel' => $type, 'hreflang' => $hreflang]]
            );
        }

        if ($this->config->getXDefault() == Config::X_DEFAULT_AUTOMATICALLY) {
            reset($storeUrls);
            $storeIdXDefault = key($storeUrls);
            $pageConfig->addRemotePageAsset(
                html_entity_decode($storeUrls[$storeIdXDefault]),
                $type,
                ['attributes' => ['rel' => $type, 'hreflang' => 'x-default']]
            );
        } elseif ($this->config->getXDefault()) {
            $pageConfig->addRemotePageAsset(
                html_entity_decode($storeUrls[$this->config->getXDefault()]),
                $type,
                ['attributes' => ['rel' => $type, 'hreflang' => 'x-default']]
            );
        }
    }

    /**
     * Parse additional  url.
     *
     * @param string $urlAddition
     *
     * @return array
     */
    protected function getUrlAdditionalParsed($urlAddition)
    {
        if (!$urlAddition) {
            return [];
        }
        $preparedUrlAddition = [];
        $urlAdditionParsed = (substr($urlAddition, 0, 1) == '?') ? substr($urlAddition, 1) : $urlAddition;
        $urlAdditionParsed = explode('&', $urlAdditionParsed);
        foreach ($urlAdditionParsed as $urlAdditionValue) {
            if (strpos($urlAdditionValue, '=') !== false) {
                $urlAdditionValueArray = explode('=', $urlAdditionValue);
                $preparedUrlAddition[$urlAdditionValueArray[0]] = $urlAdditionValueArray[1];
            } else {
                $preparedUrlAddition[$urlAdditionValue] = '';
            }
        }

        return $preparedUrlAddition;
    }

    /**
     * Prepare additional  url.
     *
     * @param array $preparedUrlAdditionCurrent
     * @param array $preparedUrlAdditionStore
     *
     * @return string
     */
    protected function getPreparedUrlAdditional($preparedUrlAdditionCurrent, $preparedUrlAdditionStore)
    {
        $correctUrlAddition = [];
        $mergedUrlAddition = array_merge_recursive($preparedUrlAdditionCurrent, $preparedUrlAdditionStore);
        foreach ($mergedUrlAddition as $keyUrlAddition => $valueUrlAddition) {
            if (is_array($valueUrlAddition) && $keyUrlAddition == '___store') {
                $correctUrlAddition[$keyUrlAddition] = $valueUrlAddition[1];
            } elseif (is_array($valueUrlAddition)) {
                $correctUrlAddition[$keyUrlAddition] = $valueUrlAddition[0];
            } elseif (array_key_exists($keyUrlAddition, $preparedUrlAdditionCurrent) || $keyUrlAddition == '___store') {
                $correctUrlAddition[$keyUrlAddition] = $valueUrlAddition;
            }
        }
        $urlAddition = (count($correctUrlAddition) > 0) ? $this->getUrlAdditionalString($correctUrlAddition) : '';

        return $urlAddition;
    }

    /**
     * Convert additional url array to string.
     *
     * @param array $correctUrlAddition
     *
     * @return string
     */
    protected function getUrlAdditionalString($correctUrlAddition)
    {
        $urlAddition = '?';
        $urlAdditionArray = [];
        foreach ($correctUrlAddition as $keyUrlAddition => $valueUrlAddition) {
            $urlAdditionArray[] .= $keyUrlAddition.'='.$valueUrlAddition;
        }
        $urlAddition .= implode('&', $urlAdditionArray);

        return $urlAddition;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->setupAlternateTag();
    }
}
