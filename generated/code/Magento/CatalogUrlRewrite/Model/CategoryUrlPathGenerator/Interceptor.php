<?php
namespace Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;

/**
 * Interceptor class for @see \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
 */
class Interceptor extends \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository)
    {
        $this->___init();
        parent::__construct($storeManager, $scopeConfig, $categoryRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlPathWithSuffix($category, $storeId = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getUrlPathWithSuffix');
        if (!$pluginInfo) {
            return parent::getUrlPathWithSuffix($category, $storeId);
        } else {
            return $this->___callPlugins('getUrlPathWithSuffix', func_get_args(), $pluginInfo);
        }
    }
}
