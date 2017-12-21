<?php
namespace Mirasvit\Seo\Controller\Adminhtml\Template\MassDelete;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\Template\MassDelete
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\Template\MassDelete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\Seo\Api\Service\CompatibilityServiceInterface $compatibilityService, \Mirasvit\Seo\Model\TemplateFactory $templateFactory, \Magento\Framework\Registry $registry, \Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Mirasvit\Seo\Model\ResourceModel\Template\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($compatibilityService, $templateFactory, $registry, $context, $filter, $collectionFactory);
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
