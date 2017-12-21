<?php
namespace Mirasvit\Seo\Block\Html\Pager;

/**
 * Interceptor class for @see \Mirasvit\Seo\Block\Html\Pager
 */
class Interceptor extends \Mirasvit\Seo\Block\Html\Pager implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mirasvit\Seo\Helper\Data $seoData, \Magento\Framework\Module\Manager $moduleManager, \Magento\Framework\Registry $registry, \Magento\Framework\View\Element\Template\Context $context, array $data = array())
    {
        $this->___init();
        parent::__construct($seoData, $moduleManager, $registry, $context, $data);
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
