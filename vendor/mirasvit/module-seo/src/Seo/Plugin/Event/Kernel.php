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



namespace Mirasvit\Seo\Plugin\Event;

/**
 * Builtin cache processor
 */
class Kernel
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Mirasvit\Seo\Observer\Snippet
     */
    protected $seoSnippet;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Mirasvit\Seo\Observer\Opengraph
     */
    protected $opengraph;

    /**
     * @var Mirasvit\Seo\Observer\ContactPageMeta
     */
    protected $contactPageMeta;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Mirasvit\Seo\Observer\Snippet $seoSnippet
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Mirasvit\Seo\Observer\Opengraph $opengraph
     * @param \Mirasvit\Seo\Observer\ContactPageMeta $contactPageMeta
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Mirasvit\Seo\Observer\Snippet $seoSnippet,
        \Magento\Framework\Module\Manager $moduleManager,
        \Mirasvit\Seo\Observer\Opengraph $opengraph,
        \Mirasvit\Seo\Observer\ContactPageMeta $contactPageMeta
    ) {
        $this->objectManager = $objectManager;
        $this->seoSnippet = $seoSnippet;
        $this->moduleManager = $moduleManager;
        $this->opengraph = $opengraph;
        $this->contactPageMeta = $contactPageMeta;
    }


    /**
     * Modify and cache application response
     *
     * @param Magento\Framework\App\PageCache\Kernel\Interceptor $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\Response\Http $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcess($subject, \Closure $proceed, \Magento\Framework\App\Response\Http $response)
    {
        $response = $this->seoSnippet->addProductSnippets(false, $response);
        $response = $this->opengraph->modifyHtmlResponse(false, $response);
        $response = $this->contactPageMeta->modifyHtmlResponseTitle(false, $response);
        $proceed($response);
        if ($this->moduleManager->isEnabled('Mirasvit_CacheWarmer')) {
                $this->objectManager->get('Mirasvit\CacheWarmer\Helper\Info')
                                     ->addInfoBlock($response, false);
        }
    }

}
