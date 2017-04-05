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



namespace Mirasvit\SeoSitemap\Service\Data\BlogMx;

class Sitemap implements \Mirasvit\SeoSitemap\Api\Data\BlogMx\SitemapInterface
{
    /**
     * @var \Mirasvit\Blog\Block\Sidebar\CategoryTree
     */
    protected $categoryTree;

    /**
     * @var string
     */
    protected $baseRoute;

    /**
     * @var \Mirasvit\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * @param \Mirasvit\Blog\Block\Sidebar\CategoryTree $categoryTree
     * @param \Mirasvit\Blog\Model\Config $config
     * @param \Mirasvit\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     */
    public function __construct(
        \Mirasvit\Blog\Block\Sidebar\CategoryTree $categoryTree,
        \Mirasvit\Blog\Model\Config $config,
        \Mirasvit\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
    ) {
        $this->categoryTree = $categoryTree->getTree();
        $this->postCollectionFactory = $postCollectionFactory;
        $this->baseRoute = $config->getBaseRoute();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getBlogItem()
    {
        $baseUrlCollection = new \Magento\Framework\DataObject(
            [
                'url' => $this->baseRoute,
            ]
        );

        $sitemapItem = new \Magento\Framework\DataObject(
            [
                'changefreq' => self::CHANGEFREQ,
                'priority' => self::PRIORITY,
                'collection' => ['Homepage' => $baseUrlCollection],
            ]
        );

        return $sitemapItem;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getCategoryItems()
    {
        foreach ($this->categoryTree as $category) {
            $categoryCollection[] = new \Magento\Framework\DataObject(
                [
                    'name' => $category->getName(),
                    'url' => $this->baseRoute . '/' . $category->getUrlKey(),
                ]
            );
        }

        $sitemapItem = new \Magento\Framework\DataObject(
            [
                'changefreq' => self::CHANGEFREQ,
                'priority' => self::PRIORITY,
                'collection' => $categoryCollection,
            ]
        );

        return $sitemapItem;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getPostItems()
    {
        $postCollectionFactory = $this->postCollectionFactory->create()
            ->addAttributeToSelect(['name', 'url_key'])
            ->addVisibilityFilter();


        foreach ($postCollectionFactory as $post) {
            $postCollection[] = new \Magento\Framework\DataObject(
                [
                    'name' => $post->getName(),
                    'url' => $this->baseRoute . '/' . $post->getUrlKey(),
                ]
            );
        }

        $sitemapItem = new \Magento\Framework\DataObject(
            [
                'changefreq' => self::CHANGEFREQ,
                'priority' => self::PRIORITY,
                'collection' => $postCollection,
            ]
        );

        return $sitemapItem;
    }
}
