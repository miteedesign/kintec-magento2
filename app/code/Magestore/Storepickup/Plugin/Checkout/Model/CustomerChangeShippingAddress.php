<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Storepickup
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Storepickup\Plugin\Checkout\Model;

class CustomerChangeShippingAddress extends \Magento\Checkout\Model\ShippingInformationManagement{

    
    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     */
    protected $_checkoutSession;

    /**
     * @var \Magestore\Storepickup\Model\StoreFactory
     */
    protected $_storeCollection;    

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magestore\Storepickup\Model\StoreFactory $storeCollection,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteAddressValidator $addressValidator,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_storeCollection = $storeCollection;     
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->quoteRepository = $quoteRepository;
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->scopeConfig = $scopeConfig;
        $this->totalsCollector = $totalsCollector;
    }

    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');*/
        if($addressInformation->getShippingMethodCode()=="storepickup" /*&& $customerSession->isLoggedIn()*/){
            $storepickup_session = $this->_checkoutSession->getData('storepickup_session');
            $datashipping = [];
            $storeId = $storepickup_session['store_id'];
            $storeId = is_null($storeId)?1:$storeId;
            
            $collectionstore = $this->_storeCollection->create();
            $store = $collectionstore->load($storeId, 'storepickup_id');
            $datashipping['firstname'] = (string)__('Store');
            $datashipping['lastname'] = $store->getData('store_name');
            $datashipping['street'][0] = $store->getData('address');
            $datashipping['city'] = $store->getCity();
            $datashipping['region'] = $store->getState();
            $datashipping['postcode'] = $store->getData('zipcode');
            $datashipping['country_id'] = $store->getData('country_id');
            $datashipping['company'] = '';
            if ($store->getFax()) {
                $datashipping['fax'] = $store->getFax();
            } else {
                unset($datashipping['fax']);
            }

            if ($store->getPhone()) {
                $datashipping['telephone'] = $store->getPhone();
            } else {
                unset($datashipping['telephone']);
            }
            $datashipping['shipping_method'] = 'storepickup_storepickup';
			$datashipping['save_in_address_book'] = 0;
            $addressInformation->getShippingAddress()->addData($datashipping);
            return  $proceed($cartId,$addressInformation);
        }else
            return  $proceed($cartId,$addressInformation);
    }

}
