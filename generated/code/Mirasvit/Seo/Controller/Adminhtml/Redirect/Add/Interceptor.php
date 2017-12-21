<?php
namespace Mirasvit\Seo\Controller\Adminhtml\Redirect\Add;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\Redirect\Add
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\Redirect\Add implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\Seo\Model\RedirectFactory $redirectFactory, \Magento\Framework\Registry $registry, \Magento\Backend\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($redirectFactory, $registry, $context);
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
