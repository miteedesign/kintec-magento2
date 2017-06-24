<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   1.0.63
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Plugin\Event;

use Magento\Framework\Registry;
use Magento\Framework\App\Response\Http as ResponseHttp;

class BuiltinPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\PageCache\Version
     */
    protected $version;

    /**
     * @var \Magento\Framework\App\PageCache\Kernel
     */
    protected $kernel;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

     /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

     /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Version $version
     * @param \Magento\Framework\App\PageCache\Kernel $kernel
     * @param \Magento\Framework\App\State $state
     * @param Registry $registry
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Version $version,
        \Magento\Framework\App\PageCache\Kernel $kernel,
        \Magento\Framework\App\State $state,
        Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->config = $config;
        $this->version = $version;
        $this->kernel = $kernel;
        $this->state = $state;
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\PageCache\Model\App\FrontController\BuiltinPlugin\Interceptor $subjectPageCache
     * @param callable $proceedPageCache
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\Response\Http
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAroundDispatch(
        \Magento\PageCache\Model\App\FrontController\BuiltinPlugin\Interceptor $subjectPageCache,
        \Closure $proceedPageCache,
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->config->isEnabled() || $this->config->getType() != \Magento\PageCache\Model\Config::BUILT_IN) {
            return $proceed($request);
        }
        $this->version->process();
        $result = $this->kernel->load();
        if ($result === false) {
            $result = $proceed($request);
            if ($result instanceof ResponseHttp) {
                $this->addDebugHeaders($result);
                $this->kernel->process($result);
            }
        } else {
            $this->registry->register('m__is_hit_page_cache_plugin', true, true);
            $this->addDebugHeader($result, 'X-Magento-Cache-Debug', 'HIT', true);
            if ($this->moduleManager->isEnabled('Mirasvit_CacheWarmer')) {
                $this->objectManager->get('Mirasvit\CacheWarmer\Helper\Info')
                                     ->addInfoBlock($result);
            }
        }

        return $result;
    }

    /**
     * Set cache control
     *
     * @param ResponseHttp $result
     * @return ResponseHttp
     */
    protected function addDebugHeaders(ResponseHttp $result)
    {
        $cacheControlHeader = $result->getHeader('Cache-Control');
        if ($cacheControlHeader instanceof \Zend\Http\Header\HeaderInterface) {
            $this->addDebugHeader($result, 'X-Magento-Cache-Control', $cacheControlHeader->getFieldValue());
        }
        $this->addDebugHeader($result, 'X-Magento-Cache-Debug', 'MISS', true);
        return $result;
    }

    /**
     * Add additional header for debug purpose
     *
     * @param ResponseHttp $response
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return void
     */
    protected function addDebugHeader(ResponseHttp $response, $name, $value, $replace = false)
    {
        if ($this->state->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            $response->setHeader($name, $value, $replace);
        }
    }

}