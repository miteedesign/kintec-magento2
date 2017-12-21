<?php
namespace Mirasvit\SeoSitemap\Block\Map\Pager;

/**
 * Interceptor class for @see \Mirasvit\SeoSitemap\Block\Map\Pager
 */
class Interceptor extends \Mirasvit\SeoSitemap\Block\Map\Pager implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = array())
    {
        $this->___init();
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageUrl($page)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getPageUrl');
        if (!$pluginInfo) {
            return parent::getPageUrl($page);
        } else {
            return $this->___callPlugins('getPageUrl', func_get_args(), $pluginInfo);
        }
    }
}
