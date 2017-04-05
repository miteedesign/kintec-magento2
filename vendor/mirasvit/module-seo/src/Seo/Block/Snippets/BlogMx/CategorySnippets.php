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



namespace Mirasvit\Seo\Block\Snippets\BlogMx;

class CategorySnippets extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Seo\Api\Data\BlogMx\CategoryInterface
     */
    protected $blogCategory;

    /**
     * @var \Mirasvit\Seo\Api\Config\BlogMxInterface
     */
    protected $blogMxConfig;

    /**
     * @param \Mirasvit\Seo\Api\Data\BlogMx\CategoryInterface $blogCategory
     * @param \Magento\Framework\View\Element\Template\Contex $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Seo\Api\Data\BlogMx\CategoryInterface $blogCategory,
        \Mirasvit\Seo\Api\Config\BlogMxInterface $blogMxConfig,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->blogCategory = $blogCategory;
        $this->blogMxConfig = $blogMxConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Seo\Api\Data\BlogMx\CategoryInterface
     */
    public function getBlogCategory()
    {
        return $this->blogCategory;
    }

    /**
     * @return \Mirasvit\Seo\Api\Config\BlogMxInterface
     */
    public function isEnabled()
    {
        return $this->blogMxConfig->isEnabled();
    }
}
