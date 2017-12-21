<?php
namespace Unirgy\RapidFlow\Controller\Adminhtml\Doc\Index;

/**
 * Interceptor class for @see \Unirgy\RapidFlow\Controller\Adminhtml\Doc\Index
 */
class Interceptor extends \Unirgy\RapidFlow\Controller\Adminhtml\Doc\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\RapidFlow\Model\Config $rapidFlowModelConfig)
    {
        $this->___init();
        parent::__construct($context, $rapidFlowModelConfig);
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
