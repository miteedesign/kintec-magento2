<?php
namespace Magento\Cms\Block\Page;

/**
 * Interceptor class for @see \Magento\Cms\Block\Page
 */
class Interceptor extends \Magento\Cms\Block\Page implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Context $context, \Magento\Cms\Model\Page $page, \Magento\Cms\Model\Template\FilterProvider $filterProvider, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Cms\Model\PageFactory $pageFactory, \Magento\Framework\View\Page\Config $pageConfig, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $page, $filterProvider, $storeManager, $pageFactory, $pageConfig, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getPage');
        if (!$pluginInfo) {
            return parent::getPage();
        } else {
            return $this->___callPlugins('getPage', func_get_args(), $pluginInfo);
        }
    }
}
