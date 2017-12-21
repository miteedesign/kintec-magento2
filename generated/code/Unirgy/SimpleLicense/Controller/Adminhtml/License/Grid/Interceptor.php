<?php
namespace Unirgy\SimpleLicense\Controller\Adminhtml\License\Grid;

/**
 * Interceptor class for @see \Unirgy\SimpleLicense\Controller\Adminhtml\License\Grid
 */
class Interceptor extends \Unirgy\SimpleLicense\Controller\Adminhtml\License\Grid implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\View\LayoutFactory $frameworkViewLayoutFactory)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $frameworkViewLayoutFactory);
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
