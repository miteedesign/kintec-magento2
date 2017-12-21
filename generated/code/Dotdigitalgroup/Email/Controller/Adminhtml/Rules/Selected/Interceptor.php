<?php
namespace Dotdigitalgroup\Email\Controller\Adminhtml\Rules\Selected;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Controller\Adminhtml\Rules\Selected
 */
class Interceptor extends \Dotdigitalgroup\Email\Controller\Adminhtml\Rules\Selected implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Dotdigitalgroup\Email\Model\RulesFactory $rulesFactory, \Dotdigitalgroup\Email\Model\Adminhtml\Source\Rules\Type $ruleType, \Dotdigitalgroup\Email\Model\Adminhtml\Source\Rules\Condition $ruleCondition, \Dotdigitalgroup\Email\Model\Adminhtml\Source\Rules\Value $ruleValue, \Magento\Framework\Json\Encoder $jsonEncoder, \Magento\Framework\App\Response\Http $http)
    {
        $this->___init();
        parent::__construct($context, $rulesFactory, $ruleType, $ruleCondition, $ruleValue, $jsonEncoder, $http);
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
