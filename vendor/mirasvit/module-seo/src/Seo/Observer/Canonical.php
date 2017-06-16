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

/**
 * @SuppressWarnings(PHPMD)
 */
class Canonical implements ObserverInterface
{
    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Bundle\Model\Product\TypeFactory
     */
    protected $productTypeFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $seoData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $productTypeConfigurable;

    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $productTypeBundle;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $productTypeGrouped;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
     */
    protected $urlRewrite;

    /**
     * @var \Mirasvit\Seo\Helper\UrlPrepare
     */
    protected $urlPrepare;

    /**
     * @param \Mirasvit\Seo\Model\Config                                                 $config
     * @param \Magento\Bundle\Model\Product\TypeFactory                                  $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory            $categoryCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory             $productCollectionFactory
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable
     * @param \Magento\Bundle\Model\Product\Type                                         $productTypeBundle
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped                         $productTypeGrouped
     * @param \Magento\Framework\View\Element\Template\Context                           $context
     * @param \Magento\Framework\Registry                                                $registry
     * @param \Mirasvit\Seo\Helper\Data                                                  $seoData
     * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection               $urlRewrite
     * @param \Mirasvit\Seo\Helper\UrlPrepare                                            $urlPrepare
     */
    public function __construct(
        \Mirasvit\Seo\Model\Config                                                          $config,
        \Magento\Bundle\Model\Product\TypeFactory                                           $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory                     $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory                      $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable          $productTypeConfigurable,
        \Magento\Bundle\Model\Product\Type                                                  $productTypeBundle,
        \Magento\GroupedProduct\Model\Product\Type\Grouped                                  $productTypeGrouped,
        \Magento\Framework\View\Element\Template\Context                                    $context,
        \Magento\Framework\Registry                                                         $registry,
        \Mirasvit\Seo\Helper\Data                                                           $seoData,
        \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection                        $urlRewrite,
        \Mirasvit\Seo\Helper\UrlPrepare                                                     $urlPrepare
    ) {
        $this->config = $config;
        $this->productTypeFactory = $productTypeFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productTypeConfigurable = $productTypeConfigurable;
        $this->productTypeBundle = $productTypeBundle;
        $this->productTypeGrouped = $productTypeGrouped;
        $this->context = $context;
        $this->registry = $registry;
        $this->seoData = $seoData;
        $this->storeManager = $context->getStoreManager();
        $this->request = $context->getRequest();
        $this->fullAction = $this->request->getFullActionName();
        $this->urlRewrite = $urlRewrite;
        $this->urlPrepare = $urlPrepare;
    }

    /**
     *
     */
    public function setupCanonicalUrl()
    {
        if ($this->seoData->isIgnoredActions()
            && !$this->seoData->cancelIgnoredActions()) {
                return false;
        }
        if ($canonicalUrl = $this->getCanonicalUrl()) {
            $this->addLinkCanonical($canonicalUrl);
        }
    }

