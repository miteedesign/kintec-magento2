<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Storepickup\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Cart source
 */
class DirectoryData implements SectionSourceInterface
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Directory\Helper\Data $directoryHelper)
    {
        $this->directoryHelper = $directoryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $output = [];
        $regionsData = $this->directoryHelper->getRegionData();
        /**
         * @var string $code
         * @var \Magento\Directory\Model\Country $data
         */
        foreach ($this->directoryHelper->getCountryCollection() as $code => $data) {
            $output[$code]['name'] = $data->getName();
            if (array_key_exists($code, $regionsData)) {
                foreach ($regionsData[$code] as $key => $region) {
                    $output[$code]['regions'][$key]['code'] = $region['code'];
                    $output[$code]['regions'][$key]['name'] = $region['name'];
                }
            }

        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->get('Magento\Checkout\Model\Session');
        $data = $session->getData('storepickup_session');
		$storeId = isset($data['store_id'])? $data['store_id']:'';
		$store = $objectManager->get('Magestore\Storepickup\Model\Store');
		$store->load($storeId);
        if($store && $store->getId()){
			$output['store_id'] = $storeId;
			$output['address'] = $store->getAddress();
			$output['phone'] = $store->getPhone();
		}
        return $output;
    }
}
