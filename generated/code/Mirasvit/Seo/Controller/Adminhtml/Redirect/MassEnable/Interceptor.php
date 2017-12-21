<?php
namespace Mirasvit\Seo\Controller\Adminhtml\Redirect\MassEnable;

/**
 * Interceptor class for @see \Mirasvit\Seo\Controller\Adminhtml\Redirect\MassEnable
 */
class Interceptor extends \Mirasvit\Seo\Controller\Adminhtml\Redirect\MassEnable implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\Seo\Model\RedirectFactory $redirectFactory, \Magento\Framework\Registry $registry, \Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Mirasvit\Seo\Model\ResourceModel\Rewrite\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($redirectFactory, $registry, $context, $filter, $collectionFactory);
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
