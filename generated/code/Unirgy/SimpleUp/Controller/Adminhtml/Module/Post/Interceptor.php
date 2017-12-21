<?php
namespace Unirgy\SimpleUp\Controller\Adminhtml\Module\Post;

/**
 * Interceptor class for @see \Unirgy\SimpleUp\Controller\Adminhtml\Module\Post
 */
class Interceptor extends \Unirgy\SimpleUp\Controller\Adminhtml\Module\Post implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Unirgy\SimpleUp\Helper\Data $simpleUpHelper, \Magento\Framework\View\Result\PageFactory $pageFactory)
    {
        $this->___init();
        parent::__construct($context, $simpleUpHelper, $pageFactory);
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
