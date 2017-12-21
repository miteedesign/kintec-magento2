<?php
namespace Magestore\Storepickup\Plugin\Checkout\Model\ChangeShippingAddress;

/**
 * Interceptor class for @see \Magestore\Storepickup\Plugin\Checkout\Model\ChangeShippingAddress
 */
class Interceptor extends \Magestore\Storepickup\Plugin\Checkout\Model\ChangeShippingAddress implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Checkout\Model\Session $checkoutSession, \Magestore\Storepickup\Model\StoreFactory $storeCollection, \Magento\Sales\Api\Data\OrderAddressInterface $orderAddressInterface, \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory, \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement)
    {
        $this->___init();
        parent::__construct($checkoutSession, $storeCollection, $orderAddressInterface, $quoteIdMaskFactory, $shippingInformationManagement);
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
