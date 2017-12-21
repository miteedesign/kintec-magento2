<?php
namespace Magento\Framework\App\PageCache\Kernel;

/**
 * Interceptor class for @see \Magento\Framework\App\PageCache\Kernel
 */
class Interceptor extends \Magento\Framework\App\PageCache\Kernel implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\PageCache\Cache $cache, \Magento\Framework\App\PageCache\Identifier $identifier, \Magento\Framework\App\Request\Http $request, \Magento\Framework\App\Http\Context $context = null, \Magento\Framework\App\Http\ContextFactory $contextFactory = null, \Magento\Framework\App\Response\HttpFactory $httpFactory = null, \Magento\Framework\Serialize\SerializerInterface $serializer = null)
    {
        $this->___init();
        parent::__construct($cache, $identifier, $request, $context, $contextFactory, $httpFactory, $serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function process(\Magento\Framework\App\Response\Http $response)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'process');
        if (!$pluginInfo) {
            return parent::process($response);
        } else {
            return $this->___callPlugins('process', func_get_args(), $pluginInfo);
        }
    }
}
