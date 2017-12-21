<?php
namespace Magestore\Storepickup\Controller\Index\View;

/**
 * Interceptor class for @see \Magestore\Storepickup\Controller\Index\View
 */
class Interceptor extends \Magestore\Storepickup\Controller\Index\View implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magestore\Storepickup\Model\SystemConfig $systemConfig, \Magestore\Storepickup\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->___init();
        parent::__construct($context, $systemConfig, $storeCollectionFactory, $coreRegistry, $jsonHelper);
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
