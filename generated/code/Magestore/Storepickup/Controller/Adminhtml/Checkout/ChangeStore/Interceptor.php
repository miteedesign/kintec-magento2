<?php
namespace Magestore\Storepickup\Controller\Adminhtml\Checkout\ChangeStore;

/**
 * Interceptor class for @see \Magestore\Storepickup\Controller\Adminhtml\Checkout\ChangeStore
 */
class Interceptor extends \Magestore\Storepickup\Controller\Adminhtml\Checkout\ChangeStore implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Backend\Model\Session $backendSession)
    {
        $this->___init();
        parent::__construct($context, $resultJsonFactory, $backendSession);
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
