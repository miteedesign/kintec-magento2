<?php
namespace Unirgy\SimpleLicense\Controller\Adminhtml\License\MassRemove;

/**
 * Interceptor class for @see \Unirgy\SimpleLicense\Controller\Adminhtml\License\MassRemove
 */
class Interceptor extends \Unirgy\SimpleLicense\Controller\Adminhtml\License\MassRemove implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\SimpleLicense\Model\LicenseFactory $licenseFactory, \Magento\Framework\Controller\Result\RedirectFactory $controllerResultRedirectFactory)
    {
        $this->___init();
        parent::__construct($context, $licenseFactory, $controllerResultRedirectFactory);
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
