<?php
namespace Unirgy\SimpleLicense\Controller\Adminhtml\License\CheckUpdates;

/**
 * Interceptor class for @see \Unirgy\SimpleLicense\Controller\Adminhtml\License\CheckUpdates
 */
class Interceptor extends \Unirgy\SimpleLicense\Controller\Adminhtml\License\CheckUpdates implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\SimpleLicense\Model\LicenseFactory $licenseFactory)
    {
        $this->___init();
        parent::__construct($context, $licenseFactory);
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