    /**
     * @return $this|mixed|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity) 
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCanonicalUrl()
    {
        if (!$this->config->isAddCanonicalUrl() || $this->isIgnoredCanonical()) {
            return false;
        }

        $productActions = [
            'catalog_product_view',
            'review_product_list',
            'review_product_view',
            'productquestions_show_index',
        ];

        $productCanonicalStoreId = false;
        $useCrossDomain = true;

        if (in_array($this->fullAction, $productActions)) {
            $associatedProductId = false;
            $product = $this->registry->registry('current_product');
            if (!$product) {
                return;
            }

            $associatedProductId = $this->getAssociatedProductId($product);
            $productId = ($associatedProductId) ? $associatedProductId : $product->getId();

            $productCanonicalStoreId = $product->getSeoCanonicalStoreId(); //canonical store id for current product
            $canonicalUrlForCurrentProduct = trim($product->getSeoCanonicalUrl());

            $collection = $this->productCollectionFactory->create()
                ->addFieldToFilter('entity_id', $productId)
                ->addStoreFilter()
                ->addUrlRewrite();

            $product = $collection->getFirstItem();
            $canonicalUrl = $product->getProductUrl();

            if ($this->config->isAddLongestCanonicalProductUrl()
                && $this->config->isProductLongUrlEnabled($this->storeManager->getStore()->getId())) {
                    $canonicalUrl = $this->getLongestProductUrl($product, $canonicalUrl);
            }

            if ($canonicalUrlForCurrentProduct) {
                if (strpos($canonicalUrlForCurrentProduct, 'http://') !== false
                    || strpos($canonicalUrlForCurrentProduct, 'https://') !== false) {
                    $canonicalUrl = $canonicalUrlForCurrentProduct;
                    $useCrossDomain = false;
                } else {
                    $canonicalUrlForCurrentProduct = (substr(
                        $canonicalUrlForCurrentProduct,
                        0,
                        1
                    ) == '/') ? substr($canonicalUrlForCurrentProduct, 1) : $canonicalUrlForCurrentProduct;
                    $canonicalUrl = $this->context->getUrlBuilder()->getBaseUrl().$canonicalUrlForCurrentProduct;
                }
            }
        } elseif ($this->fullAction == 'catalog_category_view') {
            $category = $this->registry->registry('current_category');
            if (!$category) {
                return;
            }
            $canonicalUrl = $category->getUrl();
        } else {
            $canonicalUrl = $this->seoData->getBaseUri();
            $canonicalUrl = $this->context->getUrlBuilder()->getUrl('', ['_direct' => ltrim($canonicalUrl, '/')]);
            $canonicalUrl = strtok($canonicalUrl, '?');
        }

        //setup crossdomian URL if this option is enabled
        if ((($crossDomainStore = $this->config->getCrossDomainStore($this->storeManager->getStore()->getId()))
                || $productCanonicalStoreId)
            && $useCrossDomain) {
            if ($productCanonicalStoreId) {
                $crossDomainStore = $productCanonicalStoreId;
            }
            $mainBaseUrl = $this->storeManager->getStore($crossDomainStore)->getBaseUrl();
            $currentBaseUrl = $this->storeManager->getStore()->getBaseUrl();
            $canonicalUrl = str_replace($currentBaseUrl, $mainBaseUrl, $canonicalUrl);

            if ($this->storeManager->getStore()->isCurrentlySecure()) {
                $canonicalUrl = str_replace('http://', 'https://', $canonicalUrl);
            }
        }

        $canonicalUrl = $this->urlPrepare->deleteDoubleSlash($canonicalUrl);

        $page = (int) $this->request->getParam('p');
        if ($page > 1 && $this->config->isPaginatedCanonical()) {
            $canonicalUrl .= "?p=$page";
        }

        return $canonicalUrl;
    }

    /**
     * Get longest product url
     *
     * @param object $product
     * @param string $canonicalUrl
     * @return string
     */
    protected function getLongestProductUrl($product, $canonicalUrl)
    {
        $rewriteData = $this->urlRewrite->addFieldToFilter('entity_type', 'product')
                            ->addFieldToFilter('redirect_type', 0)
                            ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId())
                            ->addFieldToFilter('entity_id', $product->getId());

        if ($rewriteData && $rewriteData->getSize() > 1 ) {
            $urlPath = [];
            foreach ($rewriteData as $rewrite) {
                $requestPath = $rewrite->getRequestPath();
                $requestPathExploded = explode('/', $requestPath);
                $categoryCount = count($requestPathExploded);
                $urlPath[$categoryCount] = $requestPath;
            }

            if ($urlPath) {
                $canonicalUrl = $this->storeManager->getStore()->getBaseUrl() . $urlPath[max(array_keys($urlPath))];
            }
        }

        return $canonicalUrl;
    }

    /**
     * Get associated product Id
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool|int
     */
    protected function getAssociatedProductId($product)
    {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            return false;
        }

        $associatedProductId = false;

        if ($this->config->getAssociatedCanonicalConfigurableProduct()
            && ($parentConfigurableProductIds = $this
                    ->productTypeConfigurable
                    ->getParentIdsByChild($product->getId())
                )
            && isset($parentConfigurableProductIds[0])) {
                $associatedProductId = $parentConfigurableProductIds[0];
        }

        if (!$associatedProductId && $this->config->getAssociatedCanonicalGroupedProduct()
            && ($parentGroupedProductIds = $this
                    ->productTypeGrouped
                    ->getParentIdsByChild($product->getId())
                )
            && isset($parentGroupedProductIds[0])) {
                $associatedProductId = $parentGroupedProductIds[0];
        }

        if (!$associatedProductId && $this->config->getAssociatedCanonicalBundleProduct()
                && ($parentBundleProductIds = $this
                        ->productTypeBundle
                        ->getParentIdsByChild($product->getId())
                    )
                && isset($parentBundleProductIds[0])) {
                    $associatedProductId = $parentBundleProductIds[0];
        }

        return  $associatedProductId;
    }

    /**
     * Check if canonical is ignored.
     *
     * @return bool
     */
    public function isIgnoredCanonical()
    {
        foreach ($this->config->getCanonicalUrlIgnorePages() as $page) {
            if ($this->seoData->checkPattern($this->fullAction, $page)
                || $this->seoData->checkPattern($this->seoData->getBaseUri(), $page)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create canonical.
     *
     * @param str $canonicalUrl
     *
     * @return void
     */
    public function addLinkCanonical($canonicalUrl)
    {
        $pageConfig = $this->context->getPageConfig();
        $type = 'canonical';
        $pageConfig->addRemotePageAsset(
            html_entity_decode($canonicalUrl),
            $type,
            ['attributes' => ['rel' => $type]]
        );
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
        $this->setupCanonicalUrl();
    }
}
