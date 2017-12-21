<?php
namespace Dotdigitalgroup\Email\Controller\Email\Getbasket;

/**
 * Interceptor class for @see \Dotdigitalgroup\Email\Controller\Email\Getbasket
 */
class Interceptor extends \Dotdigitalgroup\Email\Controller\Email\Getbasket implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Checkout\Model\SessionFactory $checkoutSessionFactory, \Magento\Quote\Model\QuoteFactory $quoteFactory, \Magento\Customer\Model\SessionFactory $sessionFactory, \Magento\Framework\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($checkoutSessionFactory, $quoteFactory, $sessionFactory, $context);
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
