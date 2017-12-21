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
 * @version   2.0.11
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Plugin\Snippet;

use Mirasvit\Seo\Model\Config as Config;

class Breadcrumbs
{
    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Current template name.
     *
     * @var string
     */
    protected $template = 'Mirasvit_Seo::snippets/breadcrumb/breadcrumbs.phtml';

    /**
     * @param \Mirasvit\Seo\Model\Config                       $config
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager                $moduleManager
     */
    public function __construct(
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->config = $config;
        $this->context = $context;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param Magento\Theme\Block\Html\Breadcrumbs $subject
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeToHtml($subject)
    {
        $breadcrumbs = $this->config->getBreadcrumbs($this->context->getStoreManager()->getStore()->getId());

        if (!$breadcrumbs) {
            return false;
        }

        $isVesEnabled = $this->moduleManager->isEnabled('Ves_All');
        $isSmEnabled = $this->moduleManager->isEnabled('Sm_Himarket');

        switch ($breadcrumbs) {
            case Config::BREADCRUMB:
                if ($isVesEnabled) {
                     $this->template = 'Mirasvit_Seo::snippets/breadcrumb/breadcrumbsves.phtml';
                }
                if ($isSmEnabled) {
                     $this->template = 'Mirasvit_Seo::snippets/breadcrumb/breadcrumbssm.phtml';
                }
                break;
            case Config::BREADCRUMB_LIST:
                $this->template = 'Mirasvit_Seo::snippets/breadcrumb/breadcrumbslist.phtml';
                if ($isVesEnabled) {
                     $this->template = 'Mirasvit_Seo::snippets/breadcrumb/breadcrumbsveslist.phtml';
                }
                break;
        }

        $subject->setTemplate($this->template);

        return false;
    }
}
