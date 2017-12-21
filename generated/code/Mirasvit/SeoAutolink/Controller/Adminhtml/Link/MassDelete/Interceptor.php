<?php
namespace Mirasvit\SeoAutolink\Controller\Adminhtml\Link\MassDelete;

/**
 * Interceptor class for @see \Mirasvit\SeoAutolink\Controller\Adminhtml\Link\MassDelete
 */
class Interceptor extends \Mirasvit\SeoAutolink\Controller\Adminhtml\Link\MassDelete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\SeoAutolink\Model\LinkFactory $linkFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Framework\Registry $registry, \Magento\Backend\App\Action\Context $context, \Magento\Ui\Component\MassAction\Filter $filter, \Mirasvit\SeoAutolink\Model\ResourceModel\Link\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($linkFactory, $localeDate, $registry, $context, $filter, $collectionFactory);
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
