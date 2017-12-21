<?php
namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile\NewConditionHtml;

/**
 * Interceptor class for @see \Unirgy\RapidFlow\Controller\Adminhtml\Profile\NewConditionHtml
 */
class Interceptor extends \Unirgy\RapidFlow\Controller\Adminhtml\Profile\NewConditionHtml implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\RapidFlow\Model\Profile $profile, \Magento\Catalog\Helper\Data $helper, \Unirgy\RapidFlow\Model\ResourceModel\Profile $resource, \Unirgy\RapidFlow\Model\Rule $rule)
    {
        $this->___init();
        parent::__construct($context, $profile, $helper, $resource, $rule);
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
