<?php
namespace Fooman\PdfCore\Model\Response\Http\Response;

/**
 * Interceptor class for @see \Fooman\PdfCore\Model\Response\Http\Response
 */
class Interceptor extends \Fooman\PdfCore\Model\Response\Http\Response implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Request\Http $request, \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory, \Magento\Framework\App\Http\Context $context, \Magento\Framework\Stdlib\DateTime $dateTime, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->___init();
        parent::__construct($request, $cookieManager, $cookieMetadataFactory, $context, $dateTime, $scopeConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function sendResponse()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'sendResponse');
        if (!$pluginInfo) {
            return parent::sendResponse();
        } else {
            return $this->___callPlugins('sendResponse', func_get_args(), $pluginInfo);
        }
    }
}
