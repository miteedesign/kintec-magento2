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



namespace Mirasvit\Seo\Service\Data\BlogMx;

class Post implements \Mirasvit\Seo\Api\Data\BlogMx\PostInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Mirasvit\Blog\Model\Post
     */
    protected $post;

    /**
     * @var \Mirasvit\Blog\Model\Config
     */
    protected $config;

    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $seoData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Mirasvit\Seo\Helper\BlogMx
     */
    protected $blogMx;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Mirasvit\Blog\Model\Post $post
     * @param \Mirasvit\Blog\Model\Config $config
     * @param \Mirasvit\Seo\Helper\Data $seoData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Seo\Helper\BlogMx $blogMx
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mirasvit\Blog\Block\Post\View $post,
        \Mirasvit\Blog\Model\Config $config,
        \Mirasvit\Seo\Helper\Data $seoData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Seo\Helper\BlogMx $blogMx
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->post = $post->getPost();
        $this->config = $config;
        $this->seoData = $seoData;
        $this->storeManager = $storeManager;
        $this->blogMx = $blogMx;
    }

    /**
     * @return string
     */
    public function getHeadline()
    {
        return $this->post ? $this->post->getName() : $this->config->getBlogName();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->post->getContent();
    }

    /**
     * @return string
     */
    public function getPreparedContent()
    {
        $content = $this->getContent();
        $this->blogMx->getPreparedContent($content);

        return $content;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->post->getUrl();
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->post
            ? ($this->post->getMetaKeywords() ? $this->post->getMetaKeywords() : $this->post->getName())
            : $this->config->getBaseMetaKeywords();
    }

    /**
     * @return string
     */
    public function getDatePublished()
    {
        return $this->blogMx->getDatePublished($this->post->getCreatedAt());
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->post->getAuthor()->getName();
    }

    /**
     * @return string
     */
    public function getPublisherName()
    {
        return $this->blogMx->getPublisherName();
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->blogMx->getLogoUrl();
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $this->getContent(), $image);
        if (isset($image[0][0]) && $image[0][0]) {
            $imageUrl = str_replace(['src="', 'src=\''], '', $image[0][0]);
        } else {
            $imageUrl = $this->getLogoUrl();
        }

        return $imageUrl;
    }

    /**
     * @return int
     */
    public function getImageWith()
    {
        return $this->blogMx->getImageWith($this->getImageUrl());
    }

    /**
     * @return int
     */
    public function getImageHeight()
    {
        return $this->blogMx->getImageHeight($this->getImageUrl());
    }
}
