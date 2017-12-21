<?php
namespace Mirasvit\Seo\Controller\Adminhtml\CanonicalRewrite\Index;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\CanonicalRewrite\Index
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\CanonicalRewrite\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\Seo\Api\Service\CompatibilityServiceInterface $compatibilityService, \Magento\Framework\Registry $registry, \Mirasvit\Seo\Api\Repository\CanonicalRewriteRepositoryInterface $canonicalRewriteRepository, \Magento\Backend\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($compatibilityService, $registry, $canonicalRewriteRepository, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
