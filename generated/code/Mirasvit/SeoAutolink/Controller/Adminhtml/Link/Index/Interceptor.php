<?php
namespace Mirasvit\SeoAutolink\Controller\Adminhtml\Link\Index;

/**
 * Interceptor class for @see \Mirasvit\SeoAutolink\Controller\Adminhtml\Link\Index
 */
class Interceptor extends \Mirasvit\SeoAutolink\Controller\Adminhtml\Link\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\SeoAutolink\Model\LinkFactory $linkFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Framework\Registry $registry, \Magento\Backend\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($linkFactory, $localeDate, $registry, $context);
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
