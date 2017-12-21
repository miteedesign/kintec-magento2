<?php
namespace Magestore\Storepickup\Controller\Checkout\DisableDate;

/**
 * Interceptor class for @see \Magestore\Storepickup\Controller\Checkout\DisableDate
 */
class Interceptor extends \Magestore\Storepickup\Controller\Checkout\DisableDate implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magestore\Storepickup\Model\StoreFactory $storeCollection, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $gmtdate, \Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->___init();
        parent::__construct($context, $resultJsonFactory, $storeCollection, $gmtdate, $checkoutSession);
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
