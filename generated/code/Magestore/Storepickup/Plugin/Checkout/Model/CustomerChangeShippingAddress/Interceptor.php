<?php
namespace Magestore\Storepickup\Plugin\Checkout\Model\CustomerChangeShippingAddress;

/**
 * Interceptor class for @see \Magestore\Storepickup\Plugin\Checkout\Model\CustomerChangeShippingAddress
 */
class Interceptor extends \Magestore\Storepickup\Plugin\Checkout\Model\CustomerChangeShippingAddress implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Checkout\Model\Session $checkoutSession, \Magestore\Storepickup\Model\StoreFactory $storeCollection, \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement, \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory, \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\Quote\Model\QuoteAddressValidator $addressValidator, \Psr\Log\LoggerInterface $logger, \Magento\Customer\Api\AddressRepositoryInterface $addressRepository, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector)
    {
        $this->___init();
        parent::__construct($checkoutSession, $storeCollection, $paymentMethodManagement, $paymentDetailsFactory, $cartTotalsRepository, $quoteRepository, $addressValidator, $logger, $addressRepository, $scopeConfig, $totalsCollector);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAddressInformation($cartId, \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'saveAddressInformation');
        if (!$pluginInfo) {
            return parent::saveAddressInformation($cartId, $addressInformation);
        } else {
            return $this->___callPlugins('saveAddressInformation', func_get_args(), $pluginInfo);
        }
    }
}
