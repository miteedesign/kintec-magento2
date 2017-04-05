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
 * @version   1.0.51
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Seo\Model\Config as Config;

class Opengraph implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $catalogImage;

    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $seoData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var \Mirasvit\Seo\Api\Config\BlogMxInterface
     */
    protected $blogMx;

    /**
     * @param \Magento\Catalog\Model\ProductFactory      $productFactory
     * @param \Mirasvit\Seo\Model\Config                 $config
     * @param \Magento\Catalog\Helper\Image              $catalogImage
     * @param \Mirasvit\Seo\Helper\Data                  $seoData
     * @param \Magento\Framework\UrlInterface            $urlManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Backend\Model\Auth                $auth
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Mirasvit\Seo\Helper\Data $seoData,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Auth $auth,
        \Mirasvit\Seo\Api\Config\BlogMxInterface $blogMx
    ) {
        $this->productFactory = $productFactory;
        $this->config = $config;
        $this->catalogImage = $catalogImage;
        $this->seoData = $seoData;
        $this->urlManager = $urlManager;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->auth = $auth;
        $this->blogMx = $blogMx;
    }

    /**
     * @param \Magento\Framework\Event\Observer $e
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *  @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modifyHtmlResponse($e)
    {
        $tags = [];

        if ((!$this->config->getCategoryOpenGraph() && !$this->config->isCmsOpenGraphEnabled())
            || $this->seoData->isIgnoredActions()
            || $this->seoData->isIgnoredUrls()
            || $this->registry->registry('current_product')
            || $this->auth->getUser()
            || !is_object($e)) {
                return;
        }

        $response = $e->getResponse();

        $body = $response->getBody();

        if (!$this->hasDoctype(trim($body))) {
            return;
        }

        $label = '<!-- mirasvit open graph begin -->';
        if (strpos($body, $label) !== false) {
            return;
        }

        $fullActionCode = $this->seoData->getFullActionCode();
        if (($this->config->getCategoryOpenGraph() && $fullActionCode == 'catalog_category_view')
            || ($this->config->isCmsOpenGraphEnabled() && $fullActionCode == 'cms_page_view')
            || ($this->blogMx->isOgEnabled() && in_array($fullActionCode, $this->blogMx->getActions())) ) {
            $tags[] = $label;
            $tags[] = $this->createMetaTag('type', 'website');
            $tags[] = $this->createMetaTag('url', $this->urlManager->getCurrentUrl());
            preg_match('/<title>(.*?)<\\/title>/i', $body, $titleArray);
            if (isset($titleArray[1])) {
                $tags[] = $this->createMetaTag('title', $titleArray[1]);
            }
            if ($logo = $this->seoData->getLogoUrl()) {
                $tags['image'] = $this->createMetaTag('image', $logo);
            }
            if ($fullActionCode == 'catalog_category_view'
                && ($productCollection = $this->registry->registry('category_product_for_snippets'))
                && $this->config->getCategoryOpenGraph() == Config::OPENGRAPH_PRODUCT_IMAGE) {
                if ($productCollection->count()) {
                    $tags['image'] = $this->createMetaTag(
                        'image',
                        $this->catalogImage->init($productCollection->getFirstItem(), 'product_small_image')->getUrl()
                    );
                }
            }
            if ($this->storeManager->getStore()->getName() != 'Default Store View') {
                $tags[] = $this->createMetaTag('site_name', $this->storeManager->getStore()->getName());
            }
            preg_match('/meta name\\=\\"description\\" content\\=\\"(.*?)\\"\\/\\>/i', $body, $descriptionArray);
            if (isset($descriptionArray[1])) {
                $tags[] = $this->createMetaTag('description', $descriptionArray[1]);
            }
        }

        if ($tags) {
            $tags[] = '<!-- mirasvit open graph end -->';
            $tags = array_unique($tags);
            $search = [
                '<head>',
                '<head >',
            ];
            $replace = [
                "<head>\n".implode($tags, "\n"),
                "<head >\n".implode($tags, "\n"),
            ];
            $body = str_replace($search, $replace, $body);
        }

        $response->setBody($body);
    }

    /**
     * @param string $property
     * @param string $value
     * @return string
     */
    protected function createMetaTag($property, $value)
    {
        $value = $this->seoData->cleanMetaTag($value);

        return "<meta property=\"og:$property\" content=\"$value\"/>";
    }

    /**
     * @param string $body
     * @return bool
     */
    protected function hasDoctype($body)
    {
        $doctypeCode = ['<!doctype html', '<html', '<?xml'];
        foreach ($doctypeCode as $doctype) {
            if (stripos($body, $doctype) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->modifyHtmlResponse($observer);
    }
}
