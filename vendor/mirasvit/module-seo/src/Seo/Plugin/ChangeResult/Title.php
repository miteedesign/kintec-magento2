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



namespace Mirasvit\Seo\Plugin\ChangeResult;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\ResultInterface;

class Title
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
     * @var ResponseHttp
     */
    protected $response;

    /**
     * @param ResponseHttp $response
     * @param \Mirasvit\Seo\Helper\Data $seoData
     * @param \Mirasvit\Seo\Helper\UpdateBody $updateBody
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        ResponseHttp $response,
        \Mirasvit\Seo\Helper\Data $seoData,
        \Mirasvit\Seo\Helper\UpdateBody $updateBody,
        \Magento\Framework\Registry $registry
    ) {
        $this->response = $response;
        $this->seoData = $seoData;
        $this->updateBody = $updateBody;
        $this->registry = $registry;
    }

    /**
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterRenderResult(
        ResultInterface $subject,
        ResultInterface $result) {
            if (!($subject instanceof Json)) {
                if (!$this->registry->registry('current_product')
                    || $this->seoData->isIgnoredActions()) {
                        return $result;
                }

                $seo = $this->seoData->getCurrentSeo();

                if (!$seo || !trim($seo->getTitle()) || !is_object($this->response)) {
                    return $result;
                }

                $body = $this->response->getBody();

                if (!$this->updateBody->hasDoctype($body)) {
                    return $result;
                }

                $this->updateBody->replaceFirstLevelTitle($body, $seo->getTitle());

                $this->response->setBody($body);
            }

            return $result;
    }

}
