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
 * @version   1.0.63
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SeoSitemap\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Sitemap  extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var \Mirasvit\SeoSitemap\Api\Config\CmsSitemapConfigInterface
     */
    protected $cmsSitemapConfig;

    /**
     * @var \Mirasvit\SeoSitemap\Api\Config\LinkSitemapConfigInterface
     */
    protected $linkSitemapConfig;

    /**
     * @var \Mirasvit\SeoSitemap\Helper\Data
     */
    protected $seoSitemapData;

     /**
      * @var \Magento\Framework\Module\Manager
      */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Mirasvit\SeoSitemap\Api\Config\CmsSitemapConfigInterface $cmsSitemapConfig
     * @param \Mirasvit\SeoSitemap\Api\Config\LinkSitemapConfigInterface $linkSitemapConfig
     * @param \Mirasvit\SeoSitemap\Helper\Data $seoSitemapData
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mirasvit\SeoSitemap\Api\Config\CmsSitemapConfigInterface $cmsSitemapConfig,
        \Mirasvit\SeoSitemap\Api\Config\LinkSitemapConfigInterface $linkSitemapConfig,
        \Mirasvit\SeoSitemap\Helper\Data $seoSitemapData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->cmsSitemapConfig = $cmsSitemapConfig;
        $this->linkSitemapConfig = $linkSitemapConfig;
        $this->seoSitemapData = $seoSitemapData;
        parent::__construct($context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data
        );
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }


    /**
     * Initialize sitemap items
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        /** @var $helper \Magento\Sitemap\Helper\Data */
        $helper = $this->_sitemapData;
        $storeId = $this->getStoreId();

        $this->_sitemapItems[] = new \Magento\Framework\DataObject(
            [
                'changefreq' => $helper->getCategoryChangefreq($storeId),
                'priority' => $helper->getCategoryPriority($storeId),
                'collection' => $this->_categoryFactory->create()->getCollection($storeId),
            ]
        );

        $this->_sitemapItems[] = new \Magento\Framework\DataObject(
            [
                'changefreq' => $helper->getProductChangefreq($storeId),
                'priority' => $helper->getProductPriority($storeId),
                'collection' => $this->_productFactory->create()->getCollection($storeId),
            ]
        );

        $this->_sitemapItems[] = new \Magento\Framework\DataObject(
            [
                'changefreq' => $helper->getPageChangefreq($storeId),
                'priority' => $helper->getPagePriority($storeId),
                'collection' => $this->getCmsPages($storeId),
            ]
        );

        if ($this->moduleManager->isEnabled('Aheadworks_Blog')) {
            $sitemapHelper = $this->objectManager->get('\Aheadworks\Blog\Helper\Sitemap');
            $this->_sitemapItems[] = $sitemapHelper->getBlogItem($storeId);
            $this->_sitemapItems[] = $sitemapHelper->getCategoryItems($storeId);
            $this->_sitemapItems[] = $sitemapHelper->getPostItems($storeId);
        }

        if ($this->moduleManager->isEnabled('Mirasvit_Blog')) {
            $blogMxSitemap = $this->objectManager->get('\Mirasvit\SeoSitemap\Api\Data\BlogMx\SitemapInterface');
            $this->_sitemapItems[] = $blogMxSitemap->getBlogItem();
            if ($categoryItems = $blogMxSitemap->getCategoryItems()) {
                $this->_sitemapItems[] = $categoryItems;
            }
            if ($postItems = $blogMxSitemap->getPostItems()) {
                $this->_sitemapItems[] = $postItems;
            }
        }

        $this->_tags = [
            self::TYPE_INDEX => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                PHP_EOL .
                '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                PHP_EOL,
                self::CLOSE_TAG_KEY => '</sitemapindex>',
            ],
            self::TYPE_URL => [
                self::OPEN_TAG_KEY => '<?xml version="1.0" encoding="UTF-8"?>' .
                PHP_EOL .
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' .
                ' xmlns:content="http://www.google.com/schemas/sitemap-content/1.0"' .
                ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                PHP_EOL,
                self::CLOSE_TAG_KEY => '</urlset>',
            ],
        ];
    }

    /**
     * Get cms pages
     * @param  int $storeId
     * @return array
     */
    protected function getCmsPages($storeId)
    {
        $ignore = $this->cmsSitemapConfig->getIgnoreCmsPages();
        $links = $this->linkSitemapConfig->getAdditionalLinks();
        $cmsPages = $this->_cmsFactory->create()->getCollection($storeId);
        foreach ($cmsPages as $cmsKey => $cms) {
            if (in_array($cms->getUrl(), $ignore)) {
                unset($cmsPages[$cmsKey]);
            }
            if ($cms->getUrl() == 'home') {
                $cms->setUrl('');
            }
        }

        if ($links) {
            $cmsPages = array_merge($cmsPages, $links);
        }

        return $cmsPages;
    }


    /**
     * Generate XML file
     *
     * @see http://www.sitemaps.org/protocol.html
     *
     * @return $this
     */
    public function generateXml()
    {
        $this->_initSitemapItems();
        $excludedlinks = $this->linkSitemapConfig->getExcludeLinks();
        /** @var $sitemapItem \Magento\Framework\DataObject */
        foreach ($this->_sitemapItems as $sitemapItem) {
            $changefreq = $sitemapItem->getChangefreq();
            $priority = $sitemapItem->getPriority();
            foreach ($sitemapItem->getCollection() as $item) {
                if ($this->seoSitemapData->checkArrayPattern($item->getUrl(), $excludedlinks)) {
                    continue;
                }
                $xml = $this->_getSitemapRow(
                    $item->getUrl(),
                    $item->getUpdatedAt(),
                    $changefreq,
                    $priority,
                    $item->getImages()
                );
                if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                    $this->_finalizeSitemap();
                }
                if (!$this->_fileSize) {
                    $this->_createSitemap();
                }
                $this->_writeSitemapRow($xml);
                // Increase counters
                $this->_lineCount++;
                $this->_fileSize += strlen($xml);
            }
        }
        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $path = rtrim(
                $this->getSitemapPath(),
                '/'
            ) . '/' . $this->_getCurrentSitemapFilename(
                $this->_sitemapIncrement
            );
            $destination = rtrim($this->getSitemapPath(), '/') . '/' . $this->getSitemapFilename();

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }

        // Push sitemap to robots.txt
        if ($this->_isEnabledSubmissionRobots()) {
            $this->_addSitemapToRobotsTxt($this->getSitemapFilename());
        }

        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }
}
