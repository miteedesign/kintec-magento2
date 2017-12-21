<?php
namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile\Edit;

/**
 * Interceptor class for @see \Unirgy\RapidFlow\Controller\Adminhtml\Profile\Edit
 */
class Interceptor extends \Unirgy\RapidFlow\Controller\Adminhtml\Profile\Edit implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\RapidFlow\Model\Profile $profile, \Magento\Catalog\Helper\Data $catalogHelper, \Unirgy\RapidFlow\Model\ResourceModel\Profile $resource, \Magento\Framework\Registry $registry, \Magento\Framework\View\LayoutFactory $layoutFactory)
    {
        $this->___init();
        parent::__construct($context, $profile, $catalogHelper, $resource, $registry, $layoutFactory);
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
