<?php
namespace Mirasvit\Seo\Controller\Adminhtml\System\Config\Removecategorypath\Remove;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\System\Config\Removecategorypath\Remove
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\System\Config\Removecategorypath\Remove implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->___init();
        parent::__construct($context, $resultJsonFactory);
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
