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

class ContactPageMeta extends \Magento\Framework\Model\AbstractModel implements ObserverInterface
{
    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $seoData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mirasvit\Seo\Helper\UpdateBody
     */
    protected $updateBody;

    /**
     * @var \Mirasvit\Seo\Observer\Robots
     */
    protected $robots;

    /**
     * @param \Mirasvit\Seo\Helper\Data   $seoData
     * @param \Mirasvit\Seo\Helper\UpdateBody $updateBody,
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Mirasvit\Seo\Helper\Data $seoData,
        \Mirasvit\Seo\Helper\UpdateBody $updateBody,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Seo\Observer\Robots $robots
    ) {
        $this->seoData = $seoData;
        $this->updateBody = $updateBody;
        $this->registry = $registry;
        $this->robots = $robots;
    }

    /**
     * @param string $e
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function modifyHtmlResponseTitle($e)
    {
        if (($this->seoData->getFullActionCode() != 'contact_index_index')
            || $this->seoData->isIgnoredActions()) {
                return;
        }

        $seo = $this->seoData->getCurrentSeo();

        if (!$seo || !is_object($e)) {
            return;
        }

        $response = $e->getResponse();

        $body = $response->getBody();

        if (!$this->updateBody->hasDoctype($body)) {
            return;
        }

        $seoTitle = trim($seo->getTitle());
        $seoMetaTitle = trim($seo->getMetaTitle());
        $seoMetaKeywords = trim($seo->getMetaKeywords());
        $seoMetaDescription = trim($seo->getMetaDescription());
        $robots = $this->robots->getRobots();

        if ($seoTitle) {
            $this->updateBody->replaceFirstLevelTitle($body, $seoTitle);
        }

        if ($seoMetaTitle) {
            $this->updateBody->replaceMetaTitle($body, $seoMetaTitle);
        }

        if ($seoMetaKeywords) {
            $this->updateBody->replaceMetaKeywords($body, $seoMetaKeywords);
        }

        if ($seoMetaDescription) {
            $this->updateBody->replaceMetaDescription($body, $seoMetaDescription);
        }

        if ($robots) {
            $this->updateBody->replaceRobots($body, $robots);
        }

        $response->setBody($body);
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
        $this->modifyHtmlResponseTitle($observer);
    }
}
