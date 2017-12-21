<?php
namespace Unirgy\SimpleUp\Controller\Adminhtml\Module\CheckUpdates;

/**
 * Interceptor class for @see \Unirgy\SimpleUp\Controller\Adminhtml\Module\CheckUpdates
 */
class Interceptor extends \Unirgy\SimpleUp\Controller\Adminhtml\Module\CheckUpdates implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory, \Unirgy\SimpleUp\Helper\Data $simpleUpHelper)
    {
        $this->___init();
        parent::__construct($context, $pageFactory, $simpleUpHelper);
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
